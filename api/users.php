<?php
/**
 * CargoFlow — Users (staff accounts) admin API
 * Requires admin role.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/api.php';

api_require_admin();
require_role(['admin']);

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($_SERVER['REQUEST_METHOD'] === 'GET' || $action === 'list') {
    $rows = fetchAll('SELECT id, name, email, username, role, status, last_login, created_at FROM users ORDER BY id ASC');
    json_response(['success' => true, 'data' => $rows]);
}

$input = api_input();

if ($action === 'create') {
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    $username = trim($input['username'] ?? '');
    $password = (string) ($input['password'] ?? '');
    $role = trim($input['role'] ?? 'staff');

    if ($name === '' || $email === '' || $username === '' || strlen($password) < 8) {
        api_error('Name, email, username and a password of 8+ characters are required.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        api_error('Invalid email address.');
    }
    if (fetchValue('SELECT COUNT(*) FROM users WHERE email = ? OR username = ?', [$email, $username]) > 0) {
        api_error('A user with that email or username already exists.');
    }

    $id = insertRow('users', [
        'name'     => $name,
        'email'    => $email,
        'username' => $username,
        'password' => password_hash($password, PASSWORD_BCRYPT),
        'role'     => in_array($role, ['admin', 'manager', 'staff']) ? $role : 'staff',
        'status'   => trim($input['status'] ?? 'active'),
    ]);
    api_success(['id' => $id], 'User created.');
}

if ($action === 'update') {
    $id = api_id();
    $fields = ['name', 'email', 'username', 'role', 'status'];
    $data = [];
    foreach ($fields as $f) {
        if (array_key_exists($f, $input)) {
            $data[$f] = trim((string) $input[$f]);
        }
    }
    if (!empty($input['password']) && strlen((string) $input['password']) >= 8) {
        $data['password'] = password_hash((string) $input['password'], PASSWORD_BCRYPT);
    }
    if (empty($data)) {
        api_error('No fields to update.');
    }
    updateRow('users', $id, $data);
    api_success([], 'User updated.');
}

if ($action === 'delete') {
    $id = api_id();
    if ((int) $id === (int) $_SESSION['user_id']) {
        api_error('You cannot delete your own account.');
    }
    deleteRow('users', $id);
    api_success([], 'User deleted.');
}

api_error('Unknown action.', 400);
