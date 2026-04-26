<?php
// delete_post.php - Handle deletion of music posts/events
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../YClogin.php");
    exit();
}

// Include database connection
require_once '../YCdb_connection.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // First get the post to delete the image file
        $stmt = $pdo->prepare("SELECT image_path FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        
        if ($post) {
            // Delete the post from database
            $delete_stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            $delete_stmt->execute([$id]);

            // Delete the image file if it exists
            if ($post['image_path'] && file_exists($post['image_path'])) {
                unlink($post['image_path']);
            }

            // Redirect with success message
            header("Location: YCGalleryadmin.php?page=music&delete=success");
            exit();
        } else {
            // Post not found
            header("Location: YCGalleryadmin.php?page=music&delete=error&message=" . urlencode("Post not found."));
            exit();
        }
    } catch (PDOException $e) {
        // Database error
    header("Location: YCGalleryadmin.php?page=music&delete=error&message=" . urlencode("Database error: " . $e->getMessage()));
        exit();
    }
} else {
    // Invalid ID
    header("Location: YCGalleryadmin.php?page=music&delete=error&message=" . urlencode("Invalid post ID."));
    exit();
}
?>
