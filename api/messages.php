<?php
/**
 * CargoFlow — Contact messages admin API
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'list') {
    $rows = fetchAll('SELECT * FROM contact_messages ORDER BY created_at DESC');
    json_response(['success' => true, 'data' => $rows]);
}

if ($action === 'update') {
    $id = api_id();
    if (array_key_exists('status', $_POST)) {
        updateRow('contact_messages', $id, ['status' => trim($_POST['status'])]);
    }
    api_success([], 'Message updated.');
}

if ($action === 'delete') {
    $id = api_id();
    deleteRow('contact_messages', $id);
    api_success([], 'Message deleted.');
}

api_error('Unknown action.', 400);
