<?php
/**
 * CargoFlow — Tracking lookup API (public)
 * GET /api/tracking.php?tracking=CF-XXXXXXXXX
 * Returns shipment + events as JSON.
 */
require_once __DIR__ . '/../includes/bootstrap.php';

$tracking = trim($_GET['tracking'] ?? ($_POST['tracking'] ?? ''));

if ($tracking === '') {
    json_response(['success' => false, 'message' => 'Tracking number is required.'], 400);
}

$shipment = fetchOne(
    'SELECT s.*, c.name AS customer_name, c.email AS customer_email,
            d.name AS driver_name, v.name AS vehicle_name
     FROM shipments s
     LEFT JOIN customers c ON c.id = s.customer_id
     LEFT JOIN drivers d ON d.id = s.driver_id
     LEFT JOIN vehicles v ON v.id = s.vehicle_id
     WHERE s.tracking_number = ?',
    [$tracking]
);

if (!$shipment) {
    json_response(['success' => false, 'message' => 'No shipment found for that tracking number.'], 404);
}

$events = fetchAll(
    'SELECT id, status, location, description, event_time
     FROM tracking_events WHERE shipment_id = ? ORDER BY event_time ASC, id ASC',
    [$shipment['id']]
);

$meta = status_meta($shipment['status']);

json_response([
    'success' => true,
    'data' => [
        'tracking_number' => $shipment['tracking_number'],
        'status'          => $shipment['status'],
        'status_label'    => $meta[0],
        'origin'          => $shipment['origin'],
        'destination'     => $shipment['destination'],
        'origin_address'  => $shipment['origin_address'],
        'destination_address' => $shipment['destination_address'],
        'current_location'=> $shipment['current_location'],
        'service_type'    => $shipment['service_type'],
        'package_type'    => $shipment['package_type'],
        'weight'          => $shipment['weight'],
        'dimensions'      => $shipment['dimensions'],
        'quantity'        => (int) $shipment['quantity'],
        'description'     => $shipment['description'],
        'carrier'         => $shipment['carrier'],
        'driver_name'     => $shipment['driver_name'],
        'vehicle_name'    => $shipment['vehicle_name'],
        'estimated_delivery' => $shipment['estimated_delivery'],
        'shipped_at'      => $shipment['shipped_at'],
        'delivered_at'    => $shipment['delivered_at'],
        'events'          => $events,
    ],
]);
