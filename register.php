<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
// Registration and login pages are only for signed-out visitors.
if (current_user_id() !== null) { header('Location: index.php'); exit; }
$errors = [];
$name = '';
$email = '';
// Validate and save the registration form when it is submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['password_confirmation'] ?? '');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 80) $errors[] = 'Name must be between 2 and 80 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8 || strlen($password) > 72) $errors[] = 'Password must be between 8 and 72 characters.';
    if ($password !== $confirmation) $errors[] = 'The passwords do not match.';
    if (!$errors) {
        // Check for an existing email before inserting the new account.
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) $errors[] = 'That email is already registered.';
        else {
            $insert = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $insert->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            // Sign the new user in with a fresh session ID.
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            flash('Account created.');
            header('Location: index.php'); exit;
        }
    }
}
$pageTitle = 'Register';
require __DIR__ . '/header.php';
?>
<!-- Registration form with browser-side limits and server-side validation. -->
<section class="auth-wrap"><div class="auth-card">
  <h1>Register</h1>
  <?php if ($errors): ?><div class="error-list" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <form method="post" data-validate>
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="field"><label for="name">Name</label><input id="name" name="name" value="<?= e($name) ?>" required minlength="2" maxlength="80" autocomplete="name"></div>
    <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="<?= e($email) ?>" required maxlength="254" autocomplete="email"></div>
    <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password"><div class="help"><span>At least 8 characters</span></div></div>
    <div class="field"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" maxlength="72" autocomplete="new-password" data-match="password"></div>
    <button class="button full" type="submit">Register</button>
  </form><p class="auth-switch">Already registered? <a href="login.php">Log in</a></p>
</div></section>
<?php require __DIR__ . '/footer.php'; ?>
