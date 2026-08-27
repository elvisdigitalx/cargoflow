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
    ensure_shipment_columns();
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

<section class="section" style="padding-top:3.5rem;">
    <div class="container">
        <div class="section-head text-center mx-auto mb-4 reveal">
            <div class="eyebrow mb-2">Track &amp; Trace</div>
            <h1 class="display-5">Track your shipment</h1>
            <p class="text-muted-2">Enter your tracking number for real-time status, event history and location.</p>
        </div>

        <div class="row justify-content-center mb-4">
            <div class="col-lg-8">
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
            $senderDisplay = trim((string) ($shipment['sender_name'] ?? '')) ?: trim((string) ($shipment['customer_name'] ?? ''));
            $fromDetail = trim((string) ($shipment['sender_details'] ?? '')) ?: trim((string) ($shipment['origin_address'] ?? ''));
            $toName = trim((string) ($shipment['receiver_name'] ?? '')) ?: trim((string) ($shipment['destination'] ?? ''));
            $toDetail = trim((string) ($shipment['receiver_details'] ?? '')) ?: trim((string) ($shipment['destination_address'] ?? ''));

            // Shipment journey stepper
            $journey = [
                ['icon' => 'bi-box-seam',      'label' => 'Order placed'],
                ['icon' => 'bi-box-arrow-up',  'label' => 'Picked up'],
                ['icon' => 'bi-truck',         'label' => 'In transit'],
                ['icon' => 'bi-geo-alt-fill',  'label' => 'Out for delivery'],
                ['icon' => 'bi-check2-circle', 'label' => 'Delivered'],
            ];
            $stageMap = [
                'pending' => 0, 'picked_up' => 1, 'in_transit' => 2, 'customs' => 2,
                'on_hold' => 2, 'out_for_delivery' => 3, 'delivered' => 4,
                'cancelled' => -1, 'returned' => -1,
            ];
            $stage = $stageMap[$shipment['status']] ?? 2;
            $special = in_array($shipment['status'], ['customs', 'on_hold'], true) ? 'warn'
                     : (in_array($shipment['status'], ['cancelled', 'returned'], true) ? 'bad' : '');
            $isDelivered = $shipment['status'] === 'delivered';

            $eventIconMap = [
                'pending' => 'bi-box-seam', 'created' => 'bi-box-seam',
                'picked_up' => 'bi-box-arrow-up', 'pickup' => 'bi-box-arrow-up',
                'in_transit' => 'bi-truck', 'transit' => 'bi-truck',
                'customs' => 'bi-shield-check', 'on_hold' => 'bi-pause-circle-fill',
                'out_for_delivery' => 'bi-geo-alt-fill',
                'delivered' => 'bi-check2', 'cancelled' => 'bi-x-lg',
                'returned' => 'bi-arrow-return-left',
            ];

            // Detail rows
            $detailRows = [
                ['Service', title_case($shipment['service_type']), false],
                ['Package', title_case($shipment['package_type']), false],
            ];
            if (trim((string) $shipment['description']) !== '') {
                $detailRows[] = ['Contents', $shipment['description'], true];
            }
            $detailRows[] = ['Weight', ($shipment['weight'] !== null && $shipment['weight'] !== '') ? number_format((float) $shipment['weight'], 2) . ' kg' : '—', false];
            $detailRows[] = ['Dimensions', $shipment['dimensions'] ?: '—', false];
            $detailRows[] = ['Quantity', (int) $shipment['quantity'] . ' pcs', false];
            if (trim((string) $shipment['current_location']) !== '') {
                $detailRows[] = ['Current location', $shipment['current_location'], true];
            }
            $detailRows[] = ['Est. delivery', format_date($shipment['estimated_delivery']), false];
            $detailRows[] = ['Carrier', $shipment['carrier'] ?: 'CargoFlow', false];
            if (!empty($shipment['driver_name'])) { $detailRows[] = ['Driver', $shipment['driver_name'], false]; }
            if (!empty($shipment['vehicle_name'])) { $detailRows[] = ['Vehicle', $shipment['vehicle_name'], false]; }
            ?>

            <!-- Shipment hero -->
            <div class="ship-hero reveal">
                <div class="row align-items-center g-3">
                    <div class="col-lg-7">
                        <div class="text-muted-2 small text-uppercase fw-semibold mb-1"><i class="bi bi-receipt me-1"></i>Tracking number</div>
                        <div class="ship-track-no font-monospace"><?= e($shipment['tracking_number']) ?></div>
                    </div>
                    <div class="col-lg-5 text-lg-end"><?= status_label($shipment['status']) ?></div>
                </div>

                <div class="ship-chips">
                    <span class="ship-chip"><i class="bi bi-truck"></i><?= e(title_case($shipment['service_type'])) ?></span>
                    <span class="ship-chip"><i class="bi bi-box-seam"></i><?= e(title_case($shipment['package_type'])) ?></span>
                    <?php if ($shipment['weight'] !== null && $shipment['weight'] !== ''): ?>
                    <span class="ship-chip"><i class="bi bi-speedometer"></i><?= e(number_format((float) $shipment['weight'], 2)) ?> kg</span>
                    <?php endif; ?>
                    <span class="ship-chip"><i class="bi bi-stack"></i><?= (int) $shipment['quantity'] ?> pcs</span>
                    <span class="ship-chip"><i class="bi bi-calendar-event"></i>Est. <?= format_date($shipment['estimated_delivery']) ?></span>
                </div>

                <div class="ship-route">
                    <div class="route-node">
                        <span class="route-kicker"><i class="bi bi-circle-fill" style="font-size:.55rem;color:#06b6d4;"></i>From</span>
                        <div class="route-city"><?= e($shipment['origin'] ?: 'Origin') ?></div>
                        <?php if ($shipment['origin_address']): ?><div class="route-sub"><?= e($shipment['origin_address']) ?></div><?php endif; ?>
                    </div>
                    <div class="route-path">
                        <span class="route-line"></span>
                        <i class="bi bi-truck"></i>
                        <span class="route-line"></span>
                    </div>
                    <div class="route-node route-to">
                        <span class="route-kicker"><i class="bi bi-geo-alt-fill"></i>To</span>
                        <div class="route-city"><?= e($shipment['destination'] ?: 'Destination') ?></div>
                        <?php if ($shipment['destination_address']): ?><div class="route-sub"><?= e($shipment['destination_address']) ?></div><?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Journey stepper -->
            <div class="form-card ship-stepper-card reveal">
                <div class="ship-stepper">
                    <?php foreach ($journey as $i => $j):
                        $cls = '';
                        if ($isDelivered) {
                            $cls = 'done';
                        } elseif ($special && $i === $stage) {
                            $cls = $special;
                        } elseif ($i < $stage) {
                            $cls = 'done';
                        } elseif ($i === $stage) {
                            $cls = 'active';
                        }
                    ?>
                    <div class="ship-step <?= $cls ?>">
                        <div class="ss-dot"><i class="bi <?= e($j['icon']) ?>"></i></div>
                        <span class="ss-label"><?= e($j['label']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($special === 'warn'): ?>
                    <div class="alert alert-warning d-flex align-items-center gap-2 mt-3 mb-0 small">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>Your shipment is currently <strong><?= e(str_replace('_', ' ', $shipment['status'])) ?></strong>. Our team is on it — the timeline below has the latest update.</span>
                    </div>
                <?php elseif ($special === 'bad'): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 mt-3 mb-0 small">
                        <i class="bi bi-x-octagon-fill"></i>
                        <span>This shipment is <strong><?= e(str_replace('_', ' ', $shipment['status'])) ?></strong>. Please contact support if you expected a delivery.</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="row g-4">
                <!-- Details card -->
                <div class="col-lg-4">
                    <div class="form-card p-4 h-100 reveal">
                        <div class="package-photo mb-4">
                            <img src="<?= e(package_image_url($shipment)) ?>" alt="Photo of the <?= e($shipment['package_type']) ?> for shipment <?= e($shipment['tracking_number']) ?>" loading="lazy">
                            <span class="package-photo-tag"><i class="bi bi-box-seam me-1"></i><?= e(title_case($shipment['package_type'])) ?></span>
                        </div>

                        <div class="party-grid">
                            <div class="party-card from">
                                <span class="party-ic"><i class="bi bi-box-arrow-up"></i></span>
                                <div>
                                    <div class="party-role">From</div>
                                    <div class="party-name"><?= e($senderDisplay ?: '—') ?></div>
                                    <?php if ($fromDetail): ?><div class="party-detail"><?= e($fromDetail) ?></div><?php endif; ?>
                                </div>
                            </div>
                            <div class="party-card to">
                                <span class="party-ic"><i class="bi bi-geo-alt-fill"></i></span>
                                <div>
                                    <div class="party-role">To</div>
                                    <div class="party-name"><?= e($toName ?: '—') ?></div>
                                    <?php if ($toDetail): ?><div class="party-detail"><?= e($toDetail) ?></div><?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="detail-grid">
                            <?php foreach ($detailRows as $dr): ?>
                                <div class="detail-item <?= $dr[2] ? 'wide' : '' ?>">
                                    <span class="dl-label"><?= e($dr[0]) ?></span>
                                    <span class="dl-value"><?= e((string) $dr[1]) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="ship-dates">
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted-2"><i class="bi bi-box-arrow-up me-1"></i>Shipped</span>
                                <span class="fw-semibold"><?= format_datetime($shipment['shipped_at']) ?></span>
                            </div>
                            <?php if ($shipment['delivered_at']): ?>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted-2"><i class="bi bi-check2-circle me-1"></i>Delivered</span>
                                <span class="fw-semibold text-success"><?= format_datetime($shipment['delivered_at']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Timeline + map -->
                <div class="col-lg-8">
                    <div class="form-card p-4 mb-4 reveal">
                        <div class="card-title-icon"><i class="bi bi-clock-history"></i>Tracking timeline</div>
                        <?php if ($events): ?>
                        <div class="timeline">
                            <?php
                            $last = count($events) - 1;
                            foreach ($events as $i => $ev):
                                $isLast = ($i === $last);
                                $cls = $ev['status'] === 'delivered' ? 'completed' : ($isLast ? 'current' : 'completed');
                                $evIcon = $eventIconMap[$ev['status']] ?? ($cls === 'completed' ? 'bi-check-lg' : 'bi-circle-fill');
                            ?>
                            <div class="timeline-item <?= $cls ?>">
                                <div class="timeline-dot"><i class="bi <?= e($evIcon) ?>"></i></div>
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

                    <div class="form-card map-card p-3 reveal">
                        <div class="map-card-head px-2 pt-1"><i class="bi bi-geo-alt"></i>Live route map</div>
                        <div class="map-holder" id="trackingMap" style="height:340px;"></div>
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
