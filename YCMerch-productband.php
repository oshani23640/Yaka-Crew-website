<?php
// Connect to DB via PDO
require_once __DIR__ . '/YCdb_connection.php';

// Get wristband ID from URL
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch wristband details using prepared statement
$stmt = $pdo->prepare("SELECT * FROM wristband WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$row = $stmt->fetch();

if ($row) {
  $name = $row['name'];
  $price = $row['price'];
  $caption = $row['caption'] ?? '';
  $image_main = "uploads/YCMerch-uploads/" . ($row['image'] ?? '');
  $image_side = !empty($row['image_side']) ? "uploads/YCMerch-uploads/" . $row['image_side'] : $image_main;
  $image_back = !empty($row['image_back']) ? "uploads/YCMerch-uploads/" . $row['image_back'] : $image_main;
} else {
  die("Product not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($name) ?> - YAKA Crew</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet" />
  <style>
    .navbar {
      display: flex;
      align-items: center;
      background-color: #000000ff;
      padding: 10px 20px;
      position: fixed;
      top: 0; left: 0; right: 0;
      z-index: 1000;
    }
    .logo img { width: 120px; height: auto; }
    :root{
      --bg: #000000;
      --accent: #956E2F;
      --muted: rgba(255,255,255,0.07);
      --panel: #0e0e0e;
      --glass: rgba(149,110,47,0.06);
      --glass-strong: rgba(149,110,47,0.12);
    }
    body {
      margin: 0;
      padding-top: 80px;
      font-family: Arial, sans-serif;
      background: var(--bg);
      color: #fff;
      display:flex;
      flex-direction:column;
      min-height:100vh;
    }
    main.container { padding: 40px 24px; flex:1; }
    .product-shell {
      max-width:1100px;
      margin: 0 auto;
      display:grid;
      grid-template-columns: 1fr 420px;
      gap: 36px;
      align-items: center;
    }
    .visual-card {
      border-radius: 18px;
      overflow: hidden;
      padding: 28px;
      border: 1px solid var(--glass-strong);
      box-shadow: 0 8px 40px rgba(0,0,0,0.6);
      display:flex;
      align-items:center;
      justify-content:right 7px;
      min-height:420px;
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
    }
    .product-image {
      max-height:360px;
      width:auto;
      border-radius:12px;
      box-shadow: 0 16px 30px rgba(0,0,0,0.7);
      cursor: pointer;
      transition: transform 0.35s ease;
    }
    .product-image:hover { transform: translateY(-8px) scale(1.03); }
    .thumbs { display:flex; flex-direction:column; gap:12px; align-items:center; }
    .thumb {
      width:64px;
      height:64px;
      border-radius:10px;
      overflow:hidden;
      cursor:pointer;
      border: 2px solid transparent;
      transition: border-color 0.25s;
    }
    .thumb:hover, .thumb.active { border-color: var(--accent); }
    .thumb img { width:100%; height:100%; object-fit:cover; }
    .info-card {
      border-radius: 16px;
      padding: 28px;
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border: 1px solid rgba(149,110,47,0.08);
      min-height: 420px;
      display:flex;
      flex-direction:column;
      justify-content:space-between;
    }
    .product-title {
      font-family: 'Orbitron', sans-serif;
      font-size: 26px;
      color: var(--accent);
      margin: 0 0 6px 0;
    }
    .subhead { color: #bfb2a2; font-size:14px; margin-bottom:14px; }
    .price-tag {
      font-weight:800;
      font-size:22px;
      color:#fff;
      background: linear-gradient(90deg, rgba(149,110,47,0.12), rgba(149,110,47,0.06));
      padding:8px 12px;
      border-radius:12px;
      display:inline-block;
      margin-bottom: 20px;
    }
    .desc { color: #d3c9bd; font-size:14px; line-height:1.45; margin:14px 0 18px; }
    select, input[type=number] {
      width: 100%;
      padding: 6px 10px;
      border-radius: 8px;
      border: 1px solid var(--glass);
      background-color: var(--panel);
      color: #fff;
      font-size: 14px;
      margin-bottom: 20px;
      transition: border-color 0.3s;
    }
    select:focus, input[type=number]:focus { border-color: var(--accent); outline: none; }
    button.add-to-cart-btn {
      background-color: var(--accent);
      border: none;
      color: white;
      padding: 12px 25px;
      font-weight: 700;
      font-size: 16px;
      border-radius: 12px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      width: 100%;
    }
    button.add-to-cart-btn:hover { background-color: #7d5620; }


     /* Remove margin-top and match navbar style to YCMerch-merch1.php */
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
  padding-top: 120px; /* Further increased to ensure nav bar doesn't overlap */
      overflow-x: hidden;
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
  </style>
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

<main class="container">
  <section class="product-shell">
    <div class="visual-card">
      <div class="visual-stage" style="display: flex; flex-direction: row; align-items: center; justify-content: center; gap: 32px;">
        <div class="thumbs" style="flex-direction: column; align-items: flex-end; gap: 18px; min-width: 70px;">
         
          </div>
        </div>
        <div style="flex:1; display:flex; align-items:center; justify-content:center;">
          <img
            id="mainImage"
            src="<?= htmlspecialchars($image_main) ?>"
            alt="<?= htmlspecialchars($name) ?>"
            class="product-image"
            style="display:block; margin:0 auto; max-width:350px; max-height:360px;"
          />
        </div>
      </div>
    </div>

    <aside class="info-card">
      <div>
  <h1 class="product-title"><?= htmlspecialchars($name) ?></h1>
  <div class="subhead"><?= htmlspecialchars($caption) ?></div>
  <span class="price-tag">Rs. <?= number_format($price, 2) ?></span>

  <form method="POST" action="YCMerch-checkout.php" id="bandOrderForm">
    <input type="hidden" name="band_id" value="<?= $id ?>" />
    <input type="hidden" name="price" value="<?= htmlspecialchars($price) ?>" />

    <label for="quantity">Quantity:</label>
    <input type="number" id="quantity" name="quantity" value="1" min="1" max="99" required />

    <div style="display:flex; gap:16px; flex-wrap:wrap;">
      <button type="button" class="add-to-cart-btn" id="addToCartBtn" style="flex:1; min-width:120px;">Add to Cart</button>
    </div>
  </form>
      </div>
    </aside>
  </section>
</main>

<script>
  const thumbs = document.querySelectorAll(".thumb");
  const mainImage = document.getElementById("mainImage");

  thumbs.forEach((thumb) => {
    thumb.addEventListener("click", () => {
      mainImage.src = thumb.dataset.src;
      thumbs.forEach(t => t.classList.remove("active"));
      thumb.classList.add("active");
    });
  });

  // Add to Cart button logic for wristband
  document.getElementById('addToCartBtn').addEventListener('click', function() {
    const bandId = <?= json_encode($id) ?>;
    const price = <?= json_encode($price) ?>;
    const quantity = parseInt(document.getElementById('quantity').value) || 1;
    const total = price * quantity;
    // Navigate to cart summary with GET params
    const url = `YCMerch-cartproducts.php?band_id=${bandId}&quantity=${quantity}&total=${total}`;
    window.location.href = url;
  });
</script>


<?php include_once 'footer.php'; ?>
</body>
</html>
</html>
