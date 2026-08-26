<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'payments';
$adminTitle = 'Payments';
$customers = fetchAll('SELECT id, name FROM customers ORDER BY name ASC');
$invoices = fetchAll('SELECT id, invoice_number, total FROM invoices WHERE status != "paid" ORDER BY created_at DESC LIMIT 200');
$symbol = setting('currency_symbol', '$');
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Payments</h2>
        <p class="text-muted-2 mb-0 small">Record and reconcile customer payments.</p>
    </div>
    <button class="btn btn-brand btn-sm" onclick="openCreate()"><i class="bi bi-plus-lg me-1"></i> Record Payment</button>
</div>

<div class="card-admin">
    <div class="table-responsive">
        <table class="table table-admin">
            <thead><tr><th>Date</th><th>Customer</th><th>Invoice</th><th>Method</th><th class="text-end">Amount</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="tbody"><tr><td colspan="7" class="text-center text-muted-2 py-5">Loading…</td></tr></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="payModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form data-modal-form action="<?= base_url('api/payments.php') ?>" data-on-success="onSaved">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div class="modal-header"><h5 class="modal-title">Record Payment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-12"><label class="form-label">Customer</label>
                    <select class="form-select" name="customer_id" id="f_customer"><option value="">— None —</option>
                        <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12"><label class="form-label">Invoice (optional)</label>
                    <select class="form-select" name="invoice_id" id="f_invoice"><option value="">— None —</option>
                        <?php foreach ($invoices as $i): ?><option value="<?= $i['id'] ?>"><?= e($i['invoice_number']) ?> — <?= e($symbol . number_format((float)$i['total'], 2)) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Amount (<?= e($symbol) ?>) *</label><input type="number" step="0.01" class="form-control" name="amount" id="f_amount" required></div>
                <div class="col-md-6"><label class="form-label">Method</label>
                    <select class="form-select" name="method"><option value="card">Card</option><option value="bank">Bank transfer</option><option value="paypal">PayPal</option><option value="cash">Cash</option><option value="transfer">Other</option></select>
                </div>
                <div class="col-md-6"><label class="form-label">Transaction ID</label><input class="form-control" name="transaction_id" id="f_txn"></div>
                <div class="col-md-6"><label class="form-label">Status</label>
                    <select class="form-select" name="status"><option value="completed">Completed</option><option value="pending">Pending</option><option value="failed">Failed</option><option value="refunded">Refunded</option></select>
                </div>
                <div class="col-12"><label class="form-label">Notes</label><input class="form-control" name="notes" id="f_notes"></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Save</button></div>
    </form>
</div></div></div>

<script>
var modal = new bootstrap.Modal(document.getElementById('payModal'));
var symbol = '<?= e($symbol) ?>';
var statusBadge = { completed:'success', pending:'warning', failed:'danger', refunded:'secondary' };
function load() {
    CF.api('<?= base_url('api/payments.php') ?>').then(function (json) {
        document.getElementById('tbody').innerHTML = json.data.map(function (p) {
            return '<tr>' +
                '<td class="small">' + (p.payment_date ? p.payment_date.slice(0,16).replace('T',' ') : '—') + '</td>' +
                '<td>' + (p.customer_name||'—') + '</td>' +
                '<td>' + (p.invoice_number ? '<span class="font-monospace small">' + p.invoice_number + '</span>' : '—') + '</td>' +
                '<td class="text-capitalize">' + p.method + '</td>' +
                '<td class="text-end fw-semibold">' + symbol + Number(p.amount).toFixed(2) + '</td>' +
                '<td><span class="badge bg-' + (statusBadge[p.status]||'secondary') + ' rounded-pill">' + p.status + '</span></td>' +
                '<td class="text-end"><button class="btn btn-sm btn-ghost text-danger" onclick="del(' + p.id + ')"><i class="bi bi-trash"></i></button></td></tr>';
        }).join('') || '<tr><td colspan="7" class="empty-state"><i class="bi bi-credit-card"></i><div>No payments recorded</div></td></tr>';
    });
}
function openCreate() {
    document.getElementById('payModal').querySelector('form').reset();
    modal.show();
}
function onSaved() { modal.hide(); load(); }
function del(id) {
    confirmAction('Delete this payment?', function () {
        CF.api('<?= base_url('api/payments.php') ?>', { action:'delete', id:id }).then(function (json) {
            CF.toast(json.message, json.success?'success':'danger'); if (json.success) load();
        });
    });
}
load();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
