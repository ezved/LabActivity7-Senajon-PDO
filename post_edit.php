<?php
require_once __DIR__ . '/helpers.php';
require_login();
require_once __DIR__ . '/db.php';
// Find the post and make sure it belongs to the signed-in user.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(404); exit('Post not found.'); }
$query = $pdo->prepare('SELECT id, user_id, title, body FROM posts WHERE id = ?');
$query->execute([$id]);
$post = $query->fetch();
if (!$post) { http_response_code(404); exit('Post not found.'); }
if ((int) $post['user_id'] !== current_user_id()) { http_response_code(403); exit('You can only edit your own posts.'); }
$errors = [];
$title = $post['title'];
$body = $post['body'];
// Validate changes and update only this user's post.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string) ($_POST['title'] ?? ''));
    $body = trim((string) ($_POST['body'] ?? ''));
    if ($title === '' || mb_strlen($title) > 160) $errors[] = 'Title is required and must be 160 characters or fewer.';
    if ($body === '' || mb_strlen($body) > 60000) $errors[] = 'Post text is required and must be 60,000 characters or fewer.';
    if (!$errors) {
        $update = $pdo->prepare('UPDATE posts SET title = ?, body = ?, edited_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
        $update->execute([$title, $body, $id, current_user_id()]);
        flash('Post updated.');
        header('Location: post.php?id=' . $id); exit;
    }
}
$pageTitle = 'Edit Post';
require __DIR__ . '/header.php';
?>
<!-- Edit form plus a separate protected delete form. -->
<section class="form-card"><div class="form-top"><a class="back-link" href="post.php?id=<?= (int) $id ?>">Back to post</a><h1>Edit Post</h1></div>
<?php if ($errors): ?><div class="error-list" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" data-validate><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="field"><label for="title">Title</label><input id="title" name="title" value="<?= e($title) ?>" required maxlength="160"></div><div class="field"><label for="body">Post</label><textarea id="body" name="body" required maxlength="60000"><?= e($body) ?></textarea><div class="help"><span></span><span><?= mb_strlen($body) ?> / 60000</span></div></div><button class="button" type="submit">Save</button></form>
<form method="post" action="post_delete.php" data-confirm="Delete this post and all its comments? This cannot be undone." style="margin-top:14px"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $id ?>"><button class="button button-danger" type="submit">Delete post</button></form></section>
<?php require __DIR__ . '/footer.php'; ?>
