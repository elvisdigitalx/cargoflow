<?php
/**
 * CargoFlow — Notifications API
 * GET  /api/notifications.php            → list (admin or user-scoped)
 * POST /api/notifications.php action=mark_read|mark_all_read|delete
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'list') {
    $userId = $_SESSION['user_id'];
    $limit = (int) ($_GET['limit'] ?? 20);
    $unread = ($_GET['unread'] ?? '') === '1';
    $where = 'user_id IS NULL OR user_id = ?';
    if ($unread) {
        $where .= ' AND is_read = 0';
    }
    $rows = fetchAll("SELECT * FROM notifications WHERE $where ORDER BY created_at DESC LIMIT $limit", [$userId]);
    $unreadCount = (int) fetchValue('SELECT COUNT(*) FROM notifications WHERE (user_id IS NULL OR user_id = ?) AND is_read = 0', [$userId]);
    json_response(['success' => true, 'data' => $rows, 'unread' => $unreadCount]);
}

if ($action === 'mark_read') {
    $id = api_id();
    query('UPDATE notifications SET is_read = 1 WHERE id = ?', [$id]);
    api_success([], 'Marked as read.');
}

if ($action === 'mark_all_read') {
    query('UPDATE notifications SET is_read = 1 WHERE user_id IS NULL OR user_id = ?', [$_SESSION['user_id']]);
    api_success([], 'All notifications marked as read.');
}

if ($action === 'delete') {
    $id = api_id();
    deleteRow('notifications', $id);
    api_success([], 'Notification deleted.');
}

api_error('Unknown action.', 400);
