<?php
require_once __DIR__ . '/helpers.php';
require_login();
require_once __DIR__ . '/db.php';
// Load every user's posts, newest first, with a comment count for each post.
$posts = $pdo->query('SELECT p.id, p.user_id, p.title, p.body, p.created_at, p.edited_at, u.name AS author, (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS comment_count FROM posts p JOIN users u ON u.id = p.user_id ORDER BY p.created_at DESC, p.id DESC')->fetchAll();
$pageTitle = 'Blog Posts';
require __DIR__ . '/header.php';
?>
<!-- Home feed showing recent posts from all users. -->
<section class="hero"><h1>Blog Posts</h1><p>Recent posts from all users.</p></section>
<div class="feed-heading"><h2>Latest Posts</h2><span><?= count($posts) ?> <?= count($posts) === 1 ? 'post' : 'posts' ?></span></div>
<?php if (!$posts): ?><div class="empty-state"><strong>No posts yet.</strong><a class="button" href="post_create.php">Write the first post</a></div>
<?php else: ?><div class="post-list">
<?php foreach ($posts as $post): $excerpt = mb_strlen($post['body']) > 320 ? mb_substr($post['body'], 0, 320) . '...' : $post['body']; ?>
  <article class="post-card"><div class="post-meta"><span><?= e($post['author']) ?></span> | <time><?= e(date('M j, Y', strtotime($post['created_at']))) ?></time><?php if ($post['edited_at']): ?><span>Edited</span><?php endif; ?></div>
  <h3><a href="post.php?id=<?= (int) $post['id'] ?>"><?= e($post['title']) ?></a></h3><p class="post-excerpt"><?= e($excerpt) ?></p><div class="post-foot"><span><?= (int) $post['comment_count'] ?> <?= (int) $post['comment_count'] === 1 ? 'comment' : 'comments' ?></span><div class="post-actions"><a class="text-link" href="post.php?id=<?= (int) $post['id'] ?>">View</a><?php if ((int) ($_SESSION['user_id'] ?? 0) === (int) $post['user_id']): ?><a class="quiet-link" href="post_edit.php?id=<?= (int) $post['id'] ?>">Edit</a><?php endif; ?></div></div></article>
<?php endforeach; ?></div><?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
