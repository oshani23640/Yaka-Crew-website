<?php
require_once __DIR__ . '/../YCdb_connection.php';
function getEventImagePath($image_path) {
    if (empty($image_path)) return '';
    $filename = basename($image_path);
    if (file_exists(__DIR__ . '/../source/Events/' . $filename)) return 'source/Events/' . $filename;
    if (file_exists(__DIR__ . '/../uploads/Events/' . $filename)) return 'uploads/Events/' . $filename;
    if (file_exists(__DIR__ . '/../YCEvents-images/' . $filename)) return 'YCEvents-images/' . $filename;
    return $image_path;
}

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Event ID is required']);
    exit;
}

$event_id = (int)$_GET['id'];

try {
    // Get event details
    $stmt = $pdo->prepare("SELECT *, additional_info FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if (!$event) {
        http_response_code(404);
        echo json_encode(['error' => 'Event not found']);
        exit;
    }
    
    // Get event images
    $stmt = $pdo->prepare("SELECT image_path FROM event_images WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Format response
    $response = [
        'id' => $event['id'],
        'title' => $event['title'],
        'description' => $event['description'],
        'formatted_date' => date('F d, Y', strtotime($event['event_date'])),
        'start_time' => date('gA', strtotime($event['start_time'])),
        'end_time' => date('gA', strtotime($event['end_time'])),
        'location' => $event['location'],
        'price' => (float)$event['price'],
        'images' => $images,
        'additional_info' => $event['additional_info']
    ];
    
    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>