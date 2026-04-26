<?php
require_once __DIR__ . '/../YCdb_connection.php';
define('UPLOAD_DIR', __DIR__ . '/../uploads/Events/');
if (!file_exists(UPLOAD_DIR)) { mkdir(UPLOAD_DIR, 0755, true); }
function sanitizeInput($data) { return htmlspecialchars(strip_tags(trim($data))); }
function redirect($url) { if (!headers_sent()){ header("Location: $url"); exit(); } echo '<script>window.location.href="' . addslashes($url) . '";</script>'; exit(); }

/*if (!isLoggedIn()) {
    redirect('login.php');
}
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $event_date = sanitizeInput($_POST['event_date']);
    $start_time = sanitizeInput($_POST['start_time']);
    $end_time = sanitizeInput($_POST['end_time']);
    $location = sanitizeInput($_POST['location']);
    $price = (float)$_POST['price'];
    $event_type = sanitizeInput($_POST['event_type']);
    $is_past_event = isset($_POST['is_past_event']) ? 1 : 0;
    
    // Insert event
    $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, start_time, end_time, location, price, event_type, is_past_event) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $event_date, $start_time, $end_time, $location, $price, $event_type, $is_past_event]);
    
    $event_id = $pdo->lastInsertId();
    
    // Handle image uploads
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = basename($_FILES['images']['name'][$key]);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_name = uniqid('event_', true) . '.' . $file_ext;
                $upload_path = UPLOAD_DIR . $new_file_name;
                
                if (move_uploaded_file($tmp_name, $upload_path)) {
                    $is_primary = ($key === 0) ? 1 : 0; // First image is primary
                    $pdo->prepare("INSERT INTO event_images (event_id, image_path, is_primary) VALUES (?, ?, ?)")
                        ->execute([$event_id, $new_file_name, $is_primary]);
                }
            }
        }
    }
    
    $_SESSION['message'] = "Event added successfully";
    redirect('../YCEvents-admin.php?page=events&subpage=manage');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event</title>
    <link rel="stylesheet" href="../css/YCEvents-admin-style.css">
    <link rel="stylesheet" href="../css/YCEvents.dashboard.css">
</head>
<body>
<div class="container">
    <h1>Add New Event</h1>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Event Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" required></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="event_date">Date</label>
                <input type="date" id="event_date" name="event_date" required>
            </div>
            
            <div class="form-group">
                <label for="start_time">Start Time</label>
                <input type="time" id="start_time" name="start_time" required>
            </div>
            
            <div class="form-group">
                <label for="end_time">End Time</label>
                <input type="time" id="end_time" name="end_time" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" required>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="price">Price (LKR)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="event_type">Event Type</label>
                <select id="event_type" name="event_type" required>
                    <option value="concert">Concert</option>
                    <option value="festival">Festival</option>
                    <option value="private">Private Event</option>
                    <option value="charity">Charity Show</option>
                </select>
            </div>
        </div>
        
      
        
        <div class="form-group">
            <label for="images">Event Images (Multiple allowed, first image will be primary)</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Event</button>
            <a href="../YCEvents-admin.php?page=events&subpage=manage" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
<script>
// optional: basic UX enhancements could go here
</script>
</body>
</html>

