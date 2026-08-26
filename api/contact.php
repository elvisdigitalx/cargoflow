<?php
/**
 * CargoFlow — Create a contact message (public)
 * POST /api/contact.php
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
$message = trim($input['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    api_error('Name, email and message are required.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    api_error('Please enter a valid email address.');
}

try {
    $id = insertRow('contact_messages', [
        'name'    => $name,
        'email'   => $email,
        'phone'   => trim($input['phone'] ?? ''),
        'subject' => trim($input['subject'] ?? ''),
        'message' => $message,
        'status'  => 'new',
    ]);

    notify('New contact message', "$name sent a message: " . mb_substr($message, 0, 80), 'info', null, 'admin/messages.php');

    api_success(['id' => $id], 'Message sent successfully.');
} catch (Throwable $e) {
    api_error('Could not send message. Please try again.', 500);
}
