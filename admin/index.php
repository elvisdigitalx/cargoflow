<?php
/**
 * CargoFlow — Admin dashboard
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'index';
$adminTitle = 'Dashboard';

require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Good to see you, <?= e(explode(' ', current_user()['name'] ?? 'there')[0]) ?> 👋</h2>
        <p class="text-muted-2 mb-0 small">Here's what's happening across your logistics operations today.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/shipments.php?action=new') ?>" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg me-1"></i> New Shipment</a>
        <a href="<?= base_url('admin/reports.php') ?>" class="btn btn-ghost btn-sm"><i class="bi bi-download me-1"></i> Reports</a>
    </div>
</div>

<!-- KPI cards -->
<div class="row g-3 mb-4" id="kpiCards">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card-admin stat-card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" data-kpi="total_shipments">—</div>
                    <div class="stat-label">Total shipments</div>
                </div>
                <div class="stat-icon bg-soft-blue"><i class="bi bi-box-seam"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card-admin stat-card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" data-kpi="in_transit">—</div>
                    <div class="stat-label">In transit</div>
                </div>
                <div class="stat-icon bg-soft-cyan"><i class="bi bi-truck"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card-admin stat-card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" data-kpi="delivered">—</div>
                    <div class="stat-label">Delivered</div>
                </div>
                <div class="stat-icon bg-soft-green"><i class="bi bi-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card-admin stat-card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" data-kpi="on_hold">—</div>
                    <div class="stat-label">On hold</div>
                </div>
                <div class="stat-icon bg-soft-orange"><i class="bi bi-pause-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card-admin stat-card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" data-kpi="customers">—</div>
                    <div class="stat-label">Customers</div>
                </div>
                <div class="stat-icon bg-soft-violet"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card-admin stat-card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" data-kpi="drivers">—</div>
                    <div class="stat-label">Active drivers</div>
                </div>
                <div class="stat-icon bg-soft-pink"><i class="bi bi-person-badge"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card-admin stat-card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value text-success" data-kpi="revenue" data-currency>—</div>
                    <div class="stat-label">Revenue (paid)</div>
                </div>
                <div class="stat-icon bg-soft-green"><i class="bi bi-cash-stack"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card-admin stat-card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value text-danger" data-kpi="outstanding" data-currency>—</div>
                    <div class="stat-label">Outstanding</div>
                </div>
                <div class="stat-icon bg-soft-red"><i class="bi bi-receipt"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card-admin">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Revenue &amp; volume (12 months)</span>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-ghost btn-sm active" id="chartRevenue">Revenue</button>
                    <button type="button" class="btn btn-ghost btn-sm" id="chartVolume">Volume</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="110"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-admin h-100">
            <div class="card-header">Shipment status</div>
            <div class="card-body">
                <canvas id="statusChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card-admin">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Recent shipments</span>
                <a href="<?= base_url('admin/shipments.php') ?>" class="small">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-admin">
                    <thead><tr><th>Tracking</th><th>Customer</th><th>Destination</th><th>Status</th></tr></thead>
                    <tbody id="recentShipments"><tr><td colspan="4" class="text-center text-muted-2 py-4">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-admin">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Recent payments</span>
                <a href="<?= base_url('admin/payments.php') ?>" class="small">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-admin">
                    <thead><tr><th>Customer</th><th>Method</th><th class="text-end">Amount</th></tr></thead>
                    <tbody id="recentPayments"><tr><td colspan="3" class="text-center text-muted-2 py-4">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';
    var currencySymbol = '<?= e(setting('currency_symbol', '$')) ?>';
    var trendChart, statusChart;
    var stats = null;

    function shortMonth(m) { var d = new Date(m + '-01T00:00:00'); return d.toLocaleString('en', { month: 'short' }); }
    function buildLabels(months) {
        var all = [];
        var now = new Date();
        for (var i = 11; i >= 0; i--) {
            var d = new Date(now.getFullYear(), now.getMonth() - i, 1);
            var key = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
            all.push({ key: key, label: d.toLocaleString('en', { month: 'short' }) });
        }
        return all;
    }

    function renderTrend(mode) {
        if (!stats) return;
        var labels = buildLabels();
        var source = mode === 'revenue' ? stats.monthly_revenue : stats.monthly_volume;
        var map = {};
        source.forEach(function (r) { map[r.month] = mode === 'revenue' ? parseFloat(r.total) : parseInt(r.count); });
        var data = labels.map(function (l) { return map[l.key] || 0; });
        var ctx = document.getElementById('trendChart');
        if (trendChart) trendChart.destroy();
        trendChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.map(function (l) { return l.label; }),
                datasets: [{
                    label: mode === 'revenue' ? 'Revenue' : 'Shipments',
                    data: data,
                    backgroundColor: mode === 'revenue' ? 'rgba(37,99,235,.7)' : 'rgba(6,182,212,.7)',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: 'rgba(128,128,128,.1)' } }, x: { grid: { display: false } } }
            }
        });
    }

    function renderStatus() {
        if (!stats) return;
        var labels = [], counts = [], colors = [];
        var palette = {
            pending: '#94a3b8', picked_up: '#0ea5e9', in_transit: '#2563eb',
            out_for_delivery: '#f59e0b', delivered: '#10b981', on_hold: '#f97316',
            customs: '#8b5cf6', cancelled: '#ef4444', returned: '#dc2626'
        };
        stats.status_breakdown.forEach(function (s) {
            labels.push(s.status.replace(/_/g, ' '));
            counts.push(parseInt(s.count));
            colors.push(palette[s.status] || '#64748b');
        });
        var ctx = document.getElementById('statusChart');
        if (statusChart) statusChart.destroy();
        statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: counts, backgroundColor: colors, borderWidth: 2 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } } },
                cutout: '62%'
            }
        });
    }

    function fmtCurrency(v) {
        return currencySymbol + Number(v).toLocaleString(undefined, { maximumFractionDigits: 0 });
    }

    function render() {
        if (!stats) return;
        var kpis = stats.kpis;
        document.querySelectorAll('[data-kpi]').forEach(function (el) {
            var key = el.getAttribute('data-kpi');
            var v = kpis[key] || 0;
            el.textContent = el.hasAttribute('data-currency') ? fmtCurrency(v) : Number(v).toLocaleString();
        });

        var rs = document.getElementById('recentShipments');
        rs.innerHTML = stats.recent_shipments.map(function (s) {
            var badge = { delivered: 'success', in_transit: 'primary', out_for_delivery: 'warning', pending: 'secondary', on_hold: 'warning', customs: 'info', cancelled: 'danger' }[s.status] || 'secondary';
            return '<tr><td><span class="tracking-chip">' + s.tracking_number + '</span></td>' +
                '<td>' + (s.customer_name || '—') + '</td>' +
                '<td>' + (s.destination || '—') + '</td>' +
                '<td><span class="badge bg-' + badge + ' rounded-pill">' + s.status.replace(/_/g, ' ') + '</span></td></tr>';
        }).join('') || '<tr><td colspan="4" class="text-center text-muted-2 py-4">No shipments yet</td></tr>';

        var rp = document.getElementById('recentPayments');
        rp.innerHTML = stats.recent_payments.map(function (p) {
            return '<tr><td>' + (p.customer_name || '—') + '</td>' +
                '<td class="text-capitalize">' + p.method + '</td>' +
                '<td class="text-end fw-semibold">' + fmtCurrency(p.amount) + '</td></tr>';
        }).join('') || '<tr><td colspan="3" class="text-center text-muted-2 py-4">No payments yet</td></tr>';

        renderTrend('revenue');
        renderStatus();
    }

    CF.api('<?= base_url('api/stats.php') ?>').then(function (json) {
        if (json.success) { stats = json.data; render(); }
    });

    document.getElementById('chartRevenue').addEventListener('click', function () {
        this.classList.add('active'); document.getElementById('chartVolume').classList.remove('active');
        renderTrend('revenue');
    });
    document.getElementById('chartVolume').addEventListener('click', function () {
        this.classList.add('active'); document.getElementById('chartRevenue').classList.remove('active');
        renderTrend('volume');
    });
})();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
