<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'messages';
$adminTitle = 'Messages';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Contact Messages</h2>
        <p class="text-muted-2 mb-0 small">Messages submitted through the contact form.</p>
    </div>
</div>

<div class="card-admin">
    <div class="table-responsive">
        <table class="table table-admin">
            <thead><tr><th>Date</th><th>Name</th><th>Contact</th><th>Subject</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="tbody"><tr><td colspan="6" class="text-center text-muted-2 py-5">Loading…</td></tr></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Message detail</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body" id="viewBody"></div>
    <div class="modal-footer">
        <select class="form-select form-select-sm me-auto" id="statusSelect" style="max-width:180px;">
            <option value="new">New</option><option value="read">Read</option><option value="replied">Replied</option>
        </select>
        <button class="btn btn-brand btn-sm" onclick="updateStatus()">Update</button>
    </div>
</div></div></div>

<script>
var viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
var currentId = null;
var statusBadge = { new:'primary', read:'info', replied:'success' };
function load() {
    CF.api('<?= base_url('api/messages.php') ?>').then(function (json) {
        document.getElementById('tbody').innerHTML = json.data.map(function (m) {
            return '<tr>' +
                '<td class="small">' + (m.created_at ? m.created_at.slice(0,10) : '—') + '</td>' +
                '<td class="fw-semibold">' + m.name + '</td>' +
                '<td><div class="small">' + (m.email||'—') + '</div><div class="text-muted-2 small">' + (m.phone||'') + '</div></td>' +
                '<td>' + (m.subject||'—') + '</td>' +
                '<td><span class="badge bg-' + (statusBadge[m.status]||'secondary') + ' rounded-pill">' + m.status + '</span></td>' +
                '<td class="text-end text-nowrap"><button class="btn btn-sm btn-ghost" onclick="view(' + m.id + ')"><i class="bi bi-eye"></i></button> ' +
                '<button class="btn btn-sm btn-ghost text-danger" onclick="del(' + m.id + ')"><i class="bi bi-trash"></i></button></td></tr>';
        }).join('') || '<tr><td colspan="6" class="empty-state"><i class="bi bi-envelope"></i><div>No messages</div></td></tr>';
    });
}
function view(id) {
    CF.api('<?= base_url('api/messages.php') ?>').then(function (json) {
        var m = json.data.find(function (x) { return x.id == id; });
        if (!m) return;
        currentId = m.id;
        document.getElementById('statusSelect').value = m.status;
        document.getElementById('viewBody').innerHTML =
            '<h6>' + m.subject + '</h6>' +
            '<p class="text-muted-2 small">From <b>' + m.name + '</b> &lt;' + (m.email||'') + '&gt;' + (m.phone ? ' · ' + m.phone : '') + '</p>' +
            '<hr><p class="mb-0">' + m.message + '</p>';
        viewModal.show();
        if (m.status === 'new') updateStatus('read');
    });
}
function updateStatus(force) {
    CF.api('<?= base_url('api/messages.php') ?>', { action:'update', id:currentId, status: force || document.getElementById('statusSelect').value }).then(function (json) {
        if (!force) { CF.toast(json.message, json.success?'success':'danger'); viewModal.hide(); }
        load();
    });
}
function del(id) {
    confirmAction('Delete this message?', function () {
        CF.api('<?= base_url('api/messages.php') ?>', { action:'delete', id:id }).then(function (json) {
            CF.toast(json.message, json.success?'success':'danger'); if (json.success) load();
        });
    });
}
load();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
