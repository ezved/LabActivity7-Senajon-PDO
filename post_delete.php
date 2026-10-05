<?php
require_once __DIR__ . '/helpers.php';
require_login();
require_once __DIR__ . '/db.php';
// Deletion must use POST so it cannot be triggered by opening a link.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid post.'); }
// The owner condition prevents deleting another user's post.
$delete = $pdo->prepare('DELETE FROM posts WHERE id = ? AND user_id = ?');
$delete->execute([$id, current_user_id()]);
if (!$delete->rowCount()) { http_response_code(404); exit('Post not found or you do not own it.'); }
flash('Post deleted.');
header('Location: index.php'); exit;
