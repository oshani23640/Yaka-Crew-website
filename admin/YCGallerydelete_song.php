<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../YCdb_connection.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: YCGalleryadmin.php?page=video&delete=error&message=Invalid+ID');
    exit();
}

$id = (int)$_GET['id'];

try {
    // Fetch the song record
    $stmt = $pdo->prepare('SELECT * FROM songs WHERE id = ?');
    $stmt->execute([$id]);
    $song = $stmt->fetch();
    if (!$song) {
    header('Location: YCGalleryadmin.php?page=video&delete=error&message=Audio+item+not+found');
        exit();
    }

    // Delete audio file if exists
    if (!empty($song['audio_path']) && file_exists('../' . $song['audio_path'])) {
        @unlink('../' . $song['audio_path']);
    }
    // Delete cover image if exists (try all possible columns)
    $cover_cols = ['cover_image', 'image_path', 'thumbnail_path'];
    foreach ($cover_cols as $col) {
        if (!empty($song[$col]) && file_exists('../' . $song[$col])) {
            @unlink('../' . $song[$col]);
        }
    }

    // Delete from database
    $del = $pdo->prepare('DELETE FROM songs WHERE id = ?');
    $del->execute([$id]);

    header('Location: YCGalleryadmin.php?page=video&delete=success');
    exit();
} catch (PDOException $e) {
    header('Location: YCGalleryadmin.php?page=video&delete=error&message=' . urlencode($e->getMessage()));
    exit();
}
