<?php
/**
 * CargoFlow — Payments admin API
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'list') {
    $rows = fetchAll(
        'SELECT p.*, c.name AS customer_name, i.invoice_number
         FROM payments p
         LEFT JOIN customers c ON c.id = p.customer_id
         LEFT JOIN invoices i ON i.id = p.invoice_id
         ORDER BY p.created_at DESC'
    );
    json_response(['success' => true, 'data' => $rows]);
}

$input = api_input();

if ($action === 'create') {
    $amount = (float) ($input['amount'] ?? 0);
    if ($amount <= 0) {
        api_error('Amount must be greater than zero.');
    }
    $id = insertRow('payments', [
        'invoice_id'     => !empty($input['invoice_id']) ? (int) $input['invoice_id'] : null,
        'customer_id'    => !empty($input['customer_id']) ? (int) $input['customer_id'] : null,
        'amount'         => $amount,
        'method'         => trim($input['method'] ?? 'card'),
        'transaction_id' => trim($input['transaction_id'] ?? ''),
        'status'         => trim($input['status'] ?? 'completed'),
        'payment_date'   => ($input['payment_date'] ?? '') ?: date('Y-m-d H:i:s'),
        'notes'          => trim($input['notes'] ?? ''),
    ]);

    // Mark linked invoice paid if fully paid
    if (!empty($input['invoice_id']) && $input['status'] !== 'failed') {
        $inv = fetchOne('SELECT * FROM invoices WHERE id = ?', [(int) $input['invoice_id']]);
        if ($inv) {
            $paid = (float) fetchValue('SELECT COALESCE(SUM(amount),0) FROM payments WHERE invoice_id = ? AND status != "failed"', [(int) $input['invoice_id']]);
            if ($paid >= (float) $inv['total']) {
                updateRow('invoices', (int) $input['invoice_id'], ['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')]);
                notify('Payment received', 'Payment of ' . format_currency($amount) . ' recorded.', 'success', $_SESSION['user_id'] ?? null, 'admin/payments.php');
            }
        }
    }

    api_success(['id' => $id], 'Payment recorded.');
}

if ($action === 'delete') {
    $id = api_id();
    deleteRow('payments', $id);
    api_success([], 'Payment deleted.');
}

api_error('Unknown action.', 400);
