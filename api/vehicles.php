<?php
/**
 * CargoFlow — Vehicles admin API
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'list') {
    $rows = fetchAll(
        'SELECT v.*, d.name AS driver_name FROM vehicles v LEFT JOIN drivers d ON d.id = v.driver_id ORDER BY v.name ASC'
    );
    json_response(['success' => true, 'data' => $rows]);
}

$input = api_input();

if ($action === 'create') {
    $name = trim($input['name'] ?? '');
    if ($name === '') {
        api_error('Vehicle name is required.');
    }
    $id = insertRow('vehicles', [
        'name'         => $name,
        'type'         => trim($input['type'] ?? 'truck'),
        'plate_number' => trim($input['plate_number'] ?? ''),
        'capacity'     => trim($input['capacity'] ?? ''),
        'driver_id'    => !empty($input['driver_id']) ? (int) $input['driver_id'] : null,
        'status'       => trim($input['status'] ?? 'available'),
    ]);
    api_success(['id' => $id], 'Vehicle created.');
}

if ($action === 'update') {
    $id = api_id();
    $fields = ['name', 'type', 'plate_number', 'capacity', 'driver_id', 'status'];
    $data = [];
    foreach ($fields as $f) {
        if (array_key_exists($f, $input)) {
            $data[$f] = ($input[$f] === '' && $f === 'driver_id') ? null : trim((string) $input[$f]);
        }
    }
    updateRow('vehicles', $id, $data);
    api_success([], 'Vehicle updated.');
}

if ($action === 'delete') {
    $id = api_id();
    query('UPDATE shipments SET vehicle_id = NULL WHERE vehicle_id = ?', [$id]);
    query('UPDATE drivers SET vehicle_id = NULL WHERE vehicle_id = ?', [$id]);
    deleteRow('vehicles', $id);
    api_success([], 'Vehicle deleted.');
}

api_error('Unknown action.', 400);
