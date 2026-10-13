# Nexthire: Job Portal (login and sign-up)

Pure PHP + MySQL. No JavaScript.

## What you need

- PHP 8.1 or newer, with the `pdo_mysql` extension (on by default in XAMPP, MAMP and Laragon)
- MySQL 8.0.16+ **or** MariaDB 10.4+ (XAMPP uses MariaDB)

## 1. Create the database

Open phpMyAdmin (usually http://localhost/phpmyadmin), go to the **Import** tab and import one file:

| Your database | File to import |
|---|---|
| MySQL 8.0.16+ | `db/schema.sql` |
| MariaDB / XAMPP | `db/schema_mariadb.sql` |

Both create the `job_portal` database, the 7 tables and the reference data.
The only difference is the collation (`utf8mb4_0900_ai_ci` exists only in MySQL 8).

## 2. Set your database login

Open `config/config.php` and check these lines:

```php
const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'job_portal';
const DB_USER = 'root';
const DB_PASS = '';      // XAMPP default is empty
```

## 3. Run it

**Option A: XAMPP / MAMP / Laragon.** Copy this folder into `htdocs` (or `www`), start Apache and MySQL, then open:

    http://localhost/jobportal/

**Option B: PHP's built-in server.** From inside this folder:

    php -S localhost:8000

Then open http://localhost:8000/ (MySQL must already be running).

## Files

| File | Purpose |
|---|---|
| `index.php` | Log in / Sign up page (switch with the tabs) |
| `signup.php` | Validates the form, creates the user, logs them in |
| `login.php` | Logs in with email or mobile number + password |
| `logout.php` | Ends the session |
| `welcome.php` | Placeholder shown after login. Replace with onboarding |
| `config/config.php` | Database login, site name, country codes |
| `includes/bootstrap.php` | Session, CSRF, flash messages, helpers |
| `includes/db.php` | PDO connection to MySQL |
| `db/` | SQL files |
| `assets/css/auth.css` | Styles |

## Notes

- Passwords are stored with `password_hash()`. All database queries use prepared statements.
- Every form carries a CSRF token. The session id is renewed on login.
- Mobile numbers are stored with the country code, e.g. `+919876543210`.
- Datetimes are stored in UTC.
- For a live server, set `APP_DEBUG` to `false` in `config/config.php` and serve over HTTPS.
