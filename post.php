<?php
require_once __DIR__ . '/helpers.php';
require_login();
require_once __DIR__ . '/db.php';
// Read the post ID from the URL and load the post with its author.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(404); exit('Post not found.'); }
$statement = $pdo->prepare('SELECT p.id, p.user_id, p.title, p.body, p.created_at, p.edited_at, u.name AS author FROM posts p JOIN users u ON u.id = p.user_id WHERE p.id = ?');
$statement->execute([$id]);
$post = $statement->fetch();
if (!$post) { http_response_code(404); exit('Post not found.'); }
// Load the comments for this post in the order they were added.
$statement = $pdo->prepare('SELECT c.id, c.user_id, c.body, c.created_at, c.edited_at, u.name AS author FROM comments c JOIN users u ON u.id = c.user_id WHERE c.post_id = ? ORDER BY c.created_at ASC, c.id ASC');
$statement->execute([$id]);
$comments = $statement->fetchAll();
$errors = [];
$commentBody = '';
// A submitted comment is validated and saved for the current user.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $commentBody = trim((string) ($_POST['body'] ?? ''));
    if ($commentBody === '' || mb_strlen($commentBody) > 10000) $errors[] = 'Comment is required and must be 10,000 characters or fewer.';
    if (!$errors) {
        $insert = $pdo->prepare('INSERT INTO comments (post_id, user_id, body) VALUES (?, ?, ?)');
        $insert->execute([$id, current_user_id(), $commentBody]);
        flash('Comment added.');
        header('Location: post.php?id=' . $id . '#comments'); exit;
    }
}
$pageTitle = $post['title'];
require __DIR__ . '/header.php';
?>
<!-- Show the post and give its owner an edit link. -->
<article class="detail-card"><div class="detail-top"><a class="back-link" href="index.php">Back to posts</a><?php if ((int) $post['user_id'] === current_user_id()): ?><a class="button button-small button-muted" href="post_edit.php?id=<?= (int) $id ?>">Edit</a><?php endif; ?></div>
<div class="post-meta" style="margin-top:28px"><span class="avatar"><?= e(mb_strtoupper(mb_substr($post['author'], 0, 1))) ?></span><span><?= e($post['author']) ?></span><span class="dot">·</span><time><?= e(date('F j, Y', strtotime($post['created_at']))) ?></time><?php if ($post['edited_at']): ?><span class="dot">·</span><span>Edited</span><?php endif; ?></div>
<h1><?= e($post['title']) ?></h1><div class="post-body"><?= e($post['body']) ?></div></article>
<section class="comments" id="comments"><h2>Comments (<?= count($comments) ?>)</h2>
<?php if (!$comments): ?><p>No comments.</p><?php endif; ?>
<?php foreach ($comments as $comment): ?><article class="comment"><div class="post-meta"><span class="avatar"><?= e(mb_strtoupper(mb_substr($comment['author'], 0, 1))) ?></span><strong><?= e($comment['author']) ?></strong><span class="dot">·</span><time><?= e(date('M j, Y · g:i a', strtotime($comment['created_at']))) ?></time><?php if ($comment['edited_at']): ?><span class="dot">·</span><span>Edited</span><?php endif; ?></div><p><?= e($comment['body']) ?></p><?php if ((int) $comment['user_id'] === current_user_id()): ?><div class="comment-actions"><a href="comment_edit.php?id=<?= (int) $comment['id'] ?>">Edit</a><form class="inline-form" method="post" action="comment_delete.php" data-confirm="Delete your reply?" style="display:inline"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $comment['id'] ?>"><button class="nav-link" type="submit" style="font-size:11px;color:var(--muted)">Delete</button></form></div><?php endif; ?></article><?php endforeach; ?>
<form class="comment-form" method="post" data-validate><h3>Add Comment</h3><?php if ($errors): ?><div class="error-list" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="field"><label for="body">Comment</label><textarea id="body" name="body" maxlength="10000" required><?= e($commentBody) ?></textarea></div><button class="button button-small" type="submit">Submit</button></form></section>
<?php require __DIR__ . '/footer.php'; ?>
