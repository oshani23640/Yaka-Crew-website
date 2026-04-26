<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../YCdb_connection.php';

// Get post ID from query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Invalid post ID.</p>";
    exit();
}
$post_id = intval($_GET['id']);

// Fetch post data
$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$post_id]);
$post = $stmt->fetch();
if (!$post) {
    echo "<p>Post not found.</p>";
    exit();
}

// Handle form submission
// Show success message on the same page after update
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $location = $_POST['location'] ?? '';
    $category = $_POST['category'] ?? '';
    $content = $_POST['content'] ?? '';
    $image_path = $post['image_path'];
    // Handle file upload for event image
    if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image_path']['name'], PATHINFO_EXTENSION);
        $new_image = 'uploads/Gallery/YCposts/' . uniqid('post_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['image_path']['tmp_name'], '../' . $new_image)) {
            $image_path = $new_image;
        }
    }
    $stmt = $pdo->prepare('UPDATE posts SET title=?, event_date=?, location=?, category=?, content=?, image_path=? WHERE id=?');
    $stmt->execute([$title, $event_date, $location, $category, $content, $image_path, $post_id]);
    // Show success message on the same page, no redirect
    $success_message = 'Event updated successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Music Event</title>
    <link rel="stylesheet" href="../css/YCGalleryadmin-style.css">
    <style>
        .edit-form-container { max-width: 500px; margin: 40px auto; background: #111; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); }
        .edit-form-container label { color: #fff; font-weight: 600; margin-top: 10px; display: block; }
        .edit-form-container input, .edit-form-container select, .edit-form-container textarea { 
            width: 100%; 
            padding: 10px; 
            margin-top: 5px; 
            margin-bottom: 15px; 
            border-radius: 4px; 
            border: 1px solid #654922; 
            background: #222; 
            color: #fff; 
            box-sizing: border-box;
        }
        .edit-form-container input[type="date"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            filter: invert(1);
            opacity: 1;
        }
        .edit-form-container input[type="date"]::-moz-calendar-picker {
            filter: invert(1);
            opacity: 1;
        }
        .edit-form-container button { background: #654922; color: #fff; border: none; padding: 12px 30px; border-radius: 4px; font-size: 16px; cursor: pointer; }
        .edit-form-container button:hover { background: #956E2F; }
        .edit-form-container img { display: block; margin-bottom: 10px; max-width: 180px; height: 120px; object-fit: cover; border-radius: 4px; }
    </style>
</head>
<body style="background: #000;">
<div class="edit-form-container">
        <?php if (!empty($success_message)): ?>
            <div id="edit-success-message" style="background: #654922; color: #FFFFFF; padding: 12px; border-radius: 5px; margin-bottom: 18px; text-align: center;">
                <?= $success_message; ?>
            </div>
            <script>
            window.addEventListener('DOMContentLoaded', function() {
                setTimeout(function(){
                    var msg = document.getElementById('edit-success-message');
                    if(msg){ msg.style.display = 'none'; }
                }, 1200);
            });
            </script>
        <?php endif; ?>
    <h2 style="color: #fff;">Edit Music Event</h2>
        
    <form method="post" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>

        <label for="event_date">Event Date:</label>
        <input type="date" name="event_date" id="event_date" value="<?php echo htmlspecialchars($post['event_date']); ?>">

        <label for="location">Location:</label>
        <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($post['location']); ?>">

        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <option value="concert" <?php if($post['category']==='concert') echo 'selected'; ?>>Concert</option>
            <option value="album_release" <?php if($post['category']==='album_release') echo 'selected'; ?>>Album Release</option>
            <option value="music_video" <?php if($post['category']==='music_video') echo 'selected'; ?>>Music Video</option>
            <option value="event" <?php if($post['category']==='event') echo 'selected'; ?>>General Event</option>
        </select>

        <!-- No Description field, only Content -->

        <label>Current Event Image:</label>
        <?php 
        $cover = $post['image_path'] ?? '';
        if (!empty($cover)) {
            // Extract filename from path
            $filename = basename($cover);
            $final_image_path = '';
            
            // Check source directory first
            if (file_exists('../source/Gallery/YCposts/' . $filename)) {
                $final_image_path = '../source/Gallery/YCposts/' . $filename;
            } elseif (file_exists('../uploads/Gallery/YCposts/' . $filename)) {
                $final_image_path = '../uploads/Gallery/YCposts/' . $filename;
            } elseif (file_exists('../' . $cover)) {
                // Fallback to original path
                $final_image_path = '../' . $cover;
            }
            
            if (!empty($final_image_path)) {
                echo '<img src="' . htmlspecialchars($final_image_path) . '" alt="Event Image">';
            } else {
                echo '<div style="color: #aaa; margin-bottom: 10px;">No image available.<br>(' . htmlspecialchars($cover) . ')</div>';
            }
        } else {
            echo '<div style="color: #aaa; margin-bottom: 10px;">No image available.</div>';
        }
        ?>
        <label for="image_path">Change Event Image:</label>
        <input type="file" name="image_path" id="image_path" accept="image/*">

    <button type="submit">Save Changes</button>
    <a href="YCGalleryadmin.php?page=music" style="margin-left: 20px; color: #aaa;">Cancel</a>
    </form>
</div>
</body>
</html>
