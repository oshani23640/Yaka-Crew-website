<?php
// delete_media.php - Handle deletion of gallery media items
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
        // Get the video to delete video file (and thumbnail if column exists)
        // Check columns in video table
        $columns = $pdo->query("SHOW COLUMNS FROM video")->fetchAll(PDO::FETCH_COLUMN);
        $select = 'video_path';
        $has_thumb = false;
        if (in_array('thumbnail_path', $columns)) {
            $select .= ', thumbnail_path';
            $has_thumb = true;
        }
        $stmt = $pdo->prepare("SELECT $select FROM video WHERE id = ?");
        $stmt->execute([$id]);
        $video = $stmt->fetch();
        if ($video) {
            // Delete video file
            if (!empty($video['video_path'])) {
                $video_path = $video['video_path'];
                if (!file_exists($video_path) && file_exists('../' . $video_path)) {
                    $video_path = '../' . $video_path;
                }
                if (file_exists($video_path)) {
                    unlink($video_path);
                }
            }
            // Delete thumbnail if column exists
            if ($has_thumb && !empty($video['thumbnail_path'])) {
                $thumb_path = $video['thumbnail_path'];
                if (!file_exists($thumb_path) && file_exists('../' . $thumb_path)) {
                    $thumb_path = '../' . $thumb_path;
                }
                if (file_exists($thumb_path)) {
                    unlink($thumb_path);
                }
            }
            // Delete from database
            $deleteStmt = $pdo->prepare("DELETE FROM video WHERE id = ?");
            $deleteStmt->execute([$id]);
            if ($deleteStmt->rowCount() > 0) {
                header("Location: YCGalleryadmin.php?page=video&delete=success");
                exit();
            } else {
                header("Location: YCGalleryadmin.php?page=video&delete=error&message=" . urlencode("Video could not be deleted from database."));
                exit();
            }
        } else {
            header("Location: YCGalleryadmin.php?page=video&delete=error&message=" . urlencode("Video not found."));
            exit();
        }
    } catch (PDOException $e) {
    header("Location: YCGalleryadmin.php?page=video&delete=error&message=" . urlencode("Database error: " . $e->getMessage()));
        exit();
    }
} else {
    // Invalid ID
    header("Location: YCGalleryadmin.php?page=video&delete=error&message=" . urlencode("Invalid media ID."));
    exit();
}
?>
