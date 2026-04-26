<?php
require_once __DIR__ . '/../YCdb_connection.php';

// Inline helper: resolve event image path
function getEventImagePath($image_path) {
    if (empty($image_path)) return '';
    $filename = basename($image_path);
    if (file_exists(__DIR__ . '/../source/Events/' . $filename)) return 'source/Events/' . $filename;
    if (file_exists(__DIR__ . '/../uploads/Events/' . $filename)) return 'uploads/Events/' . $filename;
    if (file_exists(__DIR__ . '/../YCEvents-images/' . $filename)) return 'YCEvents-images/' . $filename;
    return $image_path;
}

header('Content-Type: application/json');

// Read raw POST input
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($data['ids']) || !is_array($data['ids'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// Sanitize IDs
$ids = array_map('intval', $data['ids']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

try {
    $stmt = $pdo->prepare("SELECT e.id, e.title AS name, e.event_date AS date, e.location, e.price, ei.image_path AS image FROM events e LEFT JOIN event_images ei ON e.id = ei.event_id AND ei.is_primary = 1 WHERE e.id IN ($placeholders)");
    $stmt->execute($ids);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response with proper image paths
    $formattedResults = [];
    foreach ($results as $item) {
        $formattedItem = [
            'id' => $item['id'],
            'name' => $item['name'],
            'date' => $item['date'],
            'location' => $item['location'],
            'price' => (float)$item['price'],
            'image' => $item['image'] ? getEventImagePath($item['image']) : null
        ];
        $formattedResults[] = $formattedItem;
    }
    
    echo json_encode(['success' => true, 'data' => $formattedResults]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>