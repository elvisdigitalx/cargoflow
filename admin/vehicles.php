<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'vehicles';
$adminTitle = 'Vehicles';
$drivers = fetchAll('SELECT id, name FROM drivers ORDER BY name ASC');
$types = vehicle_types();
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Vehicles</h2>
        <p class="text-muted-2 mb-0 small">Manage your fleet of trucks, vans, ships and aircraft.</p>
    </div>
    <button class="btn btn-brand btn-sm" onclick="openCreate()"><i class="bi bi-plus-lg me-1"></i> New Vehicle</button>
</div>

<div class="card-admin">
    <div class="table-responsive">
        <table class="table table-admin">
            <thead><tr><th>Vehicle</th><th>Type</th><th>Plate</th><th>Capacity</th><th>Driver</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="tbody"><tr><td colspan="7" class="text-center text-muted-2 py-5">Loading…</td></tr></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="vehModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form data-modal-form action="<?= base_url('api/vehicles.php') ?>" data-on-success="onSaved">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="action" value="create">
        <input type="hidden" name="id" id="id">
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">New Vehicle</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-12"><label class="form-label">Name *</label><input class="form-control" name="name" id="f_name" required></div>
                <div class="col-md-6"><label class="form-label">Type</label>
                    <select class="form-select" name="type" id="f_type">
                        <?php foreach ($types as $t): ?><option value="<?= e($t) ?>"><?= e(title_case($t)) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Plate number</label><input class="form-control" name="plate_number" id="f_plate"></div>
                <div class="col-md-6"><label class="form-label">Capacity</label><input class="form-control" name="capacity" id="f_capacity" placeholder="e.g. 3.5 tons"></div>
                <div class="col-md-6"><label class="form-label">Assigned driver</label>
                    <select class="form-select" name="driver_id" id="f_driver"><option value="">— None —</option>
                        <?php foreach ($drivers as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12"><label class="form-label">Status</label>
                    <select class="form-select" name="status" id="f_status">
                        <option value="available">Available</option>
                        <option value="in_transit">In transit</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Save</button></div>
    </form>
</div></div></div>

<script>
var modal = new bootstrap.Modal(document.getElementById('vehModal'));
var statusBadge = { available:'success', in_transit:'primary', maintenance:'warning' };
var typeIcon = { truck:'bi-truck', van:'bi-truck', bike:'bi-bicycle', ship:'bi-water', plane:'bi-airplane' };
function load() {
    CF.api('<?= base_url('api/vehicles.php') ?>').then(function (json) {
        document.getElementById('tbody').innerHTML = json.data.map(function (v) {
            return '<tr>' +
                '<td><div class="d-flex align-items-center gap-2"><i class="bi ' + (typeIcon[v.type]||'bi-truck') + ' text-primary fs-5"></i><span class="fw-semibold">' + v.name + '</span></div></td>' +
                '<td class="text-capitalize">' + v.type + '</td>' +
                '<td class="small">' + (v.plate_number||'—') + '</td>' +
                '<td class="small">' + (v.capacity||'—') + '</td>' +
                '<td>' + (v.driver_name||'—') + '</td>' +
                '<td><span class="badge bg-' + (statusBadge[v.status]||'secondary') + ' rounded-pill">' + v.status.replace(/_/g,' ') + '</span></td>' +
                '<td class="text-end text-nowrap"><button class="btn btn-sm btn-ghost" onclick="openEdit(' + v.id + ')"><i class="bi bi-pencil"></i></button> ' +
                '<button class="btn btn-sm btn-ghost text-danger" onclick="del(' + v.id + ')"><i class="bi bi-trash"></i></button></td></tr>';
        }).join('') || '<tr><td colspan="7" class="empty-state"><i class="bi bi-truck"></i><div>No vehicles found</div></td></tr>';
    });
}
function openCreate() {
    document.getElementById('modalTitle').textContent = 'New Vehicle';
    document.getElementById('action').value = 'create';
    document.getElementById('vehModal').querySelector('form').reset();
    document.getElementById('f_status').value = 'available';
    modal.show();
}
function openEdit(id) {
    CF.api('<?= base_url('api/vehicles.php') ?>').then(function (json) {
        var v = json.data.find(function (x) { return x.id == id; });
        if (!v) return;
        document.getElementById('modalTitle').textContent = 'Edit Vehicle';
        document.getElementById('action').value = 'update';
        document.getElementById('id').value = v.id;
        var f = document.getElementById('vehModal').querySelector('form');
        f.reset();
        ['name','type','plate_number','capacity','status'].forEach(function (k) { if (f.elements[k] && v[k] != null) f.elements[k].value = v[k]; });
        if (f.elements['driver_id']) f.elements['driver_id'].value = v.driver_id || '';
        modal.show();
    });
}
function onSaved() { modal.hide(); load(); }
function del(id) {
    confirmAction('Delete this vehicle?', function () {
        CF.api('<?= base_url('api/vehicles.php') ?>', { action:'delete', id:id }).then(function (json) {
            CF.toast(json.message, json.success?'success':'danger'); if (json.success) load();
        });
    });
}
load();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
