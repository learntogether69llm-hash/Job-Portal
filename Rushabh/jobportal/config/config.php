<?php
declare(strict_types=1);

/**
 * config/config.php
 * Edit the values below to match your local setup.
 */

// ---------- Site ----------
const SITE_NAME = 'Nexthire';

// Show PHP errors on screen. Keep true on localhost, set to false on a live server.
const APP_DEBUG = true;

// ---------- Database (XAMPP / MAMP / Laragon defaults) ----------
const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'job_portal';
const DB_USER = 'root';
const DB_PASS = '';          // XAMPP default is empty. MySQL Installer: use the password you set.

if (!defined('DB_DSN')) {
    define('DB_DSN', sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME));
}

// ---------- Mobile numbers ----------
// Used when someone logs in with a 10-digit number and no country code.
const DEFAULT_COUNTRY_CODE = '+91';

const COUNTRY_CODES = [
    '+91'  => 'IN +91',
    '+971' => 'AE +971',
    '+966' => 'SA +966',
    '+974' => 'QA +974',
    '+965' => 'KW +965',
    '+968' => 'OM +968',
    '+973' => 'BH +973',
    '+92'  => 'PK +92',
    '+880' => 'BD +880',
    '+94'  => 'LK +94',
    '+977' => 'NP +977',
    '+63'  => 'PH +63',
    '+65'  => 'SG +65',
    '+60'  => 'MY +60',
    '+44'  => 'GB +44',
    '+1'   => 'US +1',
    '+61'  => 'AU +61',
];

// ---------- Sessions ----------
const REMEMBER_ME_DAYS = 30;
