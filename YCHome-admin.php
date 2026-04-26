<?php
require_once __DIR__ . '/YCdb_connection.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Band Members CRUD operations
    if ($action === 'add_member') {
        $name = $_POST['name'] ?? '';
        $role = $_POST['role'] ?? '';
        $custom_role = $_POST['custom_role'] ?? '';
        if ($role === 'other' && !empty($custom_role)) {
            $role = $custom_role;
        }
        $image_path = '';
        $uploadDir = 'uploads/YCHome-uploads/band_members/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image_path = $targetFile;
            }
        }
        $is_leader = isset($_POST['leader']) && $_POST['leader'] === '1' ? 1 : 0;

        // If this member is marked as leader, unset other leaders first
        if ($is_leader) {
            try {
                $pdo->exec("UPDATE band_members SET is_leader = 0");
            } catch (Throwable $e) { /* ignore if column missing; we'll ensure column exists below */ }
        }

        if (!empty($name) && !empty($image_path) && !empty($role)) {
            // Ensure column exists (silent attempt)
            try {
                $pdo->exec("ALTER TABLE band_members ADD COLUMN IF NOT EXISTS is_leader TINYINT(1) DEFAULT 0");
            } catch (Throwable $e) {
                // Some MySQL versions may not support IF NOT EXISTS; attempt without it
                try { $pdo->exec("ALTER TABLE band_members ADD COLUMN is_leader TINYINT(1) DEFAULT 0"); } catch (Throwable $ex) { /* ignore */ }
            }

            $stmt = $pdo->prepare("INSERT INTO band_members (name, role, image_path, is_leader) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $role, $image_path, $is_leader]);
            header("Location: YCHome-admin.php?success=member_added");
            exit;
        } else {
            $success = "Please provide a name, role, and upload an image.";
        }
    }
    
    if ($action === 'update_member') {
        $id = $_POST['id'] ?? '';
        $name = $_POST['name'] ?? '';
        $role = $_POST['role'] ?? '';
        $custom_role = $_POST['custom_role'] ?? '';
        if ($role === 'other' && !empty($custom_role)) {
            $role = $custom_role;
        }
        $image_path = $_POST['image_path'] ?? '';
        $uploadDir = 'uploads/YCHome-uploads/band_members/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($_FILES['new_image']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['new_image']['tmp_name'], $targetFile)) {
                $image_path = $targetFile;
            }
        }
        $is_leader = isset($_POST['leader']) && $_POST['leader'] === '1' ? 1 : 0;

        // Ensure column exists (silent)
        try {
            $pdo->exec("ALTER TABLE band_members ADD COLUMN IF NOT EXISTS is_leader TINYINT(1) DEFAULT 0");
        } catch (Throwable $e) {
            try { $pdo->exec("ALTER TABLE band_members ADD COLUMN is_leader TINYINT(1) DEFAULT 0"); } catch (Throwable $ex) { /* ignore */ }
        }

        if ($is_leader) {
            try {
                $pdo->exec("UPDATE band_members SET is_leader = 0");
            } catch (Throwable $e) { /* ignore */ }
        }

        if (!empty($id) && !empty($name) && !empty($image_path) && !empty($role)) {
            $stmt = $pdo->prepare("UPDATE band_members SET name = ?, role = ?, image_path = ?, is_leader = ? WHERE id = ?");
            $stmt->execute([$name, $role, $image_path, $is_leader, $id]);
            header("Location: YCHome-admin.php?success=member_updated");
            exit;
        }
    }
    
    if ($action === 'delete_member') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            $stmt = $pdo->prepare("DELETE FROM band_members WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: YCHome-admin.php?success=member_deleted");
            exit;
        }
    }
    
    // What's New CRUD operations
    if ($action === 'add_news') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_path = '';
        $uploadDir = 'uploads/YCHome-uploads/whats_new/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image_path = $targetFile;
            }
        }
        if (!empty($title) && !empty($description) && !empty($image_path)) {
            // Deactivate all current news first
            $pdo->exec("UPDATE whats_new SET is_active = 0");
            // Add new news as active
            $stmt = $pdo->prepare("INSERT INTO whats_new (title, description, image_path, is_active) VALUES (?, ?, ?, 1)");
            $stmt->execute([$title, $description, $image_path]);
            header("Location: YCHome-admin.php?success=news_added");
            exit;
        } else {
            $success = "Please provide all fields and upload an image.";
        }
    }
    
    if ($action === 'update_news') {
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_path = $_POST['image_path'] ?? '';
        $uploadDir = 'uploads/YCHome-uploads/whats_new/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($_FILES['new_image']['name']);
            $targetFile = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['new_image']['tmp_name'], $targetFile)) {
                $image_path = $targetFile;
            }
        }
        if (!empty($id) && !empty($title) && !empty($description) && !empty($image_path)) {
            $stmt = $pdo->prepare("UPDATE whats_new SET title = ?, description = ?, image_path = ? WHERE id = ?");
            $stmt->execute([$title, $description, $image_path, $id]);
            header("Location: YCHome-admin.php?success=news_updated");
            exit;
        }
    }
    
    if ($action === 'activate_news') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            // Deactivate all news first
            $pdo->exec("UPDATE whats_new SET is_active = 0");
            // Activate selected news
            $stmt = $pdo->prepare("UPDATE whats_new SET is_active = 1 WHERE id = ?");
            $stmt->execute([$id]);
            $success = "What's new content activated successfully!";
        }
    }
    
    if ($action === 'delete_news') {
        $id = $_POST['id'] ?? '';
        if (!empty($id)) {
            $stmt = $pdo->prepare("DELETE FROM whats_new WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: YCHome-admin.php?success=news_deleted");
            exit;
        }
    }
}

// Fetch all band members
$stmt = $pdo->query("SELECT * FROM band_members ORDER BY id");
$bandMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all what's new content
$stmt = $pdo->query("SELECT * FROM whats_new ORDER BY created_at DESC");
$whatsNewItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yaka Crew - Admin Panel</title>
    <link rel="stylesheet" href="css/YCHome-admin.css">
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
   <li><a href="YCMerch-merch1.php">Merchandise</a></li>
      </ul>
    </div><a href="YCHome-generate-pdf.php" target="_blank" class="pdf-btn-custom">Generate PDF Report</a>
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


    <div class="admin-container">

        <div class="admin-content">
            <!-- Band Members Management -->
            <section class="admin-section">
                <h2>Band Members Management</h2>
                <a>Add, edit, or delete audio tracks. Manage top song list and latest track list here.</a>
                
        
    <?php 
        // Show success message from redirect
        $success = '';
        if (isset($_GET['success'])) {
            if ($_GET['success'] === 'member_added') {
                $success = 'Band member added successfully!';
            } elseif ($_GET['success'] === 'news_added') {
                $success = "What's new content added successfully!";
            } elseif ($_GET['success'] === 'member_updated') {
                $success = 'Band member updated successfully!';
            } elseif ($_GET['success'] === 'news_updated') {
                $success = "What's new content updated successfully!";
            } elseif ($_GET['success'] === 'member_deleted') {
                $success = 'Band member deleted successfully!';
            } elseif ($_GET['success'] === 'news_deleted') {
                $success = "What's new content deleted successfully!";
            }
        }
    ?>
    <?php if (!empty($success)): ?>
        <div class="success-message" id="top-success-message"><?php echo htmlspecialchars($success); ?></div>
        <script>
            setTimeout(function() {
                var msg = document.getElementById('top-success-message');
                if (msg) msg.style.display = 'none';
            }, 3000);
        </script>
    <?php endif; ?>

                <!-- Add New Member Form -->
                <div class="form-container">
                    <h2>Add New Band Member</h2>

                    <form method="POST" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_member">
                        <div class="form-group">
                            <label for="member_name">Name:</label>
                            <input type="text" id="member_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="member_role">Role:</label>
                            <select id="member_role" name="role" required onchange="toggleCustomRoleInput(this)">
                                <option value="">Select Role</option>
                                <option value="Lead vocalist">Lead vocalist</option>
                                <option value="Guitarist">Guitarist</option>
                                <option value="Bassist">Bassist</option>
                                <option value="Keyboardist">Keyboardist</option>
                                <option value="Drummer">Drummer</option>
                                <option value="Percussionist">Percussionist</option>
                                <option value="other">Other</option>
                            </select>
                            <input type="text" id="custom_role_input" name="custom_role" placeholder="Enter custom role" style="display:none; margin-top:8px;" />
                        </div>
                        <div class="form-group">
                            <label for="member_leader" style="display: flex; align-items: center; gap: 10px;">
                                <span>Leader</span>
                                <input type="checkbox" id="member_leader" name="leader" value="1" class="leader-checkbox" onclick="event.stopPropagation();">
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="member_image">Image:</label>
                            <input type="file" id="member_image" name="image" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Member</button>
                        <script>
                        function toggleCustomRoleInput(select) {
                            var customInput = document.getElementById('custom_role_input');
                            if (select.value === 'other') {
                                customInput.style.display = 'block';
                                customInput.required = true;
                            } else {
                                customInput.style.display = 'none';
                                customInput.required = false;
                            }
                        }
                        </script>
                    </form>
                </div>

                <!-- Existing Members List -->
                <div class="list-container">
                    <h2>Current Band Members</h2>
                    <div class="members-grid">
                        <?php foreach($bandMembers as $member): ?>
                        <div class="member-item">
                                                        <img src="<?php echo htmlspecialchars($member['image_path']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
                                                        <h4><?php echo htmlspecialchars($member['name']); ?></h4>
                                                        <?php if (!empty($member['role'])): ?>
                                                            <div class="member-role"><strong>Role:</strong> <?php echo htmlspecialchars($member['role']); ?></div>
                                                        <?php endif; ?>
                                                        <div class="member-actions">
                                                                <button onclick="editMember(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['name']); ?>', '<?php echo htmlspecialchars($member['image_path']); ?>', '<?php echo htmlspecialchars($member['role']); ?>', <?php echo (int)($member['is_leader'] ?? 0); ?>)" class="btn btn-edit">Edit</button>
                                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this member?')">
                                                                        <input type="hidden" name="action" value="delete_member">
                                                                        <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                                                        <button type="submit" class="btn btn-delete">Delete</button>
                                                                </form>
                                                        </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- What's New Management -->
            <section class="admin-section">
                <h2>What's New Management</h2>
                
                <!-- Add New Content Form -->
                <div class="form-container">
                    <h2>Add New Content</h2>
                    <form method="POST" class="admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add_news">
                        <div class="form-group">
                            <label for="news_title">Title:</label>
                            <input type="text" id="news_title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="news_description">Description:</label>
                            <textarea id="news_description" name="description" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="news_image">Image:</label>
                            <input type="file" id="news_image" name="image" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Content</button>
                    </form>
                </div>

                <!-- Existing Content List -->
                <div class="list-container">
                    <h2>Current What's New Content</h2>
                    <div class="news-list">
                        <?php foreach($whatsNewItems as $news): ?>
                        <div class="news-item <?php echo $news['is_active'] ? 'active' : ''; ?>">
                            <img src="<?php echo htmlspecialchars($news['image_path']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>">
                            <div class="news-content">
                                <h4><?php echo htmlspecialchars($news['title']); ?></h4>
                                <p><?php echo htmlspecialchars($news['description']); ?></p>
                                <small>Created: <?php echo $news['created_at']; ?></small>
                                <?php if ($news['is_active']): ?>
                                    <span class="active-badge">ACTIVE</span>
                                <?php endif; ?>
                            </div>
                            <div class="news-actions">
                                <button onclick="editNews(<?php echo $news['id']; ?>, '<?php echo htmlspecialchars($news['title']); ?>', '<?php echo htmlspecialchars($news['description']); ?>', '<?php echo htmlspecialchars($news['image_path']); ?>')" class="btn btn-edit">Edit</button>
                                <?php if (!$news['is_active']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="activate_news">
                                    <input type="hidden" name="id" value="<?php echo $news['id']; ?>">
                                    <button type="submit" class="btn btn-success">Activate</button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this content?')">
                                    <input type="hidden" name="action" value="delete_news">
                                    <input type="hidden" name="id" value="<?php echo $news['id']; ?>">
                                    <button type="submit" class="btn btn-delete">Delete</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Edit Member Modal -->
    <div id="editMemberModal" class="modal fullpage-modal">
        <div class="modal-content edit-card">
            <!-- X close removed -->
            <h2>Edit Band Member</h2>
            <hr style="border:0; border-top:2px solid #bfa14a; margin:0 0 22px 0;">
            <form method="POST" class="admin-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_member">
                <input type="hidden" id="edit_member_id" name="id">
                <div class="form-group">
                    <label for="edit_member_name">Name:</label>
                    <input type="text" id="edit_member_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_member_role">Role:</label>
                    <select id="edit_member_role" name="role" required onchange="toggleEditCustomRoleInput(this)">
                        <option value="">Select Role</option>
                        <option value="Lead vocalist">Lead vocalist</option>
                        <option value="Guitarist">Guitarist</option>
                        <option value="Bassist">Bassist</option>
                        <option value="Keyboardist">Keyboardist</option>
                        <option value="Drummer">Drummer</option>
                        <option value="Percussionist">Percussionist</option>
                        <option value="other">Other</option>
                    </select>
                    <input type="text" id="edit_custom_role_input" name="custom_role" placeholder="Enter custom role" style="display:none; margin-top:8px;" />
                </div>
                <div class="form-group">
                    <label for="edit_member_leader" style="display: flex; align-items: center; gap: 10px;">
                        <span>Leader</span>
                        <input type="checkbox" id="edit_member_leader" name="leader" value="1" class="leader-checkbox" onclick="event.stopPropagation();">
                    </label>
                </div>
                <div class="form-group">
                    <label>Current Image:</label><br>
                    <img id="edit_member_current_image" src="" alt="Current Image" style="max-width:120px; max-height:120px; display:block; margin-bottom:8px;">
                    <input type="hidden" id="edit_member_image_path" name="image_path">
                    <label for="edit_member_new_image">Change Image:</label>
                    <input type="file" id="edit_member_new_image" name="new_image" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Member</button>
                    <button type="button" class="btn btn-cancel" onclick="closeModal('editMemberModal')">Cancel</button>
                </div>
            </form>
            <script>
            function toggleEditCustomRoleInput(select) {
                var customInput = document.getElementById('edit_custom_role_input');
                if (select.value === 'other') {
                    customInput.style.display = 'block';
                    customInput.required = true;
                } else {
                    customInput.style.display = 'none';
                    customInput.required = false;
                }
            }
            </script>
        </div>
    </div>

    <!-- Edit News Modal -->
    <div id="editNewsModal" class="modal fullpage-modal">
        <div class="modal-content edit-card">
            <!-- X close removed -->
            <h2>Edit What's New Content</h2>
            <hr style="border:0; border-top:2px solid #bfa14a; margin:0 0 22px 0;">
            <form method="POST" class="admin-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_news">
                <input type="hidden" id="edit_news_id" name="id">
                <div class="form-group">
                    <label for="edit_news_title">Title:</label>
                    <input type="text" id="edit_news_title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="edit_news_description">Description:</label>
                    <textarea id="edit_news_description" name="description" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label>Current Image:</label><br>
                    <img id="edit_news_current_image" src="" alt="Current Image" style="max-width:120px; max-height:120px; display:block; margin-bottom:8px;">
                    <input type="hidden" id="edit_news_image_path" name="image_path">
                    <label for="edit_news_new_image">Change Image (optional):</label>
                    <input type="file" id="edit_news_new_image" name="new_image" accept="image/*">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Content</button>
                    <button type="button" class="btn btn-cancel" onclick="closeModal('editNewsModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .fullpage-modal {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.98);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .fullpage-modal.active { display: flex !important; }
        .edit-card {
            background: #111;
            border-radius: 12px;
            box-shadow: 0 0 24px #000a;
            padding: 2rem 2.5rem;
            width: 100%;
            max-width: 400px;
            margin: 2rem auto;
            min-width: unset;
            border: none;
        }
        .form-actions {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-top: 1.2rem;
        }
        .btn-cancel {
            background: none;
            color: #aaa;
            border: none;
            font-weight: normal;
            text-decoration: underline;
            cursor: pointer;
            font-size: 1rem;
            padding: 0.7rem 1.5rem;
        }
        @media (max-width: 600px) {
            .edit-card { padding: 1rem; min-width: 0; }
        }
    </style>
    <script>
        function editMember(id, name, imagePath, role, isLeader) {
            document.getElementById('edit_member_id').value = id;
            document.getElementById('edit_member_name').value = name;
            document.getElementById('edit_member_image_path').value = imagePath;
            document.getElementById('edit_member_current_image').src = imagePath;
            // Set leader checkbox based on passed value
            var leaderCheckbox = document.getElementById('edit_member_leader');
            if (leaderCheckbox) leaderCheckbox.checked = Boolean(Number(isLeader));
            // Set role dropdown and custom input
            var roleSelect = document.getElementById('edit_member_role');
            var customInput = document.getElementById('edit_custom_role_input');
            if (!role || role === '') {
                roleSelect.value = '';
                customInput.style.display = 'none';
                customInput.value = '';
            } else if (["Lead vocalist","Guitarist","Bassist","Keyboardist","Drummer","Percussionist"].includes(role)) {
                roleSelect.value = role;
                customInput.style.display = 'none';
                customInput.value = '';
            } else {
                roleSelect.value = 'other';
                customInput.style.display = 'block';
                customInput.value = role;
            }
            document.getElementById('editMemberModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function editNews(id, title, description, imagePath) {
            document.getElementById('edit_news_id').value = id;
            document.getElementById('edit_news_title').value = title;
            document.getElementById('edit_news_description').value = description;
            document.getElementById('edit_news_image_path').value = imagePath;
            document.getElementById('edit_news_current_image').src = imagePath;
            document.getElementById('editNewsModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const memberModal = document.getElementById('editMemberModal');
            const newsModal = document.getElementById('editNewsModal');
            if (event.target == memberModal) {
                memberModal.classList.remove('active');
            }
            if (event.target == newsModal) {
                newsModal.classList.remove('active');
            }
        }
    </script>
</body>
</html>