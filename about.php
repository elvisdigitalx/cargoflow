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

<section class="section" style="padding-top:3rem;">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 reveal">
                <div class="eyebrow mb-2">About CargoFlow</div>
                <h1 class="display-5 mb-3">We make logistics <span class="text-gradient">transparent</span></h1>
                <p class="text-muted-2 lead">
                    Founded in 2018, CargoFlow set out to fix a simple problem:
                    shipping should never feel like a black box.
                </p>
                <p class="text-muted-2">
                    Today we move thousands of shipments a month across 120+ countries,
                    combining a modern tracking platform with an experienced operations
                    team and a global carrier network. Whether it's a document or a
                    full container, you always know where your cargo is — and when it
                    will arrive.
                </p>
                <div class="row g-3 mt-2">
                    <div class="col-6">
                        <div class="feature-card text-center py-3">
                            <div class="stat-value fs-2 fw-bold text-gradient" data-count="7">0</div>
                            <div class="text-muted-2 small">Years in business</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="feature-card text-center py-3">
                            <div class="stat-value fs-2 fw-bold text-gradient" data-count="2500">0</div>
                            <div class="text-muted-2 small">Happy clients</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 reveal">
                <img class="page-visual mb-4" src="<?= base_url('assets/img/pages/about-operations.png') ?>" alt="CargoFlow operations team monitoring shipments" loading="lazy">
                <div class="form-card p-4">
                    <div class="row g-3">
                        <div class="col-sm-6"><div class="feature-card h-100">
                            <div class="feature-icon fi-blue"><i class="bi bi-bullseye"></i></div>
                            <h5>Our Mission</h5>
                            <p class="text-muted-2 small mb-0">Give every business real-time visibility and total control over their supply chain.</p>
                        </div></div>
                        <div class="col-sm-6"><div class="feature-card h-100">
                            <div class="feature-icon fi-green"><i class="bi bi-eye"></i></div>
                            <h5>Our Vision</h5>
                            <p class="text-muted-2 small mb-0">A world where moving goods is as simple and clear as sending a message.</p>
                        </div></div>
                        <div class="col-sm-6"><div class="feature-card h-100">
                            <div class="feature-icon fi-orange"><i class="bi bi-heart"></i></div>
                            <h5>Our Values</h5>
                            <p class="text-muted-2 small mb-0">Transparency, reliability, speed and genuine care for every customer.</p>
                        </div></div>
                        <div class="col-sm-6"><div class="feature-card h-100">
                            <div class="feature-icon fi-violet"><i class="bi bi-globe2"></i></div>
                            <h5>Our Network</h5>
                            <p class="text-muted-2 small mb-0">Partners across 120+ countries, 40+ warehouses and 6 major hubs.</p>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section bg-surface-2">
    <div class="container">
        <div class="section-head text-center mx-auto mb-5 reveal">
            <div class="eyebrow mb-2">Our Team</div>
            <h2 class="display-6">Led by logistics experts</h2>
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
            <div class="col-md-6 col-lg-3 reveal">
                <div class="feature-card text-center">
                    <img class="team-avatar mx-auto mb-3" src="<?= base_url($m[2]) ?>" alt="Portrait of <?= e($m[0]) ?>" loading="lazy">
                    <h6 class="mb-1"><?= e($m[0]) ?></h6>
                    <div class="text-muted-2 small"><?= e($m[1]) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
