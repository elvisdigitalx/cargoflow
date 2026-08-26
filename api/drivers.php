<?php
/**
 * CargoFlow — Drivers admin API
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'list') {
    $rows = fetchAll(
        'SELECT d.*, v.name AS vehicle_name FROM drivers d LEFT JOIN vehicles v ON v.id = d.vehicle_id ORDER BY d.name ASC'
    );
    json_response(['success' => true, 'data' => $rows]);
}

$input = api_input();

if ($action === 'create') {
    $name = trim($input['name'] ?? '');
    if ($name === '') {
        api_error('Driver name is required.');
    }
    $id = insertRow('drivers', [
        'name'           => $name,
        'email'          => trim($input['email'] ?? ''),
        'phone'          => trim($input['phone'] ?? ''),
        'license_number' => trim($input['license_number'] ?? ''),
        'vehicle_id'     => !empty($input['vehicle_id']) ? (int) $input['vehicle_id'] : null,
        'status'         => trim($input['status'] ?? 'available'),
    ]);
    api_success(['id' => $id], 'Driver created.');
}

if ($action === 'update') {
    $id = api_id();
    $fields = ['name', 'email', 'phone', 'license_number', 'vehicle_id', 'status'];
    $data = [];
    foreach ($fields as $f) {
        if (array_key_exists($f, $input)) {
            $data[$f] = ($input[$f] === '' && $f === 'vehicle_id') ? null : trim((string) $input[$f]);
        }
    }
    updateRow('drivers', $id, $data);
    api_success([], 'Driver updated.');
}

if ($action === 'delete') {
    $id = api_id();
    query('UPDATE shipments SET driver_id = NULL WHERE driver_id = ?', [$id]);
    query('UPDATE vehicles SET driver_id = NULL WHERE driver_id = ?', [$id]);
    deleteRow('drivers', $id);
    api_success([], 'Driver deleted.');
}

api_error('Unknown action.', 400);
