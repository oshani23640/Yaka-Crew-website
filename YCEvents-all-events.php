<?php
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

// Get all events grouped by month
$events_by_month = [];
$all_events = $pdo->query("
    SELECT e.*, ei.image_path
    FROM events e
    LEFT JOIN event_images ei ON e.id = ei.event_id AND ei.is_primary = 1
    ORDER BY e.event_date DESC
")->fetchAll();

// Prepare events data for JavaScript
$events_for_js = [];
foreach ($all_events as $event) {
  $resolved = '';
  if (!empty($event['image_path'])) {
    $filename = basename($event['image_path']);
    // Prefer source/Events, then uploads/Events, then assets/images
    $tryPaths = [
      __DIR__ . '/source/Events/' . $filename => 'source/Events/' . $filename,
      __DIR__ . '/uploads/Events/' . $filename => 'uploads/Events/' . $filename,
      __DIR__ . '/assets/images/' . $filename => 'assets/images/' . $filename,
    ];
    foreach ($tryPaths as $disk => $web) {
      if (file_exists($disk)) { $resolved = $web; break; }
    }
  }
  if (empty($resolved)) {
    $resolved = 'assets/images/image6.JPG';
  }
  $events_for_js[] = [
        'id' => $event['id'],
        'title' => $event['title'],
        'date' => $event['event_date'],
        'time' => date('gA', strtotime($event['start_time'])) . ' - ' . date('gA', strtotime($event['end_time'])),
  'event_type' => $event['event_type'],
        'location' => $event['location'],
        'price' => $event['price'],
        'description' => $event['description'],
    'image' => $event['image_path'],
    'image_url' => $resolved
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>All Events | Yaka Crew</title>
  <meta name="description" content="View all upcoming and past events for Yaka Crew, Sri Lanka's premier musical band">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="css/YCEvents.css" />
  <link rel="stylesheet" href="css/YCEvents-all-events.css?v=<?php echo time(); ?>" />
</head>
<body>
  <!-- Top Navigation Bar (same as before) -->
  <div class="navbar">
    <div class="logo">
      <img src="assets/images/Yaka Crew Logo.JPG" alt="Yaka Crew Logo" style="width: 120px; height: auto;"> 
    </div>
    <ul class="nav-links">
      <li>Home</li>
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

  <div class="all-events-hero">
    <div class="all-events-hero-content">
      <h1>All Yaka Crew Events</h1>
      <p>Explore our complete schedule of upcoming shows and relive our past performances</p>
    </div>
  </div>

  <main class="events-container">
    <section class="events-filter">
      <h2>Filter Events</h2>
      <div class="filter-options">
        <div class="filter-group">
          <label for="event-type">Event Type</label>
          <select id="event-type">
            <option value="all">All Types</option>
            <option value="concert">Concerts</option>
            <option value="festival">Festivals</option>
            <option value="private">Private Events</option>
            <option value="charity">Charity Shows</option>
            <option value="workshop">Workshops</option>
          </select>
        </div>
        <div class="filter-group">
          <label for="location">Location</label>
          <select id="location">
            <option value="all">All Locations</option>
            <option value="colombo">Colombo</option>
            <option value="kandy">Kandy</option>
            <option value="galle">Galle</option>
            <option value="jaffna">Jaffna</option>
            <option value="negombo">Negombo</option>
            <option value="trincomalee">Trincomalee</option>
          </select>
        </div>
        <div class="filter-group">
          <label for="date-range">Date Range</label>
          <select id="date-range">
            <option value="upcoming">Upcoming Events</option>
           
            <option value="all">All Events</option>
            <option value="month">This Month</option>
            <option value="year">This Year</option>
          </select>
        </div>
      </div>
      <div class="filter-actions">
        <button class="btn btn-outline">Reset Filters</button>
        <button class="btn btn-primary">Apply Filters</button>
      </div>
    </section>

    <section class="events-timeline" id="events-timeline-section">
      <h2 class="section-title">Upcoming Events</h2>
      <div id="upcoming-events-container" class="timeline-events-wrapper">
        <!-- Upcoming events will be loaded here by JavaScript -->
      </div>
      
      
    </section>
  </main>


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
            <h4>Additional Information</h4>
            <p id="modalAdditionalInfo"></p>
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
  <script>
    // Pass PHP events data to JavaScript
    const allEvents = <?php echo json_encode($events_for_js); ?>;
    
    document.addEventListener('DOMContentLoaded', function() {
      // Parse 'YYYY-MM-DD' as a local date to avoid timezone shifts hiding events
      const parseLocalDate = (s) => {
        if (!s) return new Date(NaN);
        const parts = String(s).split('-').map(Number);
        if (parts.length < 3 || parts.some(isNaN)) return new Date(s);
        const [y, m, d] = parts;
        return new Date(y, (m || 1) - 1, d || 1);
      };
      const eventTypeSelect = document.getElementById('event-type');
      const locationSelect = document.getElementById('location');
      const dateRangeSelect = document.getElementById('date-range');
      const applyFiltersBtn = document.querySelector('.filter-actions .btn-primary');
      const resetFiltersBtn = document.querySelector('.filter-actions .btn-outline');
      const upcomingEventsContainer = document.getElementById('upcoming-events-container');
  const pastEventsContainer = document.getElementById('past-events-container');

    // Function to filter events based on current filters
      const filterEvents = () => {
        const eventType = eventTypeSelect.value;
        const location = locationSelect.value;
        const dateRange = dateRangeSelect.value;
        const now = new Date();
        
        return allEvents.filter(event => {
          // Filter by event type
          if (eventType !== 'all' && event.event_type !== eventType) {
            return false;
          }
          
          // Filter by location
          if (location !== 'all' && event.location.toLowerCase() !== location.toLowerCase()) {
            return false;
          }
          
          // Filter by date range
      const eventDate = parseLocalDate(event.date);
          switch(dateRange) {
            case 'upcoming':
              return eventDate >= now;
            case 'past':
              return eventDate < now;
            case 'month':
              const thisMonth = new Date(now.getFullYear(), now.getMonth(), 1);
              const nextMonth = new Date(now.getFullYear(), now.getMonth() + 1, 1);
              return eventDate >= thisMonth && eventDate < nextMonth;
            case 'year':
              const thisYear = new Date(now.getFullYear(), 0, 1);
              const nextYear = new Date(now.getFullYear() + 1, 0, 1);
              return eventDate >= thisYear && eventDate < nextYear;
            default:
              return true; // 'all' - no date filter
          }
        });
      };

      // Function to render events
      const renderEvents = (events) => {
  upcomingEventsContainer.innerHTML = '';
  if (pastEventsContainer) pastEventsContainer.innerHTML = '';

        let hasUpcoming = false;
        let hasPast = false;

        const upcomingMonths = {};
        const pastMonths = {};
        const now = new Date();

        events.forEach(event => {
          const eventDate = parseLocalDate(event.date);
          const isPast = eventDate < now;
          const monthYear = new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' }).format(eventDate);

          const rawUrl = event.image_url && String(event.image_url).length
            ? String(event.image_url)
            : 'assets/images/image6.JPG';
          const encoded = encodeURI(rawUrl);
          const isAbsolute = /^(?:[a-z]+:)?\/\//i.test(encoded) || encoded.startsWith('/');
          const bgUrl = isAbsolute ? encoded : `./${encoded}`;
          console.debug('All Events image URL:', bgUrl);

          const eventCardHtml = `
            <div class="event-card">
              <div class="event-date">${new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(eventDate)}</div>
              <div class="event-slider">
                <div class="event-slide active" style="background-image: url('${bgUrl}')"></div>
                <div class="event-slider-dots"></div>
              </div>
              <div class="event-details">
                <h3>${event.title}</h3>
                <p class="event-time-location">
                  <i class="far fa-clock"></i> ${event.time}<br>
                  <i class="fas fa-map-marker-alt"></i> ${event.location}
                </p>
                <div class="event-price">LKR ${parseFloat(event.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                <div class="event-actions">
                  <button class="btn btn-primary buy-ticket" 
                    data-event="${event.title}" 
                    data-date="${new Intl.DateTimeFormat('en-US', { month: 'long', day: 'numeric', year: 'numeric' }).format(eventDate)}" 
                    data-location="${event.location}" 
                    data-price="${event.price}"
                    data-id="${event.id}">Buy Ticket</button>
                </div>
              </div>
            </div>
          `;

          if (isPast) {
            hasPast = true;
            if (!pastMonths[monthYear]) {
              pastMonths[monthYear] = [];
            }
            pastMonths[monthYear].push(eventCardHtml);
          } else {
            hasUpcoming = true;
            if (!upcomingMonths[monthYear]) {
              upcomingMonths[monthYear] = [];
            }
            upcomingMonths[monthYear].push(eventCardHtml);
          }
        });

        // Render upcoming events
        if (hasUpcoming) {
          for (const monthYear in upcomingMonths) {
            upcomingEventsContainer.innerHTML += `
              <div class="timeline-month">
                <h2>${monthYear}</h2>
                <div class="timeline-events">
                  ${upcomingMonths[monthYear].join('')}
                </div>
              </div>
            `;
          }
        } else {
          upcomingEventsContainer.innerHTML = '<p class="no-events">No upcoming events found matching your criteria.</p>';
        }

        // Render past events
        if (pastEventsContainer) {
          if (hasPast) {
            for (const monthYear in pastMonths) {
              pastEventsContainer.innerHTML += `
                <div class="timeline-month past-events">
                  <h2>${monthYear}</h2>
                  <div class="timeline-events">
                    ${pastMonths[monthYear].join('')}
                  </div>
                </div>
              `;
            }
          } else {
            pastEventsContainer.innerHTML = '<p class="no-events">No past events found matching your criteria.</p>';
          }
        }

        // Initialize event sliders and modal
        if (typeof initEventSliders === 'function') initEventSliders();
        if (typeof initEventModal === 'function') initEventModal();
      };

      // Apply filters button
      applyFiltersBtn.addEventListener('click', function() {
        const filteredEvents = filterEvents();
        renderEvents(filteredEvents);
      });

      // Reset filters button
      resetFiltersBtn.addEventListener('click', function() {
        eventTypeSelect.value = 'all';
        locationSelect.value = 'all';
        dateRangeSelect.value = 'upcoming';
        renderEvents(filterEvents());
      });

      // Check for a date parameter in the URL
      const urlParams = new URLSearchParams(window.location.search);
      const specificDate = urlParams.get('date');

      if (specificDate) {
        // If a date is specified, filter by that date and render
        const filteredByDate = allEvents.filter(event => event.date && event.date.startsWith(specificDate));
        renderEvents(filteredByDate);
        
        // Disable other filter options as they are not applicable
        eventTypeSelect.disabled = true;
        locationSelect.disabled = true;
        dateRangeSelect.disabled = true;
        applyFiltersBtn.disabled = true;
        
        // Update the main title
        const timelineTitle = document.querySelector('.events-timeline .section-title');
        if(timelineTitle) {
            const dateObj = parseLocalDate(specificDate);
            const formattedDate = new Intl.DateTimeFormat('en-US', { month: 'long', day: 'numeric', year: 'numeric' }).format(dateObj);
            timelineTitle.textContent = `Events on ${formattedDate}`;
        }
      } else {
        // If no date is specified, run the default filter logic
        renderEvents(filterEvents());
      }
    });
  </script>

<?php include_once 'footer.php'; ?>
</body>
</html>