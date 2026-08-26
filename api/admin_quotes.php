<?php
/**
 * CargoFlow — Quote requests admin API
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'list') {
    $status = trim($_GET['status'] ?? '');
    $where = $status !== '' ? 'WHERE status = ?' : '';
    $params = $status !== '' ? [$status] : [];
    $rows = fetchAll("SELECT * FROM quotes $where ORDER BY created_at DESC", $params);
    json_response(['success' => true, 'data' => $rows]);
}

if ($action === 'update') {
    $id = api_id();
    $data = [];
    if (array_key_exists('status', $_POST)) {
        $data['status'] = trim($_POST['status']);
    }
    if (array_key_exists('estimated_price', $_POST)) {
        $data['estimated_price'] = is_numeric($_POST['estimated_price']) ? (float) $_POST['estimated_price'] : null;
    }
    updateRow('quotes', $id, $data);
    api_success([], 'Quote updated.');
}

if ($action === 'delete') {
    $id = api_id();
    deleteRow('quotes', $id);
    api_success([], 'Quote deleted.');
}

api_error('Unknown action.', 400);
