<?php
// upload_posts.php - Handle posts/events uploads for Posts.php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../YClogin.php");
    exit();
}

require_once '../YCdb_connection.php';

// Helper to get files from source/Gallery/YCposts
function getSourceFiles($directory) {
    $files = [];
    if (is_dir($directory)) {
        $items = scandir($directory);
        foreach ($items as $item) {
            if ($item != '.' && $item != '..' && is_file($directory . $item)) {
                $files[] = $item;
            }
        }
    }
    return $files;
}

$source_posts = getSourceFiles('../source/Gallery/YCposts/');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $location = $_POST['location'] ?? '';
    $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : null;
    $category = $_POST['category'] ?? '';
    $is_published = 1;
    $upload_dir = '../uploads/Gallery/YCposts/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $image_path = '';
    $upload_success = true;
    $error_message = '';

    // Option 1: Direct file upload
    if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] == 0) {
        $image_name = time() . '_post_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['image_path']['name']));
        $full_path = $upload_dir . $image_name;
        $image_path = 'uploads/Gallery/YCposts/' . $image_name;
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image_path']['type'];
        if (!in_array($file_type, $allowed_types)) {
            $upload_success = false;
            $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } elseif ($_FILES['image_path']['size'] > 10 * 1024 * 1024) {
            $upload_success = false;
            $error_message = "Image file size must be less than 10MB.";
        } else {
            if (!move_uploaded_file($_FILES['image_path']['tmp_name'], $full_path)) {
                $upload_success = false;
                $error_message = "Failed to upload image. Please check file permissions.";
            }
        }
    }
    // Option 2: Select from source
    elseif (!empty($_POST['selected_cover'])) {
        $selected_cover = $_POST['selected_cover'];
        $source_cover_path = '../source/Gallery/YCposts/' . $selected_cover;
        if (file_exists($source_cover_path)) {
            $file_extension = strtolower(pathinfo($selected_cover, PATHINFO_EXTENSION));
            if ($file_extension == 'jpg') $file_extension = 'jpeg';
            $image_name = time() . '_post_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($selected_cover));
            $destination_cover_path = $upload_dir . $image_name;
            $image_path = 'uploads/Gallery/YCposts/' . $image_name;
            if (!copy($source_cover_path, $destination_cover_path)) {
                $upload_success = false;
                $error_message = "Failed to copy cover image from source to uploads folder.";
            }
        } else {
            $upload_success = false;
            $error_message = "Selected cover image not found in source folder.";
        }
    } else {
        $upload_success = false;
        $error_message = "Please select an image file or a source image.";
    }

    // Save to database if upload successful
    if ($upload_success && $image_path) {
        try {
            $sql = "INSERT INTO posts (title, content, image_path, category, location, event_date, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $content, $image_path, $category, $location, $event_date]);
            header("Location: YCGalleryadmin.php?page=music&upload=success");
            exit();
        } catch (PDOException $e) {
            if (file_exists($destination_cover_path ?? $full_path)) {
                unlink($destination_cover_path ?? $full_path);
            }
            $error_message = "Database error: " . $e->getMessage();
        }
    }
    header("Location: YCGalleryadmin.php?page=music&upload=error&message=" . urlencode($error_message));
    exit();
}

// Show the unified form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Post - YC Admin</title>
    <link rel="stylesheet" href="../css/YCGalleryadmin-style.css">
</head>
<body>
    <div class="upload-container">
        <h2>Upload Post</h2>
    <form action="YCGalleryupload_posts.php" method="POST" enctype="multipart/form-data">
            <label for="title">Post Title:</label>
            <input type="text" name="title" id="title" required>

            <label for="content">Post Content:</label>
            <textarea name="content" id="content" rows="10" required></textarea>

            <label>Cover Image:</label>
            <div style="margin-bottom: 10px;">
                <input type="file" name="image_path" accept="image/*">
                <span style="margin: 0 10px; color: #888;">or</span>
                <select name="selected_cover">
                    <option value="">Select from source/Gallery/YCposts/</option>
                    <?php foreach ($source_posts as $image): ?>
                        <option value="<?= htmlspecialchars($image) ?>"><?= htmlspecialchars($image) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Upload Post</button>
        </form>
        <br>
    <a href="YCGalleryadmin.php?page=posts" style="color: #654922; text-decoration: none;">← Back to Posts</a>
    </div>
</body>
</html>
