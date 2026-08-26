<?php
/**
 * CargoFlow — Shipment tracking page
 * Supports ?tracking=CF-XXXXXXXXX query param and POST lookup.
 */
require_once __DIR__ . '/includes/bootstrap.php';

$page = 'track';
$pageTitle = 'Track Shipment — ' . setting('site_name', 'CargoFlow');
$pageDesc = 'Enter your CargoFlow tracking number to see the real-time status, timeline and location of your shipment.';

$tracking = trim($_GET['tracking'] ?? ($_POST['tracking'] ?? ''));
$shipment = null;
$events = [];
$error = null;

if ($tracking !== '') {
    ensure_package_image_column();
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

    if ($shipment) {
        $events = fetchAll(
            'SELECT * FROM tracking_events WHERE shipment_id = ? ORDER BY event_time ASC, id ASC',
            [$shipment['id']]
        );
    } else {
        $error = 'No shipment found for tracking number <strong>' . e($tracking) . '</strong>. Please double-check the number and try again.';
    }
}

require __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:3rem;">
    <div class="container">
        <div class="section-head text-center mx-auto mb-4 reveal">
            <div class="eyebrow mb-2">Track &amp; Trace</div>
            <h1 class="display-5">Track your shipment</h1>
            <p class="text-muted-2">Enter your tracking number for real-time status, event history and location.</p>
        </div>

        <div class="row justify-content-center mb-4">
            <div class="col-lg-9">
                <img class="page-visual reveal" src="<?= base_url('assets/img/pages/tracking-control.png') ?>" alt="Shipment tracking route dashboard on a tablet" loading="lazy">
            </div>
        </div>

        <!-- Lookup form -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-7">
                <div class="track-widget reveal">
                    <form action="<?= base_url('track.php') ?>" method="get" class="d-flex gap-2 flex-column flex-sm-row">
                        <input type="text" class="form-control" name="tracking" value="<?= e($tracking) ?>" placeholder="Enter tracking number (e.g. CF-8K4T9W2M7Q)" aria-label="Tracking number" required>
                        <button class="btn btn-brand flex-shrink-0" type="submit"><i class="bi bi-search me-1"></i> Track</button>
                    </form>
                </div>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= $error ?></span>
                    </div>
                    <div class="text-center text-muted-2 small">
                        Try demo number: <code>CF-8K4T9W2M7Q</code>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($shipment): ?>
            <?php
            $meta = status_meta($shipment['status']);
            $progress = [
                'pending' => 8, 'picked_up' => 30, 'in_transit' => 55,
                'out_for_delivery' => 82, 'delivered' => 100, 'on_hold' => 50,
                'customs' => 40, 'cancelled' => 0, 'returned' => 0,
            ];
            $pct = $progress[$shipment['status']] ?? 50;
            ?>
            <div class="row g-4 justify-content-center">
                <!-- Summary card -->
                <div class="col-lg-4">
                    <div class="form-card p-4 h-100 reveal">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="text-muted-2 small text-uppercase fw-semibold">Tracking number</div>
                                <div class="fs-4 fw-bold font-monospace"><?= e($shipment['tracking_number']) ?></div>
                            </div>
                            <?= status_label($shipment['status']) ?>
                        </div>

                        <!-- Package image -->
                        <div class="package-photo mb-4">
                            <img src="<?= e(package_image_url($shipment)) ?>" alt="Photo of the <?= e($shipment['package_type']) ?> for shipment <?= e($shipment['tracking_number']) ?>" loading="lazy">
                            <span class="package-photo-tag"><i class="bi bi-box-seam me-1"></i><?= e(title_case($shipment['package_type'])) ?></span>
                        </div>

                        <div class="progress mb-1" style="height:8px;">
                            <div class="progress-bar" role="progressbar" style="width:<?= $pct ?>%;" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted-2 mb-4">
                            <span>Picked up</span>
                            <span class="fw-semibold text-body"><?= e($meta[0]) ?></span>
                            <span>Delivered</span>
                        </div>

                        <dl class="row small mb-0">
                            <dt class="col-5 text-muted-2 fw-semibold">Service</dt>
                            <dd class="col-7 text-capitalize"><?= e($shipment['service_type']) ?></dd>

                            <dt class="col-5 text-muted-2 fw-semibold">Package type</dt>
                            <dd class="col-7 text-capitalize"><?= e($shipment['package_type']) ?></dd>

                            <?php if ($shipment['description']): ?>
                            <dt class="col-5 text-muted-2 fw-semibold">Contents</dt>
                            <dd class="col-7"><?= e($shipment['description']) ?></dd>
                            <?php endif; ?>

                            <dt class="col-5 text-muted-2 fw-semibold">Weight</dt>
                            <dd class="col-7"><?= ($shipment['weight'] !== null && $shipment['weight'] !== '') ? e(number_format((float) $shipment['weight'], 2)) . ' kg' : '—' ?></dd>

                            <dt class="col-5 text-muted-2 fw-semibold">Dimensions</dt>
                            <dd class="col-7"><?= e($shipment['dimensions'] ?: '—') ?></dd>

                            <dt class="col-5 text-muted-2 fw-semibold">Quantity</dt>
                            <dd class="col-7"><?= (int) $shipment['quantity'] ?> pcs</dd>

                            <dt class="col-5 text-muted-2 fw-semibold">Origin</dt>
                            <dd class="col-7"><?= e($shipment['origin'] ?: '—') ?>
                                <?php if ($shipment['origin_address']): ?>
                                <div class="text-muted-2" style="font-size:.8rem;"><?= e($shipment['origin_address']) ?></div>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-5 text-muted-2 fw-semibold">Destination</dt>
                            <dd class="col-7"><?= e($shipment['destination'] ?: '—') ?>
                                <?php if ($shipment['destination_address']): ?>
                                <div class="text-muted-2" style="font-size:.8rem;"><?= e($shipment['destination_address']) ?></div>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-5 text-muted-2 fw-semibold">Current location</dt>
                            <dd class="col-7"><?= e($shipment['current_location'] ?: '—') ?></dd>

                            <dt class="col-5 text-muted-2 fw-semibold">Est. delivery</dt>
                            <dd class="col-7"><?= format_date($shipment['estimated_delivery']) ?></dd>

                            <dt class="col-5 text-muted-2 fw-semibold">Carrier</dt>
                            <dd class="col-7"><?= e($shipment['carrier'] ?: 'CargoFlow') ?></dd>

                            <?php if ($shipment['driver_name']): ?>
                            <dt class="col-5 text-muted-2 fw-semibold">Driver</dt>
                            <dd class="col-7"><?= e($shipment['driver_name']) ?></dd>
                            <?php endif; ?>

                            <?php if ($shipment['vehicle_name']): ?>
                            <dt class="col-5 text-muted-2 fw-semibold">Vehicle</dt>
                            <dd class="col-7"><?= e($shipment['vehicle_name']) ?></dd>
                            <?php endif; ?>
                        </dl>

                        <hr>
                        <div class="d-flex justify-content-between small">
                            <span class="text-muted-2">Shipped</span>
                            <span><?= format_datetime($shipment['shipped_at']) ?></span>
                        </div>
                        <?php if ($shipment['delivered_at']): ?>
                        <div class="d-flex justify-content-between small mt-1">
                            <span class="text-muted-2">Delivered</span>
                            <span><?= format_datetime($shipment['delivered_at']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Timeline + map -->
                <div class="col-lg-8">
                    <div class="form-card p-4 mb-4 reveal">
                        <h5 class="fw-bold mb-4"><i class="bi bi-clock-history me-2 text-primary"></i>Tracking timeline</h5>
                        <?php if ($events): ?>
                        <div class="timeline">
                            <?php
                            $last = count($events) - 1;
                            foreach ($events as $i => $ev):
                                $isLast = ($i === $last);
                                $cls = $ev['status'] === 'delivered' ? 'completed' : ($isLast ? 'current' : 'completed');
                            ?>
                            <div class="timeline-item <?= $cls ?>">
                                <div class="timeline-dot"><i class="bi bi-<?= $cls === 'completed' ? 'check-lg' : 'dot' ?>"></i></div>
                                <div class="d-flex flex-wrap justify-content-between gap-1">
                                    <span class="tl-status text-capitalize"><?= e(str_replace('_', ' ', $ev['status'])) ?></span>
                                    <span class="tl-time"><i class="bi bi-calendar3 me-1"></i><?= format_datetime($ev['event_time']) ?></span>
                                </div>
                                <?php if ($ev['location']): ?>
                                <div class="text-muted-2 small"><i class="bi bi-geo-alt me-1"></i><?= e($ev['location']) ?></div>
                                <?php endif; ?>
                                <?php if ($ev['description']): ?>
                                <div class="small mt-1"><?= e($ev['description']) ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                            <p class="text-muted-2 mb-0">Tracking events will appear here once the shipment is processed.</p>
                        <?php endif; ?>
                    </div>

                    <div class="form-card reveal">
                        <div class="map-holder" id="trackingMap" style="height:320px;"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($shipment): ?>
<script>
(function () {
    'use strict';
    var holder = document.getElementById('trackingMap');
    var mapUrl = 'https://www.openstreetmap.org/export/embed.html?bbox=-140%2C20%2C30%2C65&layer=mapnik&marker=48.8566%2C2.3522';
    holder.innerHTML = '<iframe title="Shipment map" src="' + mapUrl + '" ' +
        'style="width:100%;height:100%;border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
})();
</script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
