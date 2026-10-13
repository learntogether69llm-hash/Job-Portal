<?php
declare(strict_types=1);

/**
 * includes/db.php
 * One shared PDO connection to MySQL. Call db() anywhere after bootstrap.php is loaded.
 */

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            echo 'Could not connect to the database. ';
            echo APP_DEBUG
                ? 'Check config/config.php and make sure MySQL is running. (' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . ')'
                : 'Please try again later.';
            exit;
        }
    }

    return $pdo;
}
