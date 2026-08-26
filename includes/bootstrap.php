<?php
/**
 * CargoFlow — Bootstrap
 * ---------------------------------------------------------------------
 * Include this at the top of every entry-point PHP file. It starts the
 * session, applies timezone/error settings and loads configuration,
 * the database layer and helper functions.
 */

declare(strict_types=1);

// Configuration must be loaded first — SESSION_NAME and APP_TIMEZONE are
// defined there and used below (previously this fataled on PHP 8+).
require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME ?? 'cargoflow_session');
    session_start();
}

date_default_timezone_set(APP_TIMEZONE);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
