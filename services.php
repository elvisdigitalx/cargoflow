<?php
/**
 * CargoFlow — Services page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$page = 'services';
$pageTitle = 'Services — ' . setting('site_name', 'CargoFlow');
$pageDesc = 'Explore CargoFlow logistics services: ground, air, ocean freight, express delivery, warehousing and customs brokerage.';

require __DIR__ . '/includes/header.php';
?>

<section class="section" style="padding-top:3rem;">
    <div class="container">
        <div class="section-head text-center mx-auto mb-5 reveal">
            <div class="eyebrow mb-2">Our Services</div>
            <h1 class="display-5">Every mode. Every scale.</h1>
            <p class="text-muted-2 lead">From a single envelope to full container loads, CargoFlow moves it all.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4 reveal" id="ground">
                <div class="feature-card">
                    <div class="feature-icon fi-blue"><i class="bi bi-truck"></i></div>
                    <h4>Ground Freight</h4>
                    <p class="text-muted-2">Reliable regional and cross-country road freight with flexible scheduling and live driver tracking.</p>
                    <ul class="list-unstyled small mb-0 d-grid gap-2">
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>FTL &amp; LTL options</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Same-day &amp; scheduled pickup</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Live GPS tracking</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 reveal" id="air">
                <div class="feature-card">
                    <div class="feature-icon fi-cyan"><i class="bi bi-airplane"></i></div>
                    <h4>Air Freight</h4>
                    <p class="text-muted-2">The fastest way to move time-sensitive cargo across borders, with door-to-door handling.</p>
                    <ul class="list-unstyled small mb-0 d-grid gap-2">
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Express &amp; economy air</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Global airport network</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Priority customs clearance</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 reveal" id="sea">
                <div class="feature-card">
                    <div class="feature-icon fi-green"><i class="bi bi-water"></i></div>
                    <h4>Ocean Freight</h4>
                    <p class="text-muted-2">Cost-effective bulk and container shipping for large volumes across major trade lanes.</p>
                    <ul class="list-unstyled small mb-0 d-grid gap-2">
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>FCL &amp; LCL containers</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Port-to-port &amp; door-to-door</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Sailing schedule visibility</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 reveal" id="express">
                <div class="feature-card">
                    <div class="feature-icon fi-orange"><i class="bi bi-lightning-charge"></i></div>
                    <h4>Express Delivery</h4>
                    <p class="text-muted-2">Urgent documents and parcels delivered same-day or next-day with signature confirmation.</p>
                    <ul class="list-unstyled small mb-0 d-grid gap-2">
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Same-day service</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Proof of delivery</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Real-time courier tracking</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 reveal" id="warehousing">
                <div class="feature-card">
                    <div class="feature-icon fi-violet"><i class="bi bi-box-seam"></i></div>
                    <h4>Warehousing &amp; Fulfillment</h4>
                    <p class="text-muted-2">Secure storage, pick-and-pack and fulfillment integrated directly with your shipments.</p>
                    <ul class="list-unstyled small mb-0 d-grid gap-2">
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Climate-controlled storage</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Pick, pack &amp; ship</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Inventory management</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 reveal" id="customs">
                <div class="feature-card">
                    <div class="feature-icon fi-pink"><i class="bi bi-shield-check"></i></div>
                    <h4>Customs &amp; Brokerage</h4>
                    <p class="text-muted-2">Expert handling of cross-border documentation, duties and compliance for seamless clearance.</p>
                    <ul class="list-unstyled small mb-0 d-grid gap-2">
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Import/export documentation</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Duty &amp; tax calculation</li>
                        <li><i class="bi bi-check-circle-fill text-success me-1"></i>Compliance consulting</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section bg-surface-2">
    <div class="container">
        <div class="cta-band reveal">
            <div class="row align-items-center position-relative" style="z-index:2;">
                <div class="col-lg-8">
                    <h2 class="display-6 mb-2">Not sure which service fits?</h2>
                    <p class="mb-0 opacity-75">Our team will recommend the best option for your cargo and budget.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="<?= base_url('quote.php') ?>" class="btn btn-light btn-lg fw-semibold px-4">Get a Quote</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
