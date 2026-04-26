<?php
require_once __DIR__ . '/YCdb_connection.php';

$stmt = $pdo->query("SELECT * FROM wristband");
$bands = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }
    body {
      background-color: #000;
      color: white;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      padding-top: 80px; /* Ensure nav bar doesn't overlap */
      overflow-x: hidden;
    }
    /* Ensure the footer is at the bottom */
    html, body {
      height: 100%;
    }
    .main, main, .container, .content, .merch-container, .blog-container, .gallery-container, .grid {
      flex: 1 0 auto;
    }
    footer {
      flex-shrink: 0;
      width: 100%;
      margin-top: auto;
    }
    /* Remove .grid min-height to avoid stretching cards */
    .grid {
      min-height: unset;
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      justify-content: center;
      align-items: flex-start;
    }
    .card {
      height: 370px;
      max-height: 370px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      box-sizing: border-box;
    }
    .thumb {
      height: 180px;
      max-height: 180px;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .thumb img {
      max-height: 100%;
      width: auto;
      object-fit: contain;
    }
     /* Navbar */
.navbar {
  display: flex;
  align-items: center;
  background-color: black;
  padding: 10px 20px;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
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
}

.nav-links > li:hover {
  border-bottom: 2px solid white;
}

.nav-links > li.active {
  border-bottom: 2px solid white;
}

/* Single underline fix: keep underline on <li>, never on <a> (prevents double lines) */
.nav-links > li > a,
.nav-links > li > a:link,
.nav-links > li > a:visited,
.nav-links > li > a:hover,
.nav-links > li > a:focus,
.nav-links > li > a.active {
  color: #fff !important;
  text-decoration: none !important;
  border-bottom: none !important;
}

/* Keep underline only from these rules */
.nav-links > li:hover,
.nav-links > li.active {
  border-bottom: 2px solid #fff;
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
    .section-title {
      margin-top: 70px;
    }
  </style>
  <meta charset="UTF-8" />
  <title>Band Wrist-bands | YAKA Crew</title>
  <link rel="stylesheet" href="css/YCMerch-tshirts.css" />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
</head>
<body>

<header>
    <!-- Top Navigation Bar -->
  <div class="navbar">
    <div class="logo">
<a href="YCMerch-merch1.php">
      <img src="assets/images/Yaka Crew Logo.JPG" alt="Yaka Crew Logo">
      </a>    </div>
    <ul class="nav-links">
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
      <li>
        <a href="YCMerch-cartproducts.php" class="cart-icon" style="position:relative; font-size:1.2rem;">
          <i class="fas fa-shopping-cart"></i>
          <span class="cart-count" id="merch-cart-count" style="position:absolute; top:-8px; right:-8px; background-color:#956E2F; color:white; border-radius:50%; width:18px; height:18px; display:flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:bold;">0</span>
        </a>
      </li>
    </ul>
</style>
<script>
// Update cart count from sessionStorage or fallback to PHP session if needed
function updateMerchCartCount() {
  let count = 0;
  if (sessionStorage.getItem('merchCartCount')) {
    count = parseInt(sessionStorage.getItem('merchCartCount'));
  }
  document.getElementById('merch-cart-count').textContent = count;
}
document.addEventListener('DOMContentLoaded', updateMerchCartCount);
</script>
    </div>

    </header>

    <h1 class="section-title">BAND WRIST-BANDS</h1>

    <section class="grid">
  <?php foreach ($bands as $row): ?>
        <article class="card">
          <div class="thumb">
            <?php if (!empty($row['image']) && file_exists("uploads/YCMerch-uploads/" . $row['image'])): ?>
              <img src="uploads/YCMerch-uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>" />
            <?php else: ?>
              <div style="width:100%;height:180px;background:#222;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:14px;">No Image</div>
            <?php endif; ?>
          </div>
          <h2 class="tshirt-name"><?= htmlspecialchars($row['name']) ?></h2>
          <span class="caption"><?= htmlspecialchars($row['caption']) ?></span>
          <span class="price">Price: Rs.<?= number_format($row['price'], 2) ?></span>
          <a href="YCMerch-productband.php?id=<?= $row['id'] ?>">
            <button class="details-btn">SEE DETAILS</button>
          </a>
        </article>
  <?php endforeach; ?>
    </section>
  </div>

<?php include_once 'footer.php'; ?>
</body>
</html>
