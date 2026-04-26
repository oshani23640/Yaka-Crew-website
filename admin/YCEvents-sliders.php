<?php
require_once __DIR__ . '/../YCdb_connection.php';

// Local constants and helpers (previously in includes/events_helpers.php)
define('UPLOAD_DIR', __DIR__ . '/../uploads/Events/');
if (!file_exists(UPLOAD_DIR)) { mkdir(UPLOAD_DIR, 0755, true); }
function getEventImagePath($image_path) {
    if (empty($image_path)) return '';
    $filename = basename($image_path);
    if (file_exists(__DIR__ . '/../source/Events/' . $filename)) return 'source/Events/' . $filename;
    if (file_exists(__DIR__ . '/../uploads/Events/' . $filename)) return 'uploads/Events/' . $filename;
    if (file_exists(__DIR__ . '/../YCEvents-images/' . $filename)) return 'YCEvents-images/' . $filename;
    return $image_path;
}
function sanitizeInput($data) { return htmlspecialchars(strip_tags(trim($data))); }
function redirect($url) { if (!headers_sent()){ header("Location: $url"); exit(); } echo '<script>window.location.href="' . addslashes($url) . '";</script>'; exit(); }

/*if (!isLoggedIn()) {
    redirect('login.php');
}*/

// Handle slider deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT image_path FROM slider_images WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch();
    
    if ($image && file_exists(UPLOAD_DIR . $image['image_path'])) {
        unlink(UPLOAD_DIR . $image['image_path']);
    }
    
    $pdo->prepare("DELETE FROM slider_images WHERE id = ?")->execute([$id]);
    $_SESSION['message'] = "Slider image deleted successfully";
    redirect('YCEvents-sliders.php');
}

// Handle slider status toggle
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE slider_images SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
    redirect('YCEvents-sliders.php');
}

// Handle new slider upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['slider_image']['name'])) {
    if ($_FILES['slider_image']['error'] === UPLOAD_ERR_OK) {
        $file_name = basename($_FILES['slider_image']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid('slider_', true) . '.' . $file_ext;
        $upload_path = UPLOAD_DIR . $new_file_name;
        
        if (move_uploaded_file($_FILES['slider_image']['tmp_name'], $upload_path)) {
            $caption = sanitizeInput($_POST['caption']);
            $pdo->prepare("INSERT INTO slider_images (image_path, caption) VALUES (?, ?)")
                ->execute([$new_file_name, $caption]);
            $_SESSION['message'] = "Slider image uploaded successfully";
            redirect('YCEvents-sliders.php');
        }
    }
}

// Get all slider images
$sliders = $pdo->query("SELECT * FROM slider_images ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Slider Images</title>
    <?php $dashCssV = @filemtime(__DIR__ . '/../css/YCEvents.dashboard.css') ?: time(); ?>
    <link rel="stylesheet" href="../css/YCEvents.dashboard.css?v=<?php echo $dashCssV; ?>">
    <link rel="stylesheet" href="../css/YCEvents-admin-style.css">
</head>
<body>
<div class="container">
    <h1>Manage Slider Images</h1>
    
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success" id="slider-success-alert"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="upload-form">
        <div class="form-group">
            <label for="slider_image">Upload New Slider Image</label>
            <input type="file" id="slider_image" name="slider_image" accept="image/*" required>
        </div>
        
        <div class="form-group">
            <label for="caption">Caption (Optional)</label>
            <input type="text" id="caption" name="caption">
        </div>
        
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
    
    <div class="sliders-grid">
        <?php foreach ($sliders as $slider): ?>
        <div class="slider-card <?php echo $slider['is_active'] ? 'active' : 'inactive'; ?>">
            <img src="<?php echo '../' . htmlspecialchars(getEventImagePath($slider['image_path'])); ?>" alt="Slider Image">
            <div class="slider-info">
                <?php if ($slider['caption']): ?>
                    <p><?php echo htmlspecialchars($slider['caption']); ?></p>
                <?php endif; ?>
                <p>Uploaded: <?php echo date('M j, Y', strtotime($slider['created_at'])); ?></p>
            </div>
            <div class="slider-actions">
                <a href="YCEvents-sliders.php?toggle=<?php echo $slider['id']; ?>" class="btn btn-sm <?php echo $slider['is_active'] ? 'btn-outline' : 'btn-primary'; ?>">
                    <?php echo $slider['is_active'] ? 'Deactivate' : 'Activate'; ?>
                </a>
                <a href="YCEvents-sliders.php?delete=<?php echo $slider['id']; ?>" class="btn btn-sm btn-outline" onclick="return confirm('Are you sure?')">Delete</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
        // Auto-hide the success alert with total ~1200ms display (≈900ms visible + 300ms fade)
    (function() {
        const alertEl = document.getElementById('slider-success-alert');
        if (!alertEl) return;
            setTimeout(() => {
            alertEl.classList.add('hide');
                setTimeout(() => {
                if (alertEl && alertEl.parentNode) alertEl.parentNode.removeChild(alertEl);
                }, 300); // matches CSS transition duration
            }, 900);
    })();
</script>

</body>
</html>