<?php
/**
 * CargoFlow — Application configuration (TEMPLATE)
 * ---------------------------------------------------------------------
 * Copy this file to config.php and fill in your real values:
 *
 *     cp config.example.php config.php
 *
 * config.php is excluded from git (it contains live credentials).
 */

// ------------------------------------------------------------------
// Database
// ------------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_db_name');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_CHARSET', 'utf8mb4');

// ------------------------------------------------------------------
// Application
// ------------------------------------------------------------------
define('APP_NAME', 'CargoFlow');
define('APP_VERSION', '1.0.0');

// Base URL — leave empty ("") to auto-detect, or set explicitly, e.g.
// define('BASE_URL', 'https://yourdomain.com');
define('BASE_URL', '');

// Default timezone
define('APP_TIMEZONE', 'Africa/Lagos');

// Session / security
define('SESSION_NAME', 'cargoflow_session');
define('REMEMBER_ME_DAYS', 14);

// Development mode — shows detailed errors (set false in production)
define('APP_DEBUG', false);
