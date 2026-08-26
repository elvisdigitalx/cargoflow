<?php
/**
 * CargoFlow — API helpers
 * Include after bootstrap.php. Provides request parsing and consistent
 * JSON responses plus an authenticated+CSRF guard for admin endpoints.
 */
require_once __DIR__ . '/auth.php';

/**
 * Return the request body as an associative array (JSON or form-encoded).
 */
function api_input(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
    return array_merge($_GET, $_POST);
}

/**
 * Guard: require login + CSRF for admin endpoints.
 * Returns a JSON 401 (instead of an HTML redirect) for AJAX callers.
 */
function api_require_admin(): void
{
    if (!is_logged_in()) {
        json_response(['success' => false, 'message' => 'Unauthorized. Please log in.'], 401);
    }
    verify_csrf();
}

/**
 * Respond with a success payload.
 */
function api_success($data = [], string $message = 'OK'): void
{
    json_response(['success' => true, 'message' => $message, 'data' => $data]);
}

/**
 * Respond with an error payload.
 */
function api_error(string $message, int $status = 400): void
{
    json_response(['success' => false, 'message' => $message], $status);
}

/**
 * Extract and validate an integer id.
 */
function api_id(): int
{
    $id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id <= 0) {
        api_error('A valid id is required.');
    }
    return $id;
}
