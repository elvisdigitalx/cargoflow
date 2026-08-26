<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'drivers';
$adminTitle = 'Drivers';
$vehicles = fetchAll('SELECT id, name FROM vehicles ORDER BY name ASC');
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Drivers</h2>
        <p class="text-muted-2 mb-0 small">Manage your delivery team and assignments.</p>
    </div>
    <button class="btn btn-brand btn-sm" onclick="openCreate()"><i class="bi bi-plus-lg me-1"></i> New Driver</button>
</div>

<div class="card-admin">
    <div class="table-responsive">
        <table class="table table-admin">
            <thead><tr><th>Driver</th><th>Contact</th><th>License</th><th>Assigned vehicle</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="tbody"><tr><td colspan="6" class="text-center text-muted-2 py-5">Loading…</td></tr></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="drvModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form data-modal-form action="<?= base_url('api/drivers.php') ?>" data-on-success="onSaved">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="action" value="create">
        <input type="hidden" name="id" id="id">
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">New Driver</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-12"><label class="form-label">Full name *</label><input class="form-control" name="name" id="f_name" required></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" id="f_email"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" id="f_phone"></div>
                <div class="col-md-6"><label class="form-label">License number</label><input class="form-control" name="license_number" id="f_license"></div>
                <div class="col-md-6"><label class="form-label">Assigned vehicle</label>
                    <select class="form-select" name="vehicle_id" id="f_vehicle"><option value="">— None —</option>
                        <?php foreach ($vehicles as $v): ?><option value="<?= $v['id'] ?>"><?= e($v['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12"><label class="form-label">Status</label>
                    <select class="form-select" name="status" id="f_status">
                        <option value="available">Available</option>
                        <option value="on_delivery">On delivery</option>
                        <option value="off_duty">Off duty</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Save</button></div>
    </form>
</div></div></div>

<script>
var modal = new bootstrap.Modal(document.getElementById('drvModal'));
var statusBadge = { available:'success', on_delivery:'primary', off_duty:'secondary' };
function load() {
    CF.api('<?= base_url('api/drivers.php') ?>').then(function (json) {
        document.getElementById('tbody').innerHTML = json.data.map(function (d) {
            return '<tr>' +
                '<td><div class="d-flex align-items-center gap-2"><span class="avatar-sm bg-soft-blue">' + (d.name||'?')[0].toUpperCase() + '</span><span class="fw-semibold">' + d.name + '</span></div></td>' +
                '<td><div class="small">' + (d.email||'—') + '</div><div class="text-muted-2 small">' + (d.phone||'') + '</div></td>' +
                '<td class="small">' + (d.license_number||'—') + '</td>' +
                '<td>' + (d.vehicle_name||'—') + '</td>' +
                '<td><span class="badge bg-' + (statusBadge[d.status]||'secondary') + ' rounded-pill">' + d.status.replace(/_/g,' ') + '</span></td>' +
                '<td class="text-end text-nowrap"><button class="btn btn-sm btn-ghost" onclick="openEdit(' + d.id + ')"><i class="bi bi-pencil"></i></button> ' +
                '<button class="btn btn-sm btn-ghost text-danger" onclick="del(' + d.id + ')"><i class="bi bi-trash"></i></button></td></tr>';
        }).join('') || '<tr><td colspan="6" class="empty-state"><i class="bi bi-person-badge"></i><div>No drivers found</div></td></tr>';
    });
}
function openCreate() {
    document.getElementById('modalTitle').textContent = 'New Driver';
    document.getElementById('action').value = 'create';
    document.getElementById('drvModal').querySelector('form').reset();
    document.getElementById('f_status').value = 'available';
    modal.show();
}
function openEdit(id) {
    CF.api('<?= base_url('api/drivers.php') ?>').then(function (json) {
        var d = json.data.find(function (x) { return x.id == id; });
        if (!d) return;
        document.getElementById('modalTitle').textContent = 'Edit Driver';
        document.getElementById('action').value = 'update';
        document.getElementById('id').value = d.id;
        var f = document.getElementById('drvModal').querySelector('form');
        f.reset();
        ['name','email','phone','license_number','status'].forEach(function (k) { if (f.elements[k] && d[k] != null) f.elements[k].value = d[k]; });
        if (f.elements['vehicle_id']) f.elements['vehicle_id'].value = d.vehicle_id || '';
        modal.show();
    });
}
function onSaved() { modal.hide(); load(); }
function del(id) {
    confirmAction('Delete this driver?', function () {
        CF.api('<?= base_url('api/drivers.php') ?>', { action:'delete', id:id }).then(function (json) {
            CF.toast(json.message, json.success?'success':'danger'); if (json.success) load();
        });
    });
}
load();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
