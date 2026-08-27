<?php
/**
 * CargoFlow — Public homepage
 */
require_once __DIR__ . '/includes/bootstrap.php';

$page = 'index';
$pageTitle = setting('site_name', 'CargoFlow') . ' — Logistics & Shipment Tracking Platform';
$pageDesc = 'Track shipments in real time, request instant quotes and manage global logistics with CargoFlow.';

require __DIR__ . '/includes/header.php';
?>

<!-- ================= HERO ================= -->
<section class="hero">
    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge-pill d-inline-flex align-items-center gap-2 mb-4">
                    <i class="bi bi-stars"></i> Trusted logistics partner since 2018
                </span>
                <h1 class="mb-4">
                    Ship anything, <span class="text-gradient">anywhere</span>,<br>
                    with total clarity.
                </h1>
                <p class="text-muted-2 lead mb-4 pe-lg-5">
                    CargoFlow gives you real-time visibility across every shipment —
                    from pickup to delivery — with instant quotes, live tracking
                    and a dashboard built for modern logistics teams.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="<?= base_url('quote.php') ?>" class="btn btn-brand btn-lg px-4">
                        Get a Free Quote <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <a href="<?= base_url('services.php') ?>" class="btn btn-ghost btn-lg px-4">
                        Explore Services
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-4 text-muted-2 small">
                    <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> 24/7 tracking</span>
                    <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> 120+ countries</span>
                    <span class="d-inline-flex align-items-center gap-2"><i class="bi bi-check-circle-fill text-success"></i> On-time guarantee</span>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="hero-illustration">
                    <div class="image-card">
                        <img class="page-visual" src="<?= base_url('assets/img/pages/hero-dashboard.png') ?>" alt="CargoFlow logistics dashboard with global shipment routes" loading="eager">
                    </div>
                    <!-- Tracking widget -->
                    <div class="track-widget mb-4">
                        <h5 class="fw-bold mb-1">Track your shipment</h5>
                        <p class="text-muted-2 small mb-3">Enter your tracking number to see live status.</p>
                        <form class="d-flex gap-2 flex-column flex-sm-row" action="<?= base_url('track.php') ?>" method="get">
                            <input type="text" class="form-control" name="tracking" placeholder="e.g. CF-8K4T9W2M7Q" aria-label="Tracking number" required>
                            <button class="btn btn-brand flex-shrink-0" type="submit"><i class="bi bi-search me-1"></i> Track</button>
                        </form>
                        <div class="small text-muted-2 mt-3 mb-0">
                            <i class="bi bi-lightbulb me-1"></i> Try demo number <code>CF-8K4T9W2M7Q</code>
                        </div>
                    </div>

                    <!-- Floating cards -->
                    <div class="float-card top-0 start-0" style="top:-24px; left:-16px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon fi-green"><i class="bi bi-truck"></i></span>
                            <div>
                                <div class="fw-bold small">Out for delivery</div>
                                <div class="text-muted-2" style="font-size:.75rem">Frankfurt, Germany</div>
                            </div>
                        </div>
                    </div>
                    <div class="float-card bottom-0 end-0" style="bottom:-20px; right:-8px; animation-delay:1.2s;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="icon fi-blue"><i class="bi bi-check-circle-fill"></i></span>
                            <div>
                                <div class="fw-bold small">Delivered</div>
                                <div class="text-muted-2" style="font-size:.75rem">Signed by D. Okafor</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= STATS ================= -->
<section class="stat-strip">
    <div class="container">
        <div class="stats-panel reveal">
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-box-seam-fill"></i></div>
                <div class="stat-value" data-count="128000">0</div>
                <div class="stat-label">Shipments delivered</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-globe2"></i></div>
                <div class="stat-value" data-count="120">0</div>
                <div class="stat-label">Countries served</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-patch-check-fill"></i></div>
                <div class="stat-value" data-count="99.2" data-decimals="1">0</div>
                <div class="stat-label">% On-time delivery</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="bi bi-building-fill-check"></i></div>
                <div class="stat-value" data-count="2400">0</div>
                <div class="stat-label">Business customers</div>
            </div>
        </div>
    </div>
</section>

<!-- ================= FEATURES ================= -->
<section class="section bg-surface-2">
    <div class="container">
        <div class="section-head text-center mx-auto mb-5 reveal">
            <div class="eyebrow mb-2">Why CargoFlow</div>
            <h2 class="display-6">Logistics without the guesswork</h2>
            <p class="text-muted-2">Everything you need to move goods confidently across town or around the globe.</p>
        </div>
        <div class="row g-4">
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="feature-card media-flush h-100">
                    <div class="service-card-media">
                        <img src="<?= base_url('assets/img/pages/feature-tracking.png') ?>" alt="Driver viewing live GPS tracking on a phone" loading="lazy">
                        <span class="media-icon fi-blue"><i class="bi bi-broadcast"></i></span>
                    </div>
                    <div class="card-body-pad">
                        <h5>Real-time tracking</h5>
                        <p class="text-muted-2 mb-0">Live GPS updates and a full event timeline for every parcel, pallet and container.</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="feature-card media-flush h-100">
                    <div class="service-card-media">
                        <img src="<?= base_url('assets/img/pages/feature-quotes.png') ?>" alt="Logistics manager reviewing an instant quote on a laptop" loading="lazy">
                        <span class="media-icon fi-green"><i class="bi bi-speedometer2"></i></span>
                    </div>
                    <div class="card-body-pad">
                        <h5>Instant quotes</h5>
                        <p class="text-muted-2 mb-0">Transparent pricing calculated in seconds — no phone calls, no waiting.</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="feature-card media-flush h-100">
                    <div class="service-card-media">
                        <img src="<?= base_url('assets/img/pages/feature-global.png') ?>" alt="Cargo plane over a container port on a global trade route" loading="lazy">
                        <span class="media-icon fi-orange"><i class="bi bi-globe2"></i></span>
                    </div>
                    <div class="card-body-pad">
                        <h5>Global reach</h5>
                        <p class="text-muted-2 mb-0">Air, ocean and ground networks covering 120+ countries and counting.</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="feature-card media-flush h-100">
                    <div class="service-card-media">
                        <img src="<?= base_url('assets/img/pages/feature-secure.png') ?>" alt="Courier handing over a sealed parcel at the doorstep" loading="lazy">
                        <span class="media-icon fi-violet"><i class="bi bi-shield-check"></i></span>
                    </div>
                    <div class="card-body-pad">
                        <h5>Secure &amp; insured</h5>
                        <p class="text-muted-2 mb-0">End-to-end protection with full insurance options on every shipment.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= HOW IT WORKS ================= -->
<section class="section">
    <div class="container">
        <div class="section-head text-center mx-auto mb-5 reveal">
            <div class="eyebrow mb-2">How it works</div>
            <h2 class="display-6">From quote to doorstep in 4 steps</h2>
        </div>
        <div class="steps-track">
            <div class="step-item reveal">
                <div class="step-visual">
                    <span class="step-badge">1</span>
                    <span class="step-icon"><i class="bi bi-ui-checks-grid"></i></span>
                </div>
                <div>
                    <h5>Get a quote</h5>
                    <p class="text-muted-2">Tell us what and where. Get an instant, transparent price.</p>
                </div>
            </div>
            <div class="step-item reveal">
                <div class="step-visual">
                    <span class="step-badge">2</span>
                    <span class="step-icon"><i class="bi bi-credit-card-2-front-fill"></i></span>
                </div>
                <div>
                    <h5>Book &amp; pay</h5>
                    <p class="text-muted-2">Confirm your shipment and pay securely online in any currency.</p>
                </div>
            </div>
            <div class="step-item reveal">
                <div class="step-visual">
                    <span class="step-badge">3</span>
                    <span class="step-icon"><i class="bi bi-geo-alt-fill"></i></span>
                </div>
                <div>
                    <h5>Track live</h5>
                    <p class="text-muted-2">Follow every milestone in real time with a unique tracking number.</p>
                </div>
            </div>
            <div class="step-item reveal">
                <div class="step-visual">
                    <span class="step-badge">4</span>
                    <span class="step-icon"><i class="bi bi-house-check-fill"></i></span>
                </div>
                <div>
                    <h5>Delivered</h5>
                    <p class="text-muted-2">Signature-confirmed delivery with full proof of handoff.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= SERVICES PREVIEW ================= -->
<section class="section bg-surface-2">
    <div class="container">
        <div class="section-head text-center mx-auto mb-5 reveal">
            <div class="eyebrow mb-2">Our services</div>
            <h2 class="display-6">Built for every kind of cargo</h2>
            <p class="text-muted-2">Six specialised networks, one platform — each handled by experts end to end.</p>
        </div>
        <div class="row g-4">
            <?php
            $servicesPreview = [
                ['img' => 'service-ground.png',   'icon' => 'bi-truck',                  'cls' => 'fi-blue',   'title' => 'Ground Freight',      'desc' => 'Reliable road delivery across regions'],
                ['img' => 'service-air.png',      'icon' => 'bi-airplane',               'cls' => 'fi-cyan',   'title' => 'Air Freight',         'desc' => 'Fastest global delivery option'],
                ['img' => 'service-ocean.png',    'icon' => 'bi-water',                  'cls' => 'fi-green',  'title' => 'Ocean Freight',       'desc' => 'Cost-effective bulk &amp; container shipping'],
                ['img' => 'service-express.png',  'icon' => 'bi-lightning-charge-fill',  'cls' => 'fi-orange', 'title' => 'Express Delivery',    'desc' => 'Same-day &amp; next-day priority'],
                ['img' => 'service-warehouse.png','icon' => 'bi-box-seam-fill',          'cls' => 'fi-violet', 'title' => 'Warehousing',         'desc' => 'Secure storage &amp; fulfillment'],
                ['img' => 'service-customs.png',  'icon' => 'bi-shield-check',           'cls' => 'fi-pink',   'title' => 'Customs &amp; Brokerage', 'desc' => 'Hassle-free cross-border clearance'],
            ];
            foreach ($servicesPreview as $s): ?>
            <div class="col-md-6 col-lg-4 reveal">
                <div class="feature-card media-flush h-100">
                    <div class="service-card-media">
                        <img src="<?= base_url('assets/img/pages/' . $s['img']) ?>" alt="<?= strip_tags($s['title']) ?> service" loading="lazy">
                        <span class="media-icon <?= $s['cls'] ?>"><i class="bi <?= $s['icon'] ?>"></i></span>
                    </div>
                    <div class="card-body-pad d-flex flex-column">
                        <h5><?= $s['title'] ?></h5>
                        <p class="text-muted-2 mb-3"><?= $s['desc'] ?></p>
                        <a href="<?= base_url('services.php') ?>" class="mt-auto d-inline-flex align-items-center fw-semibold">
                            Learn more <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ================= TESTIMONIALS ================= -->
<section class="section">
    <div class="container">
        <div class="section-head text-center mx-auto mb-5 reveal">
            <div class="eyebrow mb-2">Testimonials</div>
            <h2 class="display-6">Trusted by teams that ship daily</h2>
        </div>
        <?php
        $testimonials = [
            ['name' => 'Marcus Chen', 'role' => 'Ops Lead, Atlas Technologies', 'text' => 'CargoFlow replaced three separate tools. The live timeline and driver visibility cut our support tickets in half.', 'image' => 'assets/img/avatars/testimonial-marcus-chen.png'],
            ['name' => 'Sofia Bianchi', 'role' => 'Founder, Bella Italia Imports', 'text' => 'Customs used to be a nightmare. Their brokerage team handles everything, and tracking is flawless door-to-door.', 'image' => 'assets/img/avatars/testimonial-sofia-bianchi.png'],
            ['name' => 'Daniel Okafor', 'role' => 'Director, Savannah Retail', 'text' => 'The instant quoting alone saves us hours every week. Reliable, transparent and genuinely fast.', 'image' => 'assets/img/avatars/testimonial-daniel-okafor.png'],
        ];
        ?>
        <div id="testimonialCarousel" class="carousel slide testimonial-carousel reveal" data-bs-ride="carousel" data-bs-interval="6000">
            <div class="carousel-indicators">
                <?php foreach ($testimonials as $i => $t): ?>
                <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active" aria-current="true"' : '' ?> aria-label="Testimonial <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php foreach ($testimonials as $i => $t): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <div class="testimonial-slide">
                        <div class="quote-mark"><i class="bi bi-quote"></i></div>
                        <div class="t-stars">
                            <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                        <p class="quote-text mb-0">&ldquo;<?= e($t['text']) ?>&rdquo;</p>
                        <div class="t-person">
                            <img src="<?= base_url($t['image']) ?>" alt="Portrait of <?= e($t['name']) ?>" loading="lazy">
                            <div>
                                <div class="fw-bold"><?= e($t['name']) ?></div>
                                <div class="text-muted-2 small"><?= e($t['role']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev tc-arrow" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev" aria-label="Previous testimonial">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next tc-arrow" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next" aria-label="Next testimonial">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</section>

<!-- ================= CTA ================= -->
<section class="section pt-0">
    <div class="container">
        <div class="cta-band reveal">
            <div class="row align-items-center position-relative" style="z-index:2;">
                <div class="col-lg-8">
                    <h2 class="display-6 mb-2">Ready to move something?</h2>
                    <p class="mb-0 opacity-75">Get an instant quote and start tracking in under a minute.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="<?= base_url('quote.php') ?>" class="btn btn-light btn-lg fw-semibold px-4">Get a Quote <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
