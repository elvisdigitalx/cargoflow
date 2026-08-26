<?php
/**
 * CargoFlow — Settings & profile API
 * GET  /api/settings.php             → all settings + current user
 * POST /api/settings.php action=update_settings|update_profile|update_password
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'get');

if ($action === 'get') {
    $settings = [];
    foreach (fetchAll('SELECT setting_key, setting_value FROM settings') as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $user = current_user();
    unset($user['password']);
    json_response(['success' => true, 'settings' => $settings, 'user' => $user]);
}

$input = api_input();

if ($action === 'update_settings') {
    $allowed = ['site_name', 'site_tagline', 'site_email', 'site_phone', 'site_address', 'currency', 'currency_symbol', 'tax_rate', 'default_theme', 'support_email', 'company_registration'];
    foreach ($allowed as $key) {
        if (array_key_exists($key, $input)) {
            set_setting($key, trim((string) $input[$key]));
        }
    }
    api_success([], 'Settings saved.');
}

if ($action === 'update_profile') {
    $user = current_user();
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    if ($name === '' || $email === '') {
        api_error('Name and email are required.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        api_error('Invalid email address.');
    }
    updateRow('users', (int) $user['id'], ['name' => $name, 'email' => $email]);
    api_success([], 'Profile updated.');
}

if ($action === 'update_password') {
    $user = current_user();
    $current = (string) ($input['current_password'] ?? '');
    $new = (string) ($input['new_password'] ?? '');
    $confirm = (string) ($input['confirm_password'] ?? '');

    if (!password_verify($current, $user['password'])) {
        api_error('Current password is incorrect.');
    }
    if (strlen($new) < 8) {
        api_error('New password must be at least 8 characters.');
    }
    if ($new !== $confirm) {
        api_error('New passwords do not match.');
    }
    updateRow('users', (int) $user['id'], ['password' => password_hash($new, PASSWORD_BCRYPT)]);
    api_success([], 'Password updated.');
}

api_error('Unknown action.', 400);
