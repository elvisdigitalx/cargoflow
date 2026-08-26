<?php
/**
 * CargoFlow — Public site footer
 */
$cf_site_name = setting('site_name', 'CargoFlow');
$cf_site_phone = setting('site_phone', '+1 (800) 555-0199');
$cf_site_email = setting('site_email', 'hello@cargoflow.test');
$cf_site_address = setting('site_address', '100 Logistics Way, San Francisco, CA 94105');
?>
<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <a class="navbar-brand d-inline-block mb-3" href="<?= base_url('index.php') ?>">
                    <span class="d-inline-flex align-items-center gap-2 text-white">
                        <svg width="28" height="28" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                            <rect x="1" y="1" width="30" height="30" rx="8" fill="#e82127"/>
                            <path d="M9 20l4-8 3 6 3-6 4 8" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        </svg>
                        CargoFlow
                    </span>
                </a>
                <p class="mb-4 pe-lg-4">
                    A modern logistics platform for real-time shipment tracking,
                    freight management and global delivery — built for speed,
                    transparency and reliability.
                </p>
                <div class="social d-flex gap-2">
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="X / Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="mb-3">Company</h6>
                <ul class="list-unstyled d-grid gap-2">
                    <li><a href="<?= base_url('about.php') ?>">About us</a></li>
                    <li><a href="<?= base_url('services.php') ?>">Services</a></li>
                    <li><a href="<?= base_url('contact.php') ?>">Contact</a></li>
                    <li><a href="<?= base_url('quote.php') ?>">Get a Quote</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="mb-3">Services</h6>
                <ul class="list-unstyled d-grid gap-2">
                    <li><a href="<?= base_url('services.php') ?>#ground">Ground Freight</a></li>
                    <li><a href="<?= base_url('services.php') ?>#air">Air Freight</a></li>
                    <li><a href="<?= base_url('services.php') ?>#sea">Ocean Freight</a></li>
                    <li><a href="<?= base_url('services.php') ?>#express">Express Delivery</a></li>
                    <li><a href="<?= base_url('services.php') ?>#warehousing">Warehousing</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-6">
                <h6 class="mb-3">Get in touch</h6>
                <ul class="list-unstyled d-grid gap-3">
                    <li class="d-flex gap-2"><i class="bi bi-geo-alt-fill text-primary"></i><span><?= e($cf_site_address) ?></span></li>
                    <li class="d-flex gap-2"><i class="bi bi-telephone-fill text-primary"></i><span><?= e($cf_site_phone) ?></span></li>
                    <li class="d-flex gap-2"><i class="bi bi-envelope-fill text-primary"></i><span><?= e($cf_site_email) ?></span></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <span>&copy; <?= date('Y') ?> <?= e($cf_site_name) ?>. All rights reserved.</span>
            <span>Designed with <i class="bi bi-heart-fill text-danger"></i> for modern logistics</span>
        </div>
    </div>
</footer>

<div class="cf-toast" aria-live="polite" aria-atomic="true"></div>

<!-- Floating language switcher (Google Translate) -->
<div class="cf-lang-switcher" id="cfLangSwitcher">
    <div id="google_translate_element"></div>
</div>
<script type="text/javascript">
function googleTranslateElementInit() {
    new google.translate.TranslateElement({
        pageLanguage: 'en',
        includedLanguages: 'en,fr,es,pt,de,it,zh-CN,ar,sw,yo,ha,ig',
        layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
        autoDisplay: false
    }, 'google_translate_element');
}
</script>
<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
