<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_role(['admin']);

$adminPage = 'users';
$adminTitle = 'User Management';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Users</h2>
        <p class="text-muted-2 mb-0 small">Manage admin and staff accounts.</p>
    </div>
    <button class="btn btn-brand btn-sm" onclick="openCreate()"><i class="bi bi-plus-lg me-1"></i> New User</button>
</div>

<div class="card-admin">
    <div class="table-responsive">
        <table class="table table-admin">
            <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Last login</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="tbody"><tr><td colspan="7" class="text-center text-muted-2 py-5">Loading…</td></tr></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form data-modal-form action="<?= base_url('api/users.php') ?>" data-on-success="onSaved">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="action" value="create">
        <input type="hidden" name="id" id="id">
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">New User</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Name *</label><input class="form-control" name="name" id="f_name" required></div>
                <div class="col-md-6"><label class="form-label">Username *</label><input class="form-control" name="username" id="f_username" required></div>
                <div class="col-12"><label class="form-label">Email *</label><input type="email" class="form-control" name="email" id="f_email" required></div>
                <div class="col-md-6"><label class="form-label">Role</label>
                    <select class="form-select" name="role" id="f_role"><option value="admin">Admin</option><option value="manager">Manager</option><option value="staff">Staff</option></select>
                </div>
                <div class="col-md-6"><label class="form-label">Status</label>
                    <select class="form-select" name="status" id="f_status"><option value="active">Active</option><option value="inactive">Inactive</option></select>
                </div>
                <div class="col-12"><label class="form-label">Password <span class="text-muted-2 fw-normal" id="pwHint">(min 8 characters)</span></label><input type="password" class="form-control" name="password" id="f_password" autocomplete="new-password"></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Save</button></div>
    </form>
</div></div></div>

<script>
var modal = new bootstrap.Modal(document.getElementById('userModal'));
var roleBadge = { admin:'danger', manager:'primary', staff:'secondary' };
function load() {
    CF.api('<?= base_url('api/users.php') ?>').then(function (json) {
        document.getElementById('tbody').innerHTML = json.data.map(function (u) {
            return '<tr>' +
                '<td><div class="d-flex align-items-center gap-2"><span class="avatar-sm bg-soft-blue">' + (u.name||'?')[0].toUpperCase() + '</span><span class="fw-semibold">' + u.name + '</span></div></td>' +
                '<td class="font-monospace">' + u.username + '</td>' +
                '<td class="small">' + u.email + '</td>' +
                '<td><span class="badge bg-' + (roleBadge[u.role]||'secondary') + ' rounded-pill">' + u.role + '</span></td>' +
                '<td class="small">' + (u.last_login||'Never') + '</td>' +
                '<td><span class="badge bg-' + (u.status==='active'?'success':'secondary') + ' rounded-pill">' + u.status + '</span></td>' +
                '<td class="text-end text-nowrap"><button class="btn btn-sm btn-ghost" onclick="openEdit(' + u.id + ')"><i class="bi bi-pencil"></i></button> ' +
                '<button class="btn btn-sm btn-ghost text-danger" onclick="del(' + u.id + ')"><i class="bi bi-trash"></i></button></td></tr>';
        }).join('') || '<tr><td colspan="7" class="empty-state"><i class="bi bi-shield-lock"></i><div>No users</div></td></tr>';
    });
}
function openCreate() {
    document.getElementById('modalTitle').textContent = 'New User';
    document.getElementById('action').value = 'create';
    document.getElementById('userModal').querySelector('form').reset();
    document.getElementById('f_role').value = 'staff';
    document.getElementById('f_status').value = 'active';
    document.getElementById('pwHint').textContent = '(min 8 characters)';
    modal.show();
}
function openEdit(id) {
    CF.api('<?= base_url('api/users.php') ?>').then(function (json) {
        var u = json.data.find(function (x) { return x.id == id; });
        if (!u) return;
        document.getElementById('modalTitle').textContent = 'Edit User';
        document.getElementById('action').value = 'update';
        document.getElementById('id').value = u.id;
        var f = document.getElementById('userModal').querySelector('form');
        f.reset();
        ['name','username','email','role','status'].forEach(function (k) { if (f.elements[k] && u[k] != null) f.elements[k].value = u[k]; });
        document.getElementById('pwHint').textContent = '(leave blank to keep current)';
        modal.show();
    });
}
function onSaved() { modal.hide(); load(); }
function del(id) {
    confirmAction('Delete this user?', function () {
        CF.api('<?= base_url('api/users.php') ?>', { action:'delete', id:id }).then(function (json) {
            CF.toast(json.message, json.success?'success':'danger'); if (json.success) load();
        });
    });
}
load();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
