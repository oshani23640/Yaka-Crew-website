<?php include 'YCdb_connection.php'; ?>
<?php
session_start();
require_once __DIR__ . '/YCdb_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['message'])) {
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL) && $message !== '') {
        $stmt = $pdo->prepare("INSERT INTO messages (email, message) VALUES (?, ?)");
        $stmt->execute([$email, $message]);
        $_SESSION['contact_success'] = 'Thanks! Your message was sent.';
    } else {
        $_SESSION['contact_error'] = 'Please enter a valid email and a message.';
    }

    // Stay on the same page (POST/Redirect/GET)
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Yaka Crew - Our History</title>
  <style>
 /* Global */
body {
  margin: 0;
  font-family: Arial, sans-serif;
  background-color: #000000;
  color: white;
  padding-top: 80px; /* Add space for fixed navbar */
}
/* Navbar */
.navbar {
  display: flex;
  align-items: center;
  background-color: #000 !important;
  background: #000 !important;
  padding: 10px 20px;
  box-shadow: none;
}

.logo img {
  width: 120px;
  height: auto;
}

.nav-links {
  list-style: none;
  display: flex;
  margin-left: 300px;
  gap: 40px;
  position: relative;
}

.nav-links > li {
  position: relative;
  cursor: pointer;
  padding: 5px 10px;
  border-bottom: 2px solid transparent;
  transition: border 0.3s;
  color: white;
  text-decoration: none;
}

.nav-links > li:hover {
  border-bottom: 2px solid white;
  color: white;
}

.nav-links > li.active {
  border-bottom: 2px solid white;
  color: white;
}

/* Remove default underline from top-level nav anchors (e.g., Events) */
.nav-links > li > a,
.nav-links > li > a:hover,
.nav-links > li > a:focus,
.nav-links > li > a:active {
  text-decoration: none !important;
  color: white;
}

/* Specifically target Blogs to ensure no yellow underline */
.nav-links > li:nth-child(3) {
  border-bottom: 2px solid transparent !important;
}

.nav-links > li:nth-child(3):hover {
  border-bottom: 2px solid white !important;
}

/* Remove persistent underline from Events (5th item) but keep hover underline */
.nav-links > li:nth-child(5) {
  border-bottom: 2px solid transparent !important; /* default state: no underline */
}

/* Keep Events non-active by default; hover will still apply from generic rule above */
.nav-links > li:nth-child(5).active {
  border-bottom: 2px solid transparent !important;
}

/* Ensure hover underline shows for Events even with the default !important above */
.nav-links > li:nth-child(5):hover {
  border-bottom: 2px solid white !important;
}

/* Override any potential yellow styling from other stylesheets */
.nav-links > li * {
  border-color: white !important;
  color: white !important;
}

.nav-links > li:hover * {
  border-color: white !important;
  color: white !important;
}

/* Dropdown */
.gallery-dropdown {
  position: relative;
}

.arrow {
  margin-left: 5px;
  cursor: pointer;
  transition: transform 0.3s ease;
}

.gallery-dropdown.active .arrow {
  transform: rotate(180deg);
}

.gallery-dropdown:hover .dropdown,
.gallery-dropdown.active .dropdown {
  display: block;
}

.dropdown {
  display: none;
  position: absolute;
  top: 35px;
  left: 0;
  background-color: #222;
  padding: 10px 0;
  border-radius: 5px;
  min-width: 120px;
  z-index: 1000;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
  list-style: none; /* Remove bullet points */
}

.dropdown li {
  padding: 8px 15px;
  white-space: nowrap;
  cursor: pointer;
  transition: background-color 0.3s ease;
  list-style: none; /* Remove bullet points from list items */
}

.dropdown li:hover {
  background-color: #333;
}

.dropdown li a {
  color: white;
  text-decoration: none;
  display: block;
  width: 100%;
  height: 100%;
}

.dropdown li a:hover {
  color: white;
}

/* Add this for social icons */
.social-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.social-links li {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.social-icon {
  width: 28px;
  height: 28px;
  margin-right: 10px;
  vertical-align: middle;
  display: inline-block;
}

/* Optional: style the links */
.social-links a {
  color: white;
  text-decoration: none;
  font-size: 18px;
}

.social-links a:hover {
  text-decoration: underline;
  color: #ffd700; /* gold/yellow on hover, or pick your color */
}
/* Footer */
footer {
    background-color: #0a0a0a;
    padding: 30px 40px;
    text-align:center;
    border-top: 2px solid #654922;
    margin-top: 60px;
    margin-bottom: 0;
    width: 100%;
    flex-shrink: 0;
}

footer h3 {
    color: #B68B4B;
    margin-bottom: 20px;
}

footer form {
    max-width: 500px;
    margin: 0 auto;
}

footer input,
footer textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #654922;
    border-radius: 4px;
    background-color: #000000;
    color: white;
}

footer button {
    background-color: #654922;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

footer button:hover {
    background-color: #956E2F;
}

/* Center the Contact Us heading in .footer-right */
.footer-right h2 {
  text-align: center;
  margin-left: 0;
}

/* Contact Us Section Gradient */
.footer-content {
    background: linear-gradient(135deg, #0a0a0a, #1a1a1a);
    border-radius: 0;
    padding: 40px 0 40px 0;
    width: 100%;
    margin: 0;
    box-shadow: none;
}

.footer-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: flex-start;
  gap: 40px;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}
.footer-left {
  flex: 1 1 250px;
  min-width: 220px;
  text-align: left;
}
.footer-right {
  flex: 1 1 350px;
  min-width: 300px;
  text-align: left;
  margin-left: auto;
}


</style>
    <link rel="stylesheet" href="css/YCBlogs-style.css">
  
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
      <li>Blogs</li>
  <li><a href="YCBooking-index.php">Bookings</a></li>
      <li><a href="YCEvents.php">Events</a></li>
       <li><a href="YCMerch-merch1.php">Merchandise Store</a></li>
    </ul>
  </div>


<section class="history-intro">
  <header>
  <h1>Yaka Crew - Our History</h1>
</header>
  <h2>Our Journey</h2>
  <div class="image-slider">
  <div class="image-slider" id="historySlider">
  <div class="slides">
    <div class="slide"><img src="assets/images/1.jpg" alt="Slide 1"></div>

  </div>
</div>


  <p style="font-size: 20px;">“We pander to not just teenagers, but also 
their parents.” 
— Chanuka Moragoda 

The tastes and preferences of the Sinhala youth are changing, and the young Sinhala artist is making those changes happen fast. Some of the most exciting changes to impact the arts have been due to the efforts of young adolescents working with feverish intensity. They start out relatively penniless, and although most of them hail from the suburban middle class they are, it must be noted, certainly not rich enough to indulge in the sort of luxuriant wastefulness that the English speaking classes indulge in. Plebeian and democratic in their outlook, they pander to the majority without insulting their intelligence, and they resort to what little they have at their disposal: YouTube, the patronage of friends, relatives, acquaintances, and the media. To a considerable extent, this explains the rise of Yaka Crew and the person behind it, Chanuka Moragoda. If the image of young, suburban, middle class Sinhala artists is real, Chanuka and his crew bear out much of the stereotypes associated with those artists.   


I haven’t been to many shows, but two Saturdays ago, I attended one where they performed: Adawwa. If it wasn’t quite the mega show that TV and radio outlets organise to draw massive crowds and boost their ratings, it was definitely the kind I was clamouring for. You know it’s a Yaka Crew event the moment you see the yak beraya onstage and it is used for every other item. The band didn’t actually pioneer the use of that beraya in their performances, but – and to me this is what distinguishes them – they were the first to incorporate it in their skits along with songs and dances. It brings out the traditional element of what they are doing, and more significantly the reverence of the past that has become, today, an integral part of their oeuvre. To understand why and how, it’s instructive to revisit Chanuka’s story. 

</p>
</section>

<section class="blog-section">
  <h2>Blog Posts</h2>

<div class="blog-container">

<?php
$query = "SELECT id, title, short_description FROM blogs ORDER BY created_at DESC";
$stmt = $pdo->query($query);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($rows) > 0):
    foreach ($rows as $row):
?>
  <div class="blog-card">
    <h3><?= htmlspecialchars($row['title']) ?></h3>
    <p><?= htmlspecialchars($row['short_description']) ?></p>
    <a href="YCBlogs-post.php?id=<?= $row['id'] ?>" class="read-more">Read More</a>
  </div>
<?php endforeach; ?>
</div>
<?php else: ?>
  <p>No blog posts available.</p>
<?php endif; ?>

   
</section>

<script>
//document.addEventListener("DOMContentLoaded", function () {
  //const slider = document.getElementById("historySlider");
//  const track  = slider.querySelector(".slides");
//  const slides = Array.from(track.children);

//  if (slides.length <= 1) return; // nothing to slide

//  let index = 0;
//  let timer;

  //function goTo(i) {
  //  index = (i + slides.length) % slides.length;
  //  track.style.transform = `translateX(-${index * 100}%)`;
//  }

//  function start() {
//    stop();
  //  timer = setInterval(() => goTo(index + 1), 5000); // every 5s
// }

  //function stop() {
   // if (timer) clearInterval(timer);
// }

  // start + pause on hover (optional, nice UX)
 // start();
//  slider.addEventListener("mouseenter", stop);
//  slider.addEventListener("mouseleave", start);
//});

document.addEventListener("DOMContentLoaded", function() {
  const msg = document.getElementById("successMessage") || document.getElementById("errorMessage");
  if (msg) {
    setTimeout(() => {
      msg.style.opacity = "0"; // fade out
      setTimeout(() => msg.remove(), 800); // remove after fade
    }, 2000); // wait 2 seconds
  }
});


//</script>



<footer>
  <div class="footer-container">
    
    <!-- Left side: Social Media -->
    <div class="footer-left">
      <h2>Visit Us</h2>
      <ul class="social-links">
        <li>
          <img src="assets/images/facebook.png" alt="Facebook" class="social-icon">
          <a href="https://www.facebook.com/yakacrewonline" target="_blank">Facebook</a>
        </li>
        <li>
          <img src="assets/images/instagram.png" alt="Instagram" class="social-icon">
          <a href="https://www.instagram.com/yakacrew/" target="_blank">Instagram</a>
        </li>
        <li>
          <img src="assets/images/youtube.png" alt="YouTube" class="social-icon">
          <a href="https://www.youtube.com/@YakaCrew_Official" target="_blank">YouTube</a>
        </li>
      </ul>
    </div>

    <!-- Right side: Contact Form -->
    <div class="footer-right">
      <h2>Contact Us</h2>
    <?php if (!empty($_SESSION['contact_success'])): ?>
  <div class="success-message" id="successMessage">
      <?php echo $_SESSION['contact_success']; ?>
  </div>
  <?php unset($_SESSION['contact_success']); ?>
<?php elseif (!empty($_SESSION['contact_error'])): ?>
  <div class="error-message" id="errorMessage">
      <?php echo $_SESSION['contact_error']; ?>
  </div>
  <?php unset($_SESSION['contact_error']); ?>
<?php endif; ?>

      <form action="" method="post" class="contact-form">
        <input type="email" name="email" placeholder="Your Email" required>
        <textarea name="message" rows="4" placeholder="Your Message" required></textarea>
        <button type="submit">Send</button>
      </form>
    </div>

  </div>
</footer>
</body>
</html>










