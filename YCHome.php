<?php
require_once __DIR__ . '/YCdb_connection.php';

// Gracefully fetch data; don't error out if tables missing
$bandMembers = [];
$whatsNew = null;
try {
  $stmt = $pdo->query("SELECT * FROM band_members ORDER BY id");
  $bandMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { /* ignore, show empty state */ }

try {
  $stmt = $pdo->query("SELECT * FROM whats_new WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1");
  $whatsNew = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) { /* ignore, show empty state */ }

// Resolve background video path safely (try assets and source/Home with common extensions)
$bgVideo = '';
$candidates = [
  'assets/videos/YCHome_Background.mp4',
  'assets/videos/YCHome_Background.MP4',
  'source/Home/YCHome-videos/YCHome_Background.mp4',
  'source/Home/YCHome-videos/YCHome_Background.MP4',
];
foreach ($candidates as $rel) {
  if (file_exists(__DIR__ . '/' . $rel)) { $bgVideo = $rel; break; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Yaka Crew - Home</title>
  <?php $homeCssV = @filemtime(__DIR__ . '/css/YCHome-styles.css') ?: time(); ?>
  <link rel="stylesheet" href="css/YCHome-styles.css?v=<?php echo $homeCssV; ?>" />
  <link rel="stylesheet" href="css/YCHome-events.css?v=<?php echo time(); ?>" />
</head>
<body>

  <!-- Responsive Navigation Bar -->
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

  <div class="hero-section">
    <div class="hero-left">
      <br/><br/><br/><br/>
      <h1>Yaka Crew</h1>
    </div>
    <div class="hero-right">
      <?php if ($bgVideo): ?>
        <video src="<?php echo htmlspecialchars($bgVideo); ?>" autoplay muted loop></video>
      <?php endif; ?>
    </div>
  </div>

  <main>
    <section class="band-members">
      <h2>Band Members</h2>
      <p>Short description about the band members</p>
      <a href="YCHome-generate-pdf.php" target="_blank" style="display:inline-block;margin:10px 0;padding:8px 18px;background:#007bff;color:#fff;border:none;border-radius:4px;text-decoration:none;font-weight:bold;">Generate PDF</a>
      <div class="member-carousel">
        <button class="carousel-arrow carousel-arrow-left" id="prevBtn">&#8249;</button>


        <?php
          // Find leader (if any) and separate them from the list
          $leader = null;
          $otherMembers = [];
          foreach ($bandMembers as $m) {
            if (!empty($m['is_leader'])) { $leader = $m; } else { $otherMembers[] = $m; }
          }
        ?>

        <div class="member-row" id="memberGrid">
          <?php if ($leader): ?>
            <div class="member-card leader-center leader-fixed">
              <img src="<?php echo htmlspecialchars($leader['image_path']); ?>" alt="<?php echo htmlspecialchars($leader['name']); ?>">
              <span><?php echo htmlspecialchars($leader['name']); ?></span>
              <?php if (!empty($leader['role'])): ?>
                <div class="member-role"><?php echo htmlspecialchars($leader['role']); ?></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
          <?php foreach($otherMembers as $member): ?>
            <div class="member-card">
              <img src="<?php echo htmlspecialchars($member['image_path']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>">
              <span><?php echo htmlspecialchars($member['name']); ?></span>
              <?php if (!empty($member['role'])): ?>
                <div class="member-role"><?php echo htmlspecialchars($member['role']); ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>

        <button class="carousel-arrow carousel-arrow-right" id="nextBtn">&#8250;</button>
      </div>
    </section>

    <section class="whats-new">
      <h2>What's new?</h2>
      <?php if($whatsNew): ?>
      <div class="new-member">
        <div class="new-member-image">
          <img src="<?php echo htmlspecialchars($whatsNew['image_path']); ?>" alt="<?php echo htmlspecialchars($whatsNew['title']); ?>">
        </div>
        <div class="new-member-info">
          <h3><?php echo htmlspecialchars($whatsNew['title']); ?></h3>
          <p><?php echo htmlspecialchars($whatsNew['description']); ?></p>
        </div>
      </div>
      <?php else: ?>
      <p></p>
      <?php endif; ?>
    </section>  

    <!-- Upcoming Events Section -->
    <section class="upcoming-events">
      <h2></h2>
      <div class="events-grid">
        <?php
        // Fetch upcoming events
  require_once __DIR__ . '/YCdb_connection.php';
    // Local helper for event images
    function getEventImagePath($image_path) {
      if (empty($image_path)) return '';
      $filename = basename($image_path);
      if (file_exists(__DIR__ . '/source/Events/' . $filename)) return 'source/Events/' . $filename;
      if (file_exists(__DIR__ . '/uploads/Events/' . $filename)) return 'uploads/Events/' . $filename;
      if (file_exists(__DIR__ . '/YCEvents-images/' . $filename)) return 'YCEvents-images/' . $filename;
      return $image_path;
    }
        $events = $pdo->query("
            SELECT e.title, e.location, e.price, e.event_date, e.start_time, e.end_time, ei.image_path
            FROM events e
            LEFT JOIN event_images ei ON e.id = ei.event_id AND ei.is_primary = 1
            WHERE e.is_whats_new = 1
            ORDER BY e.event_date ASC
            LIMIT 4
        ")->fetchAll();

        if ($events):
          foreach ($events as $event):
        ?>
            <div class="event-card" style="cursor: pointer;" onclick="window.location.href='YCEvents.php'">
              <div class="event-date"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></div>
              <div class="event-slider">
                <div class="event-slide active" style="background-image: url('<?php echo htmlspecialchars(getEventImagePath($event['image_path'])); ?>')"></div>
              </div>
              <div class="event-details">
                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                <p class="event-time-location">
                  <i class="far fa-clock"></i> <?php echo date('gA', strtotime($event['start_time'])); ?> - <?php echo date('gA', strtotime($event['end_time'])); ?><br>
                  <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                </p>
                <div class="event-price">LKR <?php echo number_format($event['price'], 2); ?></div>
              </div>
            </div>
        <?php
          endforeach;
        else:
        ?>
          <p>No upcoming events at the moment.</p>
        <?php endif; ?>
      </div>
    </section>
    <!-- End of Upcoming Events Section -->

    <!-- Image Modal -->
    <div id="imageModal" class="modal">
      <span class="close-modal">&times;</span>
      <img class="modal-content" id="modalImage">
    </div>

  </main>

  <script src="js/YCHome-script.js"></script>

<?php include_once 'footer.php'; ?>
</body>
</html>