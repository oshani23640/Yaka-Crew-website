<?php
// This page displays the products added to the cart, calculates total cost, and matches the style of other product pages.


session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_checkout'])) {
    // Clear cart products
    unset($_SESSION['cart_products']);
    // Set cart count in sessionStorage to 0 via JavaScript on redirect
    echo '<script>
        sessionStorage.setItem("merchCartCount", "0");
        window.location.href = "YCMerch-checkout.php";
    </script>';
    exit();
}

require_once __DIR__ . '/YCdb_connection.php';

// Clear cart if requested
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    // If you use session for cart:
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['cart'] = [];
    // Optionally, clear other cart-related session variables
    // Respond with JSON for AJAX
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}


// Add new item to session cart if present in GET
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

// Handle item deletion
if (isset($_GET['delete_type'], $_GET['delete_id'])) {
  $delete_type = $_GET['delete_type'];
  $delete_id = (int)$_GET['delete_id'];
  $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($delete_type, $delete_id) {
    return !($item['type'] === $delete_type && $item['id'] == $delete_id);
  });
  // Redirect to avoid resubmission
  header('Location: YCMerch-cartproducts.php');
  exit;
}

// Helper for adding/updating cart items
function add_to_cart($type, $id, $name, $image, $price, $quantity) {
  foreach ($_SESSION['cart'] as &$item) {
    if ($item['type'] === $type && $item['id'] == $id) {
      $item['quantity'] += $quantity;
      $item['total'] = $item['price'] * $item['quantity'];
      unset($item);
      return;
    }
  }
  unset($item);
  $_SESSION['cart'][] = [
    'type' => $type,
    'id' => $id,
    'name' => $name,
    'image' => $image,
    'price' => $price,
    'quantity' => $quantity,
    'total' => $price * $quantity
  ];
}


// Add to cart only once, then redirect to avoid duplicate on refresh
$added = false;
if (isset($_GET['poster_id'], $_GET['quantity'], $_GET['total'])) {
  $poster_id = (int)$_GET['poster_id'];
  $quantity = (int)$_GET['quantity'];
  $stmt = $pdo->prepare('SELECT name, image, price FROM posters WHERE id = ? LIMIT 1');
  $stmt->execute([$poster_id]);
  $poster = $stmt->fetch();
  if ($poster) {
    add_to_cart('poster', $poster_id, $poster['name'], 'uploads/YCMerch-uploads/' . $poster['image'], $poster['price'], $quantity);
    $added = true;
  }
}
if (isset($_GET['band_id'], $_GET['quantity'], $_GET['total'])) {
  $band_id = (int)$_GET['band_id'];
  $quantity = (int)$_GET['quantity'];
  $stmt = $pdo->prepare('SELECT name, image, price FROM wristband WHERE id = ? LIMIT 1');
  $stmt->execute([$band_id]);
  $band = $stmt->fetch();
  if ($band) {
    add_to_cart('band', $band_id, $band['name'], 'uploads/YCMerch-uploads/' . $band['image'], $band['price'], $quantity);
    $added = true;
  }
}
if (isset($_GET['product_id'], $_GET['quantity'], $_GET['totalcost'], $_GET['size'], $_GET['color'])) {
  $product_id = (int)$_GET['product_id'];
  $quantity = (int)$_GET['quantity'];
  $size = htmlspecialchars($_GET['size']);
  $color = htmlspecialchars($_GET['color']);
  $totalcost = (float)$_GET['totalcost'];
  $stmt = $pdo->prepare('SELECT name, image_black_front, image_white_front, price FROM tshirts WHERE id = ? LIMIT 1');
  $stmt->execute([$product_id]);
  $product = $stmt->fetch();
  if ($product) {
    $image = ($color === 'White') ? $product['image_white_front'] : $product['image_black_front'];
    $_SESSION['cart'][] = [
      'type' => 'tshirt',
      'id' => $product_id,
      'name' => $product['name'],
      'image' => 'uploads/YCMerch-uploads/' . $image,
      'price' => $product['price'],
      'quantity' => $quantity,
      'size' => $size,
      'color' => $color,
      'total' => $totalcost
    ];
    $added = true;
  }
}
if (isset($_GET['hoodie_id'], $_GET['quantity'], $_GET['totalcost'], $_GET['size'], $_GET['color'])) {
  $hoodie_id = (int)$_GET['hoodie_id'];
  $quantity = (int)$_GET['quantity'];
  $size = htmlspecialchars($_GET['size']);
  $color = htmlspecialchars($_GET['color']);
  $totalcost = (float)$_GET['totalcost'];
  $stmt = $pdo->prepare('SELECT name, image_black_front, image_white_front, price FROM hoodies WHERE id = ? LIMIT 1');
  $stmt->execute([$hoodie_id]);
  $hoodie = $stmt->fetch();
  if ($hoodie) {
    $image = ($color === 'White') ? $hoodie['image_white_front'] : $hoodie['image_black_front'];
    $_SESSION['cart'][] = [
      'type' => 'hoodie',
      'id' => $hoodie_id,
      'name' => $hoodie['name'],
      'image' => 'uploads/YCMerch-uploads/' . $image,
      'price' => $hoodie['price'],
      'quantity' => $quantity,
      'size' => $size,
      'color' => $color,
      'total' => $totalcost
    ];
    $added = true;
  }
}
// If any item was added, redirect to self without GET params
if ($added) {
  header('Location: YCMerch-cartproducts.php');
  exit;
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$total = 0;

// Clear cart products display if cart count is zero (after payment)
if (isset($_SESSION['cart_count']) && $_SESSION['cart_count'] === 0) {
    // Clear cart products display
    $cartProducts = [];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Shopping Cart | Yaka Crew</title>
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
    .cart-card {
      background: #181716;
      border-radius: 16px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.6);
      max-width: 900px;
      width: 100%;
      margin: 100px auto 40px auto;
      padding: 48px 40px 32px 40px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .cart-card h1 {
      font-family: 'Orbitron', Arial, sans-serif;
      font-size: 2.7rem;
      color: #fff;
      font-weight: 800;
      margin-bottom: 32px;
      text-align: center;
    }
    .cart-card .empty-message {
      color: #fff;
      font-size: 1.2rem;
      margin: 32px 0 48px 0;
      text-align: center;
    }
    .cart-card hr {
      border: none;
      border-top: 1px solid #2d2b29;
      width: 100%;
      margin: 32px 0 24px 0;
    }
    .cart-card .summary-row {
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 1.25rem;
      color: #fff;
      margin-bottom: 32px;
    }
    .cart-card .summary-row .label {
      font-weight: 600;
      color: #fff;
    }
    .cart-card .summary-row .value {
      font-weight: 700;
      color: #fff;
      font-size: 1.3rem;
    }
    .cart-card .actions {
      display: flex;
      gap: 18px;
      width: 100%;
      justify-content: flex-end;
    }
    .cart-card .actions button {
      font-family: 'Inter', Arial, sans-serif;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 8px;
      border: 2px solid #956E2F;
      padding: 10px 28px;
      background: transparent;
      color: #956E2F;
      cursor: pointer;
      transition: background 0.2s, color 0.2s;
    }
    .cart-card .actions button.primary {
      background: #956E2F;
      color: #fff;
      border: 2px solid #956E2F;
    }
    .cart-card .actions button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  </style>
</head>
<body>
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
  </div>
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


    
    <div class="cart-card">
      <h1>Your Shopping Cart</h1>
      <?php if (!empty($_SESSION['cart'])): ?>
        <?php $grandTotal = 0; ?>
        <?php foreach ($_SESSION['cart'] as $item): ?>
          <div style="width:100%; display:flex; align-items:center; gap:32px; margin-bottom:32px;">
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:90px; height:auto; border-radius:8px; background:#222;">
            <div style="flex:1;">
              <div style="font-size:1.2rem; font-weight:700; color:#fff; margin-bottom:6px;">
                <?php
                  $typeLabel = '';
                  if ($item['type'] === 'band') $typeLabel = 'Wristband';
                  elseif ($item['type'] === 'poster') $typeLabel = 'Poster';
                  elseif ($item['type'] === 'tshirt') $typeLabel = 'T-shirt';
                  elseif ($item['type'] === 'hoodie') $typeLabel = 'Hoodie';
                  else $typeLabel = ucfirst($item['type']);
                ?>
                <?= $typeLabel ?>: <?= htmlspecialchars($item['name']) ?>
              </div>
              <div style="color:#bfb2a2; font-size:1rem;">
                Quantity: <?= $item['quantity'] ?> &nbsp; | &nbsp; Price: LKR <?= number_format($item['price'],2) ?>
                <br>Size: <?= isset($item['size']) ? htmlspecialchars($item['size']) : 'N/A' ?> &nbsp; | &nbsp; Color: <?= isset($item['color']) ? htmlspecialchars($item['color']) : 'N/A' ?>
              </div>
            </div>
            <div style="font-size:1.1rem; color:#fff; font-weight:600; display:flex; align-items:center; gap:10px;">
              Total: LKR <?= number_format($item['total'],2) ?>
              <a href="?delete_type=<?= urlencode($item['type']) ?>&delete_id=<?= urlencode($item['id']) ?>" title="Remove from cart" style="color:#fff; text-decoration:none; margin-left:8px; font-size:1.4em; display:inline-flex; align-items:center; justify-content:center;">
                <i class="fas fa-trash"></i>
              </a>
            </div>
          </div>
          <?php $grandTotal += $item['total']; ?>
        <?php endforeach; ?>
        <hr />
        <div class="summary-row">
          <span class="label">Total:</span>
          <span class="value">LKR <?= number_format($grandTotal,2) ?></span>
        </div>
      <?php else: ?>
        <div class="empty-message">Your cart is empty. Add items!</div>
        <hr />
        <div class="summary-row">
          <span class="label">Total:</span>
          <span class="value">LKR 0.00</span>
        </div>
      <?php endif; ?>
      <div class="actions">
        <button onclick="window.location.href='YCMerch-merch1.php'">Back to Merch</button>
        <form id="checkoutForm" method="post" action="YCMerch-checkout.php" style="display:inline; margin:0; padding:0;">
          <?php if (!empty($_SESSION['cart'])): ?>
            <?php foreach ($_SESSION['cart'] as $idx => $item): ?>
              <input type="hidden" name="cart[<?= $idx ?>][type]" value="<?= htmlspecialchars($item['type']) ?>">
              <input type="hidden" name="cart[<?= $idx ?>][id]" value="<?= htmlspecialchars($item['id']) ?>">
              <input type="hidden" name="cart[<?= $idx ?>][name]" value="<?= htmlspecialchars($item['name']) ?>">
              <input type="hidden" name="cart[<?= $idx ?>][image]" value="<?= htmlspecialchars($item['image']) ?>">
              <input type="hidden" name="cart[<?= $idx ?>][price]" value="<?= htmlspecialchars($item['price']) ?>">
              <input type="hidden" name="cart[<?= $idx ?>][quantity]" value="<?= htmlspecialchars($item['quantity']) ?>">
              <?php if (isset($item['size'])): ?>
                <input type="hidden" name="cart[<?= $idx ?>][size]" value="<?= htmlspecialchars($item['size']) ?>">
              <?php endif; ?>
              <?php if (isset($item['color'])): ?>
                <input type="hidden" name="cart[<?= $idx ?>][color]" value="<?= htmlspecialchars($item['color']) ?>">
              <?php endif; ?>
              <input type="hidden" name="cart[<?= $idx ?>][total]" value="<?= htmlspecialchars($item['total']) ?>">
            <?php endforeach; ?>
          <?php endif; ?>
          <button type="submit" class="primary" style="margin-left:10px;">Proceed to Checkout</button>
        </form>
      </div>
    </div>

    
</body>
<script>
// Update cart count in sessionStorage and cart icon badge
function updateMerchCartCountFromSession() {
  // Count total items in cart (sum of quantities)
  let count = 0;
  try {
    <?php if (!empty($_SESSION['cart'])): ?>
      count = <?php echo array_sum(array_map(function($item){return $item['quantity'];}, $_SESSION['cart'])); ?>;
    <?php else: ?>
      count = 0;
    <?php endif; ?>
  } catch (e) { count = 0; }
  sessionStorage.setItem('merchCartCount', count);
  var badge = document.getElementById('merch-cart-count');
  if (badge) badge.textContent = count;
}
updateMerchCartCountFromSession();
</script>
<script>
// Update all cart icons to 0 if cart is cleared
if (window.location.search.includes('clear=1')) {
  document.querySelectorAll('.cart-count').forEach(function(el) {
    el.textContent = '0';
  });
  // Also clear sessionStorage for cart count
  sessionStorage.setItem('merchCartCount', '0');
}
</script>
</html>