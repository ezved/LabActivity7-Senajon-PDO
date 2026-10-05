<?php
require_once __DIR__ . '/helpers.php';
require_login();
require_once __DIR__ . '/db.php';
// Keep form values so they can be shown again if validation fails.
$errors = [];
$title = '';
$body = '';
// Validate the post, then insert it with a prepared statement.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string) ($_POST['title'] ?? ''));
    $body = trim((string) ($_POST['body'] ?? ''));
    if ($title === '' || mb_strlen($title) > 160) $errors[] = 'Title is required and must be 160 characters or fewer.';
    if ($body === '' || mb_strlen($body) > 60000) $errors[] = 'Post text is required and must be 60,000 characters or fewer.';
    if (!$errors) {
        $insert = $pdo->prepare('INSERT INTO posts (user_id, title, body) VALUES (?, ?, ?)');
        $insert->execute([current_user_id(), $title, $body]);
        $id = (int) $pdo->lastInsertId();
        flash('Post created.');
        header('Location: post.php?id=' . $id); exit;
    }
}
$pageTitle = 'New Post';
require __DIR__ . '/header.php';
?>
<!-- Form for creating a text-only post. -->
<section class="form-card"><div class="form-top"><a class="back-link" href="index.php">Back to posts</a><h1>New Post</h1></div>
<?php if ($errors): ?><div class="error-list" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" data-validate><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="field"><label for="title">Title</label><input id="title" name="title" value="<?= e($title) ?>" required maxlength="160"></div><div class="field"><label for="body">Post</label><textarea id="body" name="body" required maxlength="60000"><?= e($body) ?></textarea><div class="help"><span></span><span id="body-count"><?= mb_strlen($body) ?> / 60000</span></div></div><button class="button" type="submit">Publish</button></form></section>
<?php require __DIR__ . '/footer.php'; ?>
