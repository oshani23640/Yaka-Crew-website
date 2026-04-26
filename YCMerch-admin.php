<?php
// Start session for admin access control
session_start();
// Start output buffering so header() redirects after POST work reliably
ob_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Include database connections
require_once __DIR__ . '/YCdb_connection.php'; // Main site (PDO)



$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Yaka Crew Admin Panel</title>
  <link rel="stylesheet" href="css/admin-style.css">
  
</head>
<body>

<!-- Navigation -->
<div class="navbar">
  <div class="logo">
  <img src="source/YCMerch-images/logo.jpg" alt="Yaka Crew Logo" style="width: 120px; height: auto;">
  </div>
<div class="right-nav">
    <div class="view-dropdown">
      <a href="#" class="view-btn">View ▼</a>
      <ul class="view-dropdown-menu">
        <li><a href="YCHome.php">Home</a></li>
        <li class="gallery-submenu">
          <a id="galleryViewDropdownLink" role="button" tabindex="0" style="cursor:pointer;">Gallery ▶</a>
          <ul class="gallery-submenu-items">
            <li><a href="YCPosts.php">Music</a></li>
            <li><a href="YCGallery.php">Video</a></li>
          </ul>
        </li>
        <li><a href="YCBooking-index.php">Bookings</a></li>
        <li><a href="YCEvents.php">Events</a></li>
        <li><a href="YCBlogs-index.php">Blogs</a></li>
        <li><a href="YCMerch-merch1.php">Merchandise Store</a></li>
      </ul>
    </div>
<a href="YCMerch-generate-pdf.php"  target="_blank" class="pdf-btn-custom">Generate PDF Report</a>
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
    <a href="YClogin.php?action=logout" class="logout-btn">Logout</a>
  </div>
</div>

<!-- Main Container -->
<div class="main-container">
  <!-- Left Sidebar -->
  <div class="left-sidebar">
    <ul class="nav-links">
      <li><a href="YCHome-admin.php">Home</a></li>
      <li class="gallery-dropdown">
        <a href="#">Gallery ▼</a>
        <ul class="dropdown">
          <li><a href="admin/YCGalleryadmin.php?page=music">Music</a></li>
          <li><a href="admin/YCGalleryadmin.php?page=video">Video</a></li>
        </ul>
      </li>
      <li><a href="admin/YCBooking_admin.php?page=bookings">Bookings</a></li>
      <li><a href="YCEvents-admin.php?page=events">Events</a></li>
    <li><a href="admin/YCBlogs-admin.php?page=blogs">Blogs</a></li>
    <li><a href="YCMerch-admin.php?page=merchandise-store">Merchandise Store</a></li>
    </ul>
  </div>

  <!-- Content -->
  <div class="admin-content">
<?php
switch ($page) {

    
case 'merchandise-store':
    // Ensure required merchandise tables exist in yaka_crew_band (PDO)
    function ycmerch_ensure_tables_pdo(PDO $pdo) {
      $ddl = [];
      $ddl['tshirts'] = "CREATE TABLE IF NOT EXISTS `tshirts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `caption` VARCHAR(500) DEFAULT NULL,
        `image_black_front` VARCHAR(255) DEFAULT NULL,
        `image_black_back` VARCHAR(255) DEFAULT NULL,
        `image_white_front` VARCHAR(255) DEFAULT NULL,
        `image_white_back` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
      $ddl['hoodies'] = str_replace('`tshirts`', '`hoodies`', $ddl['tshirts']);
      $ddl['posters'] = "CREATE TABLE IF NOT EXISTS `posters` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `caption` VARCHAR(500) DEFAULT NULL,
        `image` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
      $ddl['wristband'] = str_replace('`posters`', '`wristband`', $ddl['posters']);
      foreach ($ddl as $sql) { $pdo->exec($sql); }
    }
    ycmerch_ensure_tables_pdo($pdo);
    // Robustly fix legacy schemas: ensure `id` exists, is PRIMARY KEY, and AUTO_INCREMENT
    function ycmerch_fix_id_autoincrement(PDO $pdo, array $tables) {
      foreach ($tables as $tbl) {
        try {
          // Check if id column exists and its attributes
          $stmt = $pdo->prepare("SELECT EXTRA, COLUMN_KEY, DATA_TYPE, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'id'");
          $stmt->execute([$tbl]);
          $col = $stmt->fetch(PDO::FETCH_ASSOC);

          if (!$col) {
            // No id column: add it as AUTO_INCREMENT PRIMARY KEY
            $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
            continue;
          }

          // Ensure id is NOT NULL INT
          try { $pdo->exec("ALTER TABLE `$tbl` MODIFY `id` INT NOT NULL"); } catch (PDOException $e) { /* ignore */ }

          // Ensure primary key is on id
          $pkStmt = $pdo->prepare("SELECT k.COLUMN_NAME FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS t JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE k ON t.CONSTRAINT_NAME = k.CONSTRAINT_NAME AND t.TABLE_SCHEMA = k.TABLE_SCHEMA AND t.TABLE_NAME = k.TABLE_NAME WHERE t.TABLE_SCHEMA = DATABASE() AND t.TABLE_NAME = ? AND t.CONSTRAINT_TYPE = 'PRIMARY KEY'");
          $pkStmt->execute([$tbl]);
          $pkCols = $pkStmt->fetchAll(PDO::FETCH_COLUMN);
          $hasIdAsPK = (count($pkCols) === 1 && strtolower($pkCols[0]) === 'id');
          if (!$hasIdAsPK && !empty($pkCols)) {
            // Drop existing PK if it's different
            try { $pdo->exec("ALTER TABLE `$tbl` DROP PRIMARY KEY"); } catch (PDOException $e) { /* ignore */ }
          }
          if (!$hasIdAsPK) {
            try { $pdo->exec("ALTER TABLE `$tbl` ADD PRIMARY KEY (`id`)"); } catch (PDOException $e) { /* ignore */ }
          }

          // Ensure AUTO_INCREMENT on id
          if (stripos($col['EXTRA'] ?? '', 'auto_increment') === false) {
            try { $pdo->exec("ALTER TABLE `$tbl` MODIFY `id` INT NOT NULL AUTO_INCREMENT"); } catch (PDOException $e) { /* ignore */ }
          }
        } catch (PDOException $e) {
          // Silent continue; form handler will surface any remaining errors
          continue;
        }
      }
    }
    ycmerch_fix_id_autoincrement($pdo, ['tshirts','hoodies','posters','wristband']);

    // Helper: detect whether table has AUTO_INCREMENT on id
    function ycmerch_table_has_auto_increment(PDO $pdo, string $tbl): bool {
      try {
        $stmt = $pdo->prepare("SELECT EXTRA FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'id'");
        $stmt->execute([$tbl]);
        $extra = $stmt->fetchColumn();
        return (stripos((string)$extra, 'auto_increment') !== false);
      } catch (PDOException $e) {
        return false;
      }
    }
    // Helper: get next manual id if AUTO_INCREMENT is not available
    function ycmerch_next_id(PDO $pdo, string $tbl): int {
      try {
        $stmt = $pdo->query("SELECT COALESCE(MAX(id)+1,1) FROM `$tbl`");
        $val = $stmt->fetchColumn();
        return (int)$val;
      } catch (PDOException $e) {
        return 1;
      }
    }
    // Helper for displaying image cells in the table
    function imgCell($img) {
      if ($img && file_exists("uploads/YCMerch-uploads/" . $img)) {
        return '<img src="uploads/YCMerch-uploads/' . htmlspecialchars($img) . '" alt="" style="max-width:80px;max-height:80px;border:1px solid #956E2F;border-radius:6px;">';
      } else {
        return '<div style="width:80px;height:80px;background:#333;display:flex;align-items:center;justify-content:center;color:#999;font-size:11px;border-radius:6px;">No Image</div>';
      }
    }
  // Improved merchandise-store logic with robust image upload and DB handling (PDO)

  $productTypes = ['tshirts', 'posters', 'hoodies', 'wristband'];
  $selected = $_GET['type'] ?? ($_POST['type'] ?? 'tshirts');
    $table = in_array($selected, $productTypes) ? $selected : 'tshirts';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $caption = trim($_POST['caption'] ?? '');
    // For 4 images
    $image_black_front = '';
    $image_black_back = '';
    $image_white_front = '';
    $image_white_back = '';
    $image = '';

    $targetDir = "uploads/YCMerch-uploads/";
    if (!is_dir($targetDir)) {
      mkdir($targetDir, 0755, true);
    }
    // Helper for upload
    function uploadImgSlot($slot) {
      global $targetDir;
      if (isset($_FILES[$slot]) && $_FILES[$slot]['error'] === UPLOAD_ERR_OK) {
        $imgName = basename($_FILES[$slot]['name']);
        $uniqueName = time() . "_" . preg_replace('/[^A-Za-z0-9_.-]/', '', $imgName);
        $targetPath = $targetDir . $uniqueName;
        if (move_uploaded_file($_FILES[$slot]['tmp_name'], $targetPath)) {
          return $uniqueName;
        }
      }
      return '';
    }
    // Only for tshirts and hoodies
    if ($selected === 'tshirts' || $selected === 'hoodies') {
      $image_black_front = uploadImgSlot('image_black_front');
      $image_black_back = uploadImgSlot('image_black_back');
      $image_white_front = uploadImgSlot('image_white_front');
      $image_white_back = uploadImgSlot('image_white_back');
    } else if ($selected === 'posters' || $selected === 'wristband') {
      $image = uploadImgSlot('image');
    }


        // ADD
        if (isset($_POST['add'])) {
          try {
            // First attempt: insert without specifying id
            if ($selected === 'tshirts' || $selected === 'hoodies') {
              $stmt = $pdo->prepare("INSERT INTO `$table` (`name`, `price`, `caption`, `image_black_front`, `image_black_back`, `image_white_front`, `image_white_back`) VALUES (?, ?, ?, ?, ?, ?, ?)");
              $stmt->execute([$name, $price, $caption, $image_black_front, $image_black_back, $image_white_front, $image_white_back]);
            } else if ($selected === 'posters' || $selected === 'wristband') {
              $stmt = $pdo->prepare("INSERT INTO `$table` (`name`, `price`, `caption`, `image`) VALUES (?, ?, ?, ?)");
              $stmt->execute([$name, $price, $caption, $image]);
            } else {
              $stmt = $pdo->prepare("INSERT INTO `$table` (`name`, `price`, `caption`) VALUES (?, ?, ?)");
              $stmt->execute([$name, $price, $caption]);
            }
          } catch (PDOException $e) {
            // Fallback: compute next id and insert with explicit id to bypass strict mode / missing AI
            try {
              $nextId = ycmerch_next_id($pdo, $table);
              if ($selected === 'tshirts' || $selected === 'hoodies') {
                $stmt = $pdo->prepare("INSERT INTO `$table` (`id`, `name`, `price`, `caption`, `image_black_front`, `image_black_back`, `image_white_front`, `image_white_back`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nextId, $name, $price, $caption, $image_black_front, $image_black_back, $image_white_front, $image_white_back]);
              } else if ($selected === 'posters' || $selected === 'wristband') {
                $stmt = $pdo->prepare("INSERT INTO `$table` (`id`, `name`, `price`, `caption`, `image`) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nextId, $name, $price, $caption, $image]);
              } else {
                $stmt = $pdo->prepare("INSERT INTO `$table` (`id`, `name`, `price`, `caption`) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nextId, $name, $price, $caption]);
              }
            } catch (PDOException $e2) {
              header("Location: YCMerch-admin.php?page=merchandise-store&type=" . urlencode($table) . "&status=" . urlencode('Insert failed: ' . $e2->getMessage()));
              exit;
            }
          }
        }


        // UPDATE
        if (isset($_POST['update']) && $id) {
          try {
            if ($selected === 'tshirts' || $selected === 'hoodies') {
              $setParts = ["name = ?", "price = ?", "caption = ?"];
              $params = [$name, $price, $caption];
              if ($image_black_front) { $setParts[] = "image_black_front = ?"; $params[] = $image_black_front; }
              if ($image_black_back)  { $setParts[] = "image_black_back = ?";  $params[] = $image_black_back; }
              if ($image_white_front) { $setParts[] = "image_white_front = ?"; $params[] = $image_white_front; }
              if ($image_white_back)  { $setParts[] = "image_white_back = ?";  $params[] = $image_white_back; }
              $params[] = $id;
              $sql = "UPDATE `$table` SET " . implode(', ', $setParts) . " WHERE id = ?";
              $stmt = $pdo->prepare($sql);
              $stmt->execute($params);
            } else if ($selected === 'posters' || $selected === 'wristband') {
              if ($image) {
                $stmt = $pdo->prepare("UPDATE `$table` SET `name`=?, `price`=?, `caption`=?, `image`=? WHERE `id`=?");
                $stmt->execute([$name, $price, $caption, $image, $id]);
              } else {
                $stmt = $pdo->prepare("UPDATE `$table` SET `name`=?, `price`=?, `caption`=? WHERE `id`=?");
                $stmt->execute([$name, $price, $caption, $id]);
              }
            } else {
              $stmt = $pdo->prepare("UPDATE `$table` SET `name`=?, `price`=?, `caption`=? WHERE `id`=?");
              $stmt->execute([$name, $price, $caption, $id]);
            }
          } catch (PDOException $e) {
            header("Location: YCMerch-admin.php?page=merchandise-store&type=" . urlencode($table) . "&status=" . urlencode('Update failed: ' . $e->getMessage()));
            exit;
          }
        }

        // DELETE
        if (isset($_POST['delete']) && $id) {
          try {
            $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `id` = ?");
            $stmt->execute([$id]);
          } catch (PDOException $e) {
            header("Location: YCMerch-admin.php?page=merchandise-store&type=" . urlencode($table) . "&status=" . urlencode('Delete failed: ' . $e->getMessage()));
            exit;
          }
        }

    header("Location: YCMerch-admin.php?page=merchandise-store&type=" . urlencode($table) . "&status=success");
        exit;
    }

    $queryError = null;
    try {
      $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY `id` DESC");
      $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $queryError = $e->getMessage();
      $items = [];
    }

    // --- Begin new layout ---
    ?>
    <div class="upload-container">
      <?php if (isset($_GET['status'])): ?>
        <div style="background:#0f5132;color:#d1e7dd;border:1px solid #0f5132;padding:10px 14px;border-radius:6px;margin-bottom:12px;">
          <?php if ($_GET['status'] === 'success'): ?>
            Saved successfully.
          <?php else: ?>
            <?php echo htmlspecialchars($_GET['status']); ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      <h2>Merchandise Store Management</h2>
      <p>Add, edit, or delete merchandise products. Manage your store inventory here.</p>
      <form method="GET" style="margin-bottom: 20px;">
        <input type="hidden" name="page" value="merchandise-store">
        <label for="type" style="color:white;">Select Product Type:</label>
        <select name="type" id="type" onchange="this.form.submit()">
          <?php foreach ($productTypes as $type): ?>
            <option value="<?php echo $type; ?>" <?php if ($selected == $type) echo 'selected'; ?>><?php echo ucfirst($type); ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>


    <div class="upload-container">
      <h2 id="formTitle">Add <?php echo ucfirst($selected); ?></h2>
      <form method="POST" enctype="multipart/form-data" id="productForm" action="?page=merchandise-store&type=<?php echo urlencode($selected); ?>">
        <input type="hidden" name="id" id="editId">
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($selected); ?>">

        <label for="editName">Product Name:</label>
        <input type="text" name="name" id="editName" required>

        <label for="editPrice">Product Price:</label>
        <input type="number" step="0.01" name="price" id="editPrice" required>

        <label for="editCaption">Caption/Description:</label>
        <input type="text" name="caption" id="editCaption" required>

  <?php if ($selected === 'tshirts' || $selected === 'hoodies'): ?>
  <label>Black Front Image:</label>
  <input type="file" name="image_black_front" accept="image/*">
  <label>Black Back Image:</label>
  <input type="file" name="image_black_back" accept="image/*">
  <label>White Front Image:</label>
  <input type="file" name="image_white_front" accept="image/*">
  <label>White Back Image:</label>
  <input type="file" name="image_white_back" accept="image/*">
  <?php else: ?>
  <label for="editImage">Product Image:</label>
  <input type="file" name="image" id="editImage">
  <?php endif; ?>

        <button type="submit" name="add" id="addBtn">Add <?php echo ucfirst($selected); ?></button>
        <button type="submit" name="update" id="updateBtn" style="display:none;">Update <?php echo ucfirst($selected); ?></button>
      </form>
    </div>

    <!-- Edit Modal for Merchandise -->
  <div id="merchEditModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; background:#000; align-items:center; justify-content:center; overflow:auto;">
      <div style="background:#181818; padding:38px 36px 34px 36px; border-radius:18px; min-width:340px; max-width:95vw; width:470px; position:relative; box-shadow:0 8px 32px rgba(0,0,0,0.4); color:#fff; display:flex; flex-direction:column; align-items:stretch; gap:0; margin:40px 0; max-height:90vh; overflow:auto;">
        <h2 style="margin-top:0; color:#fff; font-size:1.35rem; font-weight:700; letter-spacing:0.5px;">Edit <?php echo ucfirst($selected); ?></h2>
        <form method="POST" enctype="multipart/form-data" id="merchModalForm" action="?page=merchandise-store&type=<?php echo urlencode($selected); ?>" style="display:flex; flex-direction:column; gap:18px;">
          <input type="hidden" name="id" id="merchModalEditId">
          <input type="hidden" name="type" value="<?php echo htmlspecialchars($selected); ?>">

          <label for="merchModalEditName" style="font-weight:700; color:#fff; font-size:1.08rem; margin-bottom:2px;">Title:</label>
          <input type="text" name="name" id="merchModalEditName" required style="background:#111; color:#fff; border:1.5px solid #bfa14a; border-radius:7px; padding:10px 12px; font-size:1.08rem; outline:none;">

          <label for="merchModalEditPrice" style="font-weight:700; color:#fff; font-size:1.08rem; margin-bottom:2px;">Price:</label>
          <input type="number" step="0.01" name="price" id="merchModalEditPrice" required style="background:#111; color:#fff; border:1.5px solid #bfa14a; border-radius:7px; padding:10px 12px; font-size:1.08rem; outline:none;">

          <label for="merchModalEditCaption" style="font-weight:700; color:#fff; font-size:1.08rem; margin-bottom:2px;">Caption/Description:</label>
          <input type="text" name="caption" id="merchModalEditCaption" required style="background:#111; color:#fff; border:1.5px solid #bfa14a; border-radius:7px; padding:10px 12px; font-size:1.08rem; outline:none;">


          <?php if ($selected === 'tshirts' || $selected === 'hoodies'): ?>
          <label style="font-weight:700; color:#fff; font-size:1.08rem; margin-bottom:2px;">Change Black Front Image:</label>
          <div style="margin-bottom:8px;">
            <img id="preview_black_front" src="" alt="Black Front Preview" style="max-width:80px;max-height:80px;display:none;border:1.5px solid #bfa14a;border-radius:7px;">
          </div>
          <input type="file" name="image_black_front" id="input_black_front" style="background:#111; color:#fff; border:1.5px solid #bfa14a; border-radius:7px; padding:10px 12px; font-size:1.08rem; outline:none;">
          <label style="font-weight:700; color:#fff; font-size:1.08rem; margin-bottom:2px;">Change Black Back Image:</label>
          <div style="margin-bottom:8px;">
            <img id="preview_black_back" src="" alt="Black Back Preview" style="max-width:80px;max-height:80px;display:none;border:1.5px solid #bfa14a;border-radius:7px;">
          </div>
          <input type="file" name="image_black_back" id="input_black_back" style="background:#111; color:#fff; border:1.5px solid #bfa14a; border-radius:7px; padding:10px 12px; font-size:1.08rem; outline:none;">
          <label style="font-weight:700; color:#fff; font-size:1.08rem; margin-bottom:2px;">Change White Front Image:</label>
          <div style="margin-bottom:8px;">
            <img id="preview_white_front" src="" alt="White Front Preview" style="max-width:80px;max-height:80px;display:none;border:1.5px solid #bfa14a;border-radius:7px;">
          </div>
          <input type="file" name="image_white_front" id="input_white_front" style="background:#111; color:#fff; border:1.5px solid #bfa14a; border-radius:7px; padding:10px 12px; font-size:1.08rem; outline:none;">
          <label style="font-weight:700; color:#fff; font-size:1.08rem; margin-bottom:2px;">Change White Back Image:</label>
          <div style="margin-bottom:8px;">
            <img id="preview_white_back" src="" alt="White Back Preview" style="max-width:80px;max-height:80px;display:none;border:1.5px solid #bfa14a;border-radius:7px;">
          </div>
          <input type="file" name="image_white_back" id="input_white_back" style="background:#111; color:#fff; border:1.5px solid #bfa14a; border-radius:7px; padding:10px 12px; font-size:1.08rem; outline:none;">
          <?php else: ?>
          <label for="merchModalEditImage" style="font-weight:700; color:#fff; font-size:1.08rem; margin-bottom:2px;">Change Product Image:</label>
          <input type="file" name="image" id="merchModalEditImage" style="background:#111; color:#fff; border:1.5px solid #bfa14a; border-radius:7px; padding:10px 12px; font-size:1.08rem; outline:none;">
          <?php endif; ?>

          <div style="display:flex; gap:24px; margin-top:18px; justify-content:flex-end; align-items:center;">
            <button type="submit" name="update" style="background:#8c6a2a; color:#fff; font-weight:500; border:none; border-radius:6px; padding:13px 38px; font-size:1.13rem; letter-spacing:0.5px; box-shadow:none; transition:background 0.2s; cursor:pointer;">Save Changes</button>
            <button type="button" onclick="closeMerchEditModal()" style="background:none; color:#fff; border:none; font-size:1.13rem; font-weight:400; text-decoration:underline; cursor:pointer; padding:0 0 2px 0;">Cancel</button>
          </div>
        </form>
      </div>
    </div>

<div class="upload-container">
      <h2>Uploaded Merchandise</h2>
      <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; background:#000; color:white;">
          <tr style="background:#654922; color:white;">
            <th style="padding:8px; border:1px solid #956E2F;">ID</th>
            <th style="padding:8px; border:1px solid #956E2F;">Name</th>
            <th style="padding:8px; border:1px solid #956E2F;">Price</th>
            <th style="padding:8px; border:1px solid #956E2F;">Caption</th>
            <?php if ($selected === 'tshirts' || $selected === 'hoodies'): ?>
            <th style="padding:8px; border:1px solid #956E2F;">Black Front</th>
            <th style="padding:8px; border:1px solid #956E2F;">Black Back</th>
            <th style="padding:8px; border:1px solid #956E2F;">White Front</th>
            <th style="padding:8px; border:1px solid #956E2F;">White Back</th>
            <?php else: ?>
            <th style="padding:8px; border:1px solid #956E2F;">Image</th>
            <?php endif; ?>
            <th style="padding:8px; border:1px solid #956E2F;">Actions</th>
          </tr>
          <?php
          if ($queryError) {
            echo '<tr><td colspan="9" style="padding:10px; border:1px solid #956E2F; color:#ffb3b3;">Query failed: ' . htmlspecialchars($queryError) . '</td></tr>';
          }
          if (!$queryError && count($items) === 0) {
            echo '<tr><td colspan="9" style="padding:10px; border:1px solid #956E2F; color:#ddd; text-align:center;">No ' . htmlspecialchars($selected) . ' items added yet.</td></tr>';
          }
          foreach ($items as $row):
            $jsonRow = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
          ?>
          <tr>
            <td style="padding:8px; border:1px solid #956E2F; text-align:center;"><?php echo $row['id']; ?></td>
            <td style="padding:8px; border:1px solid #956E2F;"><?php echo htmlspecialchars($row['name']); ?></td>
            <td style="padding:8px; border:1px solid #956E2F;"><?php echo htmlspecialchars($row['price']); ?></td>
            <td style="padding:8px; border:1px solid #956E2F;"><?php echo htmlspecialchars($row['caption']); ?></td>
            <?php if ($selected === 'tshirts' || $selected === 'hoodies'): ?>
            <td style="padding:8px; border:1px solid #956E2F; text-align:center;"><?php echo imgCell($row['image_black_front']); ?></td>
            <td style="padding:8px; border:1px solid #956E2F; text-align:center;"><?php echo imgCell($row['image_black_back']); ?></td>
            <td style="padding:8px; border:1px solid #956E2F; text-align:center;"><?php echo imgCell($row['image_white_front']); ?></td>
            <td style="padding:8px; border:1px solid #956E2F; text-align:center;"><?php echo imgCell($row['image_white_back']); ?></td>
            <?php else: ?>
            <td style="padding:8px; border:1px solid #956E2F; text-align:center;"><?php echo imgCell($row['image']); ?></td>
            <?php endif; ?>
            <td style="padding:8px; border:1px solid #956E2F; text-align:center;">
              <button onclick='editItem(<?php echo $jsonRow; ?>)' class="admin-delete-btn" type="button">Edit</button>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <!-- HIGHLIGHT: This is the Delete button for merchandise admin section -->
                <button type="submit" name="delete" onclick="return confirm('Are you sure?')" class="admin-edit-btn">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>

    <script>
      function editItem(data) {
        // Show modal for merchandise
        var modal = document.getElementById('merchEditModal');
        if (modal) {
          modal.style.display = 'flex';
        }
        // Fill modal form
        document.getElementById('merchModalEditId').value = data.id;
        document.getElementById('merchModalEditName').value = data.name;
        document.getElementById('merchModalEditPrice').value = data.price;
        document.getElementById('merchModalEditCaption').value = data.caption;
        if (document.getElementById('merchModalEditImage')) document.getElementById('merchModalEditImage').value = '';
        // Show previews for t-shirts/hoodies
        if (data.image_black_front !== undefined) {
          setPreview('black_front', data.image_black_front);
          setPreview('black_back', data.image_black_back);
          setPreview('white_front', data.image_white_front);
          setPreview('white_back', data.image_white_back);
        }
      }

      function setPreview(slot, filename) {
        var img = document.getElementById('preview_' + slot);
        if (!img) return;
        if (filename && filename !== 'null') {
          img.src = 'uploads/YCMerch-uploads/' + filename;
          img.style.display = 'inline-block';
        } else {
          img.src = '';
          img.style.display = 'none';
        }
      }
      function closeMerchEditModal() {
        document.getElementById('merchEditModal').style.display = 'none';
      }
      // Ensure modal is always hidden on page load
      document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('merchEditModal');
        if (modal) {
          modal.style.display = 'none'; // Always hide modal on page load
        }
        var modalForm = document.getElementById('merchModalForm');
        if (modalForm) {
          modalForm.onsubmit = function(e) {
            // Let the form submit normally (POST with file upload)
            // Modal will close after page reload (update is successful)
          };
        }
      });
      // Close modal on background click
      document.addEventListener('click', function(e) {
        var modal = document.getElementById('merchEditModal');
        if (modal && e.target === modal) {
          closeMerchEditModal();
        }
      });
    </script>
    <?php
    // --- End new layout ---
    break;
}
?>
  </div>
</div>

</body>
</html>
