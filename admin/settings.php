<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$adminPage = 'settings';
$adminTitle = 'Settings & Profile';
require __DIR__ . '/includes/header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-0">Settings &amp; Profile</h2>
        <p class="text-muted-2 mb-0 small">Configure your platform and manage your account.</p>
    </div>
</div>

<div class="row g-3">
    <!-- Site settings -->
    <div class="col-lg-6">
        <div class="card-admin">
            <div class="card-header">General settings</div>
            <div class="card-body">
                <form data-modal-form action="<?= base_url('api/settings.php') ?>" data-on-success="onSettingsSaved">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_settings">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Site name</label><input class="form-control" name="site_name" id="s_name"></div>
                        <div class="col-md-6"><label class="form-label">Tagline</label><input class="form-control" name="site_tagline" id="s_tagline"></div>
                        <div class="col-md-6"><label class="form-label">Contact email</label><input class="form-control" name="site_email" id="s_email"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="site_phone" id="s_phone"></div>
                        <div class="col-12"><label class="form-label">Address</label><input class="form-control" name="site_address" id="s_address"></div>
                        <div class="col-md-4"><label class="form-label">Currency code</label><input class="form-control" name="currency" id="s_currency"></div>
                        <div class="col-md-4"><label class="form-label">Currency symbol</label><input class="form-control" name="currency_symbol" id="s_symbol"></div>
                        <div class="col-md-4"><label class="form-label">Tax rate (%)</label><input type="number" step="0.01" class="form-control" name="tax_rate" id="s_tax"></div>
                        <div class="col-md-6"><label class="form-label">Support email</label><input class="form-control" name="support_email" id="s_support"></div>
                        <div class="col-md-6"><label class="form-label">Default theme</label>
                            <select class="form-select" name="default_theme" id="s_theme"><option value="light">Light</option><option value="dark">Dark</option></select>
                        </div>
                        <div class="col-12"><button class="btn btn-brand btn-sm"><i class="bi bi-save me-1"></i> Save settings</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <!-- Profile -->
        <div class="card-admin mb-3">
            <div class="card-header">Profile</div>
            <div class="card-body">
                <form data-modal-form action="<?= base_url('api/settings.php') ?>" data-on-success="onSettingsSaved">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_profile">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="name" id="p_name"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" id="p_email"></div>
                        <div class="col-12"><button class="btn btn-brand btn-sm"><i class="bi bi-save me-1"></i> Update profile</button></div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password -->
        <div class="card-admin">
            <div class="card-header">Change password</div>
            <div class="card-body">
                <form data-modal-form action="<?= base_url('api/settings.php') ?>" data-on-success="onPasswordSaved">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_password">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label">Current password</label><input type="password" class="form-control" name="current_password" required></div>
                        <div class="col-md-6"><label class="form-label">New password</label><input type="password" class="form-control" name="new_password" required minlength="8"></div>
                        <div class="col-md-6"><label class="form-label">Confirm password</label><input type="password" class="form-control" name="confirm_password" required minlength="8"></div>
                        <div class="col-12"><button class="btn btn-brand btn-sm"><i class="bi bi-shield-lock me-1"></i> Update password</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function fillSettings() {
    CF.api('<?= base_url('api/settings.php') ?>?action=get').then(function (json) {
        if (!json.success) return;
        var s = json.settings, u = json.user;
        ['site_name','site_tagline','site_email','site_phone','site_address','currency','currency_symbol','tax_rate','support_email','default_theme'].forEach(function (k) {
            var el = document.getElementById('s_' + k.replace('site_','').replace('support_email','support').replace('default_theme','theme'));
            if (el && s[k] != null) el.value = s[k];
        });
        // map support_email and default_theme ids
        if (s.support_email != null) document.getElementById('s_support').value = s.support_email;
        if (s.default_theme != null) document.getElementById('s_theme').value = s.default_theme;
        document.getElementById('p_name').value = u.name || '';
        document.getElementById('p_email').value = u.email || '';
    });
}
function onSettingsSaved(json) { CF.toast(json.message, 'success'); fillSettings(); }
function onPasswordSaved(json) {
    CF.toast(json.message, 'success');
    document.querySelectorAll('form').forEach(function (f) { if (f.querySelector('[name="current_password"]')) f.reset(); });
}
fillSettings();
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
