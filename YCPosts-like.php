<?php
// YCPosts-like.php: increments the like count for a post via AJAX
header('Content-Type: application/json');
require_once 'YCdb_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
	if ($postId > 0) {
		// Increment likes atomically
		$stmt = $pdo->prepare('UPDATE posts SET likes = likes + 1 WHERE id = ?');
		$stmt->execute([$postId]);
		// Get new like count
		$stmt2 = $pdo->prepare('SELECT likes FROM posts WHERE id = ?');
		$stmt2->execute([$postId]);
		$likes = $stmt2->fetchColumn();
		echo json_encode(['success' => true, 'likes' => (int)$likes]);
		exit;
	}
}
echo json_encode(['success' => false]);
