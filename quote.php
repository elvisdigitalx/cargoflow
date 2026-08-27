<?php
/**
 * CargoFlow — Get a Quote page
 * Form submits via AJAX to api/quotes.php (or falls back to a normal POST).
 */
require_once __DIR__ . '/includes/bootstrap.php';

$page = 'quote';
$pageTitle = 'Get a Quote — ' . setting('site_name', 'CargoFlow');
$pageDesc = 'Request an instant, transparent shipping quote from CargoFlow.';

require __DIR__ . '/includes/header.php';
?>

<section class="section inner-hero">
    <div class="container">
        <div class="row g-4 g-lg-5 align-items-start">
            <!-- Left: info -->
            <div class="col-lg-5 reveal">
                <div class="pe-lg-3">
                    <div class="eyebrow dash mb-3">Get a Quote</div>
                    <h1 class="display-5 mb-3">Instant, transparent shipping quotes</h1>
                    <p class="text-muted-2 lead mb-4">
                        Tell us about your shipment and receive an estimated price
                        in seconds. No obligation, no hidden fees.
                    </p>

                    <div class="media-frame mb-4">
                        <img class="page-visual" src="<?= base_url('assets/img/pages/quote-consultant.png') ?>" alt="Logistics consultant preparing a shipping quote" loading="lazy">
                    </div>

                    <div class="d-grid gap-3">
                        <div class="info-card">
                            <div class="info-ic fi-green"><i class="bi bi-lightning-charge-fill"></i></div>
                            <div>
                                <h6 class="mb-0">Fast response</h6>
                                <span class="info-sub">Instant estimate + email follow-up</span>
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="info-ic fi-blue"><i class="bi bi-cash-coin"></i></div>
                            <div>
                                <h6 class="mb-0">Transparent pricing</h6>
                                <span class="info-sub">All-in rates, no surprises</span>
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="info-ic fi-orange"><i class="bi bi-headset"></i></div>
                            <div>
                                <h6 class="mb-0">Dedicated support</h6>
                                <span class="info-sub">A logistics expert reviews every quote</span>
                            </div>
                        </div>
                    </div>

                    <div class="call-box mt-4">
                        <span class="info-ic fi-violet"><i class="bi bi-telephone-fill"></i></span>
                        <div>
                            <div class="fw-bold">Prefer to talk?</div>
                            <div class="text-muted-2 small">Call us at <a href="tel:<?= preg_replace('/[^0-9+]/', '', setting('site_phone', '+1 (800) 555-0199')) ?>"><?= e(setting('site_phone', '+1 (800) 555-0199')) ?></a></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: form -->
            <div class="col-lg-7">
                <div class="form-card p-4 p-md-5 reveal">
                    <h4 class="fw-bold mb-1">Request a shipping quote</h4>
                    <p class="text-muted-2 mb-4">Fields marked * are required.</p>

                    <form id="quoteForm" action="<?= base_url('api/quotes.php') ?>" method="post" data-ajax data-ajax-handler="onQuoteSuccess">
                        <?= csrf_field() ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="q_name">Full name *</label>
                                <input type="text" class="form-control" id="q_name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="q_email">Email *</label>
                                <input type="email" class="form-control" id="q_email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="q_phone">Phone</label>
                                <input type="tel" class="form-control" id="q_phone" name="phone">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="q_service">Service type *</label>
                                <select class="form-select" id="q_service" name="service_type" required>
                                    <option value="">Select…</option>
                                    <option value="standard">Standard</option>
                                    <option value="express">Express</option>
                                    <option value="overnight">Overnight</option>
                                    <option value="freight">Freight</option>
                                    <option value="air">Air Freight</option>
                                    <option value="sea">Ocean Freight</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="q_origin">Origin *</label>
                                <input type="text" class="form-control" id="q_origin" name="origin" placeholder="City, Country" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="q_destination">Destination *</label>
                                <input type="text" class="form-control" id="q_destination" name="destination" placeholder="City, Country" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="q_package">Package type</label>
                                <select class="form-select" id="q_package" name="package_type">
                                    <option value="parcel">Parcel</option>
                                    <option value="document">Document</option>
                                    <option value="pallet">Pallet</option>
                                    <option value="container">Container</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="q_weight">Weight (kg)</label>
                                <input type="text" class="form-control" id="q_weight" name="weight" placeholder="e.g. 50">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="q_message">Additional details</label>
                                <textarea class="form-control" id="q_message" name="message" rows="4" placeholder="Cargo description, dimensions, special requirements…"></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-brand btn-lg px-4 w-100">
                                    <i class="bi bi-send me-1"></i> Request Quote
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function onQuoteSuccess(json) {
    if (json && json.success) {
        var form = document.getElementById('quoteForm');
        var btn = form.querySelector('[type="submit"]');
        form.reset();
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Request Quote';
        CFToast('Quote submitted! Estimated price: ' + (json.estimated_price || 'pending review') + '. We\'ll email you shortly.', 'success');
    }
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
