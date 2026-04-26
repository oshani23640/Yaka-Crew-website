<?php

session_start();

// Check if user is logged in as admin, redirect to login if not
if (!isset($_SESSION['admin'])) {
    header("Location: YCEvents-login.php");
    exit();
}

// Include database connection for data operations
require_once __DIR__ . '/YCdb_connection.php';

// Define constants
define('UPLOAD_DIR', __DIR__ . '/uploads/Events/');
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to redirect with messages
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Function to get event image path
function getEventImagePath($filename) {
    return 'uploads/Events/' . $filename;
}

// Check if upload directory exists, create if not
if (!file_exists(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        die('Failed to create upload directory: ' . UPLOAD_DIR);
    }
}

// Get the current page from URL parameter, default to 'home'
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Yaka Crew Admin Panel</title>
  
    <link rel="stylesheet" href="css/YCEvents-admin-style.css?v=<?php echo @filemtime(__DIR__ . '/css/YCEvents-admin-style.css'); ?>">
    <link rel="stylesheet" href="css/YCEvents.dashboard.css?v=<?php echo @filemtime(__DIR__ . '/css/YCEvents.dashboard.css'); ?>">
  
</head>
<body>

<!-- Navigation -->
<div class="navbar">
    <div class="logo">
        <img src="assets/images/Yaka Crew Logo.JPG" alt="Yaka Crew Logo" style="width: 120px; height: auto;">
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
       <a href="YCEvents-generate-pdf.php"  target="_blank" class="pdf-btn-custom">Generate PDF Report</a>
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
        <a href="YCEvents-login.php?action=logout" class="logout-btn">Logout</a>
    </div>
</div>

<!-- Main Container -->
<div class="main-container">
  <!-- Left Sidebar -->
  <div class="left-sidebar">
    <ul class="nav-links">
    <li><a href="YCHome-admin.php?page=home">Home</a></li>
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
  case 'home':
    echo "<h2>Dashboard</h2>";
    echo "<p>Welcome to the Yaka Crew Admin Panel. Use the navigation to manage different sections of your website.</p>";
    break;

  case 'music':
    echo "<h2>Music Management</h2>";
    echo "<p>Add, edit, or delete audio tracks. Manage top song list and latest track list here.</p>";
    
    ?>

    <?php
    break;

  case 'bookings':
    echo "<h2>Booking Management</h2>";
    echo "<p>Handle requests for private gigs and studio appointments.</p>";
    break;
    
  case 'events':
    // Check if we're viewing the dashboard or management view
    $subpage = isset($_GET['subpage']) ? $_GET['subpage'] : 'dashboard';
    
    echo '<h2>Event Management</h2>';
    
    // Display success/error messages
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            echo '<div class="admin-message success">Operation completed successfully!</div>';
        } elseif ($_GET['status'] == 'error') {
            $message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Operation failed. Please try again.';
            echo '<div class="admin-message error">Error: ' . $message . '</div>';
        }
    }
    
    if ($subpage == 'dashboard') {
        // Display the dashboard view
        ?>
        <div class="admin-dashboard-content">
            <!-- Stats Container -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Upcoming Events</h3>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE is_past_event = FALSE");
                    $count = $stmt->fetchColumn();
                    ?>
                    <p><?php echo $count; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Slider Images</h3>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) FROM slider_images");
                    $count = $stmt->fetchColumn();
                    ?>
                    <p><?php echo $count; ?></p>
                </div>
            </div>
            <!-- Quick Links -->
            <div class="quick-links">
                <a href="?page=events&subpage=manage" class="btn btn-primary">Manage Events</a>
                <a href="?page=events&subpage=slider" class="btn btn-primary">Manage Sliders</a>
                <a href="YCEvents-generate-pdf.php" class="btn btn-primary" target="_blank" style="background:#222;margin-left:10px;">Generate PDF</a>
            </div>
        </div>
        <?php
    } elseif ($subpage == 'manage') {
        // Display the full event management view
        // Handle event deletion
        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);
            $_SESSION['message'] = "Event deleted successfully";
            redirect('?page=events&subpage=manage');
        }

        // Get all events
        $events = $pdo->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll();
        ?>
        <div class="content">
            <div class="action-bar">
                <a href="?page=events&subpage=add" class="btn btn-primary">Add New Event</a>
                <a href="YCEvents-generate-pdf.php" class="btn btn-primary" target="_blank" style="background:#222;margin-left:10px;">Generate PDF</a>
            </div>
            <table class="events-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($event['event_date'])); ?></td>
                        <td><?php echo htmlspecialchars($event['location']); ?></td>
                        <td>LKR <?php echo number_format($event['price'], 2); ?></td>
                        <td><?php echo ucfirst($event['event_type']); ?></td>
                        <td>
                           
                            <a href="admin/YCEvents-Edit.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                            <a href="?page=events&subpage=manage&delete=<?php echo $event['id']; ?>" class="btn btn-sm btn-outline" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    } elseif ($subpage == 'add') {
        // Add new event form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = sanitizeInput($_POST['title']);
            $description = sanitizeInput($_POST['description']);
            $event_date = sanitizeInput($_POST['event_date']);
            $start_time = sanitizeInput($_POST['start_time']);
            $end_time = sanitizeInput($_POST['end_time']);
            $location = sanitizeInput($_POST['location']);
            $price = (float)$_POST['price'];
            $event_type = sanitizeInput($_POST['event_type']);
            $is_whats_new = isset($_POST['is_whats_new']) ? 1 : 0;

            try {
                // Insert event
                $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, start_time, end_time, location, price, event_type, is_whats_new) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $event_date, $start_time, $end_time, $location, $price, $event_type, $is_whats_new]);

                $event_id = (int)$pdo->lastInsertId();
                if ($event_id <= 0) {
                    redirect('?page=events&subpage=add&status=error&message=' . urlencode('Failed to create event record.'));
                }

                // Handle image uploads
                if (!empty($_FILES['images']['name'][0])) {
                    // Ensure upload directory exists and is writable
                    if (!file_exists(UPLOAD_DIR)) {
                        @mkdir(UPLOAD_DIR, 0755, true);
                    }
                    if (!is_writable(UPLOAD_DIR)) {
                        @chmod(UPLOAD_DIR, 0777);
                    }
                    if (!is_writable(UPLOAD_DIR)) {
                        $msg = 'Upload directory is not writable: ' . UPLOAD_DIR . ' — please adjust permissions.';
                        redirect('?page=events&subpage=add&status=error&message=' . urlencode($msg));
                    }
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        $err = $_FILES['images']['error'][$key];
                        if ($err === UPLOAD_ERR_OK) {
                            $file_name = basename($_FILES['images']['name'][$key]);
                            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                            $new_file_name = uniqid('event_', true) . '.' . $file_ext;
                            $upload_path = UPLOAD_DIR . $new_file_name;

                            if (!move_uploaded_file($tmp_name, $upload_path)) {
                                $msg = 'Failed to move uploaded image to uploads/Events. Path: ' . $upload_path . ' — please check folder permissions.';
                                redirect('?page=events&subpage=add&status=error&message=' . urlencode($msg));
                            }

                            $is_primary = ($key === 0) ? 1 : 0;
                            $pdo->prepare("INSERT INTO event_images (event_id, image_path, is_primary) VALUES (?, ?, ?)")
                                ->execute([$event_id, $new_file_name, $is_primary]);
                        } elseif ($err !== UPLOAD_ERR_NO_FILE) {
                            redirect('?page=events&subpage=add&status=error&message=' . urlencode('Image upload error code: ' . $err));
                        }
                    }
                }

                $_SESSION['message'] = "Event added successfully";
                redirect('?page=events&subpage=manage');
            } catch (Throwable $e) {
                redirect('?page=events&subpage=add&status=error&message=' . urlencode($e->getMessage()));
            }
        }
        ?>
        <div class="container">
            <h1>Add New Event</h1>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Event Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="event_date">Date</label>
                        <input type="date" id="event_date" name="event_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" id="start_time" name="start_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" id="end_time" name="end_time" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (LKR)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_type">Event Type</label>
                        <select id="event_type" name="event_type" required>
                            <option value="concert">Concert</option>
                            <option value="festival">Festival</option>
                            <option value="private">Private Event</option>
                            <option value="charity">Charity Show</option>
                            <option value="workshop">Workshop</option>
                        </select>
                    </div>
                </div>
                
                
                
                <div class="form-group">
                    <label for="is_whats_new">
                        <input type="checkbox" id="is_whats_new" name="is_whats_new" value="1">
                        Feature in "What's New"?
                    </label>
                </div>
                
                <div class="form-group">
                    <label for="images">Event Images (Multiple allowed, first image will be primary)</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Event</button>
                    <a href="?page=events&subpage=manage" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
        <?php
    }  elseif ($subpage == 'slider') {
        // Slider management view
        // Handle slider deletion
        if (isset($_GET['delete'])) {
            $id = (int)$_GET['delete'];
            $stmt = $pdo->prepare("SELECT image_path FROM slider_images WHERE id = ?");
            $stmt->execute([$id]);
            $image = $stmt->fetch();
            
            if ($image && file_exists(UPLOAD_DIR . $image['image_path'])) {
                unlink(UPLOAD_DIR . $image['image_path']);
            }
            
            $pdo->prepare("DELETE FROM slider_images WHERE id = ?")->execute([$id]);
            $_SESSION['message'] = "Slider image deleted successfully";
            redirect('?page=events&subpage=slider');
        }
        
        // Handle slider status toggle
        if (isset($_GET['toggle'])) {
            $id = (int)$_GET['toggle'];
            $pdo->prepare("UPDATE slider_images SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
            redirect('?page=events&subpage=slider');
        }
        
        // Handle new slider upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['slider_image']['name'])) {
            $err = $_FILES['slider_image']['error'];
            if ($err === UPLOAD_ERR_OK) {
                $file_name = basename($_FILES['slider_image']['name']);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_name = uniqid('slider_', true) . '.' . $file_ext;
                $upload_path = UPLOAD_DIR . $new_file_name;

                // Ensure upload directory exists and is writable
                if (!file_exists(UPLOAD_DIR)) {
                    @mkdir(UPLOAD_DIR, 0755, true);
                }
                if (!is_writable(UPLOAD_DIR)) {
                    @chmod(UPLOAD_DIR, 0777);
                }
                if (!is_writable(UPLOAD_DIR)) {
                    $msg = 'Upload directory is not writable: ' . UPLOAD_DIR . ' — please adjust permissions.';
                    redirect('?page=events&subpage=slider&status=error&message=' . urlencode($msg));
                }

                if (!move_uploaded_file($_FILES['slider_image']['tmp_name'], $upload_path)) {
                    $msg = 'Failed to move uploaded slider image to uploads/Events. Path: ' . $upload_path . ' — please check folder permissions.';
                    redirect('?page=events&subpage=slider&status=error&message=' . urlencode($msg));
                }

                try {
                    $caption = sanitizeInput($_POST['caption']);
                    $pdo->prepare("INSERT INTO slider_images (image_path, caption) VALUES (?, ?)")->execute([$new_file_name, $caption]);
                    $_SESSION['message'] = "Slider image uploaded successfully";
                    redirect('?page=events&subpage=slider');
                } catch (Throwable $e) {
                    redirect('?page=events&subpage=slider&status=error&message=' . urlencode($e->getMessage()));
                }
            } else {
                redirect('?page=events&subpage=slider&status=error&message=' . urlencode('Upload error code: ' . $err));
            }
        }
        
        // Get all slider images
        $sliders = $pdo->query("SELECT * FROM slider_images ORDER BY created_at DESC")->fetchAll();
        ?>
        <div class="container">
            <h1>Manage Slider Images</h1>
            
                        <?php if (isset($_SESSION['message'])): ?>
                                <div class="alert alert-success" id="slider-success-alert"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                                <script>
                                    // Auto-hide the success alert with total ~1200ms display (≈900ms visible + 300ms fade)
                                    (function() {
                                        const alertEl = document.getElementById('slider-success-alert');
                                        if (!alertEl) return;
                                        setTimeout(() => {
                                            alertEl.classList.add('hide');
                                            setTimeout(() => {
                                                if (alertEl && alertEl.parentNode) alertEl.parentNode.removeChild(alertEl);
                                            }, 300); // matches CSS transition duration
                                        }, 900);
                                    })();
                                </script>
                        <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="upload-form">
                <div class="form-group">
                    <label for="slider_image">Upload New Slider Image</label>
                    <input type="file" id="slider_image" name="slider_image" accept="image/*" required>
                </div>
                
                <div class="form-group">
                    <label for="caption">Caption (Optional)</label>
                    <input type="text" id="caption" name="caption">
                </div>
                
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
            
            <div class="sliders-grid">
                <?php foreach ($sliders as $slider): ?>
                <div class="slider-card <?php echo $slider['is_active'] ? 'active' : 'inactive'; ?>">
                    <img src="<?php echo htmlspecialchars(getEventImagePath($slider['image_path'])); ?>" alt="Slider Image">
                    <div class="slider-info">
                        <?php if ($slider['caption']): ?>
                            <p><?php echo htmlspecialchars($slider['caption']); ?></p>
                        <?php endif; ?>
                        <p>Uploaded: <?php echo date('M j, Y', strtotime($slider['created_at'])); ?></p>
                    </div>
                    <div class="slider-actions">
                        <a href="?page=events&subpage=slider&toggle=<?php echo $slider['id']; ?>" class="btn btn-sm <?php echo $slider['is_active'] ? 'btn-outline' : 'btn-primary'; ?>">
                            <?php echo $slider['is_active'] ? 'Deactivate' : 'Activate'; ?>
                        </a>
                        <a href="?page=events&subpage=slider&delete=<?php echo $slider['id']; ?>" class="btn btn-sm btn-outline" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
    break;

  case 'blogs':
    echo "<h2>Blog Management</h2>";
    echo "<p>Publish blog posts and manage content.</p>";
    break;

  case 'merchandise-store':
    echo "<h2>Merchandise Store Management</h2>";
    echo "<p>Manage items for sale and payment gateway orders (PayHere sandbox).</p>";
    break;

  case 'gallery':
    echo "<h2>Gallery Overview</h2>";
    echo "<p>Manage all gallery content including music, videos, and bookings.</p>";
    break;

  default:
    echo "<h2>Dashboard</h2>";
    echo "<p>Welcome to the Yaka Crew Admin Panel. Use the navigation to manage different sections of your website.</p>";
}
?>
  </div>
</div>
</body>
</html>