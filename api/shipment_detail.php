<?php
/**
 * CargoFlow — Single shipment detail (with events) for admin
 * GET /api/shipment_detail.php?id=123
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();
ensure_shipment_columns();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    api_error('A valid shipment id is required.');
}

$shipment = fetchOne(
    'SELECT s.*, c.name AS customer_name, c.email AS customer_email,
            d.name AS driver_name, v.name AS vehicle_name
     FROM shipments s
     LEFT JOIN customers c ON c.id = s.customer_id
     LEFT JOIN drivers d ON d.id = s.driver_id
     LEFT JOIN vehicles v ON v.id = s.vehicle_id
     WHERE s.id = ?',
    [$id]
);

if (!$shipment) {
    api_error('Shipment not found.', 404);
}

$events = fetchAll('SELECT * FROM tracking_events WHERE shipment_id = ? ORDER BY event_time ASC, id ASC', [$id]);

json_response(['success' => true, 'shipment' => $shipment, 'events' => $events]);
