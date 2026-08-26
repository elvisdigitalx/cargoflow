<?php
/**
 * CargoFlow — Health check endpoint
 * GET /api/health.php  →  { "status": "ok", ... }
 */
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';

$dbOk = true;
try {
    db()->query('SELECT 1');
} catch (Throwable $e) {
    $dbOk = false;
}

json_response([
    'status'    => $dbOk ? 'ok' : 'degraded',
    'app'       => APP_NAME,
    'version'   => APP_VERSION,
    'php'       => PHP_VERSION,
    'database'  => $dbOk ? 'connected' : 'error',
    'timestamp' => date('c'),
]);
