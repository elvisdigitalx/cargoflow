<?php
/**
 * CargoFlow — Logout
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();
redirect(base_url('login.php'));
