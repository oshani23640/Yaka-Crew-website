<?php
// edit_media.php - Edit video entry
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../YClogin.php");
    exit();
}
require_once '../YCdb_connection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('Invalid video ID.');
}

// Fetch video data
$stmt = $pdo->prepare("SELECT * FROM video WHERE id = ?");
$stmt->execute([$id]);
$video = $stmt->fetch();
if (!$video) {
    die('Video not found.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $artist_name = $_POST['artist_name'] ?? ($video['artist_name'] ?? '');
    $music_category = $_POST['music_category'] ?? ($video['music_category'] ?? '');
    $category = $_POST['category'] ?? ($video['category'] ?? '');
    $description = $_POST['description'] ?? ($video['description'] ?? '');

    // Handle cover image upload
    $cover_image = $video['cover_image'] ?? ($video['image_path'] ?? null);
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $new_cover = 'uploads/Gallery/covers/YCvideos/' . uniqid('video_cover_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], '../' . $new_cover)) {
            $cover_image = $new_cover;
        }
    }

    // Handle video file upload
    $media_file = $video['media_file'] ?? ($video['video_file'] ?? null);
    $current_video_path = $video['video_path'] ?? null;
    $new_video_path = $current_video_path;
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION);
        $new_video = 'uploads/Gallery/covers/YCvideos/' . uniqid('video_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['video_file']['tmp_name'], '../' . $new_video)) {
            $new_video_path = $new_video;
            $media_file = $new_video;
        }
    }

    // Only update columns that exist in the table
    $columns = $pdo->query("SHOW COLUMNS FROM video")->fetchAll(PDO::FETCH_COLUMN);
    $fields = [];
    $params = [];
    $fields[] = 'title=?'; $params[] = $title;
    if (in_array('artist_name', $columns)) { $fields[] = 'artist_name=?'; $params[] = $artist_name; }
    if (in_array('music_category', $columns)) { $fields[] = 'music_category=?'; $params[] = $music_category; }
    if (in_array('category', $columns)) { $fields[] = 'category=?'; $params[] = $category; }
    if (in_array('description', $columns)) { $fields[] = 'description=?'; $params[] = $description; }
    if (in_array('cover_image', $columns)) { $fields[] = 'cover_image=?'; $params[] = $cover_image; }
    if (in_array('image_path', $columns)) { $fields[] = 'image_path=?'; $params[] = $cover_image; }
    if (in_array('video_path', $columns)) { $fields[] = 'video_path=?'; $params[] = $new_video_path; }
    if (in_array('media_file', $columns)) { $fields[] = 'media_file=?'; $params[] = $media_file; }
    if (in_array('video_file', $columns)) { $fields[] = 'video_file=?'; $params[] = $media_file; }
    $sql = "UPDATE video SET " . implode(", ", $fields) . " WHERE id=?";
    $params[] = $id;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $_SESSION['edit_media_success'] = 'Changes saved successfully!';
        header("Location: YCGalleryedit_media.php?id=$id");
        exit();
    } catch (PDOException $e) {
        $_SESSION['edit_media_error'] = 'Error updating video: ' . htmlspecialchars($e->getMessage()) . '<br>SQL: ' . htmlspecialchars($sql) . '<br>Params: ' . htmlspecialchars(json_encode($params));
        header("Location: YCGalleryedit_media.php?id=$id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Video - Yaka Crew Admin</title>
    <link rel="stylesheet" href="../css/YCGalleryadmin-style.css">
    <style>
        .edit-form-top {
            position: sticky;
            top: 0;
            background: #111;
            z-index: 2;
            padding-top: 30px;
        }
        .edit-form-container {
            max-width: 500px;
            margin: 40px auto;
            background: #111;
            padding: 0 30px 30px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .edit-form-container label {
            color: #fff;
            font-weight: 600;
            margin-top: 10px;
            display: block;
        }
        .edit-form-container input[type="text"],
        .edit-form-container input[type="file"],
        .edit-form-container select,
        .edit-form-container textarea {
            width: 100%;
            min-width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #654922;
            background: #222;
            color: #fff;
            font-size: 16px;
        }
        .edit-form-container button {
            background: #654922;
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .edit-form-container button:hover {
            background: #956E2F;
        }
        .edit-form-container img {
            display: block;
            margin-bottom: 10px;
            max-width: 180px;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
        }
        .edit-form-container video {
            display: block;
            margin-bottom: 10px;
            max-width: 240px;
            height: 140px;
            object-fit: cover;
            border-radius: 4px;
        }
        .edit-form-container h2 {
            color: #fff;
            margin-bottom: 20px;
        }
        body {
            background-color: #000000;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
<div class="edit-form-container">
    <div class="edit-form-top">
        <?php if (!empty($_SESSION['edit_media_success'])): ?>
            <div id="edit-media-success-message" style="background: #654922; color: #FFFFFF; padding: 12px; border-radius: 5px; margin-bottom: 18px; border: 1px solid #654922; text-align:center;">
                <?= $_SESSION['edit_media_success']; unset($_SESSION['edit_media_success']); ?>
            </div>
            <script>
            window.addEventListener('DOMContentLoaded', function() {
              setTimeout(function(){
                var msg = document.getElementById('edit-media-success-message');
                if(msg){ msg.style.display = 'none'; }
              }, 1200);
            });
            </script>
        <?php endif; ?>
        <?php if (!empty($_SESSION['edit_media_error'])): ?>
            <div style="background: #654922; color: #FFFFFF; padding: 12px; border-radius: 5px; margin-bottom: 18px; border: 1px solid #654922; text-align:center;">
                <?= $_SESSION['edit_media_error']; unset($_SESSION['edit_media_error']); ?>
            </div>
        <?php endif; ?>
    <h2>Edit Video</h2>
        
    </div>
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($video['title']) ?>" required>

        <label for="artist_name">Artist Name:</label>
        <input type="text" name="artist_name" id="artist_name" value="<?= htmlspecialchars($video['artist_name'] ?? '') ?>">


        <label for="music_category">Music Category:</label>
        <select name="music_category" id="music_category" required>
            <option value="hip_hop" <?= (isset($video['music_category']) && $video['music_category'] === 'hip_hop') ? 'selected' : '' ?>>Hip hop</option>
            <option value="traditional" <?= (isset($video['music_category']) && $video['music_category'] === 'traditional') ? 'selected' : '' ?>>Traditional</option>
            <option value="pop" <?= (isset($video['music_category']) && $video['music_category'] === 'pop') ? 'selected' : '' ?>>Pop</option>
            <option value="rock" <?= (isset($video['music_category']) && $video['music_category'] === 'rock') ? 'selected' : '' ?>>Rock</option>
            <option value="jazz" <?= (isset($video['music_category']) && $video['music_category'] === 'jazz') ? 'selected' : '' ?>>Jazz</option>
            <option value="reggae" <?= (isset($video['music_category']) && $video['music_category'] === 'reggae') ? 'selected' : '' ?>>Reggae</option>
            <option value="classical" <?= (isset($video['music_category']) && $video['music_category'] === 'classical') ? 'selected' : '' ?>>Classical</option>
            <option value="electronic" <?= (isset($video['music_category']) && $video['music_category'] === 'electronic') ? 'selected' : '' ?>>Electronic</option>
            <option value="folk" <?= (isset($video['music_category']) && $video['music_category'] === 'folk') ? 'selected' : '' ?>>Folk</option>
            <option value="country" <?= (isset($video['music_category']) && $video['music_category'] === 'country') ? 'selected' : '' ?>>Country</option>
            <option value="blues" <?= (isset($video['music_category']) && $video['music_category'] === 'blues') ? 'selected' : '' ?>>Blues</option>
            <option value="r&b" <?= (isset($video['music_category']) && $video['music_category'] === 'r&b') ? 'selected' : '' ?>>R&amp;B</option>
        </select>

        <label for="category">Location:</label>
        <select name="category" id="category" required>
            <option value="latest" <?= (isset($video['category']) && $video['category'] === 'latest') ? 'selected' : '' ?>>Latest Album</option>
            <option value="top" <?= (isset($video['category']) && $video['category'] === 'top') ? 'selected' : '' ?>>Top Album</option>
            <option value="video" <?= (isset($video['category']) && $video['category'] === 'video') ? 'selected' : '' ?>>Video</option>
        </select>

        <label for="description">Description:</label>
        <textarea name="description" id="description"><?= htmlspecialchars($video['description'] ?? '') ?></textarea>

        <label>Current Cover Image:</label>
        <?php 
        $cover = $video['cover_image'] ?? ($video['image_path'] ?? null);
        if (!empty($cover)) {
            // Extract filename from path
            $filename = basename($cover);
            $final_image_path = '';
            
            // Check source directory first
            if (file_exists('../source/Gallery/covers/YCvideos/' . $filename)) {
                $final_image_path = '../source/Gallery/covers/YCvideos/' . $filename;
            } elseif (file_exists('../uploads/Gallery/covers/YCvideos/' . $filename)) {
                $final_image_path = '../uploads/Gallery/covers/YCvideos/' . $filename;
            } elseif (file_exists('../' . $cover)) {
                // Fallback to original path
                $final_image_path = '../' . $cover;
            }
            
            if (!empty($final_image_path)) {
                echo '<img src="' . htmlspecialchars($final_image_path) . '" alt="Cover Image">';
            } else {
                echo '<div style="color: #aaa; margin-bottom: 10px;">No cover image available.<br>(' . htmlspecialchars($cover) . ')</div>';
            }
        } else {
            echo '<div style="color: #aaa; margin-bottom: 10px;">No cover image available.</div>';
        }
        ?>
        <label for="cover_image">Change Cover Image:</label>
        <input type="file" name="cover_image" id="cover_image" accept="image/*">


        <label>Current Video File:</label>
        <?php
            $vid = $video['video_path'] ?? ($video['media_file'] ?? $video['video_file'] ?? '');
            if ($vid && !preg_match('/(youtube\.com|youtu\.be)\//i', $vid)) {
                $filename = basename($vid);
                $final_video_path = '';
                if (file_exists('../uploads/Gallery/covers/YCvideos/' . $filename)) {
                    $final_video_path = '../uploads/Gallery/covers/YCvideos/' . $filename;
                } elseif (file_exists('../' . $vid)) {
                    $final_video_path = '../' . $vid;
                }
                if (!empty($final_video_path)) {
                    echo '<video src="' . htmlspecialchars($final_video_path) . '" controls></video>';
                } else {
                    echo '<div style="color: #aaa; margin-bottom: 10px;">No video file available.<br>(' . htmlspecialchars($vid) . ')</div>';
                }
            } else {
                echo '<div style="color: #aaa; margin-bottom: 10px;">No video file available.</div>';
            }
        ?>
        <label for="video_file">Change Video File:</label>
        <input type="file" name="video_file" id="video_file" accept="video/*">

        <button type="submit">Save Changes</button>
    <a href="YCGalleryadmin.php?page=video" style="margin-left: 20px; color: #aaa;">Cancel</a>
    </form>
</div>
</body>
</html>
