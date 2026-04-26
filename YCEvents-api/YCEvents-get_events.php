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

try {
    if (!isset($pdo)) {
        throw new PDOException('Database connection not initialized');
    }
    $where_clauses = [];
    $params = [];

    // Filter by event type
    if (isset($_GET['event_type']) && $_GET['event_type'] !== 'all') {
        $where_clauses[] = 'e.event_type = ?';
        $params[] = $_GET['event_type'];
    }

    // Filter by location
    if (isset($_GET['location']) && $_GET['location'] !== 'all') {
        $where_clauses[] = 'e.location = ?';
        $params[] = $_GET['location'];
    }

    // Filter by date range
    if (isset($_GET['date_range'])) {
        switch ($_GET['date_range']) {
            case 'upcoming':
                $where_clauses[] = 'e.event_date >= CURDATE()';
                break;
            case 'past':
                $where_clauses[] = 'e.event_date < CURDATE()';
                break;
            case 'month':
                $where_clauses[] = 'YEAR(e.event_date) = YEAR(CURDATE()) AND MONTH(e.event_date) = MONTH(CURDATE())';
                break;
            case 'year':
                $where_clauses[] = 'YEAR(e.event_date) = YEAR(CURDATE())';
                break;
            // 'all' case means no date filter, so no clause is added
        }
    }

    $sql = "SELECT 
                e.id,
                e.title,
                e.description,
                e.event_date AS date,
                CONCAT(DATE_FORMAT(e.start_time, '%h%p'), ' - ', DATE_FORMAT(e.end_time, '%h%p')) AS time,
                e.location,
                e.price,
                e.additional_info,
                ei.image_path AS image
            FROM events e
            LEFT JOIN event_images ei ON e.id = ei.event_id AND ei.is_primary = 1";

    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }

    $sql .= " ORDER BY e.event_date ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $formattedEvents = array_map(function($event) {
        return [
            'id' => $event['id'],
            'title' => $event['title'],
            'description' => $event['description'],
            'date' => $event['date'],
            'time' => $event['time'],
            'location' => $event['location'],
            'price' => (float)$event['price'],
            'additional_info' => $event['additional_info'],
            'image' => $event['image']
        ];
    }, $events);
    
    echo json_encode($formattedEvents);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>