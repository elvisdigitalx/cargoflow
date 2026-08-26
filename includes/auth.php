<?php
/**
 * CargoFlow — Authentication & authorization helpers
 * ---------------------------------------------------------------------
 * Provides session-based login/logout, an admin route guard and role
 * checks. Passwords are hashed with password_hash()/password_verify().
 */

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return !empty($_SESSION['user_id']);
    }
}

if (!function_exists('current_user')) {
    /**
     * Return the currently logged-in user (or null).
     */
    function current_user(): ?array
    {
        if (!is_logged_in()) {
            return null;
        }
        static $user = null;
        if ($user === null) {
            $user = fetchOne('SELECT * FROM users WHERE id = ?', [(int) $_SESSION['user_id']]);
        }
        return $user;
    }
}

if (!function_exists('require_login')) {
    /**
     * Redirect unauthenticated users to the login page.
     */
    function require_login(): void
    {
        if (!is_logged_in()) {
            redirect(base_url('login.php') . '?next=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
        }
    }
}

if (!function_exists('require_role')) {
    function require_role(array $roles): void
    {
        require_login();
        $user = current_user();
        if (!$user || !in_array($user['role'], $roles, true)) {
            http_response_code(403);
            die('Access denied. You do not have permission to view this page.');
        }
    }
}

if (!function_exists('attempt_login')) {
    /**
     * Validate credentials and start a session.
     *
     * @return array{success: bool, message?: string, user?: array}
     */
    function attempt_login(string $identifier, string $password, bool $remember = false): array
    {
        $user = fetchOne(
            'SELECT * FROM users WHERE (email = ? OR username = ?) LIMIT 1',
            [$identifier, $identifier]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email/username or password.'];
        }

        if ($user['status'] !== 'active') {
            return ['success' => false, 'message' => 'Your account has been deactivated. Contact an administrator.'];
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_role'] = $user['role'];

        query('UPDATE users SET last_login = NOW() WHERE id = ?', [$user['id']]);

        if ($remember) {
            // Extend the session cookie lifetime for "remember me".
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                session_id(),
                ['expires' => time() + REMEMBER_ME_DAYS * 86400, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']
            );
        }

        return ['success' => true, 'user' => $user];
    }
}

if (!function_exists('logout_user')) {
    function logout_user(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        setcookie('cf_theme', '', time() - 3600, '/');
    }
}
