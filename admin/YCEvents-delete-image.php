<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and check authentication
/*session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}*/

require_once __DIR__ . '/../YCdb_connection.php';

// Inline helpers (previously in includes/events_helpers.php)
define('UPLOAD_DIR', __DIR__ . '/../uploads/Events/');
if (!file_exists(UPLOAD_DIR)) { mkdir(UPLOAD_DIR, 0755, true); }
function sanitizeInput($data) { return htmlspecialchars(strip_tags(trim($data))); }
function redirect($url) { if (!headers_sent()){ header("Location: $url"); exit(); } echo '<script>window.location.href="' . addslashes($url) . '";</script>'; exit(); }

// Get image ID from POST data
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'error' => 'Invalid image ID']);
    exit();
}

$image_id = intval($_POST['id']);

try {
    // Get image info before deletion
    $stmt = $pdo->prepare('SELECT * FROM event_images WHERE id = ?');
    $stmt->execute([$image_id]);
    $image = $stmt->fetch();
    
    if (!$image) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'error' => 'Image not found']);
        exit();
    }
    
    // Delete the file
    $file_path = UPLOAD_DIR . $image['image_path'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Delete from database
    $stmt = $pdo->prepare('DELETE FROM event_images WHERE id = ?');
    $stmt->execute([$image_id]);
    
    // If this was the primary image, set a new primary if available
    if ($image['is_primary']) {
        $stmt = $pdo->prepare('SELECT id FROM event_images WHERE event_id = ? LIMIT 1');
        $stmt->execute([$image['event_id']]);
        $new_primary = $stmt->fetch();
        
        if ($new_primary) {
            $pdo->prepare('UPDATE event_images SET is_primary = 1 WHERE id = ?')
                ->execute([$new_primary['id']]);
        }
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}