<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'reports';
$adminTitle = 'Reports & Statistics';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Reports &amp; Statistics</h2>
        <p class="text-muted-2 mb-0 small">Visualize performance across shipments, revenue and fleet.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-ghost btn-sm" onclick="exportCSV('shipments')"><i class="bi bi-download me-1"></i> Shipments CSV</button>
        <button class="btn btn-ghost btn-sm" onclick="exportCSV('invoices')"><i class="bi bi-download me-1"></i> Invoices CSV</button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card-admin"><div class="card-header">Revenue trend (12 months)</div>
            <div class="card-body"><canvas id="revChart" height="120"></canvas></div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-admin"><div class="card-header">Volume trend (12 months)</div>
            <div class="card-body"><canvas id="volChart" height="120"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card-admin"><div class="card-header">Shipments by status</div>
            <div class="card-body"><canvas id="statusChart" height="220"></canvas></div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card-admin">
            <div class="card-header">Performance summary</div>
            <div class="card-body">
                <div class="row g-3" id="perfGrid"><div class="col-12 text-center text-muted-2 py-4">Loading…</div></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
var symbol = '<?= e(setting('currency_symbol', '$')) ?>';
var stats = null;

function buildSeries(source, numeric) {
    var now = new Date();
    var labels = [], data = [];
    var map = {};
    source.forEach(function (r) { map[r.month] = numeric ? parseFloat(r.total) : parseInt(r.count); });
    for (var i = 11; i >= 0; i--) {
        var d = new Date(now.getFullYear(), now.getMonth() - i, 1);
        var key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
        labels.push(d.toLocaleString('en', { month: 'short' }));
        data.push(map[key] || 0);
    }
    return { labels: labels, data: data };
}

function lineChart(id, labels, data, color) {
    return new Chart(document.getElementById(id), {
        type: 'line',
        data: { labels: labels, datasets: [{ data: data, borderColor: color, backgroundColor: color + '22', fill: true, tension: .35, pointRadius: 3 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: 'rgba(128,128,128,.1)' } }, x: { grid: { display: false } } } }
    });
}

function render() {
    var rev = buildSeries(stats.monthly_revenue, true);
    var vol = buildSeries(stats.monthly_volume, false);
    lineChart('revChart', rev.labels, rev.data, '#2563eb');
    lineChart('volChart', vol.labels, vol.data, '#06b6d4');

    var palette = { pending:'#94a3b8', picked_up:'#0ea5e9', in_transit:'#2563eb', out_for_delivery:'#f59e0b', delivered:'#10b981', on_hold:'#f97316', customs:'#8b5cf6', cancelled:'#ef4444', returned:'#dc2626' };
    new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
            labels: stats.status_breakdown.map(function (s) { return s.status.replace(/_/g,' '); }),
            datasets: [{ data: stats.status_breakdown.map(function (s) { return parseInt(s.count); }),
                backgroundColor: stats.status_breakdown.map(function (s) { return palette[s.status] || '#64748b'; }), borderRadius: 6 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: 'rgba(128,128,128,.1)' } }, x: { grid: { display: false } } } }
    });

    var k = stats.kpis;
    var total = k.total_shipments || 1;
    var deliveredRate = (k.delivered / total * 100).toFixed(1);
    var cards = [
        ['Delivered', k.delivered, 'bg-soft-green', 'bi-check-circle'],
        ['In transit', k.in_transit, 'bg-soft-cyan', 'bi-truck'],
        ['On hold', k.on_hold, 'bg-soft-orange', 'bi-pause-circle'],
        ['Delivery rate', deliveredRate + '%', 'bg-soft-blue', 'bi-speedometer2'],
        ['Customers', k.customers, 'bg-soft-violet', 'bi-people'],
        ['Active drivers', k.drivers, 'bg-soft-pink', 'bi-person-badge'],
        ['Revenue', symbol + Number(k.revenue).toLocaleString(), 'bg-soft-green', 'bi-cash-stack'],
        ['Outstanding', symbol + Number(k.outstanding).toLocaleString(), 'bg-soft-red', 'bi-receipt'],
    ];
    document.getElementById('perfGrid').innerHTML = cards.map(function (c) {
        return '<div class="col-6 col-md-3"><div class="d-flex align-items-center gap-2">' +
            '<span class="stat-icon ' + c[2] + '" style="width:40px;height:40px;font-size:1.1rem;"><i class="bi ' + c[3] + '"></i></span>' +
            '<div><div class="fw-bold">' + c[1] + '</div><div class="text-muted-2 small">' + c[0] + '</div></div></div></div>';
    }).join('');
}

function exportCSV(type) {
    var url = type === 'shipments' ? '<?= base_url('api/shipments.php') ?>' : '<?= base_url('api/invoices.php') ?>';
    CF.api(url).then(function (json) {
        if (!json.success || !json.data.length) { CF.toast('No data to export', 'warning'); return; }
        var rows = json.data;
        var cols = Object.keys(rows[0]);
        var csv = cols.join(',') + '\n';
        rows.forEach(function (r) {
            csv += cols.map(function (c) { var v = r[c] == null ? '' : String(r[c]).replace(/"/g, '""'); return '"' + v + '"'; }).join(',') + '\n';
        });
        var blob = new Blob([csv], { type: 'text/csv' });
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = type + '-' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
        CF.toast('Export started', 'success');
    });
}

CF.api('<?= base_url('api/stats.php') ?>').then(function (json) {
    if (json.success) { stats = json.data; render(); }
});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
