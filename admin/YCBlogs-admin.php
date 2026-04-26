
<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure uploads/Blogs/ and source/Blogs/ directories exist and are writable
$uploads_root = __DIR__ . '/../uploads/';
$uploads_dir = $uploads_root . 'Blogs/';
$source_root = __DIR__ . '/../source/';
$source_dir = $source_root . 'Blogs/';
foreach ([$uploads_root, $uploads_dir, $source_root, $source_dir] as $dir) {
  if (!is_dir($dir)) {
    @mkdir($dir, 0777, true);
  }
  if (!is_writable($dir)) {
    @chmod($dir, 0777);
    clearstatcache();
    if (!is_writable($dir)) {
      echo '<div style="color:red; font-weight:bold; padding:10px;">Error: '.htmlspecialchars($dir).' is not writable and permissions could not be changed automatically. Please contact your server administrator.</div>';
    }
  }
}

session_start();
require_once __DIR__ . '/../YCdb_connection.php';
if (!isset($pdo) || !$pdo) {
  die('Database connection failed. Please check YCdb_connection.php.');
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

/* ======================
   BLOG FORM HANDLING
   ====================== */
if ($page === 'blogs') {

  // Add Blog
  if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_GET['id'])) {
    $title = $_POST['title'];
    $short_description = $_POST['short_description'];
    $full_description = $_POST['full_description'];
    $image_name = null;

    $upload_dir = __DIR__ . '/../uploads/Blogs/';
    $source_dir = __DIR__ . '/../source/Blogs/';

    // Priority: uploaded file, then source selection
      // Only allow upload: save to uploads/Blogs/
      if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_FILES['image']['name'])) {
        $image_name = basename($_FILES['image']['name']);
        $upload_path = $upload_dir . $image_name;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
          $_SESSION['error'] = 'Image upload failed. Check folder permissions for uploads/Blogs/.';
          header("Location: YCBlogs-admin.php?page=blogs");
          exit;
        }
      }

    try {
      $stmt = $pdo->prepare("INSERT INTO blogs (title, short_description, full_description, image) VALUES (?, ?, ?, ?)");
      $stmt->execute([$title, $short_description, $full_description, $image_name]);
    } catch (PDOException $e) {
      $_SESSION['error'] = 'Database error: ' . $e->getMessage();
      header("Location: YCBlogs-admin.php?page=blogs");
      exit;
    }

    $_SESSION['success'] = 'Blog post added successfully!';
    header("Location: YCBlogs-admin.php?page=blogs");
    exit;
  }

  // Update Blog
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $title = $_POST['title'];
    $short = $_POST['short_description'];
    $full = $_POST['full_description'];

    // Ensure uploads/Blogs/ directory exists
    $upload_dir = __DIR__ . '/../uploads/Blogs/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if (!empty($_FILES['image']['name'])) {
      $image_name = basename($_FILES['image']['name']);
      move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
      $stmt = $pdo->prepare("UPDATE blogs SET title=?, short_description=?, full_description=?, image=? WHERE id=?");
      $stmt->execute([$title, $short, $full, $image_name, $id]);
    } else {
      $stmt = $pdo->prepare("UPDATE blogs SET title=?, short_description=?, full_description=? WHERE id=?");
      $stmt->execute([$title, $short, $full, $id]);
    }

    header("Location: YCBlogs-admin.php?page=blogs");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Yaka Crew Admin Panel</title>
  <link rel="stylesheet" href="../css/YCBlogs-admin-style.css">
</head>
<body>

<!-- Navigation -->
<div class="navbar">
  <div class="logo">
    <img src="../assets/images/Yaka Crew Logo.JPG" alt="Yaka Crew Logo" style="width: 120px; height: auto;">
  </div>
  <div class="right-nav">
    <div class="view-dropdown">
      <a href="#" class="view-btn">View ▼</a>
      <ul class="view-dropdown-menu">
        <li><a href="../YCHome.php">Home</a></li>
        <li class="gallery-submenu">
          <a id="galleryViewDropdownLink" role="button" tabindex="0" style="cursor:pointer;">Gallery ▶</a>
          <ul class="gallery-submenu-items">
            <li><a href="../YCPosts.php">Music</a></li>
            <li><a href="../YCGallery.php">Video</a></li>
          </ul>
        </li>
        <li><a href="../YCBooking-index.php">Bookings</a></li>
        <li><a href="../YCEvents.php">Events</a></li>
        <li><a href="../YCBlogs-index.php">Blogs</a></li>
        <li><a href="../YCMerch-merch1.php">Merchandise Store</a></li>
      </ul>
    </div>
     <a href="YCBlogs-generate-pdf.php" target="_blank" class="pdf-btn-custom">Generate PDF Report</a>
     <style>
    .pdf-btn-custom {
      color: white;
      text-decoration: none;
      padding: 8px 15px;
      border: 1px solid #654922;
      background-color: #000000;
      font-weight: 500;
      display: block;
      transition: background 0.2s, color 0.2s;
      text-align: center;
      cursor: pointer;
    }
    .pdf-btn-custom:hover {
      background-color: #654922;
      color: #fff;
    }
  </style>
    <a href="../YClogin.php?action=logout" class="logout-btn">Logout</a>
  </div>
</div>

<!-- Main Container -->
<div class="main-container">
<!-- Left Sidebar -->
<div class="left-sidebar">
    <ul class="nav-links">
  <li><a href="../YCHome-admin.php">Home</a></li>
     <li class="gallery-dropdown">
        <a href="#">Gallery ▼</a>
        <ul class="dropdown">
          <li><a href="YCGalleryadmin.php?page=music">Music</a></li>
          <li><a href="YCGalleryadmin.php?page=video">Video</a></li>
        </ul>
      </li>
  <li><a href="YCBooking_admin.php?page=bookings">Bookings</a></li>
      <li><a href="../YCEvents-admin.php?page=events">Events</a></li>
    <li><a href="YCBlogs-admin.php?page=blogs">Blogs</a></li>
    
  <li><a href="../YCMerch-admin.php?page=merchandise-store">Merchandise</a></li>
  </ul>
</div>

  <!-- Content -->
  <div class="admin-content">
<?php
switch ($page) {
  case 'home':
    echo "<h2>Dashboard</h2>";
    echo "<p>Welcome to the Yaka Crew Admin Panel. Use the navigation to manage different sections of your website.</p>";
    break;

  // Music, Video, Bookings, Events cases stay same as your version...

  case 'blogs':
    echo "<h2>Blog Management</h2>";
    echo "<p>Publish blog posts and manage content.</p>";
// Flash messages
if (isset($_SESSION['success'])) {
    echo '<div class="success-message">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']); // remove after showing
}

if (isset($_SESSION['error'])) {
    echo '<div class="error-message">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']); // remove after showing
}


?>
  <div class="upload-container">
    <h2>Add New Blog Post</h2>
    <form method="post" enctype="multipart/form-data">
      <label>Title:</label>
      <input type="text" name="title" required>

      <label>Short Description:</label>
      <textarea name="short_description" rows="3" required></textarea>

      <label>Full Description:</label>
      <textarea name="full_description" rows="6" required></textarea>

      <label>Image (optional):</label>
      <input type="file" name="image">
      

      <button type="submit">Add Blog</button>
    </form>
  </div>

    <div class="upload-container">
        <h2>Existing Blog Posts</h2>
    <table class="blog-table" width="800">
      <tr class="blog-table-header">
        <th>ID</th>
        <th>Title</th>
        <th>Short Description</th>
        <th>Actions</th>
      </tr>
            <?php
      $stmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC");
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (count($rows) > 0):
        foreach ($rows as $row): ?>
                    <tr align="Center">
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars(substr($row['short_description'], 0, 100)) ?>...</td>
                        
            <td style="text-align:center;">
              <div class="action-btns">
                <form method="GET" action="YCBLogs-blogedit.php" style="display:inline; margin-right:8px;">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <button type="submit" class="admin-edit-btn">Edit</button>
                </form>
                <form method="POST" action="YCBLogs-deleteblog.php" onsubmit="return confirm('Are you sure?');" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $row['id'] ?>">
                  <button type="submit" class="admin-delete-btn">Delete</button>
                </form>
              </div>
            </td>
                    </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="4">No blog posts found.</td></tr>
      <?php endif; ?>
        </table>
  
    </div>
    <div class="upload-container">
  <h2>Contact Messages</h2>
  <table class="blog-table" width="800">
    <tr class="blog-table-header">
      <th>ID</th>
      <th>Email</th>
      <th>Actions</th>
    </tr>
    <?php
    $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) > 0):
        foreach ($rows as $row): ?>
        <tr align="Center">
          <td><?= htmlspecialchars($row['id']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td>
            <form method="GET" action="YCBlogs-messageread.php" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" class="admin-edit-btn">Read Message</button>
            </form>
          </td>
        </tr>
    <?php endforeach; else: ?>
        <tr><td colspan="3">No messages found.</td></tr>
    <?php endif; ?>
  </table>
</div>

<?php
    break;



  // other cases...

}
?>
  </div>
</div>
</body>
</html>
