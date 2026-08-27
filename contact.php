<?php
/**
 * CargoFlow — Contact page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$page = 'contact';
$pageTitle = 'Contact — ' . setting('site_name', 'CargoFlow');
$pageDesc = 'Get in touch with the CargoFlow team for support, sales or partnership inquiries.';

require __DIR__ . '/includes/header.php';
?>

<section class="section inner-hero">
    <div class="container">
        <div class="section-head text-center mx-auto mb-5 reveal">
            <div class="eyebrow mb-2">Contact Us</div>
            <h1 class="display-5">We're here to help</h1>
            <p class="text-muted-2 lead">Questions, support or partnership ideas — reach us any way you like.</p>
        </div>

        <div class="row g-4 g-lg-5">
            <div class="col-lg-5">
                <div class="media-frame reveal mb-4">
                    <img class="page-visual" src="<?= base_url('assets/img/pages/contact-office.png') ?>" alt="CargoFlow support office" loading="lazy">
                </div>
                <div class="d-grid gap-3 reveal">
                    <div class="info-card">
                        <div class="info-ic fi-blue"><i class="bi bi-geo-alt-fill"></i></div>
                        <div>
                            <h6 class="mb-0">Head office</h6>
                            <span class="info-sub"><?= e(setting('site_address', '100 Logistics Way, San Francisco, CA 94105')) ?></span>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-ic fi-green"><i class="bi bi-telephone-fill"></i></div>
                        <div>
                            <h6 class="mb-0">Phone</h6>
                            <span class="info-sub"><?= e(setting('site_phone', '+1 (800) 555-0199')) ?></span>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-ic fi-orange"><i class="bi bi-envelope-fill"></i></div>
                        <div>
                            <h6 class="mb-0">Email</h6>
                            <span class="info-sub"><?= e(setting('site_email', 'hello@cargoflow.test')) ?></span>
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-ic fi-violet"><i class="bi bi-clock-fill"></i></div>
                        <div>
                            <h6 class="mb-0">Support hours</h6>
                            <span class="info-sub">Mon–Fri, 8am–8pm · 24/7 for urgent shipments</span>
                        </div>
                    </div>
                </div>

                <div class="form-card map-card p-3 mt-4 reveal">
                    <div class="map-card-head px-2 pt-1"><i class="bi bi-geo-alt"></i>Find us</div>
                    <div class="map-holder" style="height:250px;">
                        <iframe title="Office location" src="https://www.openstreetmap.org/export/embed.html?bbox=-122.45%2C37.77%2C-122.38%2C37.81&layer=mapnik&marker=37.7905%2C-122.4050" style="width:100%;height:100%;border:0;border-radius:12px;" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="form-card p-4 p-md-5 reveal h-100">
                    <h4 class="fw-bold mb-1">Send us a message</h4>
                    <p class="text-muted-2 mb-4">We typically respond within one business day.</p>

                    <form id="contactForm" action="<?= base_url('api/contact.php') ?>" method="post" data-ajax data-ajax-handler="onContactSuccess">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="c_name">Full name *</label>
                                <input type="text" class="form-control" id="c_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="c_email">Email *</label>
                                <input type="email" class="form-control" id="c_email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="c_phone">Phone</label>
                                <input type="tel" class="form-control" id="c_phone" name="phone">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="c_subject">Subject</label>
                                <input type="text" class="form-control" id="c_subject" name="subject">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="c_message">Message *</label>
                                <textarea class="form-control" id="c_message" name="message" rows="6" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-brand btn-lg px-4">
                                    <i class="bi bi-send me-1"></i> Send Message
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="trust-row">
                        <span class="trust-chip"><i class="bi bi-reply-all-fill"></i> 1-business-day reply</span>
                        <span class="trust-chip"><i class="bi bi-shield-check"></i> Your details stay private</span>
                        <span class="trust-chip"><i class="bi bi-headset"></i> Real people, no bots</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function onContactSuccess(json) {
    if (json && json.success) {
        var form = document.getElementById('contactForm');
        form.reset();
        CFToast('Thank you! Your message has been sent.', 'success');
    }
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
