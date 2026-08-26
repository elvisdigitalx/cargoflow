<?php
/**
 * CargoFlow — Admin layout header (sidebar + topbar)
 * Expects: $adminPage (current section) and optional $adminTitle.
 */
if (!function_exists('base_url')) {
    require_once __DIR__ . '/../../includes/bootstrap.php';
}
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$user = current_user();
$adminPage = $adminPage ?? '';
$adminTitle = $adminTitle ?? 'Dashboard';

$navSections = [
    'Overview' => [
        'index'      => ['bi-grid-1x2', 'Dashboard', 'admin/index.php'],
    ],
    'Operations' => [
        'shipments'  => ['bi-box-seam', 'Shipments', 'admin/shipments.php'],
        'customers'  => ['bi-people', 'Customers', 'admin/customers.php'],
        'drivers'    => ['bi-person-badge', 'Drivers', 'admin/drivers.php'],
        'vehicles'   => ['bi-truck', 'Vehicles', 'admin/vehicles.php'],
        'quotes'     => ['bi-receipt', 'Quotes', 'admin/quotes.php'],
    ],
    'Finance' => [
        'invoices'   => ['bi-file-earmark-text', 'Invoices', 'admin/invoices.php'],
        'payments'   => ['bi-credit-card', 'Payments', 'admin/payments.php'],
    ],
    'Insights' => [
        'reports'    => ['bi-bar-chart', 'Reports', 'admin/reports.php'],
        'messages'   => ['bi-envelope', 'Messages', 'admin/messages.php'],
        'notifications' => ['bi-bell', 'Notifications', 'admin/notifications.php'],
    ],
    'System' => [
        'settings'   => ['bi-gear', 'Settings', 'admin/settings.php'],
        'users'      => ['bi-shield-lock', 'Users', 'admin/users.php'],
    ],
];

// Resolve current section icon/title for breadcrumb
$currentLabel = $adminTitle;
foreach ($navSections as $items) {
    if (isset($items[$adminPage])) {
        $currentLabel = $items[$adminPage][1];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= e(current_theme()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($adminTitle) ?> — CargoFlow Admin</title>
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <link rel="icon" href="<?= base_url('assets/img/favicon.svg') ?>" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('admin/assets/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin">
<div class="admin-wrapper">

    <!-- ============ SIDEBAR ============ -->
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="<?= base_url('admin/index.php') ?>" class="sidebar-brand">
            <svg width="28" height="28" viewBox="0 0 32 32" fill="none"><rect x="1" y="1" width="30" height="30" rx="8" fill="#e82127"/><path d="M9 20l4-8 3 6 3-6 4 8" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
            CargoFlow
        </a>
        <nav class="sidebar-nav">
            <?php foreach ($navSections as $section => $items): ?>
                <div class="nav-section"><?= e($section) ?></div>
                <?php foreach ($items as $key => $item): ?>
                    <a class="nav-link <?= $adminPage === $key ? 'active' : '' ?>" href="<?= base_url($item[2]) ?>">
                        <i class="bi <?= $item[0] ?>"></i>
                        <span><?= e($item[1]) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-footer">
            <a href="<?= base_url('logout.php') ?>" class="nav-link mb-1"><i class="bi bi-box-arrow-right"></i><span>Sign out</span></a>
            <a href="<?= base_url('index.php') ?>" class="nav-link mb-0"><i class="bi bi-globe"></i><span>View website</span></a>
        </div>
    </aside>

    <!-- ============ MAIN ============ -->
    <div class="admin-main">
        <header class="admin-topbar">
            <button class="btn btn-sm btn-ghost d-lg-none" data-sidebar-toggle aria-label="Toggle sidebar"><i class="bi bi-list fs-4"></i></button>
            <div class="d-none d-sm-block">
                <h1 class="h5 mb-0 fw-bold"><?= e($adminTitle) ?></h1>
            </div>

            <div class="ms-auto d-flex align-items-center gap-2">
                <button class="btn btn-ghost btn-sm" data-theme-toggle aria-label="Toggle theme">
                    <i class="bi <?= current_theme() === 'dark' ? 'bi-sun-fill' : 'bi-moon-stars-fill' ?>" data-theme-icon></i>
                </button>

                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-ghost btn-sm position-relative" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="notifBadge">0</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow p-2" style="width:320px; max-height:380px; overflow:auto;" id="notifDropdown">
                        <div class="text-muted-2 small px-2 py-1">Loading…</div>
                    </div>
                </div>

                <!-- User -->
                <div class="dropdown">
                    <button class="btn d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar-sm bg-soft-blue"><?= e(strtoupper(substr($user['name'] ?? 'U', 0, 1))) ?></span>
                        <span class="d-none d-md-inline text-start lh-1">
                            <span class="d-block fw-semibold small"><?= e($user['name'] ?? 'User') ?></span>
                            <span class="d-block text-muted-2" style="font-size:.72rem;"><?= e(ucfirst($user['role'] ?? 'staff')) ?></span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="<?= base_url('admin/settings.php') ?>"><i class="bi bi-person me-2"></i>Profile &amp; settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= base_url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="admin-content">
