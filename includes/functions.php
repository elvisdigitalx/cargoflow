<?php
/**
 * CargoFlow — Global helper functions
 * ---------------------------------------------------------------------
 * Loaded by includes/bootstrap.php. Contains output helpers, formatting,
 * settings access, CSRF protection, tracking-number generation and more.
 */

// PHP 7.4 compatibility polyfills (native in PHP 8+)
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('base_url')) {
    /**
     * Resolve an absolute URL against the configured base URL.
     */
    function base_url(string $path = ''): string
    {
        if (defined('BASE_URL') && BASE_URL !== '') {
            $base = rtrim(BASE_URL, '/');
        } else {
            $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            $scheme = $https ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            // Detect sub-folder installs (e.g. example.com/cargoflow)
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
            $base = $scheme . '://' . $host . ($scriptDir === '/' ? '' : $scriptDir);
        }
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('e')) {
    /**
     * HTML-escape output.
     */
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }
}

if (!function_exists('json_response')) {
    function json_response($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}

// ---------------------------------------------------------------------
// Session flash messages
// ---------------------------------------------------------------------
if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('get_flashes')) {
    function get_flashes(): array
    {
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }
}

// ---------------------------------------------------------------------
// CSRF protection
// ---------------------------------------------------------------------
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('verify_csrf')) {
    /**
     * Validate the CSRF token from POST/header. Exits with 403 on failure.
     */
    function verify_csrf(): void
    {
        $token = $_POST['csrf_token']
            ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
            if (is_api_request()) {
                json_response(['success' => false, 'message' => 'Invalid or expired CSRF token.'], 403);
            }
            http_response_code(403);
            die('Invalid CSRF token.');
        }
    }
}

if (!function_exists('is_api_request')) {
    function is_api_request(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $path = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($accept, 'application/json') !== false
            || strpos($path, '/api/') !== false;
    }
}

// ---------------------------------------------------------------------
// Formatting helpers
// ---------------------------------------------------------------------
if (!function_exists('format_currency')) {
    function format_currency($amount, ?string $currency = null): string
    {
        $symbol = setting('currency_symbol', '$');
        $currency = $currency ?: setting('currency', 'USD');
        $num = number_format((float) $amount, 2);
        return $symbol . $num . ' ' . $currency;
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'M j, Y'): string
    {
        if (!$date) {
            return '—';
        }
        $ts = strtotime($date);
        return $ts ? date($format, $ts) : '—';
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(?string $date, string $format = 'M j, Y g:i A'): string
    {
        if (!$date) {
            return '—';
        }
        $ts = strtotime($date);
        return $ts ? date($format, $ts) : '—';
    }
}

if (!function_exists('time_ago')) {
    function time_ago(?string $datetime): string
    {
        if (!$datetime) {
            return '—';
        }
        $ts = strtotime($datetime);
        $diff = time() - $ts;
        if ($diff < 0) {
            $diff = 0;
        }
        $units = [
            31536000 => 'year', 2592000 => 'month', 604800 => 'week',
            86400 => 'day', 3600 => 'hour', 60 => 'minute', 1 => 'second',
        ];
        foreach ($units as $seconds => $label) {
            if ($diff >= $seconds) {
                $val = (int) floor($diff / $seconds);
                return $val . ' ' . $label . ($val > 1 ? 's' : '') . ' ago';
            }
        }
        return 'just now';
    }
}

// ---------------------------------------------------------------------
// Domain constants / lookups
// ---------------------------------------------------------------------
if (!function_exists('shipment_statuses')) {
    function shipment_statuses(): array
    {
        return [
            'pending'          => ['Pending', 'secondary', 'clock'],
            'picked_up'        => ['Picked Up', 'info', 'box-seam'],
            'in_transit'       => ['In Transit', 'primary', 'truck'],
            'out_for_delivery' => ['Out for Delivery', 'warning', 'geo-alt'],
            'delivered'        => ['Delivered', 'success', 'check-circle'],
            'on_hold'          => ['On Hold', 'warning', 'pause-circle'],
            'customs'          => ['Customs', 'info', 'shield'],
            'cancelled'        => ['Cancelled', 'danger', 'x-circle'],
            'returned'         => ['Returned', 'danger', 'arrow-return-left'],
        ];
    }
}

if (!function_exists('status_meta')) {
    function status_meta(string $status): array
    {
        $all = shipment_statuses();
        return $all[$status] ?? [$status, 'secondary', 'circle'];
    }
}

if (!function_exists('status_label')) {
    function status_label(string $status): string
    {
        $meta = status_meta($status);
        return '<span class="badge bg-' . $meta[1] . ' rounded-pill px-2 py-1">'
            . '<i class="bi bi-' . $meta[2] . ' me-1"></i>' . e($meta[0]) . '</span>';
    }
}

if (!function_exists('service_types')) {
    function service_types(): array
    {
        return ['standard', 'express', 'overnight', 'freight', 'air', 'sea'];
    }
}

if (!function_exists('package_types')) {
    function package_types(): array
    {
        return ['document', 'parcel', 'pallet', 'container'];
    }
}

if (!function_exists('vehicle_types')) {
    function vehicle_types(): array
    {
        return ['truck', 'van', 'bike', 'ship', 'plane'];
    }
}

if (!function_exists('title_case')) {
    function title_case(string $value): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $value));
    }
}

// ---------------------------------------------------------------------
// Tracking number generation
// ---------------------------------------------------------------------
if (!function_exists('generate_tracking_number')) {
    /**
     * Generate a unique, human-friendly tracking number (e.g. CF-8K4T9W2M7Q).
     * Confusing characters are excluded. Uniqueness is guaranteed against the DB.
     */
    function generate_tracking_number(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = '';
            for ($i = 0; $i < 10; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
            $number = 'CF-' . $code;
        } while (fetchValue('SELECT COUNT(*) FROM shipments WHERE tracking_number = ?', [$number]) > 0);

        return $number;
    }
}

if (!function_exists('generate_invoice_number')) {
    function generate_invoice_number(): string
    {
        $year = date('Y');
        $count = (int) fetchValue("SELECT COUNT(*) FROM invoices WHERE invoice_number LIKE ?", ["INV-$year-%"]);
        return sprintf('INV-%s-%04d', $year, $count + 1);
    }
}

if (!function_exists('generate_customer_code')) {
    function generate_customer_code(): string
    {
        $count = (int) fetchValue('SELECT COUNT(*) FROM customers');
        return sprintf('CUS-%04d', $count + 1);
    }
}

// ---------------------------------------------------------------------
// Settings access
// ---------------------------------------------------------------------
if (!function_exists('setting')) {
    /**
     * Read a setting value (with optional default). Cached per-request.
     */
    function setting(string $key, $default = '')
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [];
            try {
                foreach (fetchAll('SELECT setting_key, setting_value FROM settings') as $row) {
                    $cache[$row['setting_key']] = $row['setting_value'];
                }
            } catch (Throwable $e) {
                // Table may not exist yet (installer). Fall through gracefully.
            }
        }
        return $cache[$key] ?? $default;
    }
}

if (!function_exists('set_setting')) {
    function set_setting(string $key, string $value): void
    {
        $exists = fetchValue('SELECT COUNT(*) FROM settings WHERE setting_key = ?', [$key]);
        if ($exists) {
            query('UPDATE settings SET setting_value = ? WHERE setting_key = ?', [$value, $key]);
        } else {
            insertRow('settings', ['setting_key' => $key, 'setting_value' => $value]);
        }
    }
}

// ---------------------------------------------------------------------
// Notifications
// ---------------------------------------------------------------------
if (!function_exists('notify')) {
    function notify(string $title, string $message, string $type = 'info', ?int $userId = null, ?string $link = null): void
    {
        insertRow('notifications', [
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'link'    => $link,
        ]);
    }
}

// ---------------------------------------------------------------------
// Misc
// ---------------------------------------------------------------------
if (!function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }
}

if (!function_exists('current_theme')) {
    function current_theme(): string
    {
        if (isset($_COOKIE['cf_theme']) && in_array($_COOKIE['cf_theme'], ['light', 'dark'])) {
            return $_COOKIE['cf_theme'];
        }
        return setting('default_theme', 'light');
    }
}

if (!function_exists('active_nav')) {
    function active_nav(string $page, string $current): string
    {
        return $page === $current ? 'active' : '';
    }
}
