<?php
/**
 * CargoFlow — Public site header (navbar)
 * Include after bootstrap.php. Expects $page (current nav) and
 * optional $pageTitle/$pageDesc for SEO.
 */
if (!function_exists('base_url')) {
    require_once __DIR__ . '/bootstrap.php';
}

$cf_page = $page ?? '';
$cf_title = $pageTitle ?? (setting('site_name', 'CargoFlow') . ' — Logistics & Shipment Tracking');
$cf_desc  = $pageDesc ?? 'CargoFlow is a modern logistics and shipment tracking platform offering real-time tracking, quotes, freight and delivery services.';
$cf_theme = current_theme();
$cf_nav   = [
    'index'    => 'Home',
    'track'    => 'Track Shipment',
    'services' => 'Services',
    'quote'    => 'Get a Quote',
    'about'    => 'About',
    'contact'  => 'Contact',
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= e($cf_theme) ?>" data-theme="<?= e($cf_theme) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($cf_title) ?></title>
    <meta name="description" content="<?= e($cf_desc) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="icon" href="<?= base_url('assets/img/favicon.svg') ?>" type="image/svg+xml">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- CargoFlow -->
    <link href="<?= base_url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-cf sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('index.php') ?>">
            <span class="d-inline-flex align-items-center gap-2">
                <span class="mark">
                    <svg width="30" height="30" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                        <rect x="1" y="1" width="30" height="30" rx="8" fill="#2563eb"/>
                        <path d="M9 20l4-8 3 6 3-6 4 8" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        <circle cx="16" cy="20" r="1.6" fill="#06b6d4"/>
                    </svg>
                </span>
                Cargo<span class="mark">Flow</span>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#cfNav" aria-controls="cfNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="cfNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <?php foreach ($cf_nav as $slug => $label): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $cf_page === $slug ? 'active' : '' ?>" href="<?= base_url($slug === 'index' ? 'index.php' : $slug . '.php') ?>"><?= e($label) ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <button class="theme-toggle" data-theme-toggle aria-label="Toggle dark mode">
                    <i class="bi <?= $cf_theme === 'dark' ? 'bi-sun-fill' : 'bi-moon-stars-fill' ?>" data-theme-icon></i>
                </button>
                <a href="<?= base_url('login.php') ?>" class="btn btn-ghost btn-sm">Sign in</a>
                <a href="<?= base_url('quote.php') ?>" class="btn btn-brand btn-sm">Get a Quote</a>
            </div>
        </div>
    </div>
</nav>
