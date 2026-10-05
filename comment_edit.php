<?php
require_once __DIR__ . '/helpers.php';
require_login();
require_once __DIR__ . '/db.php';
// Load the comment and check that it belongs to the signed-in user.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(404); exit('Comment not found.'); }
$query = $pdo->prepare('SELECT id, user_id, post_id, body FROM comments WHERE id = ?');
$query->execute([$id]);
$comment = $query->fetch();
if (!$comment) { http_response_code(404); exit('Comment not found.'); }
if ((int) $comment['user_id'] !== current_user_id()) { http_response_code(403); exit('You can only edit your own comments.'); }
$body = $comment['body'];
$errors = [];
// Validate the new text and update the comment with a prepared statement.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $body = trim((string) ($_POST['body'] ?? ''));
    if ($body === '' || mb_strlen($body) > 10000) $errors[] = 'Comment is required and must be 10,000 characters or fewer.';
    if (!$errors) {
        $update = $pdo->prepare('UPDATE comments SET body = ?, edited_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
        $update->execute([$body, $id, current_user_id()]);
        flash('Comment updated.');
        header('Location: post.php?id=' . (int) $comment['post_id'] . '#comments'); exit;
    }
}
$pageTitle = 'Edit Comment';
require __DIR__ . '/header.php';
?>
<!-- Form for editing the user's own comment. -->
<section class="form-card"><div class="form-top"><a class="back-link" href="post.php?id=<?= (int) $comment['post_id'] ?>#comments">Back to comments</a><h1>Edit Comment</h1></div><?php if ($errors): ?><div class="error-list" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><form method="post" data-validate><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="field"><label for="body">Comment</label><textarea id="body" name="body" maxlength="10000" required><?= e($body) ?></textarea></div><button class="button" type="submit">Save</button></form></section>
<?php require __DIR__ . '/footer.php'; ?>
