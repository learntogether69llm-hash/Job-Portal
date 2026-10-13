<?php
declare(strict_types=1);

/**
 * logout.php: ends the session (POST only, CSRF protected).
 */

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['csrf_token'] ?? null)) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => $params['path'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?: 'Lax',
        ]);
    }

    session_destroy();
    session_start();
    flash('success', 'You have been logged out.', 'login');
}

redirect('index.php');
