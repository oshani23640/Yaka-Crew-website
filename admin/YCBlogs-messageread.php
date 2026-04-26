<?php
require_once __DIR__ . '/../YCdb_connection.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
  echo "Message not found.";
  exit;
}

$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
$stmt->execute([$id]);
$message = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$message) {
  echo "Message not found.";
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Read Message</title>
  <style>
        body {
            background: #000;
            font-family: Arial, sans-serif;
            color: #fff;
        }
        .edit-form-container {
            max-width: 500px;
            margin: 40px auto;
            background: #111;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .edit-form-container h2 {
            color: #fff;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #956E2F; /* Golden line */
        }
        .edit-form-container label {
            color: #fff;
            font-weight: 600;
            margin-top: 10px;
            display: block;
        }
        .edit-form-container .value-box {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #654922;
            background: #222;
            color: #fff;
            min-height: 40px;
        }
        .edit-form-container textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #654922;
            background: #222;
            color: #fff;
        }
        .cancel-link {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #654922;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
        }
        .cancel-link:hover {
            background: #956E2F;
        }
    </style>
</head>
<body>
  <div class="edit-form-container">
    <h2>Read Message</h2>

    <label>Email:</label>
    <div class="value-box"><?= htmlspecialchars($message['email']) ?></div>

    <label>Message:</label>
    <div class="value-box"><?= nl2br(htmlspecialchars($message['message'])) ?></div>

    <a href="YCBlogs-admin.php?page=blogs" class="cancel-link">Back</a>
</div>
</body>
</html>
