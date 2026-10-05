<?php
require_once __DIR__ . '/helpers.php';
require_login();
require_once __DIR__ . '/db.php';
// Delete requests require POST and a valid form token.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Invalid comment.'); }
// Keep the ownership check and delete in one all-or-nothing operation.
$pdo->beginTransaction();
try {
    // Lock the owned comment while checking it and deleting it as one operation.
    // Named placeholders keep SQL fixed and values separate from the query.
    $query = $pdo->prepare('SELECT post_id FROM comments WHERE id = :id AND user_id = :user_id FOR UPDATE');
    $query->execute([
        ':id' => $id,
        ':user_id' => current_user_id(),
    ]);
    $comment = $query->fetch();

    if (!$comment) {
        $pdo->rollBack();
        http_response_code(404);
        exit('Comment not found or you do not own it.');
    }

    // Delete only this user's comment, then commit the change.
    $delete = $pdo->prepare('DELETE FROM comments WHERE id = :id AND user_id = :user_id');
    $delete->execute([
        ':id' => $id,
        ':user_id' => current_user_id(),
    ]);
    $pdo->commit();
} catch (Throwable $exception) {
    // Undo any unfinished database changes if a step fails.
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Comment deletion failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Could not delete comment.');
}
flash('Comment deleted.');
header('Location: post.php?id=' . (int) $comment['post_id'] . '#comments'); exit;
