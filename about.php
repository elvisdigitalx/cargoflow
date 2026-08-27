<?php
/**
 * CargoFlow — About page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$page = 'about';
$pageTitle = 'About Us — ' . setting('site_name', 'CargoFlow');
$pageDesc = 'Learn about CargoFlow — our mission, values and the team behind a modern logistics platform.';

require __DIR__ . '/includes/header.php';
?>

<section class="section inner-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 reveal">
                <div class="eyebrow dash mb-3">About CargoFlow</div>
                <h1 class="display-5 mb-3">We make logistics <span class="text-gradient">transparent</span></h1>
                <p class="text-muted-2 lead mb-3">
                    Founded in 2018, CargoFlow set out to fix a simple problem:
                    shipping should never feel like a black box.
                </p>
                <p class="text-muted-2 mb-4">
                    Today we move thousands of shipments a month across 120+ countries,
                    combining a modern tracking platform with an experienced operations
                    team and a global carrier network. Whether it's a document or a
                    full container, you always know where your cargo is — and when it
                    will arrive.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= base_url('quote.php') ?>" class="btn btn-brand px-4">Get a Free Quote <i class="bi bi-arrow-right ms-1"></i></a>
                    <a href="<?= base_url('contact.php') ?>" class="btn btn-ghost px-4">Contact us</a>
                </div>
                <div class="row g-3 mt-4">
                    <div class="col-6 col-md-4">
                        <div class="mini-stat h-100">
                            <div class="mini-stat-num text-gradient" data-count="7">0</div>
                            <div class="mini-stat-label">Years in business</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="mini-stat h-100">
                            <div class="mini-stat-num text-gradient" data-count="2500">0</div>
                            <div class="mini-stat-label">Happy clients</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="mini-stat h-100">
                            <div class="mini-stat-num text-gradient" data-count="120">0</div>
                            <div class="mini-stat-label">Countries served</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 reveal">
                <div class="media-frame">
                    <img class="page-visual" src="<?= base_url('assets/img/pages/about-operations.png') ?>" alt="CargoFlow operations team monitoring shipments" loading="lazy">
                    <div class="media-badge">
                        <i class="bi bi-truck" style="font-size:1.6rem;"></i>
                        <div>
                            <div class="mb-num">128k+</div>
                            <div class="mb-text">Shipments delivered</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission / values -->
<section class="section bg-surface-2 section-tight-top">
    <div class="container">
        <div class="section-head text-center mx-auto mb-5 reveal">
            <div class="eyebrow mb-2">What drives us</div>
            <h2 class="display-6">Built on clarity, run on trust</h2>
            <p class="text-muted-2">Four principles guide every shipment we touch.</p>
        </div>
        <div class="row g-4">
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="value-card">
                    <div class="feature-icon fi-blue"><i class="bi bi-bullseye"></i></div>
                    <h5>Our Mission</h5>
                    <p class="text-muted-2 small mb-0">Give every business real-time visibility and total control over their supply chain.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="value-card">
                    <div class="feature-icon fi-green"><i class="bi bi-eye"></i></div>
                    <h5>Our Vision</h5>
                    <p class="text-muted-2 small mb-0">A world where moving goods is as simple and clear as sending a message.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="value-card">
                    <div class="feature-icon fi-orange"><i class="bi bi-heart-fill"></i></div>
                    <h5>Our Values</h5>
                    <p class="text-muted-2 small mb-0">Transparency, reliability, speed and genuine care for every customer.</p>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3 reveal">
                <div class="value-card">
                    <div class="feature-icon fi-violet"><i class="bi bi-globe2"></i></div>
                    <h5>Our Network</h5>
                    <p class="text-muted-2 small mb-0">Partners across 120+ countries, 40+ warehouses and 6 major hubs.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team -->
<section class="section pt-0">
    <div class="container">
        <div class="section-head text-center mx-auto mb-5 reveal">
            <div class="eyebrow mb-2">Our Team</div>
            <h2 class="display-6">Led by logistics experts</h2>
            <p class="text-muted-2">Decades of combined freight, operations and engineering experience.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <?php
            $team = [
                ['Nadia Hussain', 'Chief Executive Officer', 'assets/img/avatars/team-nadia-hussain.png'],
                ['Robert Kim', 'Head of Operations', 'assets/img/avatars/team-robert-kim.png'],
                ['Elena Petrova', 'Chief Technology Officer', 'assets/img/avatars/team-elena-petrova.png'],
                ['Kwame Mensah', 'Director of Freight', 'assets/img/avatars/team-kwame-mensah.png'],
            ];
            foreach ($team as $m): ?>
            <div class="col-6 col-md-6 col-lg-3 reveal">
                <div class="team-card h-100">
                    <img class="team-avatar mx-auto" src="<?= base_url($m[2]) ?>" alt="Portrait of <?= e($m[0]) ?>" loading="lazy">
                    <h6 class="mb-1"><?= e($m[0]) ?></h6>
                    <div class="text-muted-2 small"><?= e($m[1]) ?></div>
                    <div class="team-social">
                        <a href="#" aria-label="<?= e($m[0]) ?> on LinkedIn"><i class="bi bi-linkedin"></i></a>
                        <a href="#" aria-label="Email <?= e($m[0]) ?>"><i class="bi bi-envelope-fill"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="section pt-0">
    <div class="container">
        <div class="cta-band reveal">
            <div class="row align-items-center position-relative" style="z-index:2;">
                <div class="col-lg-8">
                    <h2 class="display-6 mb-2">Let's move something together</h2>
                    <p class="mb-0 opacity-75">Get an instant quote and see the CargoFlow difference in under a minute.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="<?= base_url('quote.php') ?>" class="btn btn-light btn-lg fw-semibold px-4">Get a Quote <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
