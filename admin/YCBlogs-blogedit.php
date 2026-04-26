<?php
session_start();
require_once __DIR__ . '/../YCdb_connection.php';

// Get blog ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Invalid blog ID.</p>";
    exit();
}
$blog_id = intval($_GET['id']);

// Fetch blog post
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->execute([$blog_id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$blog) {
    echo "<p>Blog post not found.</p>";
    exit();
}

// Handle update
$success_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'] ?? '';
    $short_description = $_POST['short_description'] ?? '';
    $full_description = $_POST['full_description'] ?? '';
    $image_name = $blog['image'];

    $target_dir = __DIR__ . "/../uploads/Blogs/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid('blog_', true) . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_name);
    }

    $stmt = $pdo->prepare("UPDATE blogs SET title=?, short_description=?, full_description=?, image=? WHERE id=?");
    $stmt->execute([$title, $short_description, $full_description, $image_name, $blog_id]);

    // Update variables for form repopulation
    $blog['title'] = $title;
    $blog['short_description'] = $short_description;
    $blog['full_description'] = $full_description;
    $blog['image'] = $image_name;

    $success_message = 'Blog post updated successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Blog Post</title>
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
        .edit-form-container input,
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
        .edit-form-container button {
            background: #654922;
            color: #fff;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .edit-form-container button:hover {
            background: #956E2F;
        }
        .edit-form-container img {
            display: block;
            margin-bottom: 10px;
            max-width: 180px;
            height: auto;
            object-fit: cover;
            border-radius: 4px;
        }
        .success-message {
            background: #654922;
            color: #fff;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 18px;
            text-align: center;
        }
        .cancel-link {
            margin-left: 20px;
            color: #aaa;
            text-decoration: none;
        }
        .cancel-link:hover {
            color: #fff;
        }
    </style>
</head>
<body>

<div class="edit-form-container">
    <h2>Edit Blog Post</h2>

    <?php if (!empty($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($blog['title']) ?>" required>

        <label for="short_description">Short Description:</label>
        <textarea name="short_description" id="short_description" rows="3" required><?= htmlspecialchars($blog['short_description']) ?></textarea>

        <label for="full_description">Full Description:</label>
        <textarea name="full_description" id="full_description" rows="6" required><?= htmlspecialchars($blog['full_description']) ?></textarea>

        <label>Current Image:</label>
        <?php if (!empty($blog['image']) && file_exists(__DIR__ . "/../uploads/Blogs/" . $blog['image'])) {
    echo '<img src="../uploads/Blogs/' . htmlspecialchars($blog['image']) . '" alt="Blog Image">';
} else {
    echo '<div style="color: #aaa; margin-bottom: 10px;">No image available.</div>';
}
?>

        <label for="image">Change Image:</label>
        <input type="file" name="image" id="image" accept="image/*">

        <button type="submit">Save Changes</button>
        <a href="YCBlogs-admin.php?page=blogs" class="cancel-link">Cancel</a>
    </form>
</div>

</body>
</html>
