<?php
/**
 * CargoFlow — Create a quote request (public)
 * POST /api/quotes.php
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Method not allowed.', 405);
}

verify_csrf();

$input = api_input();

$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$origin = trim($input['origin'] ?? '');
$destination = trim($input['destination'] ?? '');

if ($name === '' || $email === '' || $origin === '' || $destination === '') {
    api_error('Name, email, origin and destination are required.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    api_error('Please enter a valid email address.');
}

$serviceType = trim($input['service_type'] ?? 'standard');
$packageType = trim($input['package_type'] ?? 'parcel');
$weight = trim($input['weight'] ?? '');

// Rough price estimation for the instant estimate.
$base = ['standard' => 45, 'express' => 90, 'overnight' => 120, 'freight' => 400, 'air' => 250, 'sea' => 500];
$perKg = ['standard' => 1.2, 'express' => 2.4, 'overnight' => 3.0, 'freight' => 0.8, 'air' => 3.5, 'sea' => 0.5];
$kg = is_numeric($weight) ? (float) $weight : 1.0;
$estimated = round(($base[$serviceType] ?? 45) + ($perKg[$serviceType] ?? 1.2) * $kg, 2);

try {
    $id = insertRow('quotes', [
        'name'            => $name,
        'email'           => $email,
        'phone'           => trim($input['phone'] ?? ''),
        'origin'          => $origin,
        'destination'     => $destination,
        'service_type'    => $serviceType,
        'package_type'    => $packageType,
        'weight'          => $weight !== '' ? $weight : null,
        'message'         => trim($input['message'] ?? ''),
        'estimated_price' => $estimated,
        'status'          => 'new',
    ]);

    // Notify admins.
    notify('New quote request', "$name requested a quote for $origin → $destination.", 'info', null, 'admin/quotes.php');

    api_success(['id' => $id, 'estimated_price' => format_currency($estimated)], 'Quote request received.');
} catch (Throwable $e) {
    api_error('Could not submit quote. Please try again.', 500);
}
