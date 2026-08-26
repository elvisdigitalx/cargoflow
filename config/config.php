<?php
/**
 * CargoFlow — Application configuration
 * ---------------------------------------------------------------------
 * Edit these constants to match your cPanel / MySQL environment.
 * The database credentials are the ONLY values you normally need to change.
 */

// ------------------------------------------------------------------
// Database
// ------------------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'cargoflow');
define('DB_USER', 'cargoflow_user');
define('DB_PASS', 'change_me');
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
define('APP_TIMEZONE', 'UTC');

// Session / security
define('SESSION_NAME', 'cargoflow_session');
define('REMEMBER_ME_DAYS', 14);

// Development mode — shows detailed errors (set false in production)
define('APP_DEBUG', false);
