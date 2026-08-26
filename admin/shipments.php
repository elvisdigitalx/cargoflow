<?php
/**
 * CargoFlow — Shipments management (CRUD)
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'shipments';
$adminTitle = 'Shipments';

$customers = fetchAll('SELECT id, name FROM customers ORDER BY name ASC');
$drivers = fetchAll('SELECT id, name FROM drivers ORDER BY name ASC');
$vehicles = fetchAll('SELECT id, name FROM vehicles ORDER BY name ASC');
$statuses = shipment_statuses();

require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Shipments</h2>
        <p class="text-muted-2 mb-0 small">Create, track and manage every shipment in one place.</p>
    </div>
    <button class="btn btn-brand btn-sm" data-bs-toggle="modal" data-bs-target="#shipmentModal" onclick="openCreate()">
        <i class="bi bi-plus-lg me-1"></i> New Shipment
    </button>
</div>

<div class="card-admin">
    <div class="card-header d-flex flex-wrap gap-2 align-items-center">
        <div class="input-group input-group-sm" style="max-width:280px;">
            <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Search tracking, customer, location…">
        </div>
        <select class="form-select form-select-sm" id="statusFilter" style="max-width:180px;">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $key => $meta): ?>
                <option value="<?= e($key) ?>"><?= e($meta[0]) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-ghost btn-sm ms-auto" id="refreshBtn"><i class="bi bi-arrow-clockwise"></i></button>
    </div>
    <div class="table-responsive">
        <table class="table table-admin align-middle">
            <thead>
                <tr>
                    <th>Tracking #</th>
                    <th>Customer</th>
                    <th>Route</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Est. Delivery</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody id="shipmentsBody">
                <tr><td colspan="7" class="text-center text-muted-2 py-5">Loading shipments…</td></tr>
            </tbody>
        </table>
    </div>
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="text-muted-2 small" id="paginationInfo"></span>
        <div class="btn-group btn-group-sm" id="paginationButtons"></div>
    </div>
</div>

<!-- ============ CREATE / EDIT MODAL ============ -->
<div class="modal fade" id="shipmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form data-modal-form action="<?= base_url('api/shipments.php') ?>" data-on-success="onShipmentSaved">
                <?= csrf_field() ?>
                <input type="hidden" name="action" id="shipmentAction" value="create">
                <input type="hidden" name="id" id="shipmentId" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="shipmentModalTitle">New Shipment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer</label>
                            <select class="form-select" name="customer_id" id="f_customer">
                                <option value="">— Walk-in / none —</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service type</label>
                            <select class="form-select" name="service_type" id="f_service">
                                <?php foreach (service_types() as $s): ?>
                                    <option value="<?= e($s) ?>"><?= e(title_case($s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Origin *</label>
                            <input type="text" class="form-control" name="origin" id="f_origin" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Destination *</label>
                            <input type="text" class="form-control" name="destination" id="f_destination" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Package type</label>
                            <select class="form-select" name="package_type" id="f_package">
                                <?php foreach (package_types() as $p): ?>
                                    <option value="<?= e($p) ?>"><?= e(title_case($p)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" step="0.01" class="form-control" name="weight" id="f_weight">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="quantity" id="f_quantity" value="1" min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned driver</label>
                            <select class="form-select" name="driver_id" id="f_driver">
                                <option value="">— None —</option>
                                <?php foreach ($drivers as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Assigned vehicle</label>
                            <select class="form-select" name="vehicle_id" id="f_vehicle">
                                <option value="">— None —</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?= $v['id'] ?>"><?= e($v['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="f_status">
                                <?php foreach ($statuses as $key => $meta): ?>
                                    <option value="<?= e($key) ?>"><?= e($meta[0]) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current location</label>
                            <input type="text" class="form-control" name="current_location" id="f_location">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Est. delivery date</label>
                            <input type="date" class="form-control" name="estimated_delivery" id="f_est">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Price</label>
                            <input type="number" step="0.01" class="form-control" name="price" id="f_price" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Currency</label>
                            <input type="text" class="form-control" name="currency" id="f_currency" value="USD">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Carrier</label>
                            <input type="text" class="form-control" name="carrier" id="f_carrier" value="CargoFlow">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dimensions</label>
                            <input type="text" class="form-control" name="dimensions" id="f_dimensions" placeholder="LxWxH cm">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description / contents</label>
                            <textarea class="form-control" name="description" id="f_description" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-brand">Save Shipment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============ DETAIL MODAL ============ -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-box-seam me-2 text-primary"></i>Shipment detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailBody"></div>
        </div>
    </div>
</div>

<script>
var state = { page: 1, search: '', status: '' };
var modal = new bootstrap.Modal(document.getElementById('shipmentModal'));
var detailModal = new bootstrap.Modal(document.getElementById('detailModal'));

function statusBadge(s) {
    var map = { pending:'secondary', picked_up:'info', in_transit:'primary', out_for_delivery:'warning', delivered:'success', on_hold:'warning', customs:'info', cancelled:'danger', returned:'danger' };
    return '<span class="badge bg-' + (map[s]||'secondary') + ' rounded-pill">' + s.replace(/_/g,' ') + '</span>';
}

function loadShipments() {
    var params = new URLSearchParams({ page: state.page, search: state.search, status: state.status });
    CF.api('<?= base_url('api/shipments.php') ?>?' + params.toString()).then(function (json) {
        if (!json.success) return;
        var tbody = document.getElementById('shipmentsBody');
        var rows = json.data;
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="empty-state"><i class="bi bi-inbox"></i><div>No shipments found</div></td></tr>';
        } else {
            tbody.innerHTML = rows.map(function (s) {
                return '<tr>' +
                    '<td><span class="tracking-chip">' + s.tracking_number + '</span></td>' +
                    '<td>' + (s.customer_name || '—') + '</td>' +
                    '<td><div class="small fw-semibold">' + (s.origin||'—') + '</div><div class="text-muted-2 small"><i class="bi bi-arrow-down"></i> ' + (s.destination||'—') + '</div></td>' +
                    '<td class="text-capitalize">' + s.service_type + '</td>' +
                    '<td>' + statusBadge(s.status) + '</td>' +
                    '<td>' + (s.estimated_delivery ? s.estimated_delivery : '—') + '</td>' +
                    '<td class="text-end text-nowrap">' +
                        '<button class="btn btn-sm btn-ghost" onclick="openDetail(' + s.id + ')" title="View"><i class="bi bi-eye"></i></button> ' +
                        '<button class="btn btn-sm btn-ghost" onclick="openEdit(' + s.id + ')" title="Edit"><i class="bi bi-pencil"></i></button> ' +
                        '<button class="btn btn-sm btn-ghost text-danger" onclick="delShipment(' + s.id + ')" title="Delete"><i class="bi bi-trash"></i></button>' +
                    '</td></tr>';
            }).join('');
        }
        var meta = json.meta || {};
        document.getElementById('paginationInfo').textContent = 'Showing ' + (rows.length ? (meta.page-1)*meta.per_page+1 : 0) + '–' + ((meta.page-1)*meta.per_page+rows.length) + ' of ' + meta.total;
        renderPagination(meta);
    });
}

function renderPagination(meta) {
    var wrap = document.getElementById('paginationButtons');
    var html = '';
    for (var i = 1; i <= meta.pages; i++) {
        html += '<button class="btn btn-ghost btn-sm' + (i === meta.page ? ' active' : '') + '" onclick="goPage(' + i + ')">' + i + '</button>';
    }
    wrap.innerHTML = html;
}
function goPage(p) { state.page = p; loadShipments(); }

function openCreate() {
    document.getElementById('shipmentModalTitle').textContent = 'New Shipment';
    document.getElementById('shipmentAction').value = 'create';
    document.getElementById('shipmentId').value = '';
    document.getElementById('shipmentModal').querySelector('form').reset();
    document.getElementById('f_service').value = 'standard';
    document.getElementById('f_package').value = 'parcel';
    document.getElementById('f_status').value = 'pending';
    document.getElementById('f_currency').value = 'USD';
    document.getElementById('f_quantity').value = '1';
}
function openEdit(id) {
    CF.api('<?= base_url('api/shipment_detail.php') ?>?id=' + id).then(function (json) {
        if (!json.success) return;
        var s = json.shipment;
        document.getElementById('shipmentModalTitle').textContent = 'Edit Shipment';
        document.getElementById('shipmentAction').value = 'update';
        document.getElementById('shipmentId').value = s.id;
        var f = document.getElementById('shipmentModal').querySelector('form');
        f.reset();
        ['customer_id','service_type','origin','destination','package_type','weight','quantity','driver_id','vehicle_id','status','current_location','estimated_delivery','price','currency','carrier','dimensions','description'].forEach(function (k) {
            var el = f.elements[k];
            if (el && s[k] !== null && s[k] !== undefined) el.value = s[k];
        });
        modal.show();
    });
}
function onShipmentSaved(json) {
    modal.hide();
    loadShipments();
}
function delShipment(id) {
    confirmAction('Delete this shipment and all its tracking history?', function () {
        CF.api('<?= base_url('api/shipments.php') ?>', { action: 'delete', id: id }).then(function (json) {
            CF.toast(json.message || 'Deleted', json.success ? 'success' : 'danger');
            if (json.success) loadShipments();
        });
    });
}

function openDetail(id) {
    CF.api('<?= base_url('api/shipment_detail.php') ?>?id=' + id).then(function (json) {
        if (!json.success) return;
        var s = json.shipment;
        var events = json.events;
        var timeline = events.map(function (e, i) {
            var last = i === events.length - 1;
            var cls = e.status === 'delivered' ? 'completed' : (last ? 'current' : 'completed');
            return '<div class="timeline-item ' + cls + '">' +
                '<div class="timeline-dot"><i class="bi bi-' + (cls === 'completed' ? 'check-lg' : 'dot') + '"></i></div>' +
                '<div class="d-flex justify-content-between"><span class="fw-semibold text-capitalize">' + e.status.replace(/_/g,' ') + '</span><span class="text-muted-2 small">' + e.event_time + '</span></div>' +
                (e.location ? '<div class="text-muted-2 small"><i class="bi bi-geo-alt me-1"></i>' + e.location + '</div>' : '') +
                (e.description ? '<div class="small">' + e.description + '</div>' : '') +
                '</div>';
        }).join('') || '<p class="text-muted-2">No events yet.</p>';

        document.getElementById('detailBody').innerHTML =
            '<div class="d-flex justify-content-between align-items-start mb-3">' +
                '<div><div class="text-muted-2 small text-uppercase">Tracking #</div><div class="fs-5 fw-bold font-monospace">' + s.tracking_number + '</div></div>' +
                statusBadge(s.status) + '</div>' +
            '<div class="row small mb-4">' +
                '<div class="col-6 mb-2"><span class="text-muted-2">Origin:</span> ' + (s.origin||'—') + '</div>' +
                '<div class="col-6 mb-2"><span class="text-muted-2">Destination:</span> ' + (s.destination||'—') + '</div>' +
                '<div class="col-6 mb-2"><span class="text-muted-2">Customer:</span> ' + (s.customer_name||'—') + '</div>' +
                '<div class="col-6 mb-2"><span class="text-muted-2">Driver:</span> ' + (s.driver_name||'—') + '</div>' +
                '<div class="col-6 mb-2"><span class="text-muted-2">Vehicle:</span> ' + (s.vehicle_name||'—') + '</div>' +
                '<div class="col-6 mb-2"><span class="text-muted-2">Weight:</span> ' + (s.weight||'—') + ' kg</div>' +
                '<div class="col-6 mb-2"><span class="text-muted-2">Service:</span> <span class="text-capitalize">' + s.service_type + '</span></div>' +
                '<div class="col-6 mb-2"><span class="text-muted-2">Est. delivery:</span> ' + (s.estimated_delivery||'—') + '</div>' +
            '</div>' +
            '<h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Tracking timeline</h6>' +
            '<div class="timeline">' + timeline + '</div>' +
            '<hr>' +
            '<h6 class="fw-bold mb-3">Add tracking event</h6>' +
            '<form data-modal-form action="<?= base_url('api/shipments.php') ?>" data-on-success="onEventAdded">' +
                '<?= csrf_field() ?>' +
                '<input type="hidden" name="action" value="add_event">' +
                '<input type="hidden" name="shipment_id" value="' + s.id + '">' +
                '<div class="row g-2">' +
                    '<div class="col-md-4"><select class="form-select" name="status">' +
                        Object.keys(<?= json_encode($statuses) ?>).map(function (k) { return '<option value="' + k + '">' + k.replace(/_/g,' ') + '</option>'; }).join('') +
                    '</select></div>' +
                    '<div class="col-md-4"><input type="text" class="form-control" name="location" placeholder="Location"></div>' +
                    '<div class="col-md-4"><input type="datetime-local" class="form-control" name="event_time"></div>' +
                    '<div class="col-12"><input type="text" class="form-control" name="description" placeholder="Description (optional)"></div>' +
                    '<div class="col-12 text-end"><button type="submit" class="btn btn-brand btn-sm">Add event</button></div>' +
                '</div>' +
            '</form>';
        detailModal.show();
    });
}
function onEventAdded(json) {
    detailModal.hide();
    loadShipments();
    CF.toast(json.message || 'Event added', 'success');
}

document.getElementById('searchInput').addEventListener('input', debounce(function () {
    state.search = this.value; state.page = 1; loadShipments();
}, 350));
document.getElementById('statusFilter').addEventListener('change', function () {
    state.status = this.value; state.page = 1; loadShipments();
});
document.getElementById('refreshBtn').addEventListener('click', loadShipments);

function debounce(fn, ms) { var t; return function () { clearTimeout(t); t = setTimeout(fn.bind(this), ms); }; }

loadShipments();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
