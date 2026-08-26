<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'notifications';
$adminTitle = 'Notifications';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Notifications</h2>
        <p class="text-muted-2 mb-0 small">System alerts and activity updates.</p>
    </div>
    <button class="btn btn-ghost btn-sm" onclick="markAll()"><i class="bi bi-check2-all me-1"></i> Mark all read</button>
</div>

<div class="card-admin">
    <div id="notifList" class="p-2"><div class="text-center text-muted-2 py-5">Loading…</div></div>
</div>

<script>
var typeIcon = { info:'bi-info-circle text-primary', success:'bi-check-circle text-success', warning:'bi-exclamation-triangle text-warning', error:'bi-x-circle text-danger' };
function load() {
    CF.api('<?= base_url('api/notifications.php') ?>?limit=100').then(function (json) {
        document.getElementById('notifList').innerHTML = json.data.map(function (n) {
            return '<div class="d-flex align-items-start gap-3 p-3 border-bottom ' + (n.is_read == 0 ? 'bg-soft-blue bg-opacity-10' : '') + '">' +
                '<i class="bi ' + (typeIcon[n.type]||'bi-info-circle') + ' fs-4"></i>' +
                '<div class="flex-grow-1">' +
                    '<div class="d-flex justify-content-between"><span class="fw-semibold">' + n.title + '</span><span class="text-muted-2 small">' + (n.created_at||'') + '</span></div>' +
                    '<div class="text-muted-2 small">' + (n.message||'') + '</div>' +
                '</div>' +
                (n.is_read == 0 ? '<button class="btn btn-sm btn-ghost" onclick="markRead(' + n.id + ')"><i class="bi bi-check2"></i></button>' : '') +
                '<button class="btn btn-sm btn-ghost text-danger" onclick="del(' + n.id + ')"><i class="bi bi-trash"></i></button>' +
            '</div>';
        }).join('') || '<div class="empty-state"><i class="bi bi-bell"></i><div>No notifications</div></div>';
    });
}
function markRead(id) {
    CF.api('<?= base_url('api/notifications.php') ?>', { action:'mark_read', id:id }).then(load);
}
function markAll() {
    CF.api('<?= base_url('api/notifications.php') ?>', { action:'mark_all_read' }).then(function () { load(); CF.toast('All marked as read', 'success'); });
}
function del(id) {
    CF.api('<?= base_url('api/notifications.php') ?>', { action:'delete', id:id }).then(load);
}
load();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
