<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
// Signed-in users can go straight to the blog.
if (current_user_id() !== null) { header('Location: index.php'); exit; }
$error = '';
$email = '';
// Check the submitted email and password against the saved password hash.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') $error = 'Enter your email and password.';
    else {
        $query = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
        $query->execute([$email]);
        $user = $query->fetch();
        if ($user && password_verify($password, $user['password'])) {
            // Replace the session ID after login to prevent session fixation.
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['name'];
            flash('Logged in.');
            header('Location: index.php'); exit;
        }
        $error = 'We could not find a matching email and password.';
    }
}
$pageTitle = 'Log in';
require __DIR__ . '/header.php';
?>
<!-- Login form. The server verifies the password after submission. -->
<section class="auth-wrap"><div class="auth-card">
  <h1>Log in</h1>
  <?php if ($error): ?><div class="error-list" role="alert"><?= e($error) ?></div><?php endif; ?>
  <form method="post" data-validate><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="field"><label for="email">Email</label><input id="email" type="email" name="email" value="<?= e($email) ?>" required maxlength="254" autocomplete="email"></div>
    <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" required maxlength="72" autocomplete="current-password"></div>
    <button class="button full" type="submit">Log in</button>
  </form><p class="auth-switch">No account? <a href="register.php">Register</a></p>
</div></section>
<?php require __DIR__ . '/footer.php'; ?>
