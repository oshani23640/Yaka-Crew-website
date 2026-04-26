<?php
// Start session for admin access control
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../YClogin.php");
    exit();
}

// Include database connection
require_once __DIR__ . '/../YCdb_connection.php';

// Helper: format large hit counts as K/M (e.g. 250000 -> 250K, 1200000 -> 1.2M)
function formatHitsCount($hits) {
  $hits = (int)$hits;
  if ($hits >= 1000000) {
    return round($hits / 1000000, 1) . 'M';
  } elseif ($hits >= 1000) {
    return round($hits / 1000, 1) . 'K';
  }
  return (string)$hits;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Yaka Crew Admin Panel</title>
  <link rel="stylesheet" href="../css/YCGalleryadmin-style.css">
  
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
    <?php if ($page === 'music'): ?>
      <a href="../YCMusic-generate-pdf.php" target="_blank" class="pdf-btn-custom">Generate PDF Report</a>
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
    <?php elseif ($page === 'video'): ?>
      <a href="../YCVideo-generate-pdf.php" target="_blank" class="pdf-btn-custom">Generate PDF Report</a>
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
    <?php else: ?>
      <form method="post" action="admin_report.php" style="display:inline; margin-left:20px;">
        <input type="hidden" name="page" value="<?php echo htmlspecialchars($page); ?>">
        <button type="submit" class="pdf-btn-custom">Generate PDF Report</button>
      </form>
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
  case 'home':
  echo "<h2>Dashboard</h2>";
  echo "<p>Welcome to the Yaka Crew Admin Panel. Use the navigation to manage different sections of your website.</p>";
  echo '<form method="post" action="dashboard_report.php" style="margin-top:20px;">';
  echo '<button type="submit" style="padding:10px 18px; background:#654922; color:#fff; border:none; border-radius:4px; font-size:16px; cursor:pointer;">Generate PDF Report</button>';
  echo '</form>';
  break;

  case 'music':
    echo "<h2>Music Management</h2>";
    echo "<p>Add, edit, or delete audio tracks. Manage top song list and latest track list here.</p>";
    
    // Display success/error messages
    if (isset($_GET['upload'])) {
      if ($_GET['upload'] == 'success') {
        echo '<div style="background-color: #654922; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #654922;" id="success-message">Music event uploaded successfully!</div>';
                echo '<script>setTimeout(function(){ var msg = document.getElementById("success-message"); if(msg){ msg.style.display = "none"; } }, 1200);</script>';
      } elseif ($_GET['upload'] == 'error') {
        $message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Upload failed. Please try again.';
        echo '<div style="background-color: #654922; color: #FFFFFF; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #654922;">Error: ' . $message . '</div>';
      }
    }
    if (isset($_GET['delete'])) {
      if ($_GET['delete'] == 'success') {
        echo '<div style="background-color: #654922; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #654922;" id="delete-message">Music event deleted successfully!</div>';
  echo '<script>setTimeout(function(){ var msg = document.getElementById("delete-message"); if(msg){ msg.style.display = "none"; } }, 1200);</script>';
      } elseif ($_GET['delete'] == 'error') {
        $message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Delete failed. Please try again.';
        echo '<div style="background-color: #654922; color: #FFFFFF; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #654922;">Error: ' . $message . '</div>';
      }
    }
    ?>
    
    
    
    <!-- Music Upload Form -->
    <div class="upload-container">
      <h2>Add Music Event/Post (Direct Upload)</h2>
  <form action="YCGalleryupload_posts.php" method="POST" enctype="multipart/form-data">
        <label for="title">Event Title:</label>
        <input type="text" name="title" id="title" required>

        <label for="location">Location:</label>
        <input type="text" name="location" id="location">

        <label for="event_date">Event Date:</label>
        <input type="date" name="event_date" id="event_date">

        <label for="category">Category:</label>
        <select name="category" id="category" required>
          <option value="concert">Concert</option>
          <option value="album_release">Album Release</option>
          <option value="music_video">Music Video</option>
          <option value="event">General Event</option>
        </select>

        <label for="image_path">Event Image:</label>
        <input type="file" name="image_path" id="image_path" accept="image/*" required>

        <button type="submit">Add Music Event</button>
      </form>
    </div>
    

    <!-- Display Uploaded Music Events (Styled like Uploaded Video section) -->
    <div class="upload-container">
      <h2>Uploaded Music Events</h2>
      <?php
      try {
  $stmt = $pdo->prepare("SELECT * FROM posts ORDER BY created_at DESC");
        $stmt->execute();
        $posts = $stmt->fetchAll();
        if ($posts) {
          echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">';
          foreach ($posts as $post) {
            echo '<div style="border: 1px solid #654922; padding: 12px; border-radius: 8px; background: #000000; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
            echo '<h4 style="margin: 0 0 8px 0; color: white; font-size: 16px; line-height: 1.2;">' . htmlspecialchars($post['title']) . '</h4>';
            // Show like count if available
            if (isset($post["likes"])) {
              echo '<div style="color: white; font-size: 15px; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; justify-content: flex-start;">';
              echo '<i class="fa fa-heart" style="color:white;"></i> ';
              echo '<span style="font-weight:600; color:white;">' . (int)$post["likes"] . '</span> Likes';
              echo '</div>';
            }
            $cover = $post['image_path'] ?? '';
            if ($cover) {
              // Check for post image in source/Gallery/YCposts first, then uploads/Gallery/YCposts
              $image_exists = false;
              $final_image_path = '';
              $filename = basename($cover);
              $source_image = '../source/Gallery/YCposts/' . $filename;
              $uploads_image = '../uploads/Gallery/YCposts/' . $filename;
              
              if (file_exists($source_image)) {
                $final_image_path = '../source/Gallery/YCposts/' . $filename;
                $image_exists = true;
              } elseif (file_exists($uploads_image)) {
                $final_image_path = '../uploads/Gallery/YCposts/' . $filename;
                $image_exists = true;
              }
              
              if ($image_exists) {
                echo '<img src="' . htmlspecialchars($final_image_path) . '" alt="Event Image" style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px; margin-bottom: 8px;">';
              } else {
                echo '<div style="width: 100%; height: 100px; background: #333; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 8px; font-size: 11px; color: #999;">No Image Available<br>(' . htmlspecialchars($cover) . ')</div>';
              }
            } else {
              echo '<div style="width: 100%; height: 100px; background: #333; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 8px; font-size: 11px; color: #999;">No Image Available</div>';
            }
            echo '<div style="font-size: 13px; line-height: 1.3; color: white;">';
            echo '<p style="margin: 3px 0; color: white;"><strong>Category:</strong> ' . htmlspecialchars($post['category'] ?? '') . '</p>';
            echo '<p style="margin: 3px 0; color: white;"><strong>Location:</strong> ' . htmlspecialchars($post['location'] ?? '') . '</p>';
            echo '<p style="margin: 3px 0; color: white;"><strong>Date:</strong> ' . htmlspecialchars($post['event_date'] ?? '') . '</p>';
            if ($post['content']) {
              echo '<p style="margin: 3px 0; color: white;"><strong>Description:</strong> ' . htmlspecialchars(substr($post['content'], 0, 60)) . (strlen($post['content']) > 60 ? '...' : '') . '</p>';
            }
            echo '</div>';
            echo '<div style="margin-top: 10px; display: flex; gap: 8px; justify-content: center;">';
            echo '<button onclick="editMusicEvent(' . $post['id'] . ')" class="admin-edit-btn">Edit</button>';
            echo '<button onclick="deletePost(' . $post['id'] . ')" class="admin-delete-btn">Delete</button>';
            echo '</div>';
            echo '</div>';
          }
          echo '</div>';
        } else {
          echo '<p style="text-align: center; color: white; padding: 20px;">No music events uploaded yet.</p>';
        }
      } catch (PDOException $e) {
        echo '<p style="color: #FFFFFF;">Error loading music events: ' . htmlspecialchars($e->getMessage()) . '</p>';
      }
      ?>
    </div>
    
    <?php
    break;

  case 'video':
    echo "<h2>Video Management</h2>";
    echo "<p>Upload and organize music videos.</p>";
    
    // Display success/error messages
    if (isset($_GET['upload'])) {
      if ($_GET['upload'] == 'success') {
        echo '<div style="background-color: #654922; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #654922;" id="success-message">Media uploaded successfully!</div>';
                echo '<script>setTimeout(function(){ var msg = document.getElementById("success-message"); if(msg){ msg.style.display = "none"; } }, 1200);</script>';
      } elseif ($_GET['upload'] == 'error') {
        $message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Upload failed. Please try again.';
        echo '<div style="background-color: #654922; color: #FFFFFF; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #654922;">Error: ' . $message . '</div>';
      }
    }
    if (isset($_GET['delete'])) {
      if ($_GET['delete'] == 'success') {
        echo '<div style="background-color: #654922; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #654922;" id="delete-message">Media deleted successfully!</div>';
  echo '<script>setTimeout(function(){ var msg = document.getElementById("delete-message"); if(msg){ msg.style.display = "none"; } }, 1200);</script>';
      } elseif ($_GET['delete'] == 'error') {
    // Edit success message (if present)
    if (isset($_GET['edit']) && $_GET['edit'] == 'success') {
      echo '<div style="background-color: #654922; color: white; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #654922;" id="edit-message">Edit successful!</div>';
      echo '<script>setTimeout(function(){ var msg = document.getElementById("edit-message"); if(msg){ msg.style.display = "none"; } }, 1200);</script>';
    }
        $message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : 'Delete failed. Please try again.';
        echo '<div style="background-color: #654922; color: #FFFFFF; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #654922;">Error: ' . $message . '</div>';
      }
    }
    ?>
    
  
    
    <!-- Video/Audio Upload Form -->
    <div class="upload-container">
      <h2>Upload Audio/Video to Gallery</h2>
  <form action="YCGalleryupload_media.php" method="POST" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required>

        <label for="description">Description:</label>
        <textarea name="description" id="description"></textarea>

        <label for="artist_name">Artist Name:</label>
        <input type="text" name="artist_name" id="artist_name" required>


        <label for="file_type">Upload Type:</label>
        <select name="file_type" id="file_type" required>
          <option value="audio">Audio</option>
          <option value="video">Video</option>
        </select>

        <label for="cover_image">Cover Image:</label>
        <input type="file" name="cover_image" id="cover_image" accept="image/*" required>

        <!-- Media file (shown for both Audio and Video) -->
        <div id="media-file-field">
          <label for="media_file">Media File:</label>
          <input type="file" name="media_file" id="media_file" accept="audio/*,video/*">
          <small style="color:#888; display:block; margin-top:4px;">Upload audio or video file directly. YouTube URLs are not supported.</small>
        </div>

        <label for="category">Location:</label>
        <select name="category" id="category" required>
          <option value="latest">Latest Album</option>
          <option value="top">Top Album</option>
          <option value="video">Video</option>
        </select>

        <div id="hits-field" style="display: none;">
          <label for="hits">Hits Amount: <span id="hits-suffix" style="font-weight:600; color:#ccc;">K/M</span></label>
          <div style="display:flex; gap:8px; align-items:center;">
            <input type="number" name="hits" id="hits" min="1" max="9999" placeholder="e.g., 250 for 250K or 1400 for 1.4M">
            <div id="hits-preview" style="color:#ddd; font-size:13px;">—</div>
          </div>
          <small style="color: #666; display: block; margin-top: 5px;">Enter the number of hits in thousands (e.g., 250 for 250K, 1400 for 1.4M). Preview shows formatted value.</small>
        </div>

        <label for="music_category">Music Category:</label>
        <select name="music_category" id="music_category" required>
          <option value="hip_hop">Hip Hop</option>
          <option value="traditional">Traditional</option>
          <option value="pop">Pop</option>
          <option value="rock">Rock</option>
          <option value="jazz">Jazz</option>
          <option value="reggae">Reggae</option>
          <option value="classical">Classical</option>
          <option value="electronic">Electronic</option>
          <option value="folk">Folk</option>
          <option value="country">Country</option>
          <option value="blues">Blues</option>
          <option value="r&b">R&B</option>
        </select>

        <button type="submit">Upload to Gallery</button>
      </form>
    </div>
    
    <script>
      // Show/hide hits field based on category selection
      document.getElementById('category').addEventListener('change', function() {
        const hitsField = document.getElementById('hits-field');
        const hitsInput = document.getElementById('hits');
        
        if (this.value === 'top') {
          hitsField.style.display = 'block';
          hitsInput.required = true;
        } else {
          hitsField.style.display = 'none';
          hitsInput.required = false;
          hitsInput.value = '';
        }
      });

      // Toggle between Audio and Video file upload
      const fileTypeSel = document.getElementById('file_type');
      const mediaFileField = document.getElementById('media-file-field');
      const mediaFileInput = document.getElementById('media_file');

      function updateUploadInputs() {
        if (fileTypeSel.value === 'video') {
          mediaFileInput.accept = 'video/*';
          mediaFileInput.required = true;
        } else {
          mediaFileInput.accept = 'audio/*';
          mediaFileInput.required = true;
        }
      }
      // Format preview: input is thousands; convert to raw then show K/M
      function updateHitsPreview() {
        const input = document.getElementById('hits');
        const preview = document.getElementById('hits-preview');
        const suffix = document.getElementById('hits-suffix');
        if (!input || !preview) return;
        const val = parseInt(input.value, 10);
        if (!val || val <= 0) {
          preview.textContent = '—';
          return;
        }
        const raw = val * 1000;
        let display = '';
        if (raw >= 1000000) {
          display = (Math.round((raw / 1000000) * 10) / 10) + 'M';
        } else if (raw >= 1000) {
          display = (Math.round((raw / 1000) * 10) / 10) + 'K';
        } else {
          display = String(raw);
        }
        preview.textContent = display;
      }

      document.getElementById('hits').addEventListener('input', updateHitsPreview);
      // Ensure suffix/preview update when category toggles the field into view
      document.getElementById('category').addEventListener('change', function() {
        if (this.value === 'top') updateHitsPreview();
      });

      fileTypeSel.addEventListener('change', updateUploadInputs);
      // Initialize on load
      updateUploadInputs();
    </script>
    
    <!-- Display Uploaded Audio -->
    <div class="upload-container">
      <h2>Uploaded Audio</h2>
      <?php
      try {
  $stmt = $pdo->prepare("SELECT * FROM songs ORDER BY id DESC");
        $stmt->execute();
        $audio = $stmt->fetchAll();
        if ($audio) {
          echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">';
          foreach ($audio as $item) {
            echo '<div style="border: 1px solid #654922; padding: 12px; border-radius: 8px; background: #000000; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
            echo '<h4 style="margin: 0 0 8px 0; color: white; font-size: 16px; line-height: 1.2;">' . htmlspecialchars($item['title']) . '</h4>';
            // Try to get cover image from possible columns
            $cover_path = '';
            if (!empty($item['cover_image'])) {
                $cover_path = $item['cover_image'];
            } elseif (!empty($item['image_path'])) {
                $cover_path = $item['image_path'];
            } elseif (!empty($item['thumbnail_path'])) {
                $cover_path = $item['thumbnail_path'];
            }
            if ($cover_path) {
                // Check for audio cover in source/Gallery/images/audio first, then uploads/Gallery/covers/YCaudio
                $image_exists = false;
                $final_cover_path = '';
                $filename = basename($cover_path);
                $source_cover = '../source/Gallery/images/audio/' . $filename;
                $uploads_cover = '../uploads/Gallery/covers/YCaudio/' . $filename;
                
                if (file_exists($source_cover)) {
                  $final_cover_path = '../source/Gallery/images/audio/' . $filename;
                  $image_exists = true;
                } elseif (file_exists($uploads_cover)) {
                  $final_cover_path = '../uploads/Gallery/covers/YCaudio/' . $filename;
                  $image_exists = true;
                }
                
                if ($image_exists) {
                    echo '<img src="' . htmlspecialchars($final_cover_path) . '" alt="Cover Image" style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px; margin-bottom: 8px;">';
                } else {
                    echo '<div style="width: 100%; height: 100px; background: #333; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 8px; font-size: 11px; color: #999;">No Image Available<br>(' . htmlspecialchars($cover_path) . ')</div>';
                }
            } else {
                echo '<div style="width: 100%; height: 100px; background: #333; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 8px; font-size: 11px; color: #999;">No Image Path</div>';
            }
            echo '<div style="font-size: 13px; line-height: 1.3; color: white;">';
            // Show music category for audio
            $music_cat = '-';
            if (isset($item['music_category']) && $item['music_category']) {
                $music_cat = ucfirst(str_replace('_', ' ', htmlspecialchars($item['music_category'])));
            }
            echo '<p style="margin: 3px 0; color: white;"><strong>Music Category:</strong> ' . $music_cat . '</p>';
            if (isset($item['artist_name']) && $item['artist_name']) {
              echo '<p style="margin: 3px 0; color: white;"><strong>Artist:</strong> ' . htmlspecialchars($item['artist_name']) . '</p>';
            }
            if (isset($item['hits']) && isset($item['category']) && $item['category'] === 'top') {
              echo '<p style="margin: 3px 0; color: white;"><strong>Hits:</strong> ' . htmlspecialchars(formatHitsCount($item['hits'])) . '</p>';
            }
            if (isset($item['description']) && $item['description']) {
              echo '<p style="margin: 3px 0; color: white;"><strong>Description:</strong> ' . htmlspecialchars(substr($item['description'], 0, 60)) . (strlen($item['description']) > 60 ? '...' : '') . '</p>';
            }
            echo '</div>';
            echo '<div style="margin-top: 10px; display: flex; gap: 8px; justify-content: center;">';
            echo '<button onclick="editSong(' . $item['id'] . ')" class="admin-edit-btn">Edit</button>';
            echo '<button onclick="deleteSong(' . $item['id'] . ')" class="admin-delete-btn">Delete</button>';
            echo '</div>';
            echo '</div>';
          }
          echo '</div>';
        } else {
          echo '<p style="text-align: center; color: white; padding: 20px;">No audio uploaded yet.</p>';
        }
/* Add this near the top of the file or in your CSS file for grid layout and empty state */
/*
.admin-media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 15px;
}
.admin-media-empty {
  text-align: center;
  color: white;
  padding: 20px;
}
*/
      } catch (PDOException $e) {
        echo '<p style="color: #FFFFFF;">Error loading audio: ' . htmlspecialchars($e->getMessage()) . '</p>';
      }
      ?>
    </div>

    <!-- Display Uploaded Video -->
    <div class="upload-container">
      <h2>Uploaded Video</h2>
      <?php
      try {
        $stmt = $pdo->prepare("SELECT * FROM video ORDER BY id DESC LIMIT 10");
        $stmt->execute();
        $video = $stmt->fetchAll();
        if ($video) {
          echo '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">';
          foreach ($video as $item) {
            echo '<div style="border: 1px solid #654922; padding: 12px; border-radius: 8px; background: #000000; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
            echo '<h4 style="margin: 0 0 8px 0; color: white; font-size: 16px; line-height: 1.2;">' . htmlspecialchars($item['title']) . '</h4>';
            // Try all possible cover image columns for video
            $cover = '';
            if (isset($item['cover_image']) && $item['cover_image']) {
              $cover = $item['cover_image'];
            } elseif (isset($item['thumbnail_path']) && $item['thumbnail_path']) {
              $cover = $item['thumbnail_path'];
            } elseif (isset($item['image_path']) && $item['image_path']) {
              $cover = $item['image_path'];
            }
            if ($cover) {
              // Check for video cover in source/Gallery/images/video first, then uploads/Gallery/covers/YCvideos
              $image_exists = false;
              $final_cover_path = '';
              $filename = basename($cover);
              $source_cover = '../source/Gallery/images/video/' . $filename;
              $uploads_cover = '../uploads/Gallery/covers/YCvideos/' . $filename;
              if (file_exists($source_cover)) {
                $final_cover_path = '../source/Gallery/images/video/' . $filename;
                $image_exists = true;
              } elseif (file_exists($uploads_cover)) {
                $final_cover_path = '../uploads/Gallery/covers/YCvideos/' . $filename;
                $image_exists = true;
              }
              if ($image_exists) {
                echo '<img src="' . htmlspecialchars($final_cover_path) . '" alt="Gallery Image" style="width: 100%; height: 100px; object-fit: cover; border-radius: 4px; margin-bottom: 8px;">';
              } else {
                echo '<div style="width: 100%; height: 100px; background: #333; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 8px; font-size: 11px; color: #999;">No Image Available<br>(' . htmlspecialchars($cover) . ')</div>';
              }
            } else {
              echo '<div style="width: 100%; height: 100px; background: #333; display: flex; align-items: center; justify-content: center; border-radius: 4px; margin-bottom: 8px; font-size: 11px; color: #999;">No Image Available</div>';
            }
            // Do NOT display the video player here; only show the cover image above
            echo '<div style="font-size: 13px; line-height: 1.3; color: white;">';
            if (isset($item['music_category']) && $item['music_category']) {
              echo '<p style="margin: 3px 0; color: white;"><strong>Music Category:</strong> ' . ucfirst(str_replace('_', ' ', htmlspecialchars($item['music_category']))) . '</p>';
            }
            if (isset($item['artist_name']) && $item['artist_name']) {
              echo '<p style="margin: 3px 0; color: white;"><strong>Artist:</strong> ' . htmlspecialchars($item['artist_name']) . '</p>';
            }
            // Show hits as K/M if present and category is 'top'
            if (isset($item['hits']) && isset($item['category']) && $item['category'] === 'top') {
              echo '<p style="margin: 3px 0; color: white;"><strong>Hits:</strong> ' . htmlspecialchars(formatHitsCount($item['hits'])) . '</p>';
            }
            if (isset($item['description']) && $item['description']) {
              $short_desc = mb_substr($item['description'], 0, 60);
              echo '<p style="margin: 3px 0; color: white;"><strong>Description:</strong> ' . htmlspecialchars($short_desc) . (mb_strlen($item['description']) > 60 ? '...' : '') . '</p>';
            }
            echo '</div>';
            echo '<div style="margin-top: 10px; display: flex; gap: 8px; justify-content: center;">';
            echo '<button onclick="editVideo(' . $item['id'] . ')" class="admin-edit-btn">Edit</button>';
            echo '<button onclick="deleteMedia(' . $item['id'] . ')" class="admin-delete-btn">Delete</button>';
            echo '</div>';
            echo '</div>';
          }
          echo '</div>';
        } else {
          echo '<p style="text-align: center; color: white; padding: 20px;">No video uploaded yet.</p>';
        }
      } catch (PDOException $e) {
        echo '<p style="color: #721c24;">Error loading video: ' . htmlspecialchars($e->getMessage()) . '</p>';
      }
      ?>
    </div>
    
    <?php
    break;

  case 'bookings':
    echo "<h2>Booking Management</h2>";
    echo "<p>Handle requests for private gigs and studio appointments.</p>";
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


  case 'gallery':
    echo "<h2>Gallery Overview</h2>";
    echo "<p>Below is a summary of all uploaded music and videos in the gallery.</p>";

    echo '<div class="upload-container">';
    echo '<h3>Uploaded Audio</h3>';
    try {
  $stmt = $pdo->prepare("SELECT * FROM songs ORDER BY id DESC");
      $stmt->execute();
      $audio = $stmt->fetchAll();
      if ($audio) {
        echo '<div class="admin-media-grid">';
        foreach ($audio as $item) {
          echo '<div class="admin-audio-card">';
          echo '<h4 class="admin-audio-title">' . htmlspecialchars($item['title']) . '</h4>';
          if ($item['cover_image']) {
            $cover_path = $item['cover_image'];
            // Check for audio cover in source/Gallery/images/audio first, then uploads/Gallery/covers/YCaudio
            $image_exists = false;
            $final_cover_path = '';
            $filename = basename($cover_path);
            $source_cover = '../source/Gallery/images/audio/' . $filename;
            $uploads_cover = '../uploads/Gallery/covers/YCaudio/' . $filename;
            
            if (file_exists($source_cover)) {
              $final_cover_path = '../source/Gallery/images/audio/' . $filename;
              $image_exists = true;
            } elseif (file_exists($uploads_cover)) {
              $final_cover_path = '../uploads/Gallery/covers/YCaudio/' . $filename;
              $image_exists = true;
            }
            
            if ($image_exists) {
              echo '<img src="' . htmlspecialchars($final_cover_path) . '" alt="Cover Image" class="admin-audio-img">';
            } else {
              echo '<div class="admin-audio-img admin-img-placeholder">No Image<br>(' . htmlspecialchars($cover_path) . ')</div>';
            }
          } else {
            echo '<div class="admin-audio-img admin-img-placeholder">No Image Path</div>';
          }
          echo '<div class="admin-audio-meta">';
// Show Location from 'Location' or 'location' column if present, else fallback to category
if (!empty($item['Location'])) {
    echo '<p><strong>Location:</strong> ' . htmlspecialchars($item['Location']) . '</p>';
} elseif (!empty($item['location'])) {
    echo '<p><strong>Location:</strong> ' . htmlspecialchars($item['location']) . '</p>';
} else {
}
if (isset($item['music_category']) && $item['music_category']) {
    echo '<p><strong>Music Category:</strong> ' . ucfirst(str_replace('_', ' ', htmlspecialchars($item['music_category']))) . '</p>';
}
if (isset($item['artist_name']) && $item['artist_name']) {
    echo '<p><strong>Artist:</strong> ' . htmlspecialchars($item['artist_name']) . '</p>';
}
          if (isset($item['hits']) && isset($item['category']) && $item['category'] === 'top') {
            echo '<p><strong>Hits:</strong> ' . htmlspecialchars(formatHitsCount($item['hits'])) . '</p>';
          }
          if (isset($item['description']) && $item['description']) {
            echo '<p><strong>Description:</strong> ' . htmlspecialchars(substr($item['description'], 0, 60)) . (strlen($item['description']) > 60 ? '...' : '') . '</p>';
          }
          echo '</div>';
          echo '</div>';
        }
        echo '</div>';
      } else {
        echo '<p class="admin-media-empty">No audio uploaded yet.</p>';
      }
    } catch (PDOException $e) {
      echo '<p style="color: #FFFFFF;">Error loading audio: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    echo '</div>';

    echo '<div class="upload-container">';
    echo '<h3>Uploaded Video</h3>';
    try {
      $stmt = $pdo->prepare("SELECT * FROM video ORDER BY id DESC LIMIT 12");
      $stmt->execute();
      $video = $stmt->fetchAll();
      if ($video) {
        echo '<div class="admin-media-grid">';
        foreach ($video as $item) {
          echo '<div class="admin-audio-card">';
          echo '<h4 class="admin-audio-title">' . htmlspecialchars($item['title']) . '</h4>';
          // Try all possible cover image columns for video
          $cover = '';
          if (!empty($item['cover_image'])) {
              $cover = $item['cover_image'];
          } elseif (!empty($item['thumbnail_path'])) {
              $cover = $item['thumbnail_path'];
          } elseif (!empty($item['image_path'])) {
              $cover = $item['image_path'];
          }
          if ($cover) {
              // Check for video cover in source/Gallery/images/video first, then uploads/Gallery/covers/YCvideos
              $image_exists = false;
              $final_cover_path = '';
              $filename = basename($cover);
              $source_cover = '../source/Gallery/images/video/' . $filename;
              $uploads_cover = '../uploads/Gallery/covers/YCvideos/' . $filename;
              
              if (file_exists($source_cover)) {
                $final_cover_path = '../source/Gallery/images/video/' . $filename;
                $image_exists = true;
              } elseif (file_exists($uploads_cover)) {
                $final_cover_path = '../uploads/Gallery/covers/YCvideos/' . $filename;
                $image_exists = true;
              }
              
              if ($image_exists) {
                  echo '<img src="' . htmlspecialchars($final_cover_path) . '" alt="Gallery Image" class="admin-audio-img">';
              } else {
                  echo '<div class="admin-audio-img admin-img-placeholder">No Image<br>(' . htmlspecialchars($cover) . ')</div>';
              }
          } else {
              echo '<div class="admin-audio-img admin-img-placeholder">No Image</div>';
          }
          echo '<div class="admin-audio-meta">';
          // Show Music Category instead of Location
          $music_cat = '-';
          if (isset($item['music_category']) && $item['music_category']) {
            $music_cat = ucfirst(str_replace('_', ' ', htmlspecialchars($item['music_category'])));
          }
          echo '<p><strong>Music Category:</strong> ' . $music_cat . '</p>';
          if (isset($item['artist_name']) && $item['artist_name']) {
            echo '<p><strong>Artist:</strong> ' . htmlspecialchars($item['artist_name']) . '</p>';
          }
          // Show hits as K/M if present (show for all, not just top)
          if (isset($item['hits']) && is_numeric($item['hits']) && $item['hits'] > 0) {
            echo '<p><strong>Hits:</strong> ' . htmlspecialchars(formatHitsCount($item['hits'])) . '</p>';
          }
          if (isset($item['description']) && $item['description']) {
            $short_desc = mb_substr($item['description'], 0, 60);
            echo '<p><strong>Description:</strong> ' . htmlspecialchars($short_desc) . (mb_strlen($item['description']) > 60 ? '...' : '') . '</p>';
          }
          echo '</div>';
          echo '</div>';
        }
        echo '</div>';
      } else {
        echo '<p class="admin-media-empty">No video uploaded yet.</p>';
      }
    } catch (PDOException $e) {
      echo '<p style="color: #FFFFFF;">Error loading video: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    echo '</div>';
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
