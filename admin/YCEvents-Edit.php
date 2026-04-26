<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../YCdb_connection.php';
// Ensure upload directory constant exists (match other admin scripts)
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../uploads/Events/');
}
if (!file_exists(UPLOAD_DIR)) { @mkdir(UPLOAD_DIR, 0755, true); }
function getEventImagePath($image_path) {
    if (empty($image_path)) return '';
    $filename = basename($image_path);
    if (file_exists(__DIR__ . '/../source/Events/' . $filename)) return 'source/Events/' . $filename;
    if (file_exists(__DIR__ . '/../uploads/Events/' . $filename)) return 'uploads/Events/' . $filename;
    if (file_exists(__DIR__ . '/../YCEvents-images/' . $filename)) return 'YCEvents-images/' . $filename;
    return $image_path;
}

// Get event ID from query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Invalid event ID.</p>";
    exit();
}
$event_id = intval($_GET['id']);

// Fetch event data
$stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
$stmt->execute([$event_id]);
$event = $stmt->fetch();
if (!$event) {
    echo "<p>Event not found.</p>";
    exit();
}

// Fetch event images
$images_stmt = $pdo->prepare('SELECT * FROM event_images WHERE event_id = ?');
$images_stmt->execute([$event_id]);
$event_images = $images_stmt->fetchAll();

// Handle form submission
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $location = $_POST['location'] ?? '';
    $price = $_POST['price'] ?? 0;
    $event_type = $_POST['event_type'] ?? '';
    $is_whats_new = isset($_POST['is_whats_new']) ? 1 : 0;

    // Update event in database
    $stmt = $pdo->prepare('UPDATE events SET title=?, description=?, event_date=?, start_time=?, end_time=?, location=?, price=?, event_type=?, is_whats_new=? WHERE id=?');
    $stmt->execute([$title, $description, $event_date, $start_time, $end_time, $location, $price, $event_type, $is_whats_new, $event_id]);

    // Handle new image uploads
    if (!empty($_FILES['images']['name'][0])) {
        // Delete existing primary image if any
        $existing_primary_image_stmt = $pdo->prepare("SELECT image_path FROM event_images WHERE event_id = ? AND is_primary = 1");
        $existing_primary_image_stmt->execute([$event_id]);
        $existing_primary_image = $existing_primary_image_stmt->fetchColumn();
        if ($existing_primary_image && file_exists(UPLOAD_DIR . $existing_primary_image)) {
            unlink(UPLOAD_DIR . $existing_primary_image);
            $pdo->prepare("DELETE FROM event_images WHERE event_id = ? AND is_primary = 1")->execute([$event_id]);
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = basename($_FILES['images']['name'][$key]);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_name = uniqid('event_', true) . '.' . $file_ext;
                $upload_path = UPLOAD_DIR . $new_file_name;
                
                if (move_uploaded_file($tmp_name, $upload_path)) {
                    $is_primary = ($key === 0) ? 1 : 0;
                    $pdo->prepare("INSERT INTO event_images (event_id, image_path, is_primary) VALUES (?, ?, ?)")
                        ->execute([$event_id, $new_file_name, $is_primary]);
                }
            }
        }
    }

    $success_message = 'Event updated successfully!';
    // Refresh event data
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    $images_stmt = $pdo->prepare('SELECT * FROM event_images WHERE event_id = ?');
    $images_stmt->execute([$event_id]);
    $event_images = $images_stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <link rel="stylesheet" href="../css/YCEvents-admin-style.css?v=<?php echo @filemtime(__DIR__ . '/../css/YCEvents-admin-style.css'); ?>">
    <link rel="stylesheet" href="../css/YCEvents-Edit.css?v=<?php echo @filemtime(__DIR__ . '/../css/YCEvents-Edit.css'); ?>">
    <link rel="stylesheet" href="../css/YCEvents.dashboard.css?v=<?php echo @filemtime(__DIR__ . '/../css/YCEvents.dashboard.css'); ?>">
    
</head>
<body>
<div class="edit-event-container">
    <div class="edit-event-header">
        <?php if (!empty($success_message)): ?>
            <div class="success-message" id="edit-success-message">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <h2>Edit Event</h2>
    </div>

    <div class="edit-event-body">
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Event Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($event['description']); ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="event_date">Date</label>
                <input type="date" id="event_date" name="event_date" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="start_time">Start Time</label>
                <input type="time" id="start_time" name="start_time" value="<?php echo htmlspecialchars($event['start_time']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="end_time">End Time</label>
                <input type="time" id="end_time" name="end_time" value="<?php echo htmlspecialchars($event['end_time']); ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="price">Price (LKR)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($event['price']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="event_type">Event Type</label>
                <select id="event_type" name="event_type" required>
                    <option value="concert" <?php echo ($event['event_type'] == 'concert') ? 'selected' : ''; ?>>Concert</option>
                    <option value="festival" <?php echo ($event['event_type'] == 'festival') ? 'selected' : ''; ?>>Festival</option>
                    <option value="private" <?php echo ($event['event_type'] == 'private') ? 'selected' : ''; ?>>Private Event</option>
                    <option value="charity" <?php echo ($event['event_type'] == 'charity') ? 'selected' : ''; ?>>Charity Show</option>
                    <option value="workshop" <?php echo ($event['event_type'] == 'workshop') ? 'selected' : ''; ?>>Workshop</option>
                </select>
            </div>
        </div>
        
       
        <div class="form-group">
            <label for="is_whats_new">
                <input type="checkbox" id="is_whats_new" name="is_whats_new" value="1" <?php echo ($event['is_whats_new'] ?? 0) ? 'checked' : ''; ?>>
                Feature in "What's New"?
            </label>
        </div>

        <div class="form-group">
            <label>Current Event Images</label>
            <?php if (!empty($event_images)): ?>
                <div class="current-images">
                    <?php foreach ($event_images as $image): ?>
                        <div class="image-item">
                            <img src="<?php echo '../' . htmlspecialchars(getEventImagePath($image['image_path'])); ?>" alt="Event Image">
                            <?php if ($image['is_primary']): ?>
                               
                            <?php endif; ?>
                            <button type="button" class="delete-btn" onclick="deleteImage(<?php echo $image['id']; ?>)">×</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #aaa;">No images uploaded for this event.</p>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="images">Upload New Images</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*">
            <small style="color: #aaa;">First image will be set as primary</small>
        </div>
        
        <div class="form-actions">

            <button type="submit" class="btn btn-primary">Update Event</button>
            <a href="../YCEvents-admin.php?page=events&subpage=manage" style="margin-left: 20px; color: #aaa; text-decoration: underline !important;">Cancel</a>
        </div>
    </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const msg = document.getElementById('edit-success-message');
    if (msg) {
        setTimeout(() => {
            msg.style.transition = 'opacity 300ms ease';
            msg.style.opacity = '0';
            setTimeout(() => {
                if (msg && msg.parentNode) {
                    msg.parentNode.removeChild(msg);
                }
            }, 320);
        }, 1200);
    }
});

function deleteImage(imageId) {
    if (confirm('Are you sure you want to delete this image?')) {
    fetch('../admin/YCEvents-delete-image.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + imageId
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the image. Please check console for details.');
        });
    }
}
</script>
</body>
</html>