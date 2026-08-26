<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'customers';
$adminTitle = 'Customers';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Customers</h2>
        <p class="text-muted-2 mb-0 small">Manage your customer accounts and contact details.</p>
    </div>
    <button class="btn btn-brand btn-sm" onclick="openCreate()"><i class="bi bi-plus-lg me-1"></i> New Customer</button>
</div>

<div class="card-admin">
    <div class="card-header d-flex flex-wrap gap-2 align-items-center">
        <div class="input-group input-group-sm" style="max-width:280px;">
            <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Search name, email, company…">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-admin">
            <thead><tr><th>Code</th><th>Name</th><th>Contact</th><th>Company</th><th>Location</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="tbody"><tr><td colspan="7" class="text-center text-muted-2 py-5">Loading…</td></tr></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="custModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <form data-modal-form action="<?= base_url('api/customers.php') ?>" data-on-success="onSaved">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="action" value="create">
        <input type="hidden" name="id" id="id">
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">New Customer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Full name *</label><input class="form-control" name="name" id="f_name" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" id="f_email"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" id="f_phone"></div>
                <div class="col-md-6"><label class="form-label">Company</label><input class="form-control" name="company" id="f_company"></div>
                <div class="col-12"><label class="form-label">Address</label><input class="form-control" name="address" id="f_address"></div>
                <div class="col-md-4"><label class="form-label">City</label><input class="form-control" name="city" id="f_city"></div>
                <div class="col-md-4"><label class="form-label">State / Region</label><input class="form-control" name="state" id="f_state"></div>
                <div class="col-md-4"><label class="form-label">Postal code</label><input class="form-control" name="postal_code" id="f_postal"></div>
                <div class="col-md-6"><label class="form-label">Country</label><input class="form-control" name="country" id="f_country"></div>
                <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status" id="f_status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" id="f_notes" rows="2"></textarea></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Save</button></div>
    </form>
</div></div></div>

<script>
var modal = new bootstrap.Modal(document.getElementById('custModal'));
function load() {
    var q = document.getElementById('searchInput').value;
    CF.api('<?= base_url('api/customers.php') ?>' + (q ? '?search=' + encodeURIComponent(q) : '')).then(function (json) {
        var t = document.getElementById('tbody');
        t.innerHTML = json.data.map(function (c) {
            return '<tr>' +
                '<td class="text-muted-2 small">' + c.customer_code + '</td>' +
                '<td class="fw-semibold">' + c.name + '</td>' +
                '<td><div class="small">' + (c.email||'—') + '</div><div class="text-muted-2 small">' + (c.phone||'') + '</div></td>' +
                '<td>' + (c.company||'—') + '</td>' +
                '<td class="small">' + (c.city||'—') + ', ' + (c.country||'—') + '</td>' +
                '<td><span class="badge bg-' + (c.status==='active'?'success':'secondary') + ' rounded-pill">' + c.status + '</span></td>' +
                '<td class="text-end text-nowrap"><button class="btn btn-sm btn-ghost" onclick="openEdit(' + c.id + ')"><i class="bi bi-pencil"></i></button> ' +
                '<button class="btn btn-sm btn-ghost text-danger" onclick="del(' + c.id + ')"><i class="bi bi-trash"></i></button></td></tr>';
        }).join('') || '<tr><td colspan="7" class="empty-state"><i class="bi bi-people"></i><div>No customers found</div></td></tr>';
    });
}
function openCreate() {
    document.getElementById('modalTitle').textContent = 'New Customer';
    document.getElementById('action').value = 'create';
    document.getElementById('custModal').querySelector('form').reset();
    document.getElementById('f_status').value = 'active';
    modal.show();
}
function openEdit(id) {
    CF.api('<?= base_url('api/customers.php') ?>').then(function (json) {
        var c = json.data.find(function (x) { return x.id == id; });
        if (!c) return;
        document.getElementById('modalTitle').textContent = 'Edit Customer';
        document.getElementById('action').value = 'update';
        document.getElementById('id').value = c.id;
        var f = document.getElementById('custModal').querySelector('form');
        f.reset();
        ['name','email','phone','company','address','city','state','country','postal_code','notes','status'].forEach(function (k) {
            if (f.elements[k] && c[k] != null) f.elements[k].value = c[k];
        });
        modal.show();
    });
}
function onSaved() { modal.hide(); load(); }
function del(id) {
    confirmAction('Delete this customer?', function () {
        CF.api('<?= base_url('api/customers.php') ?>', { action:'delete', id:id }).then(function (json) {
            CF.toast(json.message, json.success?'success':'danger'); if (json.success) load();
        });
    });
}
document.getElementById('searchInput').addEventListener('input', debounce(load, 300));
function debounce(fn, ms) { var t; return function () { clearTimeout(t); t = setTimeout(fn, ms); }; }
load();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
