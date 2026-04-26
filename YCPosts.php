<?php
require_once 'YCdb_connection.php';

// Handle delete post request
if (isset($_GET['delete_post_id'])) {
    $delete_id = intval($_GET['delete_post_id']);
    $stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');
    $stmt->execute([$delete_id]);
    // Redirect to avoid resubmission
    header('Location: YCPosts.php');
    exit();
}

// Get all posts from database
$stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll();

// Get filter categories
$category_stmt = $pdo->query("SELECT DISTINCT category FROM posts");
$categories = $category_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Yaka Crew - Posts & Events</title>
  <?php $cssv = @filemtime(__DIR__ . '/css/YCGalleryposts-style.css') ?: time(); ?>
  <link rel="stylesheet" href="css/YCGalleryposts-style.css?v=<?php echo $cssv; ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
  .like-btn { background: none; border: none; cursor: pointer; font-size: 1.3em; color: #fff; display: flex; align-items: center; gap: 4px; transition: color 0.2s; }
  .like-btn .fa-heart, .like-btn .fa-solid.fa-heart { color: #fff !important; transition: color 0.2s; font-weight: 900; }
  .like-btn.liked .fa-heart, .like-btn.liked .fa-solid.fa-heart { color: #ffffffff !important; }
  .like-btn:hover .fa-heart, .like-btn:hover .fa-solid.fa-heart { color: #f8f8f8ff !important; }
  .like-count { font-size: 0.95em; color: #fff; margin-left: 2px; }
  </style>
</head>
<body>
  <!-- Top Navigation Bar -->
  <nav class="navbar">
    <div class="logo">
      <img src="assets/images/Yaka Crew Logo.JPG" alt="Yaka Crew Logo">
    </div>
    <ul class="nav-links" id="navLinks">
      <li><a href="YCHome.php">Home</a></li>
      <li class="gallery-dropdown">
        Gallery <span class="arrow">&#9662;</span>
        <ul class="dropdown">
          <li><a href="YCPosts.php">Music</a></li>
          <li><a href="YCGallery.php">Video</a></li>
        </ul>
      </li>
      <li><a href="YCBlogs-index.php">Blogs</a></li>
      <li><a href="YCBooking-index.php">Bookings</a></li>
      <li><a href="YCEvents.php">Events</a></li>
      <li><a href="YCMerch-merch1.php">Merchandise Store</a></li>
    </ul>
    <button class="menu-btn" id="menuBtn" aria-label="Open navigation menu">
      <span class="menu-icon"></span>
    </button>
  </nav>

  <script>
    // Responsive menu toggle (safe: checks elements exist)
    document.addEventListener('DOMContentLoaded', function() {
      const menuBtn = document.getElementById('menuBtn');
      const navLinks = document.getElementById('navLinks');
      if (menuBtn && navLinks) {
        menuBtn.addEventListener('click', function() {
          navLinks.classList.toggle('nav-open');
          menuBtn.classList.toggle('open');
        });
      }
    });
  </script>

  <!-- Main Content -->
  <div class="main-content">
    <div class="posts-header">
      <h1>Band Events Gallery</h1>
    </div>

    <!-- All Events Gallery -->
    <div class="event-gallery-grid">
      <?php 
  if (!empty($posts) && is_array($posts) && count($posts) > 0) {
        $layouts = ['large-image-left', 'small-images-right', 'medium-image', 'small-images-left', 'wide-image'];
        $layout_index = 0;
        foreach ($posts as $post) {
          $layout_class = $layouts[$layout_index % count($layouts)];
          $layout_index++;
          $event_date = $post['event_date'] ? date('F j, Y', strtotime($post['event_date'])) : date('F j, Y', strtotime($post['created_at']));
          if ($layout_class == 'small-images-right') {
            echo '<div class="small-images-right">';
            echo '<div class="image-container">';
          } else {
            echo '<div class="' . $layout_class . '">';
          }
          echo '<div class="image-hover-overlay">';
          echo '<h3>' . htmlspecialchars($post['title']) . '</h3>';
          echo '<p>' . $event_date . '</p>';
          if (!empty($post['location'])) {
            echo '<p>' . htmlspecialchars($post['location']) . '</p>';
          }
          // Like button (DB-backed, Font Awesome white heart)
          $likeId = 'post-' . $post['id'];
          $likeCount = isset($post['likes']) ? (int)$post['likes'] : 0;
          echo '<button class="like-btn" data-like-id="' . $likeId . '" data-post-id="' . $post['id'] . '">
            <i class="fa-solid fa-heart"></i> <span class="like-count">' . $likeCount . '</span>
          </button>';
          echo '</div>';
          if (!empty($post['image_path'])) {
            // Check for post image in source/Gallery/YCposts first, then uploads/Gallery/YCposts
            $image_exists = false;
            $final_image_path = '';
            $filename = basename($post['image_path']);
            $source_image = 'source/Gallery/YCposts/' . $filename;
            $uploads_image = 'uploads/Gallery/YCposts/' . $filename;
            
            if (file_exists($source_image)) {
              $final_image_path = $source_image;
              $image_exists = true;
            } elseif (file_exists($uploads_image)) {
              $final_image_path = $uploads_image;
              $image_exists = true;
            }
            
            if ($image_exists) {
              echo '<img src="' . htmlspecialchars($final_image_path) . '" alt="' . htmlspecialchars($post['title']) . '">';
            } else {
              echo '<img src="assets/images/image3.jpeg" alt="' . htmlspecialchars($post['title']) . '">';
            }
          } else {
            // Fallback to default image if no image path
            echo '<img src="assets/images/image3.jpeg" alt="' . htmlspecialchars($post['title']) . '">';
          }
          if ($layout_class == 'small-images-right') {
            echo '</div></div>';
          } else {
            echo '</div>';
          }
        }
      } else {
        echo '<div class="no-events">No events available at the moment. Check back later.</div>';
      }
      // If there are no posts, a helpful message is shown above
      ?>
    </div>
  </div>

<script>
// Like button logic: DB-backed, only one like per post per browser
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.like-btn').forEach(function(btn) {
    const likeId = btn.getAttribute('data-like-id');
    const postId = btn.getAttribute('data-post-id');
    const countSpan = btn.querySelector('.like-count');
    let liked = localStorage.getItem(likeId + '-liked') === '1';
    if (liked) {
      btn.classList.add('liked');
      btn.disabled = true;
    }
    btn.addEventListener('click', function() {
      if (btn.disabled) return;
  // AJAX to YCPosts-like.php
  fetch('YCPosts-like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'post_id=' + encodeURIComponent(postId)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          countSpan.textContent = data.likes;
          localStorage.setItem(likeId + '-liked', '1');
          btn.classList.add('liked');
          btn.disabled = true;
        }
      });
    });
  });
});
</script>

<?php include_once 'footer.php'; ?>
</body>
</html>

