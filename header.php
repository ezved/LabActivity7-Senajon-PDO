<?php
// Set up shared page values and read any one-time notice.
require_once __DIR__ . '/helpers.php';
$pageTitle = $pageTitle ?? 'Blog';
$notice = take_flash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Blog</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<!-- Shared page header and navigation used by all screens. -->
<header class="topbar">
  <a class="brand" href="index.php"><span>Blog</span></a>
  <nav aria-label="Main navigation">
    <?php if (current_user_id() !== null): ?>
      <span class="nav-greeting">Hi, <?= e($_SESSION['user_name'] ?? 'there') ?></span>
      <a class="button button-small" href="post_create.php">New Post</a>
      <form class="inline-form" action="logout.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="nav-link" type="submit">Log out</button></form>
    <?php else: ?>
      <a class="nav-link" href="login.php">Log in</a><a class="button button-small" href="register.php">Register</a>
    <?php endif; ?>
  </nav>
</header>
<main class="shell">
  <!-- Show success messages after actions such as creating or editing content. -->
  <?php if ($notice): ?><div class="alert alert-<?= e($notice['type']) ?>" role="status"><?= e($notice['message']) ?></div><?php endif; ?>
