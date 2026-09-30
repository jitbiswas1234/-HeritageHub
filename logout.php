<?php
// logout.php
require_once __DIR__ . '/includes/auth.php';
logout_user();
set_flash('info', 'You have been logged out successfully.');
header("Location: " . base_url('login.php'));
exit;
