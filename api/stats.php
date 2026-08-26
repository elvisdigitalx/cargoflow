<?php
/**
 * CargoFlow — Dashboard statistics API
 * GET /api/stats.php → KPIs, status breakdown, monthly revenue & volume, recent activity
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();

$totalShipments = (int) fetchValue('SELECT COUNT(*) FROM shipments');
$inTransit = (int) fetchValue("SELECT COUNT(*) FROM shipments WHERE status IN ('picked_up','in_transit','out_for_delivery')");
$delivered = (int) fetchValue("SELECT COUNT(*) FROM shipments WHERE status = 'delivered'");
$onHold = (int) fetchValue("SELECT COUNT(*) FROM shipments WHERE status IN ('on_hold','customs')");

$totalCustomers = (int) fetchValue('SELECT COUNT(*) FROM customers');
$activeDrivers = (int) fetchValue("SELECT COUNT(*) FROM drivers WHERE status != 'off_duty'");

// Revenue (paid invoices)
$revenue = (float) fetchValue("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status = 'paid'");
$outstanding = (float) fetchValue("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status IN ('unpaid','overdue')");

// Status breakdown
$statusBreakdown = fetchAll(
    'SELECT status, COUNT(*) AS count FROM shipments GROUP BY status ORDER BY count DESC'
);

// Monthly revenue (last 12 months)
$monthlyRevenue = fetchAll(
    "SELECT DATE_FORMAT(payment_date, '%Y-%m') AS month, SUM(amount) AS total
     FROM payments
     WHERE status = 'completed' AND payment_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
     GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
     ORDER BY month ASC"
);

// Monthly volume (last 12 months)
$monthlyVolume = fetchAll(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count
     FROM shipments
     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
     GROUP BY DATE_FORMAT(created_at, '%Y-%m')
     ORDER BY month ASC"
);

// Recent shipments (prefer the new sender/receiver fields, fall back to legacy customer)
$senderSelect = shipment_column_exists('sender_name')
    ? "COALESCE(NULLIF(s.sender_name, ''), c.name) AS customer_name, s.sender_name, s.receiver_name"
    : 'c.name AS customer_name';
$recentShipments = fetchAll(
    "SELECT s.tracking_number, s.status, s.destination, s.created_at, $senderSelect
     FROM shipments s LEFT JOIN customers c ON c.id = s.customer_id
     ORDER BY s.created_at DESC LIMIT 6"
);

// Recent payments
$recentPayments = fetchAll(
    'SELECT p.amount, p.method, p.payment_date, c.name AS customer_name
     FROM payments p LEFT JOIN customers c ON c.id = p.customer_id
     ORDER BY p.created_at DESC LIMIT 6'
);

json_response([
    'success' => true,
    'data' => [
        'kpis' => [
            'total_shipments' => $totalShipments,
            'in_transit'      => $inTransit,
            'delivered'       => $delivered,
            'on_hold'         => $onHold,
            'customers'       => $totalCustomers,
            'drivers'         => $activeDrivers,
            'revenue'         => $revenue,
            'outstanding'     => $outstanding,
        ],
        'status_breakdown' => $statusBreakdown,
        'monthly_revenue'  => $monthlyRevenue,
        'monthly_volume'   => $monthlyVolume,
        'recent_shipments' => $recentShipments,
        'recent_payments'  => $recentPayments,
    ],
]);
