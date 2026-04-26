<?php
require_once __DIR__ . '/YCdb_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (!empty($email) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (email, message) VALUES (?, ?)");
        $stmt->execute([$email, $message]);
    }
}

header("Location: YCBlogs-index.php");
exit;
?>
