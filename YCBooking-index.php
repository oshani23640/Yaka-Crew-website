<?php
// YCBooking-index.php
require_once 'YCdb_connection.php';

$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $info = trim($_POST['info'] ?? '');
    $terms = isset($_POST['terms']);

    if ($firstName && $lastName && $email && $mobile && $date && $time && $terms) {
        try {
            $stmt = $pdo->prepare('INSERT INTO bookings (first_name, last_name, email, mobile_number, booking_date, booking_time, other_info) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$firstName, $lastName, $email, $mobile, $date, $time, $info]);
            $success = true;
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all required fields and agree to the terms.';
    }
}

include_once 'header.php'; // Optional: for shared header/navbar if you modularize later
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YCBooking - Complete Your Booking</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/YCBooking-index.css">
</head>
<body>
<?php // ...existing HTML code from body... ?>
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
      </li>
      <li><a href="YCBlogs-index.php">Blogs</a></li>
      <li><a href="YCBooking-index.php">Bookings</a></li>
      <li><a href="YCEvents.php">Events</a></li>
      <li><a href="YCMerch-merch1.php">Merchandise Store</a></li>
  </div>
  
    <div class="container">
        <div class="title">Complete Your Booking</div>
        <?php if ($success): ?>
            <div class="success-message" style="background: #654922; color: #fff; padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; text-align:left; font-weight: 500; font-size: 1.08em; border: 2px solid #654922; max-width: 340px; margin-left: 0;">Booking submitted successfully!</div>
        <?php elseif ($error): ?>
            <div style="color: #ff5252; background: #222; padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; text-align:left; max-width: 340px; margin-left: 0;">Error: <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <div class="booking-section">
            <form class="form-box" id="bookingForm" autocomplete="off" method="post" action="">
                <div style="display: flex; gap: 12px;">
                    <div style="flex:1;">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" required>
                    </div>
                    <div style="flex:1;">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" required>
                    </div>
                </div>
                <div style="display: flex; gap: 12px;">
                    <div style="flex:1;">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div style="flex:1;">
                        <label for="mobile">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" required>
                    </div>
                </div>
                <label for="date">Booking Date</label>
                <input type="date" id="date" name="date" required>
                <label for="time">Booking Time</label>
                <input type="time" id="time" name="time" required>
                <label for="info">Other Information</label>
                <input type="text" id="info" name="info" placeholder="Optional">
                <div style="display: flex; align-items: center; margin-top: 4px;">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms" style="font-size:0.95em; color:#fff; margin:0;">I agree to the terms and conditions</label>
                </div>
                <button type="submit">SUBMIT BOOKING</button>
            </form>
            <div class="summary-box">
                <div class="summary-title">Booking Summary</div>
                <div class="summary-list">
                    <div><label>Name:</label> <span id="summaryName">- -</span></div>
                    <div><label>Email:</label> <span id="summaryEmail">-</span></div>
                    <div><label>Mobile:</label> <span id="summaryMobile">-</span></div>
                    <div><label>Date:</label> <span id="summaryDate">-</span></div>
                    <div><label>Time:</label> <span id="summaryTime">-</span></div>
                    <div><label>Other Info:</label> <span id="summaryInfo">-</span></div>
                </div>
                <div class="summary-note">Please check your details before submitting.</div>
            </div>
        </div>
    </div>
    <script>
        // Live update summary
        const form = document.getElementById('bookingForm');
        const summary = {
            name: document.getElementById('summaryName'),
            email: document.getElementById('summaryEmail'),
            mobile: document.getElementById('summaryMobile'),
            date: document.getElementById('summaryDate'),
            time: document.getElementById('summaryTime'),
            info: document.getElementById('summaryInfo')
        };
        form.addEventListener('input', function() {
            summary.name.textContent = (form.firstName.value || '-') + ' ' + (form.lastName.value || '-');
            summary.email.textContent = form.email.value || '-';
            summary.mobile.textContent = form.mobile.value || '-';
            summary.date.textContent = form.date.value || '-';
            summary.time.textContent = form.time.value || '-';
            summary.info.textContent = form.info.value || '-';
        });
            // Hide success message after 1200ms
            document.addEventListener('DOMContentLoaded', function() {
                const msg = document.querySelector('.success-message');
                if (msg) {
                    setTimeout(() => {
                        msg.style.display = 'none';
                    }, 1200);
                }
            });
    </script>

<?php include_once 'footer.php'; ?>
</body>
</html>
<?php // include_once 'footer.php'; // Optional: for shared footer if you modularize later ?>
