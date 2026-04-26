<?php
// No login required for admin page (public access)
require_once __DIR__ . '/../YCdb_connection.php';
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
// Handle booking deletion
if (isset($_GET['page']) && $_GET['page'] === 'delete_booking' && isset($_GET['id'])) {
  $delete_id = intval($_GET['id']);
  try {
    $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = ?');
    $stmt->execute([$delete_id]);
    header('Location: YCBooking_admin.php?page=bookings&delete=success');
    exit();
  } catch (PDOException $e) {
    header('Location: YCBooking_admin.php?page=bookings&delete=error');
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Yaka Crew Admin Panel</title>
  <link rel="stylesheet" href="../css/YCBooking_admin.css">

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
    <?php if ($page === 'bookings'): ?>
      <a href="../YCBooking-generate-pdf.php"  target="_blank" class="pdf-btn-custom">Generate PDF Report</a>
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
    <?php endif; ?>
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
  case 'edit_booking':
    $edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $edit_error = '';
    $edit_success = false;
    if ($edit_id > 0) {
      // Handle update
      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile_number'] ?? '');
        $date = $_POST['booking_date'] ?? '';
        $time = $_POST['booking_time'] ?? '';
        $other_info = trim($_POST['other_info'] ?? '');
        $booking_status = $_POST['booking_status'] ?? '';
        $payment_status = $_POST['payment_status'] ?? '';
        $admin_notes = trim($_POST['admin_notes'] ?? '');
        try {
          // Fetch current values for comparison
          $stmt = $pdo->prepare("SELECT payment_status, booking_status, admin_notes FROM bookings WHERE id = ?");
          $stmt->execute([$edit_id]);
          $current = $stmt->fetch(PDO::FETCH_ASSOC);
          $fields = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'mobile_number' => $mobile,
            'booking_date' => $date,
            'booking_time' => $time,
            'other_info' => $other_info,
            'booking_status' => $booking_status,
            'payment_status' => $payment_status,
            'admin_notes' => $admin_notes
          ];
          $set = [];
          $params = [];
          foreach ($fields as $col => $val) {
            $set[] = "$col=?";
            $params[] = $val;
          }
          // Track which *_updated_at fields to update
          if ($current) {
            if ($current['admin_notes'] !== $admin_notes) {
              $set[] = "admin_note_updated_at=NOW()";
            }
            if ($current['payment_status'] !== $payment_status) {
              $set[] = "payment_status_updated_at=NOW()";
            }
            if ($current['booking_status'] !== $booking_status) {
              $set[] = "booking_status_updated_at=NOW()";
            }
          }
          $params[] = $edit_id;
          $sql = "UPDATE bookings SET ".implode(", ", $set)." WHERE id=?";
          $stmt = $pdo->prepare($sql);
          $stmt->execute($params);
          $edit_success = true;
        } catch (PDOException $e) {
          $edit_error = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
      }
      // Fetch booking
      $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
      $stmt->execute([$edit_id]);
      $booking = $stmt->fetch();
      if ($booking) {
        echo '<div style="max-width:480px;margin:48px auto 0 auto;background:#181818;padding:36px 32px 28px 32px;border-radius:12px;box-shadow:0 4px 32px #000a;">';
        echo '<h2 style="color:#fff;text-align:left;font-size:2em;font-weight:700;margin-bottom:8px;">Edit Booking Information</h2>';
        echo '<div style="height:3px;width:100%;background:linear-gradient(90deg,#c59d5f 60%,transparent 100%);margin-bottom:28px;"></div>';
        if ($edit_success) echo '<div style="background:#7a5a27;color:#fff;padding:12px 18px;border-radius:6px;margin-bottom:18px;font-weight:500;">Booking updated successfully!</div>';
        if ($edit_error) echo '<div style="background:#c0392b;color:#fff;padding:12px 18px;border-radius:6px;margin-bottom:18px;font-weight:500;">'. $edit_error .'</div>';
        echo '<form method="post" autocomplete="off">';
        $input_style = 'width:100%;background:#222;border:1.5px solid #c59d5f;color:#fff;padding:10px 12px;font-size:1.08em;border-radius:4px;margin-bottom:18px;';
        $label_style = 'color:#fff;font-size:1.08em;font-weight:700;display:block;margin-bottom:6px;';
        echo '<div style="margin-bottom:16px;"><label style="'.$label_style.'">First Name:<input type="text" name="first_name" value="'.htmlspecialchars($booking['first_name']).'" style="'.$input_style.'" required></label></div>';
        echo '<div style="margin-bottom:16px;"><label style="'.$label_style.'">Last Name:<input type="text" name="last_name" value="'.htmlspecialchars($booking['last_name']).'" style="'.$input_style.'" required></label></div>';
        echo '<div style="margin-bottom:16px;"><label style="'.$label_style.'">Email:<input type="email" name="email" value="'.htmlspecialchars($booking['email']).'" style="'.$input_style.'" required></label></div>';
        echo '<div style="margin-bottom:16px;"><label style="'.$label_style.'">Mobile Number:<input type="text" name="mobile_number" value="'.htmlspecialchars($booking['mobile_number']).'" style="'.$input_style.'" required></label></div>';
        echo '<div style="margin-bottom:16px;"><label style="'.$label_style+'">Booking Date:<input type="date" name="booking_date" value="'.htmlspecialchars($booking['booking_date']).'" style="'.$input_style+'" required></label></div>';
        echo '<div style="margin-bottom:16px;"><label style="'.$label_style.'">Booking Time:<input type="time" name="booking_time" value="'.htmlspecialchars($booking['booking_time']).'" style="'.$input_style.'" required></label></div>';
        echo '<div style="margin-bottom:16px;"><label style="'.$label_style.'">Other Info:<input type="text" name="other_info" value="'.htmlspecialchars($booking['other_info']).'" style="'.$input_style.'"></label></div>';
        // Booking Status
        $statuses = ['Pending','Confirmed','Cancelled'];
        echo '<div style="margin-bottom:16px;"><label style="'.$label_style.'">Booking Status:';
        echo '<select name="booking_status" style="'.$input_style.'">';
        foreach ($statuses as $status) {
          $sel = ($booking['booking_status'] ?? 'Pending') === $status ? 'selected' : '';
          echo '<option value="'.$status.'" '.$sel.'>'.$status.'</option>';
        }
        echo '</select></label></div>';
        // Payment Status
        $pay_statuses = ['Unpaid','Paid','Refunded'];
        echo '<div style="margin-bottom:16px;"><label style="'.$label_style.'">Payment Status:';
        echo '<select name="payment_status" style="'.$input_style.'">';
        foreach ($pay_statuses as $status) {
          $sel = ($booking['payment_status'] ?? 'Unpaid') === $status ? 'selected' : '';
          echo '<option value="'.$status.'" '.$sel.'>'.$status.'</option>';
        }
        echo '</select></label></div>';
        // Admin Notes
        echo '<div style="margin-bottom:16px;"><label style="'.$label_style.'">Admin Notes:<input type="text" name="admin_notes" value="'.htmlspecialchars($booking['admin_notes'] ?? '').'" style="'.$input_style.'"></label></div>';
        echo '<div style="display:flex;justify-content:flex-end;gap:16px;margin-top:18px;">';
        echo '<button type="submit" style="background:#c59d5f;color:#222;padding:10px 28px;border-radius:6px;font-weight:700;font-size:1.1em;border:none;cursor:pointer;">Save Changes</button>';
        echo '<a href="YCBooking_admin.php?page=bookings" style="color:#fff;text-decoration:underline;font-size:1.08em;padding:10px 0 0 0;align-self:center;">Cancel</a>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
      } else {
        echo '<div style="color:#fff;background:#222;padding:16px;border-radius:8px;max-width:600px;margin:40px auto;">Booking not found.</div>';
      }
    } else {
      echo '<div style="color:#fff;background:#222;padding:16px;border-radius:8px;max-width:600px;margin:40px auto;">Invalid booking ID.</div>';
    }
    break;
  case 'home':
    echo "<h2>Dashboard</h2>";
    echo "<p>Welcome to the Yaka Crew Admin Panel. Use the navigation to manage different sections of your website.</p>";
    break;

  case 'music':
    echo "<h2>Music Management</h2>";
    echo "<p>Add, edit, or delete audio tracks. Manage top song list and latest track list here.</p>";
    break;

  case 'video':
    echo "<h2>Video Management</h2>";
    echo "<p>Upload and organize music videos.</p>";
    break;

  case 'bookings':
    echo "<h2>Booking Management</h2>";
    if (isset($_GET['delete'])) {
      if ($_GET['delete'] === 'success') {
        echo '<div class="delete-success-message" style="background:#7a5a27;color:#fff;padding:15px 18px;border-radius:6px;margin-bottom:18px;text-align:left;font-weight:500;font-size:1.08em;border:1px solid #7a5a27;">Booking deleted successfully!</div>';
        echo '<script>document.addEventListener("DOMContentLoaded",function(){const msg=document.querySelector(".delete-success-message");if(msg){setTimeout(()=>{msg.style.display="none";},1200);}});</script>';
      } elseif ($_GET['delete'] === 'error') {
        echo '<div style="background:#c0392b;color:#fff;padding:12px 18px;border-radius:8px;margin-bottom:18px;text-align:center;font-weight:500;font-size:1.08em;border:2px solid #b08d4a;">Error deleting booking. Please try again.</div>';
      }
    }
    try {
      $stmt = $pdo->query("SELECT * FROM bookings ORDER BY booking_date DESC, booking_time DESC");
      $bookings = $stmt->fetchAll();
      if ($bookings) {
      echo '<div style="overflow-x:auto;max-width:100vw;">'
        .'<table style="min-width:900px;width:100%;border-collapse:collapse;background:#181818;color:#fff;font-size:1em;">';
      echo '<thead style="position:sticky;top:0;z-index:2;">'
        .'<tr style="background:#654922;color:#fff;">'
        .'<th style="padding:10px 8px;border:1px solid #654922;position:sticky;top:0;z-index:3;">First Name</th>'
        .'<th style="padding:10px 8px;border:1px solid #654922;position:sticky;top:0;z-index:3;">Last Name</th>'
        .'<th style="padding:10px 8px;border:1px solid #654922;position:sticky;top:0;z-index:3;">Email</th>'
        .'<th style="padding:10px 8px;border:1px solid #654922;position:sticky;top:0;z-index:3;">Mobile Number</th>'
        .'<th style="padding:10px 8px;border:1px solid #654922;position:sticky;top:0;z-index:3;">Booking Date</th>'
        .'<th style="padding:10px 8px;border:1px solid #654922;position:sticky;top:0;z-index:3;">Booking Time</th>'
        .'<th style="padding:10px 8px;border:1px solid #654922;position:sticky;top:0;z-index:3;">Other Info</th>'
        .'<th style="padding:10px 8px;border:1px solid #654922;position:sticky;top:0;z-index:3;min-width:120px;">Admin Note</th>'
        .'<th style="padding:10px 8px;border:1px solid #654922;position:sticky;top:0;z-index:3;min-width:120px;">Payment Status</th>'
        .'<th style="padding:10px 8px;border:1px solid #654922;position:sticky;top:0;z-index:3;min-width:120px;">Booking Status</th>'
           .'<th style="padding:10px 8px;border:1px solid #654922;position:sticky;top:0;z-index:3;width: 380px;min-width: 260px;">Actions</th>'
        .'</tr></thead>';
        foreach ($bookings as $row) {
          echo '<tr>'
            .'<td style="padding:8px;border:1px solid #654922;">'.htmlspecialchars($row['first_name']).'</td>'
            .'<td style="padding:8px;border:1px solid #654922;">'.htmlspecialchars($row['last_name']).'</td>'
            .'<td style="padding:8px;border:1px solid #654922;">'.htmlspecialchars($row['email']).'</td>'
            .'<td style="padding:8px;border:1px solid #654922;">'.htmlspecialchars($row['mobile_number']).'</td>'
            .'<td style="padding:8px;border:1px solid #654922;">'.htmlspecialchars($row['booking_date']).'</td>'
            .'<td style="padding:8px;border:1px solid #654922;">'.htmlspecialchars($row['booking_time']).'</td>'
            .'<td style="padding:8px;border:1px solid #654922;">'.htmlspecialchars($row['other_info']).'</td>'
            .'<td style="padding:8px;border:1px solid #654922;white-space:pre-line;">'.htmlspecialchars($row['admin_notes'] ?? '').'</td>'
            .'<td style="padding:8px;border:1px solid #654922;white-space:pre-line;">'.htmlspecialchars($row['payment_status'] ?? '').'</td>'
            .'<td style="padding:8px;border:1px solid #654922;white-space:pre-line;">'.htmlspecialchars($row['booking_status'] ?? '').'</td>'
            .'<td class="admin-action-cell">'
              .'<div class="admin-action-row">'
                .'<a href="YCBooking_edit.php?id='.urlencode($row['id']).'" class="admin-edit-btn">Edit</a>'
                .'<a href="?page=delete_booking&id='.urlencode($row['id']).'" class="admin-delete-btn" onclick="return confirm(\'Are you sure you want to delete this booking?\');">Delete</a>'
              .'</div>'
            .'</td>'
            .'</tr>';
        }
        echo '</table></div>';
      } else {
        echo '<div style="color:#fff;background:#222;padding:16px;border-radius:8px;">No bookings found.</div>';
      }
    } catch (PDOException $e) {
      echo '<div style="color:#ff5252;background:#222;padding:16px;border-radius:8px;">Database error: '.htmlspecialchars($e->getMessage()).'</div>';
    }
    break;

  case 'events':
    echo "<h2>Event Management</h2>";
    echo "<p>Create upcoming shows and manage ticketing options.</p>";
    break;

  case 'blogs':
    echo "<h2>Blog Management</h2>";
    echo "<p>Publish blog posts and manage content.</p>";
    break;

  case 'merchandise-store':
    echo "<h2>Merchandise Store Management</h2>";
    echo "<p>Manage items for sale and payment gateway orders (PayHere sandbox).</p>";
    break;

  default:
    echo "<h2>Dashboard</h2>";
    echo "<p>Welcome to the Yaka Crew Admin Panel. Use the navigation to manage different sections of your website.</p>";
}
?>
  </div>
</div>

<script>

function editMusicEvent(id) {
  window.location.href = 'YCGalleryedit_post.php?id=' + id;
}

function deletePost(id) {
  if (confirm('Are you sure you want to delete this music event? This action cannot be undone.')) {
  window.location.href = 'YCGallerydelete_post.php?id=' + id;
  }
}

function editSong(id) {
  window.location.href = 'YCGalleryedit_song.php?id=' + id;
}

function editVideo(id) {
  window.location.href = 'YCGalleryedit_media.php?id=' + id;
}

function deleteMedia(id) {
  if (confirm('Are you sure you want to delete this media item? This action cannot be undone.')) {
  window.location.href = 'YCGallerydelete_media.php?id=' + id;
  }
}

function deleteSong(id) {
  if (confirm('Are you sure you want to delete this audio item? This action cannot be undone.')) {
  window.location.href = 'YCGallerydelete_song.php?id=' + id;
  }
}
</script>

</body>
</html>
