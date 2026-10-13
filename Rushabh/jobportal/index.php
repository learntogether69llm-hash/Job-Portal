<?php
declare(strict_types=1);

/**
 * index.php: Log in / Sign up page (pure PHP, no JavaScript).
 * The forms post to login.php and signup.php, which validate on the server
 * and send people back here with messages (see flash() in includes/bootstrap.php).
 */

require_once __DIR__ . '/includes/bootstrap.php';

if (current_user() !== null) {
    redirect('welcome.php');
}

$csrf = csrf_token();

$flash  = $_SESSION['flash']  ?? null;
$errors = $_SESSION['errors'] ?? [];
$old    = $_SESSION['old']    ?? [];
unset($_SESSION['flash'], $_SESSION['errors'], $_SESSION['old']);

$tab = $_GET['tab'] ?? ($flash['tab'] ?? 'login');
$tab = ($tab === 'signup') ? 'signup' : 'login';

$selectedCode = $old['country_code'] ?? DEFAULT_COUNTRY_CODE;

function field_class(array $errors, string $id): string
{
    return isset($errors[$id]) ? ' has-error' : '';
}

function field_invalid(array $errors, string $id): string
{
    return isset($errors[$id]) ? 'true' : 'false';
}

function field_error_html(array $errors, string $id): string
{
    if (!isset($errors[$id])) {
        return '';
    }
    return '<p class="field-error" id="' . e($id) . '-error">' . e($errors[$id]) . '</p>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#0B3040">
  <title><?= $tab === 'signup' ? 'Sign up' : 'Log in' ?> · <?= e(SITE_NAME) ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>

<div class="auth">

  <!-- ============ Left: brand / showcase ============ -->
  <aside class="showcase" aria-label="About <?= e(SITE_NAME) ?>">
    <a class="brand" href="index.php">
      <svg viewBox="0 0 36 32" width="36" height="32" aria-hidden="true">
        <circle cx="13" cy="16" r="10" fill="#FFB547"/>
        <circle cx="23" cy="16" r="9.25" fill="none" stroke="#fff" stroke-width="2.5"/>
      </svg>
      <span><?= e(SITE_NAME) ?></span>
    </a>

    <div class="showcase-copy">
      <p class="tagline">The right role is looking for you.</p>
      <p class="lede">Employers post what they need. Job seekers share who they are. We make the introduction.</p>
    </div>

    <div class="stage" aria-hidden="true">
      <div class="mcard mcard--seeker">
        <div class="mcard-head">
          <span class="avatar">PS</span>
          <div>
            <strong>Priya Shah</strong>
            <span>Looking for work · Ahmedabad</span>
          </div>
        </div>
        <ul class="chips">
          <li>Information Technology</li>
          <li>Bachelor's Degree</li>
          <li>3.5 yrs experience</li>
        </ul>
      </div>

      <div class="match">
        <span class="match-chip">
          <svg viewBox="0 0 16 16" width="14" height="14"><path d="M3.5 8.5l3 3 6-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          It's a match
        </span>
      </div>

      <div class="mcard mcard--role">
        <div class="mcard-head">
          <span class="logo-tile">B</span>
          <div>
            <strong>Senior PHP Developer</strong>
            <span>Brightpath Systems · Ahmedabad</span>
          </div>
        </div>
        <ul class="chips">
          <li>Information Technology</li>
          <li>Full Time</li>
          <li>₹ 9,00,000 / year</li>
        </ul>
      </div>
    </div>

    <p class="showcase-foot">Free to join. Your profile takes about two minutes to set up.</p>
  </aside>

  <!-- ============ Right: forms ============ -->
  <main class="panel">
    <div class="panel-inner">

      <nav class="tabs" aria-label="Account" data-active="<?= e($tab) ?>">
        <span class="tabs-pill" aria-hidden="true"></span>
        <a href="index.php?tab=login"<?= $tab === 'login' ? ' aria-current="page"' : '' ?>>Log in</a>
        <a href="index.php?tab=signup"<?= $tab === 'signup' ? ' aria-current="page"' : '' ?>>Sign up</a>
      </nav>

      <?php if ($flash && !empty($flash['message'])): ?>
        <div class="alert alert--<?= ($flash['type'] ?? 'error') === 'success' ? 'success' : 'error' ?>" role="alert">
          <?= e($flash['message']) ?>
        </div>
      <?php endif; ?>

<?php if ($tab === 'login'): ?>
      <!-- ---------- LOG IN ---------- -->
      <section id="panel-login">
        <h1 class="heading">Welcome back</h1>
        <p class="subheading">Log in to pick up where you left off.</p>

        <form class="form" method="post" action="login.php" novalidate>
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

          <div class="field<?= field_class($errors, 'login-identifier') ?>">
            <label for="login-identifier">Email or mobile number</label>
            <div class="control">
              <input type="text" id="login-identifier" name="identifier"
                     value="<?= e($old['identifier'] ?? '') ?>"
                     placeholder="you@example.com or +919876543210"
                     autocomplete="username" autocapitalize="none" spellcheck="false" autofocus
                     aria-invalid="<?= field_invalid($errors, 'login-identifier') ?>"
                     <?= isset($errors['login-identifier']) ? 'aria-describedby="login-identifier-error"' : '' ?>>
            </div>
            <?= field_error_html($errors, 'login-identifier') ?>
          </div>

          <div class="field<?= field_class($errors, 'login-password') ?>">
            <label for="login-password">Password</label>
            <div class="control">
              <input type="password" id="login-password" name="password"
                     placeholder="Enter your password"
                     autocomplete="current-password"
                     aria-invalid="<?= field_invalid($errors, 'login-password') ?>"
                     <?= isset($errors['login-password']) ? 'aria-describedby="login-password-error"' : '' ?>>
            </div>
            <?= field_error_html($errors, 'login-password') ?>
          </div>

          <div class="row">
            <label class="check">
              <input type="checkbox" name="remember" value="1">
              <span>Keep me signed in</span>
            </label>
            <a href="forgot-password.php">Forgot password?</a>
          </div>

          <button type="submit" class="btn">Log in</button>

          <p class="switch">New to <?= e(SITE_NAME) ?>?
            <a href="index.php?tab=signup">Create an account</a>
          </p>
        </form>
      </section>

<?php else: ?>
      <!-- ---------- SIGN UP ---------- -->
      <section id="panel-signup">
        <h1 class="heading">Create your account</h1>
        <p class="subheading">Takes about two minutes. Next, you'll choose whether you're hiring or looking for work.</p>

        <form class="form" method="post" action="signup.php" novalidate>
          <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

          <div class="field<?= field_class($errors, 'signup-name') ?>">
            <label for="signup-name">Full name</label>
            <div class="control">
              <input type="text" id="signup-name" name="name"
                     value="<?= e($old['name'] ?? '') ?>"
                     placeholder="Priya Shah" maxlength="150"
                     autocomplete="name" autofocus
                     aria-invalid="<?= field_invalid($errors, 'signup-name') ?>"
                     <?= isset($errors['signup-name']) ? 'aria-describedby="signup-name-error"' : '' ?>>
            </div>
            <?= field_error_html($errors, 'signup-name') ?>
          </div>

          <div class="field<?= field_class($errors, 'signup-email') ?>">
            <label for="signup-email">Email</label>
            <div class="control">
              <input type="email" id="signup-email" name="email"
                     value="<?= e($old['email'] ?? '') ?>"
                     placeholder="you@example.com" maxlength="255"
                     autocomplete="email" autocapitalize="none" spellcheck="false"
                     aria-invalid="<?= field_invalid($errors, 'signup-email') ?>"
                     <?= isset($errors['signup-email']) ? 'aria-describedby="signup-email-error"' : '' ?>>
            </div>
            <?= field_error_html($errors, 'signup-email') ?>
          </div>

          <div class="field<?= field_class($errors, 'signup-mobile') ?>">
            <label for="signup-mobile">Mobile number</label>
            <div class="phone">
              <select id="signup-country" name="country_code" aria-label="Country code">
                <?php foreach (COUNTRY_CODES as $code => $label): ?>
                  <option value="<?= e($code) ?>"<?= $code === $selectedCode ? ' selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
              <input type="tel" id="signup-mobile" name="mobile"
                     value="<?= e($old['mobile'] ?? '') ?>"
                     placeholder="98765 43210" inputmode="numeric"
                     autocomplete="tel-national"
                     aria-invalid="<?= field_invalid($errors, 'signup-mobile') ?>"
                     <?= isset($errors['signup-mobile']) ? 'aria-describedby="signup-mobile-error"' : '' ?>>
            </div>
            <?= field_error_html($errors, 'signup-mobile') ?>
          </div>

          <div class="field<?= field_class($errors, 'signup-password') ?>">
            <label for="signup-password">Password</label>
            <div class="control">
              <input type="password" id="signup-password" name="password"
                     placeholder="Create a password"
                     autocomplete="new-password"
                     aria-describedby="signup-password-hint<?= isset($errors['signup-password']) ? ' signup-password-error' : '' ?>"
                     aria-invalid="<?= field_invalid($errors, 'signup-password') ?>">
            </div>
            <p class="hint" id="signup-password-hint">Use 8+ characters with at least one letter and one number.</p>
            <?= field_error_html($errors, 'signup-password') ?>
          </div>

          <div class="field<?= field_class($errors, 'signup-confirm') ?>">
            <label for="signup-confirm">Confirm password</label>
            <div class="control">
              <input type="password" id="signup-confirm" name="confirm_password"
                     placeholder="Re-enter your password"
                     autocomplete="new-password"
                     aria-invalid="<?= field_invalid($errors, 'signup-confirm') ?>"
                     <?= isset($errors['signup-confirm']) ? 'aria-describedby="signup-confirm-error"' : '' ?>>
            </div>
            <?= field_error_html($errors, 'signup-confirm') ?>
          </div>

          <div class="field<?= field_class($errors, 'signup-terms') ?>">
            <label class="check check--top">
              <input type="checkbox" id="signup-terms" name="terms" value="1"
                     aria-invalid="<?= field_invalid($errors, 'signup-terms') ?>"
                     <?= isset($errors['signup-terms']) ? 'aria-describedby="signup-terms-error"' : '' ?>>
              <span>I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a>.</span>
            </label>
            <?= field_error_html($errors, 'signup-terms') ?>
          </div>

          <button type="submit" class="btn">Create account</button>

          <p class="switch">Already have an account?
            <a href="index.php?tab=login">Log in</a>
          </p>
        </form>
      </section>
<?php endif; ?>

    </div>
  </main>

</div>

</body>
</html>
