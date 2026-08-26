<?php
/**
 * CargoFlow — Invoices admin API
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'list') {
    $rows = fetchAll(
        'SELECT i.*, c.name AS customer_name, s.tracking_number
         FROM invoices i
         LEFT JOIN customers c ON c.id = i.customer_id
         LEFT JOIN shipments s ON s.id = i.shipment_id
         ORDER BY i.created_at DESC'
    );
    json_response(['success' => true, 'data' => $rows]);
}

$input = api_input();

if ($action === 'create') {
    $amount = (float) ($input['amount'] ?? 0);
    if ($amount <= 0) {
        api_error('Amount must be greater than zero.');
    }
    $taxRate = (float) setting('tax_rate', '8.5');
    $tax = round($amount * $taxRate / 100, 2);
    $total = round($amount + $tax, 2);

    $id = insertRow('invoices', [
        'invoice_number' => generate_invoice_number(),
        'shipment_id'    => !empty($input['shipment_id']) ? (int) $input['shipment_id'] : null,
        'customer_id'    => !empty($input['customer_id']) ? (int) $input['customer_id'] : null,
        'amount'         => $amount,
        'tax'            => $tax,
        'total'          => $total,
        'status'         => trim($input['status'] ?? 'unpaid'),
        'issue_date'     => ($input['issue_date'] ?? '') ?: date('Y-m-d'),
        'due_date'       => ($input['due_date'] ?? '') ?: date('Y-m-d', strtotime('+14 days')),
    ]);
    api_success(['id' => $id], 'Invoice created.');
}

if ($action === 'update') {
    $id = api_id();
    $fields = ['amount', 'tax', 'total', 'status', 'issue_date', 'due_date', 'shipment_id', 'customer_id'];
    $data = [];
    foreach ($fields as $f) {
        if (array_key_exists($f, $input)) {
            $data[$f] = ($input[$f] === '' && in_array($f, ['shipment_id', 'customer_id', 'due_date'])) ? null : $input[$f];
        }
    }
    if (isset($data['status']) && $data['status'] === 'paid' && !fetchValue('SELECT paid_at FROM invoices WHERE id = ?', [$id])) {
        $data['paid_at'] = date('Y-m-d H:i:s');
    }
    updateRow('invoices', $id, $data);
    api_success([], 'Invoice updated.');
}

if ($action === 'delete') {
    $id = api_id();
    query('DELETE FROM payments WHERE invoice_id = ?', [$id]);
    deleteRow('invoices', $id);
    api_success([], 'Invoice deleted.');
}

api_error('Unknown action.', 400);
