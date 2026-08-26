<?php
/**
 * CargoFlow — Shipments admin API
 * GET  /api/shipments.php            → list (with optional search/status/pagination)
 * POST /api/shipments.php action=create|update|delete|add_event
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'list') {
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 15;
    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = '(s.tracking_number LIKE ? OR c.name LIKE ? OR s.destination LIKE ? OR s.origin LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like, $like);
    }
    if ($status !== '') {
        $where[] = 's.status = ?';
        $params[] = $status;
    }
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $total = (int) fetchValue("SELECT COUNT(*) FROM shipments s LEFT JOIN customers c ON c.id = s.customer_id $whereSql", $params);
    $offset = ($page - 1) * $perPage;

    $rows = fetchAll(
        "SELECT s.*, c.name AS customer_name, d.name AS driver_name
         FROM shipments s
         LEFT JOIN customers c ON c.id = s.customer_id
         LEFT JOIN drivers d ON d.id = s.driver_id
         $whereSql
         ORDER BY s.created_at DESC
         LIMIT $perPage OFFSET $offset",
        $params
    );

    json_response([
        'success' => true,
        'data' => $rows,
        'meta' => ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'pages' => (int) ceil($total / $perPage)],
    ]);
}

if ($action === 'create') {
    $input = api_input();
    $origin = trim($input['origin'] ?? '');
    $destination = trim($input['destination'] ?? '');
    if ($origin === '' || $destination === '') {
        api_error('Origin and destination are required.');
    }

    $tracking = generate_tracking_number();
    $status = trim($input['status'] ?? 'pending');

    $id = insertRow('shipments', [
        'tracking_number'    => $tracking,
        'customer_id'        => !empty($input['customer_id']) ? (int) $input['customer_id'] : null,
        'origin'             => $origin,
        'destination'        => $destination,
        'origin_address'     => trim($input['origin_address'] ?? ''),
        'destination_address'=> trim($input['destination_address'] ?? ''),
        'service_type'       => trim($input['service_type'] ?? 'standard'),
        'package_type'       => trim($input['package_type'] ?? 'parcel'),
        'weight'             => is_numeric($input['weight'] ?? '') ? (float) $input['weight'] : null,
        'dimensions'         => trim($input['dimensions'] ?? ''),
        'quantity'           => max(1, (int) ($input['quantity'] ?? 1)),
        'description'        => trim($input['description'] ?? ''),
        'carrier'            => trim($input['carrier'] ?? 'CargoFlow'),
        'driver_id'          => !empty($input['driver_id']) ? (int) $input['driver_id'] : null,
        'vehicle_id'         => !empty($input['vehicle_id']) ? (int) $input['vehicle_id'] : null,
        'status'             => $status,
        'current_location'   => trim($input['current_location'] ?? $origin),
        'estimated_delivery' => $input['estimated_delivery'] ?: null,
        'shipped_at'         => ($input['shipped_at'] ?? '') ?: null,
        'price'              => is_numeric($input['price'] ?? '') ? (float) $input['price'] : 0,
        'currency'           => trim($input['currency'] ?? 'USD'),
        'notes'              => trim($input['notes'] ?? ''),
        'created_by'         => $_SESSION['user_id'] ?? null,
    ]);

    // Initial tracking event
    insertRow('tracking_events', [
        'shipment_id' => $id,
        'status'      => $status,
        'location'    => trim($input['current_location'] ?? $origin),
        'description' => 'Shipment created and registered in the system',
        'event_time'  => date('Y-m-d H:i:s'),
        'created_by'  => $_SESSION['user_id'] ?? null,
    ]);

    notify('New shipment created', "Shipment $tracking has been created.", 'success', $_SESSION['user_id'] ?? null, 'admin/shipments.php');

    api_success(['id' => $id, 'tracking_number' => $tracking], 'Shipment created successfully.');
}

if ($action === 'update') {
    $id = api_id();
    $input = api_input();
    $allowed = [
        'customer_id', 'origin', 'destination', 'origin_address', 'destination_address',
        'service_type', 'package_type', 'weight', 'dimensions', 'quantity', 'description',
        'carrier', 'driver_id', 'vehicle_id', 'status', 'current_location',
        'estimated_delivery', 'shipped_at', 'delivered_at', 'price', 'currency', 'notes',
    ];
    $data = [];
    foreach ($allowed as $field) {
        if (array_key_exists($field, $input)) {
            $val = $input[$field];
            if ($val === '' || $val === null) {
                // allow nullable clears for FK-ish/date fields
                if (in_array($field, ['customer_id', 'driver_id', 'vehicle_id', 'estimated_delivery', 'shipped_at', 'delivered_at', 'origin_address', 'destination_address', 'dimensions', 'notes'])) {
                    $data[$field] = null;
                } elseif (in_array($field, ['weight', 'price'])) {
                    $data[$field] = null;
                } else {
                    continue;
                }
                continue;
            }
            $data[$field] = $val;
        }
    }

    if (empty($data)) {
        api_error('No fields to update.');
    }

    // If delivered_at set, mark delivered
    if (isset($data['status']) && $data['status'] === 'delivered' && empty($data['delivered_at'])) {
        $data['delivered_at'] = date('Y-m-d H:i:s');
    }

    updateRow('shipments', $id, $data);
    api_success(['id' => $id], 'Shipment updated.');
}

if ($action === 'add_event') {
    $shipmentId = (int) ($_POST['shipment_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $eventTime = trim($_POST['event_time'] ?? '');

    if ($shipmentId <= 0 || $status === '') {
        api_error('Shipment and status are required.');
    }

    $eventTime = $eventTime ?: date('Y-m-d H:i:s');

    insertRow('tracking_events', [
        'shipment_id' => $shipmentId,
        'status'      => $status,
        'location'    => $location,
        'description' => $description,
        'event_time'  => $eventTime,
        'created_by'  => $_SESSION['user_id'] ?? null,
    ]);

    // Sync shipment status/location
    updateRow('shipments', $shipmentId, [
        'status'           => $status,
        'current_location' => $location !== '' ? $location : null,
    ]);
    if ($status === 'delivered') {
        updateRow('shipments', $shipmentId, ['delivered_at' => date('Y-m-d H:i:s')]);
    }

    api_success([], 'Tracking event added.');
}

if ($action === 'delete') {
    $id = api_id();
    // tracking_events cascade via FK; remove invoice/payment references first.
    query('DELETE FROM payments WHERE invoice_id IN (SELECT id FROM invoices WHERE shipment_id = ?)', [$id]);
    query('DELETE FROM invoices WHERE shipment_id = ?', [$id]);
    deleteRow('shipments', $id);
    api_success([], 'Shipment deleted.');
}

api_error('Unknown action.', 400);
