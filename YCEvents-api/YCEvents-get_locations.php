<?php
require_once '../admin/config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT DISTINCT location FROM events ORDER BY location ASC");
    $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($locations);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>