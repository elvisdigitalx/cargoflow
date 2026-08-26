<?php
/**
 * CargoFlow — Customers admin API
 * GET  /api/customers.php          → list
 * POST /api/customers.php action=create|update|delete
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'list') {
    $search = trim($_GET['search'] ?? '');
    $where = '';
    $params = [];
    if ($search !== '') {
        $where = 'WHERE name LIKE ? OR email LIKE ? OR company LIKE ? OR customer_code LIKE ?';
        $like = '%' . $search . '%';
        $params = [$like, $like, $like, $like];
    }
    $rows = fetchAll("SELECT * FROM customers $where ORDER BY created_at DESC", $params);
    json_response(['success' => true, 'data' => $rows]);
}

$input = api_input();

if ($action === 'create') {
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    if ($name === '') {
        api_error('Customer name is required.');
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        api_error('Invalid email address.');
    }

    $id = insertRow('customers', [
        'customer_code' => generate_customer_code(),
        'name'          => $name,
        'email'         => $email,
        'phone'         => trim($input['phone'] ?? ''),
        'company'       => trim($input['company'] ?? ''),
        'address'       => trim($input['address'] ?? ''),
        'city'          => trim($input['city'] ?? ''),
        'state'         => trim($input['state'] ?? ''),
        'country'       => trim($input['country'] ?? ''),
        'postal_code'   => trim($input['postal_code'] ?? ''),
        'notes'         => trim($input['notes'] ?? ''),
        'status'        => trim($input['status'] ?? 'active'),
    ]);
    api_success(['id' => $id], 'Customer created.');
}

if ($action === 'update') {
    $id = api_id();
    $fields = ['name', 'email', 'phone', 'company', 'address', 'city', 'state', 'country', 'postal_code', 'notes', 'status'];
    $data = [];
    foreach ($fields as $f) {
        if (array_key_exists($f, $input)) {
            $data[$f] = trim((string) $input[$f]);
        }
    }
    if (isset($data['email']) && $data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        api_error('Invalid email address.');
    }
    updateRow('customers', $id, $data);
    api_success([], 'Customer updated.');
}

if ($action === 'delete') {
    $id = api_id();
    // Prevent orphaned shipments
    query('UPDATE shipments SET customer_id = NULL WHERE customer_id = ?', [$id]);
    query('UPDATE invoices SET customer_id = NULL WHERE customer_id = ?', [$id]);
    deleteRow('customers', $id);
    api_success([], 'Customer deleted.');
}

api_error('Unknown action.', 400);
