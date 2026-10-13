<?php
declare(strict_types=1);

/**
 * signup.php: handles the Sign up form (POST only).
 * Creates a row in `users` with role = NULL. The role (employer / job seeker)
 * is chosen in the next step, onboarding.
 */

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php?tab=signup');
}

if (!csrf_check($_POST['csrf_token'] ?? null)) {
    flash('error', 'Your session expired. Please try again.', 'signup');
    redirect('index.php?tab=signup');
}

$name        = trim((string) ($_POST['name'] ?? ''));
$email       = strtolower(trim((string) ($_POST['email'] ?? '')));
$countryCode = (string) ($_POST['country_code'] ?? DEFAULT_COUNTRY_CODE);
$mobileRaw   = trim((string) ($_POST['mobile'] ?? ''));
$password    = (string) ($_POST['password'] ?? '');
$confirm     = (string) ($_POST['confirm_password'] ?? '');
$terms       = !empty($_POST['terms']);

if (!isset(COUNTRY_CODES[$countryCode])) {
    $countryCode = DEFAULT_COUNTRY_CODE;
}

$errors = [];

// ---------- Validate ----------
$nameLength = mb_strlen($name);
if ($name === '') {
    $errors['signup-name'] = 'Enter your full name.';
} elseif ($nameLength < 2) {
    $errors['signup-name'] = 'Your name needs at least 2 characters.';
} elseif ($nameLength > 150) {
    $errors['signup-name'] = 'Your name is too long (150 characters at most).';
}

if ($email === '') {
    $errors['signup-email'] = 'Enter your email address.';
} elseif (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['signup-email'] = 'Enter a valid email address.';
}

$mobileDigits = ltrim(preg_replace('/\D/', '', $mobileRaw) ?? '', '0');
$mobileNumber = null;
if ($mobileDigits === '') {
    $errors['signup-mobile'] = 'Enter your mobile number.';
} elseif ($countryCode === '+91' && !preg_match('/^[6-9]\d{9}$/', $mobileDigits)) {
    $errors['signup-mobile'] = 'Enter a 10-digit Indian mobile number.';
} elseif (strlen($mobileDigits) < 6 || strlen($mobileDigits) > 12) {
    $errors['signup-mobile'] = 'Enter a valid mobile number.';
} else {
    $mobileNumber = $countryCode . $mobileDigits; // e.g. +919876543210
}

if ($password === '') {
    $errors['signup-password'] = 'Create a password.';
} elseif (strlen($password) < 8) {
    $errors['signup-password'] = 'Use at least 8 characters.';
} elseif (strlen($password) > 72) {
    $errors['signup-password'] = 'Use 72 characters or fewer.'; // bcrypt ignores anything longer
} elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
    $errors['signup-password'] = 'Include at least one letter and one number.';
}

if ($confirm === '') {
    $errors['signup-confirm'] = 'Re-enter your password.';
} elseif ($password !== $confirm) {
    $errors['signup-confirm'] = 'Passwords do not match.';
}

if (!$terms) {
    $errors['signup-terms'] = 'Accept the Terms and Privacy Policy to continue.';
}

// ---------- Check for existing accounts ----------
if (!isset($errors['signup-email'])) {
    $stmt = db()->prepare('SELECT 1 FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetchColumn()) {
        $errors['signup-email'] = 'That email is already registered. Try logging in instead.';
    }
}
if ($mobileNumber !== null) {
    $stmt = db()->prepare('SELECT 1 FROM users WHERE mobile_number = :mobile LIMIT 1');
    $stmt->execute([':mobile' => $mobileNumber]);
    if ($stmt->fetchColumn()) {
        $errors['signup-mobile'] = 'That mobile number is already registered. Try logging in instead.';
    }
}

// Never send passwords back to the form.
$old = ['name' => $name, 'email' => $email, 'mobile' => $mobileRaw, 'country_code' => $countryCode];

if ($errors) {
    flash('error', 'Please fix the highlighted fields.', 'signup', $errors, $old);
    redirect('index.php?tab=signup');
}

// ---------- Create the account ----------
try {
    $stmt = db()->prepare(
        'INSERT INTO users (name, email, mobile_number, password_hash)
         VALUES (:name, :email, :mobile, :hash)'
    );
    $stmt->execute([
        ':name'   => $name,
        ':email'  => $email,
        ':mobile' => $mobileNumber,
        ':hash'   => password_hash($password, PASSWORD_DEFAULT),
    ]);
    $userId = (int) db()->lastInsertId();
} catch (PDOException $e) {
    // Two people signing up with the same details at the same moment.
    if ($e->getCode() === '23000') {
        flash('error', 'That email or mobile number is already registered.', 'signup', [], $old);
        redirect('index.php?tab=signup');
    }
    throw $e;
}

log_in_user($userId, false);
redirect('welcome.php');
