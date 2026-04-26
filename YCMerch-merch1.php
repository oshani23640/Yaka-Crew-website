<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="css/YCEvents.css" />
  <meta charset="UTF-8">
  <title>Merchandise Store</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">

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

    /* Welcome Section */
    .welcome-message {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 40px 20px;
      background: url('https://images.unsplash.com/photo-1535083786484-d03d7b8f71e7?auto=format&fit=crop&w=1400&q=80') no-repeat center center/cover;
      background-attachment: fixed;
      position: relative;
    }

    .welcome-message::after {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      z-index: 1;
    }

    .welcome-message h1,
    .welcome-message p {
      z-index: 2;
      position: relative;
    }

    .welcome-message h1 {
      font-size: 48px;
      color: #956E2F;
      font-family: Arial, sans-serif;
      text-shadow: 2px 2px 10px rgba(149, 110, 47, 0.7);
      margin-bottom: 20px;
      animation: fadeInDown 1.5s ease-out;
    }

    .welcome-message p {
      font-size: 20px;
      color: #ccc;
      animation: fadeIn 2.5s ease-out;
    }

    /* Main Store Section */
    main {
      flex: 1;
      padding: 60px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .section-title {
      font-size: 32px;
      color: #956E2F;
      margin-bottom: 40px;
      font-family: Arial, sans-serif;
      text-shadow: 1px 1px #222;
      animation: fadeInDown 1s ease;
    }

    .products-wrapper {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 30px;
      max-width: 1200px;
      width: 100%;
      animation: fadeInUp 1s ease-in-out;
      justify-items: center;
      margin: 48px auto 0 auto;
    }


    .product {
      background-color: #111;
      padding: 25px;
      border-radius: 12px;
      text-align: center;
      transition: transform 0.3s ease, box-shadow 0.4s ease;
      border: 1px solid #333;
    }

      .product {
        background: #000000ff;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 24px 18px 18px 18px;
        margin: 18px;
        text-align: center;
        transition: box-shadow 0.2s;
        position: relative;
        min-width: 260px;
        max-width: 300px;
        flex: 1 1 260px;
      }
    .product:hover {
      transform: translateY(-8px) scale(1.03);
      box-shadow: 0 0 15px rgba(149, 110, 47, 0.6);
    }

    .photo {
      background: #111;
      border-radius: 16px;
      border: 3px solid #222;
      padding: 18px;
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 220px;
      min-height: 220px;
      max-width: 260px;
      max-height: 260px;
      box-sizing: border-box;
    }
    .photo img {
      max-width: 100%;
      max-height: 200px;
      border-radius: 12px;
      background: #fff;
      display: block;
      margin: 0 auto;
      box-shadow: 0 0 0 4px #111;
    }

    .product p {
      margin-bottom: 18px;
      font-size: 16px;
      color: #fff;
    }

    .product button {
      background: linear-gradient(145deg, #956E2F, #b1833a);
      color: #202221;
      border: none;
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .product button:hover {
      transform: scale(1.05);
      background: linear-gradient(145deg, #b1833a, #956E2F);
    }

 

    /* Animations */
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @media (max-width: 768px) {
      .welcome-message h1 {
        font-size: 32px;
      }
      .section-title {
        font-size: 24px;
      }
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
</style>
  
<script>
// Update cart count from sessionStorage or fallback to PHP session if needed
function updateMerchCartCount() {
  let count = 0;
  // Try to get from sessionStorage (if you use it in your cart logic)
  if (sessionStorage.getItem('merchCartCount')) {
    count = parseInt(sessionStorage.getItem('merchCartCount'));
  } else {
    // Fallback: try to get from PHP session via AJAX (optional, not implemented here)
    // Or leave as 0 if not available
  }
  document.getElementById('merch-cart-count').textContent = count;
}
document.addEventListener('DOMContentLoaded', updateMerchCartCount);
</script>
      </li>
      </ul>
    </div>
  </header>

  <!-- Main Product Grid at the top, welcome banner removed -->
  <main>
    <div style="text-align:center; margin-bottom:36px;">
  <h2 style="color:#956E2F; font-family:'Orbitron',Arial,sans-serif; font-size:1.3rem; letter-spacing:2px; margin:0; margin-top:18px;">YAKA CREW MERCHANDISE STORE</h2>
    </div>
  <div class="products-wrapper">
      <div class="product">
        <div class="photo">
          <img src="source/YCMerch-images/Main.png" alt="YAKA Band Poster">
        </div>
        <p>T-Shirts</p>
        <a href="YCMerch-tshirts.php"><button>EXPLORE NOW!</button></a>
      </div>
      <div class="product">
        <div class="photo">
          <img src="source/YCMerch-images/YakaMain.jpeg" alt="YAKA Band Poster">
        </div>
        <p>Posters</p>
        <a href="YCMerch-posters.php"><button>EXPLORE NOW!</button></a>
      </div>
      <div class="product">
        <div class="photo">
          <img src="source/YCMerch-images/MainHoodie.png" alt="YAKA Band Poster">
        </div>
        <p>Hoodies</p>
        <a href="YCMerch-hoodies.php"><button>EXPLORE NOW!</button></a>
      </div>
      <div class="product">
        <div class="photo">
          <img src="source/YCMerch-images/poster.jpg" alt="YAKA Band Poster">
        </div>
        <p>Wrist Bands</p>
        <a href="YCMerch-wristband.php"><button>EXPLORE NOW!</button></a>
      </div>
  </div>
  </main>


<?php include_once 'footer.php'; ?>
</body>
</html>
