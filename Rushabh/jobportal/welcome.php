<?php
declare(strict_types=1);

/**
 * welcome.php: placeholder page shown after signing up or logging in.
 * Replace this with the onboarding step ("Looking for an Employee" / "Looking for Work").
 */

require_once __DIR__ . '/includes/bootstrap.php';

$user  = require_login();
$first = explode(' ', trim($user['name']))[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Welcome · <?= e(SITE_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
  <main class="welcome">
    <div class="welcome-card">
      <p class="welcome-brand"><?= e(SITE_NAME) ?></p>
      <h1 class="heading">You're in, <?= e($first) ?>.</h1>
      <p class="subheading">
        Your account is ready<?= $user['role'] === null ? '. Next up: tell us whether you are hiring or looking for work.' : '.' ?>
      </p>

      <dl class="details">
        <div><dt>Email</dt><dd><?= e($user['email']) ?></dd></div>
        <div><dt>Mobile</dt><dd><?= e($user['mobile_number']) ?></dd></div>
        <div><dt>Role</dt><dd><?= $user['role'] === null ? 'Not chosen yet' : e(str_replace('_', ' ', $user['role'])) ?></dd></div>
      </dl>

      <form method="post" action="logout.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <button type="submit" class="btn btn--quiet">Log out</button>
      </form>
    </div>
  </main>
</body>
</html>
