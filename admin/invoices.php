<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'invoices';
$adminTitle = 'Invoices';
$customers = fetchAll('SELECT id, name FROM customers ORDER BY name ASC');
$shipments = fetchAll('SELECT id, tracking_number FROM shipments ORDER BY created_at DESC LIMIT 200');
$symbol = setting('currency_symbol', '$');
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Invoices</h2>
        <p class="text-muted-2 mb-0 small">Generate and manage invoices for your shipments.</p>
    </div>
    <button class="btn btn-brand btn-sm" onclick="openCreate()"><i class="bi bi-plus-lg me-1"></i> New Invoice</button>
</div>

<div class="row g-3 mb-4" id="summaryRow"></div>

<div class="card-admin">
    <div class="table-responsive">
        <table class="table table-admin">
            <thead><tr><th>Invoice #</th><th>Customer</th><th>Shipment</th><th>Issue date</th><th>Due date</th><th class="text-end">Total</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="tbody"><tr><td colspan="8" class="text-center text-muted-2 py-5">Loading…</td></tr></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="invModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form data-modal-form action="<?= base_url('api/invoices.php') ?>" data-on-success="onSaved">
        <?= csrf_field() ?>
        <input type="hidden" name="action" id="action" value="create">
        <input type="hidden" name="id" id="id">
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">New Invoice</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-12"><label class="form-label">Customer</label>
                    <select class="form-select" name="customer_id" id="f_customer"><option value="">— None —</option>
                        <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12"><label class="form-label">Shipment (optional)</label>
                    <select class="form-select" name="shipment_id" id="f_shipment"><option value="">— None —</option>
                        <?php foreach ($shipments as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['tracking_number']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">Amount (<?= e($symbol) ?>) *</label><input type="number" step="0.01" class="form-control" name="amount" id="f_amount" required oninput="updateTotals()"></div>
                <div class="col-md-6"><label class="form-label">Status</label>
                    <select class="form-select" name="status" id="f_status"><option value="unpaid">Unpaid</option><option value="draft">Draft</option><option value="paid">Paid</option><option value="overdue">Overdue</option></select>
                </div>
                <div class="col-md-6"><label class="form-label">Issue date</label><input type="date" class="form-control" name="issue_date" id="f_issue"></div>
                <div class="col-md-6"><label class="form-label">Due date</label><input type="date" class="form-control" name="due_date" id="f_due"></div>
                <div class="col-12"><div class="alert alert-light border mb-0 small" id="totalPreview">Tax (<?= e(setting('tax_rate', '8.5')) ?>%) + total will be calculated automatically.</div></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button><button class="btn btn-brand">Save</button></div>
    </form>
</div></div></div>

<script>
var modal = new bootstrap.Modal(document.getElementById('invModal'));
var taxRate = <?= (float) setting('tax_rate', '8.5') ?>;
var symbol = '<?= e($symbol) ?>';
var statusBadge = { draft:'secondary', unpaid:'warning', paid:'success', overdue:'danger', cancelled:'secondary' };

function updateTotals() {
    var amt = parseFloat(document.getElementById('f_amount').value) || 0;
    var tax = amt * taxRate / 100;
    document.getElementById('totalPreview').innerHTML = 'Tax: <b>' + symbol + tax.toFixed(2) + '</b> &nbsp; Total: <b>' + symbol + (amt + tax).toFixed(2) + '</b>';
}

function load() {
    CF.api('<?= base_url('api/invoices.php') ?>').then(function (json) {
        var rows = json.data;
        document.getElementById('tbody').innerHTML = rows.map(function (i) {
            return '<tr>' +
                '<td class="fw-semibold font-monospace">' + i.invoice_number + '</td>' +
                '<td>' + (i.customer_name||'—') + '</td>' +
                '<td>' + (i.tracking_number ? '<span class="tracking-chip">' + i.tracking_number + '</span>' : '—') + '</td>' +
                '<td>' + (i.issue_date||'—') + '</td>' +
                '<td>' + (i.due_date||'—') + '</td>' +
                '<td class="text-end fw-semibold">' + symbol + Number(i.total).toFixed(2) + '</td>' +
                '<td><span class="badge bg-' + (statusBadge[i.status]||'secondary') + ' rounded-pill">' + i.status + '</span></td>' +
                '<td class="text-end text-nowrap"><button class="btn btn-sm btn-ghost" onclick="markPaid(' + i.id + ')" title="Mark paid"><i class="bi bi-check2-circle"></i></button> ' +
                '<button class="btn btn-sm btn-ghost text-danger" onclick="del(' + i.id + ')"><i class="bi bi-trash"></i></button></td></tr>';
        }).join('') || '<tr><td colspan="8" class="empty-state"><i class="bi bi-receipt"></i><div>No invoices found</div></td></tr>';

        var paid = rows.filter(function (r) { return r.status === 'paid'; }).reduce(function (a, r) { return a + parseFloat(r.total); }, 0);
        var outstanding = rows.filter(function (r) { return ['unpaid','overdue'].includes(r.status); }).reduce(function (a, r) { return a + parseFloat(r.total); }, 0);
        document.getElementById('summaryRow').innerHTML =
            '<div class="col-md-3 col-6"><div class="card-admin p-3"><div class="text-muted-2 small">Total invoices</div><div class="fw-bold fs-5">' + rows.length + '</div></div></div>' +
            '<div class="col-md-3 col-6"><div class="card-admin p-3"><div class="text-muted-2 small">Collected</div><div class="fw-bold fs-5 text-success">' + symbol + paid.toFixed(2) + '</div></div></div>' +
            '<div class="col-md-3 col-6"><div class="card-admin p-3"><div class="text-muted-2 small">Outstanding</div><div class="fw-bold fs-5 text-danger">' + symbol + outstanding.toFixed(2) + '</div></div></div>' +
            '<div class="col-md-3 col-6"><div class="card-admin p-3"><div class="text-muted-2 small">Overdue</div><div class="fw-bold fs-5 text-warning">' + rows.filter(function (r) { return r.status === 'overdue'; }).length + '</div></div></div>';
    });
}
function openCreate() {
    document.getElementById('modalTitle').textContent = 'New Invoice';
    document.getElementById('action').value = 'create';
    document.getElementById('invModal').querySelector('form').reset();
    document.getElementById('f_status').value = 'unpaid';
    document.getElementById('f_issue').value = new Date().toISOString().slice(0,10);
    document.getElementById('f_due').value = new Date(Date.now()+14*86400000).toISOString().slice(0,10);
    updateTotals();
    modal.show();
}
function markPaid(id) {
    confirmAction('Mark this invoice as paid?', function () {
        CF.api('<?= base_url('api/invoices.php') ?>', { action:'update', id:id, status:'paid' }).then(function (json) {
            CF.toast(json.message, json.success?'success':'danger'); if (json.success) load();
        });
    });
}
function onSaved() { modal.hide(); load(); }
function del(id) {
    confirmAction('Delete this invoice?', function () {
        CF.api('<?= base_url('api/invoices.php') ?>', { action:'delete', id:id }).then(function (json) {
            CF.toast(json.message, json.success?'success':'danger'); if (json.success) load();
        });
    });
}
load();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
