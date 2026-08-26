<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'quotes';
$adminTitle = 'Quote Requests';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Quote Requests</h2>
        <p class="text-muted-2 mb-0 small">Review incoming quote requests from the website.</p>
    </div>
    <select class="form-select form-select-sm" id="statusFilter" style="max-width:180px;">
        <option value="">All statuses</option>
        <option value="new">New</option>
        <option value="reviewed">Reviewed</option>
        <option value="converted">Converted</option>
        <option value="declined">Declined</option>
    </select>
</div>

<div class="card-admin">
    <div class="table-responsive">
        <table class="table table-admin">
            <thead><tr><th>Date</th><th>Name</th><th>Contact</th><th>Route</th><th>Service</th><th>Est. price</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="tbody"><tr><td colspan="8" class="text-center text-muted-2 py-5">Loading…</td></tr></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Quote detail</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="viewBody"></div>
    <div class="modal-footer">
        <select class="form-select form-select-sm me-auto" id="statusSelect" style="max-width:180px;">
            <option value="new">New</option><option value="reviewed">Reviewed</option><option value="converted">Converted</option><option value="declined">Declined</option>
        </select>
        <button class="btn btn-brand btn-sm" onclick="updateStatus()">Update status</button>
    </div>
</div></div></div>

<script>
var viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
var currentId = null;
var symbol = '<?= e(setting('currency_symbol', '$')) ?>';
var statusBadge = { new:'primary', reviewed:'info', converted:'success', declined:'secondary' };
function load() {
    var s = document.getElementById('statusFilter').value;
    CF.api('<?= base_url('api/admin_quotes.php') ?>' + (s ? '?status=' + s : '')).then(function (json) {
        document.getElementById('tbody').innerHTML = json.data.map(function (q) {
            return '<tr>' +
                '<td class="small">' + (q.created_at ? q.created_at.slice(0,10) : '—') + '</td>' +
                '<td class="fw-semibold">' + q.name + '</td>' +
                '<td><div class="small">' + (q.email||'—') + '</div><div class="text-muted-2 small">' + (q.phone||'') + '</div></td>' +
                '<td class="small">' + (q.origin||'—') + ' → ' + (q.destination||'—') + '</td>' +
                '<td class="text-capitalize">' + q.service_type + '</td>' +
                '<td>' + (q.estimated_price ? symbol + Number(q.estimated_price).toFixed(2) : '—') + '</td>' +
                '<td><span class="badge bg-' + (statusBadge[q.status]||'secondary') + ' rounded-pill">' + q.status + '</span></td>' +
                '<td class="text-end text-nowrap"><button class="btn btn-sm btn-ghost" onclick="view(' + q.id + ')"><i class="bi bi-eye"></i></button> ' +
                '<button class="btn btn-sm btn-ghost text-danger" onclick="del(' + q.id + ')"><i class="bi bi-trash"></i></button></td></tr>';
        }).join('') || '<tr><td colspan="8" class="empty-state"><i class="bi bi-receipt"></i><div>No quote requests</div></td></tr>';
    });
}
function view(id) {
    CF.api('<?= base_url('api/admin_quotes.php') ?>').then(function (json) {
        var q = json.data.find(function (x) { return x.id == id; });
        if (!q) return;
        currentId = q.id;
        document.getElementById('statusSelect').value = q.status;
        document.getElementById('viewBody').innerHTML =
            '<dl class="row small mb-0">' +
            '<dt class="col-4 text-muted-2">Name</dt><dd class="col-8 fw-semibold">' + q.name + '</dd>' +
            '<dt class="col-4 text-muted-2">Email</dt><dd class="col-8">' + (q.email||'—') + '</dd>' +
            '<dt class="col-4 text-muted-2">Phone</dt><dd class="col-8">' + (q.phone||'—') + '</dd>' +
            '<dt class="col-4 text-muted-2">Origin</dt><dd class="col-8">' + (q.origin||'—') + '</dd>' +
            '<dt class="col-4 text-muted-2">Destination</dt><dd class="col-8">' + (q.destination||'—') + '</dd>' +
            '<dt class="col-4 text-muted-2">Service</dt><dd class="col-8 text-capitalize">' + (q.service_type||'—') + '</dd>' +
            '<dt class="col-4 text-muted-2">Package</dt><dd class="col-8 text-capitalize">' + (q.package_type||'—') + '</dd>' +
            '<dt class="col-4 text-muted-2">Weight</dt><dd class="col-8">' + (q.weight||'—') + ' kg</dd>' +
            '<dt class="col-4 text-muted-2">Est. price</dt><dd class="col-8">' + (q.estimated_price ? symbol + Number(q.estimated_price).toFixed(2) : '—') + '</dd>' +
            '<dt class="col-4 text-muted-2">Message</dt><dd class="col-8">' + (q.message ? q.message : '—') + '</dd>' +
            '</dl>';
        viewModal.show();
    });
}
function updateStatus() {
    CF.api('<?= base_url('api/admin_quotes.php') ?>', { action:'update', id:currentId, status:document.getElementById('statusSelect').value }).then(function (json) {
        CF.toast(json.message, json.success?'success':'danger');
        if (json.success) { viewModal.hide(); load(); }
    });
}
function del(id) {
    confirmAction('Delete this quote?', function () {
        CF.api('<?= base_url('api/admin_quotes.php') ?>', { action:'delete', id:id }).then(function (json) {
            CF.toast(json.message, json.success?'success':'danger'); if (json.success) load();
        });
    });
}
document.getElementById('statusFilter').addEventListener('change', load);
load();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
