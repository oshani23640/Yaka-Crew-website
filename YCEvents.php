<?php
require_once __DIR__ . '/YCdb_connection.php';

// Local helper: resolve event image path (source -> uploads -> fallback)
function getEventImagePath($image_path) {
  if (empty($image_path)) return '';
  $filename = basename($image_path);
  if (file_exists(__DIR__ . '/source/Events/' . $filename)) return 'source/Events/' . $filename;
  if (file_exists(__DIR__ . '/uploads/Events/' . $filename)) return 'uploads/Events/' . $filename;
  if (file_exists(__DIR__ . '/YCEvents-images/' . $filename)) return 'YCEvents-images/' . $filename;
  return $image_path;
}

// Get upcoming events
$upcoming_events = $pdo->query("
    SELECT e.*, ei.image_path 
    FROM events e
    LEFT JOIN event_images ei ON e.id = ei.event_id AND ei.is_primary = 1
    WHERE e.is_past_event = FALSE
    ORDER BY e.event_date ASC
    LIMIT 10
")->fetchAll();

// Get slider images
$sliders = $pdo->query("SELECT * FROM slider_images WHERE is_active = TRUE ORDER BY RAND() LIMIT 3")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Events | Yaka Crew</title>
  <meta name="description" content="Book tickets for Yaka Crew's live performances and get information about upcoming shows in Sri Lanka">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="css/YCEvents.css" />
  
<?php
// Fetch all upcoming events for the calendar
$all_events_stmt = $pdo->query("
    SELECT 
        e.id,
        e.title,
        e.description,
        DATE(e.event_date) AS date,
        CONCAT(DATE_FORMAT(e.start_time, '%h:%i%p'), ' - ', DATE_FORMAT(e.end_time, '%h:%i%p')) AS time,
        e.location,
        e.price,
        ei.image_path AS image
    FROM events e
    LEFT JOIN event_images ei ON e.id = ei.event_id AND ei.is_primary = 1
    WHERE e.is_past_event = FALSE
    ORDER BY e.event_date ASC
");
$all_events = $all_events_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<script>
    const allEventsData = <?php echo json_encode($all_events, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES); ?>;
</script>
</head>
<body>
 <!-- Top Navigation Bar -->
  <div class="navbar">
    <div class="logo">
      <img src="assets/images/Yaka Crew Logo.JPG" alt="Yaka Crew Logo">
    </div>
    <ul class="nav-links">
  <li><a href="YCHome.php">Home</a></li>
    <li class="gallery-dropdown">
  Gallery <span class="arrow">&#9662;</span>
  <ul class="dropdown">
    <li><a href="YCPosts.php">Music</a></li>      <!-- ✅ Correct PHP file -->
    <li><a href="YCGallery.php">Video</a></li>     <!-- ✅ Correct PHP file -->
  </ul>
       <li><a href="YCBlogs-index.php">Blogs</a></li>
  <li><a href="YCBooking-index.php">Bookings</a></li>
      <li><a href="YCEvents.php">Events</a></li>
       <li><a href="YCMerch-merch1.php">Merchandise Store</a></li>
      <li>
  <a href="YCEvents-cart.php" class="cart-icon">
          <i class="fas fa-shopping-cart"></i>
          <span class="cart-count">0</span>
        </a>
      </li>
    </ul>
    <button class="mobile-menu-btn">
      <i class="fas fa-bars"></i>
    </button>
  </div>
  
  <div class="events-hero">
    <div class="hero-slider">
      <?php foreach ($sliders as $index => $slider): ?>
  <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo htmlspecialchars(getEventImagePath($slider['image_path'])); ?>')">
        <div class="slide-content">
          <h2><?php echo htmlspecialchars($slider['caption'] ?: 'Experience Yaka Crew Live'); ?></h2>
          <p>Book tickets for our upcoming events across Sri Lanka</p>
          <a href="YCEvents-all-events.php" class="btn btn-primary explore-btn">View All Events</a>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="slider-nav">
        <button class="slider-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
        <div class="slider-dots"></div>
        <button class="slider-btn next-btn"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
  </div>

  <main class="events-container">
    <section class="featured-events">
      <div class="section-header">
        <h2>Upcoming Events</h2>
        <div class="controls">
          <a href="YCEvents-all-events.php" class="view-all">View All</a>
          <div class="carousel-nav">
            <button class="nav-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
            <button class="nav-btn next-btn"><i class="fas fa-chevron-right"></i></button>
          </div>
        </div>
      </div>

      <div class="events-carousel">
        <div class="carousel-track">
          <?php foreach ($upcoming_events as $event): 
            // Get all images for this event
            $event_images = $pdo->prepare("SELECT image_path FROM event_images WHERE event_id = ?");
            $event_images->execute([$event['id']]);
            $images = $event_images->fetchAll();
          ?>
          <div class="event-card">
            <div class="event-date"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></div>
            <div class="event-slider">
              <?php foreach ($images as $img_index => $image): ?>
              <div class="event-slide <?php echo $img_index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo htmlspecialchars(getEventImagePath($image['image_path'])); ?>')"></div>
              <?php endforeach; ?>
              <?php if (count($images) > 1): ?>
              <div class="event-slider-dots"></div>
              <?php endif; ?>
            </div>
            <div class="event-details">
              <h3><?php echo htmlspecialchars($event['title']); ?></h3>
              <p class="event-time-location">
                <i class="far fa-clock"></i> <?php echo date('gA', strtotime($event['start_time'])); ?> - <?php echo date('gA', strtotime($event['end_time'])); ?><br>
                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
              </p>
              <div class="event-price">LKR <?php echo number_format($event['price'], 2); ?></div>
              <div class="event-actions">
                <!-- <button class="btn btn-outline more-info" data-id="<?php echo $event['id']; ?>">More Info</button> -->
                <button class="btn btn-primary buy-ticket" 
                  data-event="<?php echo htmlspecialchars($event['title']); ?>" 
                  data-date="<?php echo date('F d, Y', strtotime($event['event_date'])); ?>" 
                  data-location="<?php echo htmlspecialchars($event['location']); ?>" 
                  data-price="<?php echo $event['price']; ?>"
                  data-id="<?php echo $event['id']; ?>"
                  data-image="<?php echo htmlspecialchars($event['image_path']); ?>">Buy Ticket</button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="upcoming-events">
      <div class="section-header">
        <h2>Events</h2>
        <div class="view-options">
          <button class="view-option active" data-view="list">List View</button>
          <button class="view-option" data-view="calendar">Calendar View</button>
        </div>
      </div>

      <div class="events-list">
        <?php 
        // Get 2 upcoming events for list view
        $list_events = $pdo->query("
            SELECT e.*, ei.image_path 
            FROM events e
            LEFT JOIN event_images ei ON e.id = ei.event_id AND ei.is_primary = 1
            WHERE e.is_past_event = FALSE
            ORDER BY e.event_date ASC
            LIMIT 10 
        ")->fetchAll();
        
        foreach ($list_events as $event): 
            $event_images = $pdo->prepare("SELECT image_path FROM event_images WHERE event_id = ?");
            $event_images->execute([$event['id']]);
            $images = $event_images->fetchAll();
        ?>
        <div class="upcoming-event">
          <div class="event-slider">
            <?php foreach ($images as $img_index => $image): ?>
            <div class="event-slide <?php echo $img_index === 0 ? 'active' : ''; ?>" style="background-image: url('<?php echo htmlspecialchars(getEventImagePath($image['image_path'])); ?>')"></div>
            <?php endforeach; ?>
            <?php if (count($images) > 1): ?>
            <div class="event-slider-dots"></div>
            <?php endif; ?>
          </div>
          <div class="event-info">
            <div class="event-date"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></div>
            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
            <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
            <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
            <div class="event-meta">
              <span class="event-price">LKR <?php echo number_format($event['price'], 2); ?></span>
              <span class="event-time"><i class="far fa-clock"></i> <?php echo date('gA', strtotime($event['start_time'])); ?> - <?php echo date('gA', strtotime($event['end_time'])); ?></span>
            </div>
          </div>
          <div class="event-actions">

            <button class="btn btn-primary buy-ticket" 
              data-event="<?php echo htmlspecialchars($event['title']); ?>" 
              data-date="<?php echo date('F d, Y', strtotime($event['event_date'])); ?>" 
              data-location="<?php echo htmlspecialchars($event['location']); ?>" 
              data-price="<?php echo $event['price']; ?>"
              data-id="<?php echo $event['id']; ?>"
              data-image="<?php echo htmlspecialchars($event['image_path']); ?>">Buy Ticket</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="calendar-view" style="display: none;">
        <div class="calendar-header">
          <button class="calendar-nav prev-month"><i class="fas fa-chevron-left"></i></button>
          <h3 class="calendar-month"><?php echo date('F Y'); ?></h3>
          <button class="calendar-nav next-month"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="calendar-grid">
          <div class="calendar-weekdays">
            <div>Sun</div>
            <div>Mon</div>
            <div>Tue</div>
            <div>Wed</div>
            <div>Thu</div>
            <div>Fri</div>
            <div>Sat</div>
          </div>
          <div class="calendar-days">
            <!-- Calendar days will be populated by JavaScript -->
          </div>
        </div>
        <div class="calendar-events">
          <h4>Shows on <span class="selected-date"><?php echo date('F j, Y'); ?></span></h4>
          <div class="calendar-event-list">
            <!-- Events for selected date will be populated by JavaScript -->
          </div>
        </div>
      </div>
    </section>

  

  <!-- Event Modal -->
  <div class="modal" id="eventModal">
    <div class="modal-content">
      <span class="close-modal">&times;</span>
      <div class="modal-body">
        <div class="modal-slider">
          <div class="modal-slide active"></div>
          <div class="modal-slide"></div>
          <div class="modal-slide"></div>
          <div class="modal-slider-dots"></div>
        </div>
        <div class="modal-info">
          <h3 id="modalEventTitle">Show Title</h3>
          <div class="modal-meta">
            <p><i class="far fa-calendar-alt"></i> <span id="modalEventDate">Date</span></p>
            <p><i class="far fa-clock"></i> <span id="modalEventTime">Time</span></p>
            <p><i class="fas fa-map-marker-alt"></i> <span id="modalEventLocation">Location</span></p>
            <p><i class="fas fa-tag"></i> <span id="modalEventPrice">Price</span></p>
          </div>
          <div class="modal-description">
            <h4>About the Show</h4>
            <p id="modalEventDescription">Show description will be loaded here...</p>
            <h4>Set List</h4>
            <p>This performance will include fan favorites from our latest album plus special surprises!</p>
          </div>
          <div class="modal-actions">
            <button class="btn btn-outline" id="setReminderButton">Add to Calendar</button>
            <button class="btn btn-primary" id="modalBuyBtn">Buy Tickets</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Notification Toast -->
  <div class="toast" id="toast">
    <div class="toast-content">
      <i class="fas fa-check-circle toast-icon"></i>
      <div class="toast-message">
        <p class="toast-title">Success!</p>
        <p class="toast-text">Ticket added to your cart</p>
      </div>
    </div>
    <div class="toast-progress"></div>
  </div>

  
  <script src="js/YCEvents.js"></script>
  <!-- ... -->

<?php include_once 'footer.php'; ?>
</body>
</html>