<?php
/**
 * CargoFlow — Admin dashboard (interactive)
 * KPI counters, clickable shipment pipeline, charts, live clock and a
 * quick "manual entry" shipment form.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'index';
$adminTitle = 'Dashboard';

$statuses = shipment_statuses();

$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0"><?= e($greeting) ?>, <?= e(explode(' ', current_user()['name'] ?? 'there')[0]) ?> 👋</h2>
        <p class="text-muted-2 mb-0 small d-flex align-items-center gap-2">
            <span class="pulse-dot"></span>
            Live dashboard · <span class="live-clock" id="liveClock"></span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/shipments.php') ?>" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg me-1"></i> New Shipment</a>
        <a href="<?= base_url('admin/customers.php') ?>" class="btn btn-ghost btn-sm"><i class="bi bi-people me-1"></i> Customers</a>
        <a href="<?= base_url('admin/reports.php') ?>" class="btn btn-ghost btn-sm"><i class="bi bi-download me-1"></i> Reports</a>
        <button class="btn btn-ghost btn-sm" id="refreshStats" title="Refresh now"><i class="bi bi-arrow-clockwise"></i></button>
    </div>
</div>

<!-- ============ KPI CARDS ============ -->
<div class="row g-3 mb-4" id="kpiCards">
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card-admin stat-card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-value" data-kpi="total_shipments">—</div>
                    <div class="stat-label">Total shipments</div>
                </div>
                <div class="stat-icon bg-grad-red"><i class="bi bi-box-seam"></i></div>
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
                <div class="stat-icon bg-grad-orange"><i class="bi bi-truck"></i></div>
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
                <div class="stat-icon bg-grad-green"><i class="bi bi-check-circle"></i></div>
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
                <div class="stat-icon bg-grad-cyan"><i class="bi bi-pause-circle"></i></div>
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
                <div class="stat-icon bg-grad-violet"><i class="bi bi-people"></i></div>
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
                <div class="stat-icon bg-grad-pink"><i class="bi bi-person-badge"></i></div>
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
                <div class="stat-icon bg-grad-green"><i class="bi bi-cash-stack"></i></div>
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
                <div class="stat-icon bg-grad-red"><i class="bi bi-receipt"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- ============ SHIPMENT PIPELINE ============ -->
<div class="card-admin mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="bi bi-diagram-3 me-2 text-primary"></i>Shipment pipeline</span>
        <span class="text-muted-2 small">Click a stage to open filtered shipments</span>
    </div>
    <div class="card-body">
        <div class="pipeline" id="pipeline"></div>
        <div class="d-flex flex-wrap gap-2 mt-3 pipeline-legend" id="pipelineLegend"></div>
    </div>
</div>

<!-- ============ CHARTS ============ -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card-admin h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Revenue &amp; volume (12 months)</span>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-ghost btn-sm active" id="chartRevenue">Revenue</button>
                    <button type="button" class="btn btn-ghost btn-sm" id="chartVolume">Volume</button>
                </div>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-admin h-100">
            <div class="card-header">Shipment status</div>
            <div class="card-body">
                <canvas id="statusChart" height="230"></canvas>
                <p class="text-muted-2 small text-center mb-0 mt-2">Click a slice to filter shipments</p>
            </div>
        </div>
    </div>
</div>

<!-- ============ QUICK ADD + RECENT ============ -->
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card-admin h-100">
            <div class="card-header"><i class="bi bi-pencil-square me-2 text-primary"></i>Add shipment — manual entry</div>
            <div class="card-body">
                <form data-modal-form action="<?= base_url('api/shipments.php') ?>" data-on-success="onQuickAddSuccess" id="quickAddForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="create">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Sender name</label>
                            <input type="text" class="form-control" name="sender_name" placeholder="Full name / company">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Receiver name</label>
                            <input type="text" class="form-control" name="receiver_name" placeholder="Full name / company">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sender details</label>
                            <textarea class="form-control" name="sender_details" rows="2" placeholder="Phone, email, address…"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Receiver details</label>
                            <textarea class="form-control" name="receiver_details" rows="2" placeholder="Phone, email, address…"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Origin *</label>
                            <input type="text" class="form-control" name="origin" required placeholder="City, Country">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Destination *</label>
                            <input type="text" class="form-control" name="destination" required placeholder="City, Country">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service</label>
                            <select class="form-select" name="service_type">
                                <?php foreach (service_types() as $s): ?>
                                    <option value="<?= e($s) ?>"><?= e(title_case($s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Package</label>
                            <select class="form-select" name="package_type">
                                <?php foreach (package_types() as $p): ?>
                                    <option value="<?= e($p) ?>"><?= e(title_case($p)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="weight" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Qty</label>
                            <input type="number" class="form-control" name="quantity" value="1" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="price" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description / contents</label>
                            <input type="text" class="form-control" name="description" placeholder="What's inside?">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <?php foreach ($statuses as $key => $meta): ?>
                                    <option value="<?= e($key) ?>"><?= e($meta[0]) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Est. delivery</label>
                            <input type="date" class="form-control" name="estimated_delivery">
                        </div>
                        <div class="col-12 d-grid mt-3">
                            <button type="submit" class="btn btn-brand"><i class="bi bi-plus-circle me-1"></i> Create shipment</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card-admin mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Recent shipments</span>
                <a href="<?= base_url('admin/shipments.php') ?>" class="small">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table table-admin">
                    <thead><tr><th>Tracking</th><th>Sender</th><th>Destination</th><th>Status</th></tr></thead>
                    <tbody id="recentShipments"><tr><td colspan="4" class="text-center text-muted-2 py-4">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
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
    var shipmentsUrl = '<?= base_url('admin/shipments.php') ?>';
    var trendChart = null, statusChart = null;
    var stats = null;

    /* ---------- Live clock ---------- */
    function tickClock() {
        var d = new Date();
        document.getElementById('liveClock').textContent =
            d.toLocaleDateString('en', { weekday: 'short', month: 'short', day: 'numeric' }) +
            ' · ' + d.toLocaleTimeString('en', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
    setInterval(tickClock, 1000);
    tickClock();

    /* ---------- Animated counters ---------- */
    function animateCount(el, target, isCurrency) {
        var dur = 900, start = null;
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / dur, 1);
            var eased = 1 - Math.pow(1 - p, 3);
            var v = target * eased;
            el.textContent = isCurrency ? fmtCurrency(v) : Math.round(v).toLocaleString();
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    function fmtCurrency(v) {
        return currencySymbol + Number(v).toLocaleString(undefined, { maximumFractionDigits: 0 });
    }

    /* ---------- Helpers ---------- */
    function shortMonth(m) { var d = new Date(m + '-01T00:00:00'); return d.toLocaleString('en', { month: 'short' }); }
    function buildLabels() {
        var all = [], now = new Date();
        for (var i = 11; i >= 0; i--) {
            var d = new Date(now.getFullYear(), now.getMonth() - i, 1);
            all.push({ key: d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0'), label: d.toLocaleString('en', { month: 'short' }) });
        }
        return all;
    }

    /* ---------- KPI render ---------- */
    function renderKpis() {
        var kpis = stats.kpis;
        document.querySelectorAll('[data-kpi]').forEach(function (el) {
            var key = el.getAttribute('data-kpi');
            var isCurrency = el.hasAttribute('data-currency');
            var v = Number(kpis[key] || 0);
            animateCount(el, v, isCurrency);
        });
    }

    /* ---------- Pipeline ---------- */
    var PIPE_ORDER = ['pending', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered'];
    var PIPE_COLORS = { pending: '#94a3b8', picked_up: '#f97316', in_transit: '#e82127', out_for_delivery: '#f59e0b', delivered: '#10b981' };
    var OTHER_STAGES = ['on_hold', 'customs', 'cancelled', 'returned'];

    function renderPipeline() {
        var map = {};
        stats.status_breakdown.forEach(function (s) { map[s.status] = parseInt(s.count) || 0; });
        var total = PIPE_ORDER.reduce(function (a, k) { return a + (map[k] || 0); }, 0);
        var otherTotal = OTHER_STAGES.reduce(function (a, k) { return a + (map[k] || 0); }, 0);
        var pipe = document.getElementById('pipeline');
        var legend = document.getElementById('pipelineLegend');

        if (!total && !otherTotal) {
            pipe.innerHTML = '<div class="text-muted-2 small py-2">No shipments yet — add one with the form on the right.</div>';
            legend.innerHTML = '';
            return;
        }
        pipe.innerHTML = PIPE_ORDER.map(function (k) {
            var c = map[k] || 0;
            return '<div class="seg" data-status="' + k + '" style="background:' + PIPE_COLORS[k] + ';flex-grow:' + Math.max(c, 0.4) + ';" title="' + k.replace(/_/g, ' ') + ' (' + c + ')">' + (c ? c : '') + '</div>';
        }).join('');

        legend.innerHTML = PIPE_ORDER.concat(OTHER_STAGES).map(function (k) {
            var c = map[k] || 0;
            var color = PIPE_COLORS[k] || '#64748b';
            return '<a href="' + shipmentsUrl + '?status=' + k + '" class="pipeline-legend-item" style="background:' + color + '22;color:' + color + ';">' + k.replace(/_/g, ' ') + ' · ' + c + '</a>';
        }).join('');
    }

    /* ---------- Charts ---------- */
    var centerTextPlugin = {
        id: 'centerText',
        afterDraw: function (chart) {
            var opts = chart.config.options.centerText;
            if (!opts) return;
            var meta = chart.getDatasetMeta(0);
            if (!meta.data.length) return;
            var pt = meta.data[0];
            var ctx = chart.ctx;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.font = '700 26px "Space Grotesk", Inter, sans-serif';
            ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--a-text').trim() || '#1e293b';
            ctx.fillText(opts.value, pt.x, pt.y - 9);
            ctx.font = '500 12px Inter, sans-serif';
            ctx.fillStyle = getComputedStyle(document.documentElement).getPropertyValue('--a-muted').trim() || '#64748b';
            ctx.fillText(opts.label, pt.x, pt.y + 15);
            ctx.restore();
        }
    };

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
                    backgroundColor: mode === 'revenue' ? 'rgba(232,33,39,.75)' : 'rgba(249,115,22,.75)',
                    borderRadius: 6,
                    hoverBackgroundColor: mode === 'revenue' ? '#e82127' : '#f97316'
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
        var labels = [], counts = [], colors = [], keys = [];
        var palette = {
            pending: '#94a3b8', picked_up: '#f97316', in_transit: '#e82127',
            out_for_delivery: '#f59e0b', delivered: '#10b981', on_hold: '#f97316',
            customs: '#8b5cf6', cancelled: '#ef4444', returned: '#dc2626'
        };
        stats.status_breakdown.forEach(function (s) {
            keys.push(s.status);
            labels.push(s.status.replace(/_/g, ' '));
            counts.push(parseInt(s.count));
            colors.push(palette[s.status] || '#64748b');
        });
        var total = counts.reduce(function (a, b) { return a + b; }, 0);
        var ctx = document.getElementById('statusChart');
        if (statusChart) statusChart.destroy();
        statusChart = new Chart(ctx, {
            type: 'doughnut',
            data: { labels: labels, datasets: [{ data: counts, backgroundColor: colors, borderWidth: 2, hoverOffset: 8 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '62%',
                centerText: { value: Number(total).toLocaleString(), label: 'shipments' },
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } } },
                onClick: function (evt, items) {
                    if (items.length) window.location.href = shipmentsUrl + '?status=' + encodeURIComponent(keys[items[0].index]);
                }
            },
            plugins: [centerTextPlugin]
        });
    }

    /* ---------- Tables ---------- */
    function renderTables() {
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
    }

    /* ---------- Master render ---------- */
    function render() {
        renderKpis();
        renderPipeline();
        renderTrend(document.getElementById('chartRevenue').classList.contains('active') ? 'revenue' : 'volume');
        renderStatus();
        renderTables();
    }

    function refreshStats() {
        CF.api('<?= base_url('api/stats.php') ?>').then(function (json) {
            if (!json.success || !json.data || !json.data.kpis) {
                var err = '<tr><td colspan="4" class="text-center text-muted-2 py-4">Could not load data — <a href="#" onclick="location.reload();return false;">retry</a></td></tr>';
                document.getElementById('recentShipments').innerHTML = err;
                document.getElementById('recentPayments').innerHTML = err.replace('colspan="4"', 'colspan="3"');
                return;
            }
            stats = json.data;
            render();
        });
    }

    /* ---------- Quick add (manual entry) ---------- */
    window.onQuickAddSuccess = function (json) {
        var tracking = (json.data && json.data.tracking_number) ? json.data.tracking_number : '';
        CF.toast('Shipment ' + tracking + ' created', 'success');
        var f = document.getElementById('quickAddForm');
        f.reset();
        f.elements.service_type.value = 'standard';
        f.elements.package_type.value = 'parcel';
        f.elements.status.value = 'pending';
        f.elements.quantity.value = '1';
        f.elements.price.value = '0';
        refreshStats();
    };

    /* ---------- Events ---------- */
    document.getElementById('pipeline').addEventListener('click', function (e) {
        var seg = e.target.closest('.seg');
        if (seg) window.location.href = shipmentsUrl + '?status=' + seg.getAttribute('data-status');
    });

    document.getElementById('refreshStats').addEventListener('click', refreshStats);

    document.getElementById('chartRevenue').addEventListener('click', function () {
        this.classList.add('active'); document.getElementById('chartVolume').classList.remove('active');
        renderTrend('revenue');
    });
    document.getElementById('chartVolume').addEventListener('click', function () {
        this.classList.add('active'); document.getElementById('chartRevenue').classList.remove('active');
        renderTrend('volume');
    });

    /* ---------- Auto refresh every 45s ---------- */
    setInterval(refreshStats, 45000);

    refreshStats();
})();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
