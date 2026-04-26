<?php
session_start();
require_once __DIR__ . '/../YCdb_connection.php';
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo "Invalid blog post ID.";
    exit;
}
$blog_id = intval($_POST['id']);
$stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
if ($stmt->execute([$blog_id])) {
    $_SESSION['success'] = "Blog deleted successfully.";
    header("Location: YCBlogs-admin.php?page=blogs");
    exit;
} else {
    $_SESSION['error'] = "Error deleting blog post.";
    header("Location: YCBlogs-admin.php?page=blogs");
    exit;
}
?>
