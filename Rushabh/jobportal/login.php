<?php
declare(strict_types=1);

/**
 * login.php: handles the Log in form (POST only).
 * Accepts an email or a mobile number plus a password.
 */

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

if (!csrf_check($_POST['csrf_token'] ?? null)) {
    flash('error', 'Your session expired. Please try again.', 'login');
    redirect('index.php?tab=login');
}

$identifier = trim((string) ($_POST['identifier'] ?? ''));
$password   = (string) ($_POST['password'] ?? '');
$remember   = !empty($_POST['remember']);

$errors = [];
if ($identifier === '') {
    $errors['login-identifier'] = 'Enter your email or mobile number.';
}
if ($password === '') {
    $errors['login-password'] = 'Enter your password.';
}

if ($errors) {
    flash('error', '', 'login', $errors, ['identifier' => $identifier]);
    redirect('index.php?tab=login');
}

// Find the account by email or by mobile number.
if (str_contains($identifier, '@')) {
    $column = 'email';
    $value  = strtolower($identifier);
} else {
    $column = 'mobile_number';
    $value  = normalize_mobile($identifier);
}

$user = false;
if ($value !== null) {
    // $column comes from the fixed choice above, never from user input.
    $stmt = db()->prepare("SELECT id, password_hash, status FROM users WHERE {$column} = :value LIMIT 1");
    $stmt->execute([':value' => $value]);
    $user = $stmt->fetch();
}

// Always run a password check so a missing account takes about as long as a wrong password.
const DUMMY_HASH = '$2y$10$hQwz29iIy26Y.knFXaqwjOetdHdzaKMnFfV.8PULxTKwjnIYTzAVq';
$passwordOk = password_verify($password, $user['password_hash'] ?? DUMMY_HASH);

if (!$user || !$passwordOk) {
    flash('error', 'Incorrect email, mobile number or password.', 'login', [], ['identifier' => $identifier]);
    redirect('index.php?tab=login');
}

if ($user['status'] !== 'active') {
    flash('error', 'This account is not active. Please contact support.', 'login', [], ['identifier' => $identifier]);
    redirect('index.php?tab=login');
}

// Upgrade the stored hash if PHP's default algorithm or cost has changed.
if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    $stmt = db()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
    $stmt->execute([':hash' => password_hash($password, PASSWORD_DEFAULT), ':id' => $user['id']]);
}

log_in_user((int) $user['id'], $remember);
redirect('welcome.php');
