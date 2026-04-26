<?php
// --- PHP cart math stays the same ---
$cart = isset($_POST['cart']) ? $_POST['cart'] : [];
$subtotal = 0;
$itemCount = 0;
if (!empty($cart)) {
  foreach ($cart as $item) {
    $itemCount += (int)($item['quantity'] ?? 1);
    $subtotal += (float)($item['total'] ?? 0);
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="css/YCEvents.css" />
  <meta charset="UTF-8" />
  <title>Checkout - YAKA Crew</title>
  <link rel="stylesheet" href="css/YCMerch-tshirts.css" />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://js.stripe.com/v3/"></script>
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
      padding-top: 80px; /* Reduce top padding to move content up */
      overflow-x: hidden;
      align-items: flex-start; /* Match YCMerch-merch1.php alignment */
    }
    /* Navbar */
    .navbar {
      display: flex;
      align-items: center;
      background-color: black;
      padding: 10px 20px;
      position: fixed;
      top: 0; /* Move navbar slightly up */
      left: 10px;
      right: 0;
      z-index: 1000;
      justify-content: flex-start; /* Match YCMerch-merch1.php alignment */
    }
    .logo {
      margin-right: 20px;
    }
    .logo img {
      width: 120px;
      height: auto;
      display: block;
    }
    .nav-links {
      list-style: none;
      display: flex;
      margin-left: 290px; /* Slightly less than before for a little left shift */
      gap: 40px;
      position: relative;
      justify-content: flex-start;
    }
    .nav-links > li {
      position: relative;
      cursor: pointer;
      padding: 5px 10px;
      border-bottom: 2px solid transparent;
      transition: border 0.3s;
      text-align: left; /* Left align text */
    }
    .nav-links > li:hover {
      border-bottom: 2px solid white;
    }
    .nav-links > li.active {
      border-bottom: 2px solid white;
    }
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
    .nav-links > li:hover,
    .nav-links > li.active {
      border-bottom: 2px solid #fff;
    }
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
      list-style: none;
    }
    .dropdown li {
      padding: 8px 15px;
      white-space: nowrap;
      cursor: pointer;
      transition: background-color 0.3s ease;
      list-style: none;
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
    :root { --bg:#000; --accent:#956E2F; --muted:rgba(255,255,255,0.07); --panel:#0e0e0e; --glass:rgba(149,110,47,0.06); --glass-strong:rgba(149,110,47,0.12); }
    .checkout-container { max-width:900px; margin:60px auto 40px; background:var(--panel); border-radius:18px; box-shadow:0 8px 40px rgba(0,0,0,0.18); display:flex; flex-direction:column; align-items:center; gap:32px; padding:40px 30px 30px; }
    @media (min-width: 900px) { .checkout-container { flex-direction:row; align-items:flex-start; justify-content:center; } }
    .checkout-left, .checkout-right { flex:1 1 340px; min-width:320px; max-width:420px; border-radius:12px; padding:32px 24px; box-shadow:0 2px 12px rgba(0,0,0,0.12); margin-bottom:24px; }
    .checkout-left { background:#181818; } .checkout-right { background:#232323; }
    .express-checkout { display:flex; gap:16px; margin-bottom:24px; justify-content:center; }
    .express-checkout button { flex:1; min-width:120px; font-size:1.1rem; border-radius:8px; border:none; padding:12px 0; font-weight:700; cursor:pointer; }
    .shop-pay { background:#6e3ff6; color:#fff; } .gpay { background:#000; color:#fff; }
    .checkout-section { margin-bottom:28px; }
    .checkout-section label { font-weight:700; margin-bottom:6px; display:block; color:var(--accent); font-size:1.08rem; }
    .checkout-section input, .checkout-section select { width:100%; padding:10px 14px; border-radius:8px; border:1px solid var(--glass-strong); margin-bottom:16px; font-size:1.08rem; background:#191919; color:#fff; box-shadow:0 1px 4px #0002; }
    .checkout-section input::placeholder { color:#aaa; }
    .order-summary { border-bottom:1px solid #222; margin-bottom:18px; padding-bottom:18px; background:#181818; border-radius:10px; box-shadow:0 1px 6px #0002; }
    .order-summary-item { display:flex; align-items:center; margin-bottom:12px; }
    .order-summary-item img { width:54px; height:54px; object-fit:cover; border-radius:8px; margin-right:16px; border:1px solid #333; }
    .order-summary-details { flex:1; }
    .order-summary-title { font-weight:700; font-size:1.1rem; margin-bottom:2px; color:#fff; }
    .order-summary-meta { font-size:0.97rem; color:#bfb2a2; }
    .order-summary-price { font-weight:700; font-size:1.1rem; color:#fff; margin-left:12px; }
    .order-summary-total { font-size:1.2rem; font-weight:800; color:#fff; text-align:right; margin-top:18px; border-top:1px solid #333; padding-top:12px; }
    .checkout-btn { width:100%; background:var(--accent); color:#fff; border:none; border-radius:10px; padding:14px 0; font-size:1.2rem; font-weight:700; margin-top:18px; cursor:pointer; transition:background .2s; box-shadow:0 2px 8px #0002; }
    .checkout-btn:hover { background:#7d5620; }
    .stripe-input { padding:12px 10px; border:1px solid #333; border-radius:8px; background:#191919; }
    .StripeElement--focus { border-color:#888; }
    .StripeElement--invalid { border-color:#c00; }
    #card-errors { color:#f08; min-height:20px; margin-top:-8px; margin-bottom:10px; font-size:.95rem; }
    .smallnote { color:#bbb; font-size:.9rem; }
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

  <div class="checkout-container" data-subtotal-lkr="<?php echo htmlspecialchars(number_format($subtotal, 2, '.', '')); ?>">
    <div class="checkout-left">
      <div class="express-checkout">
        <button class="shop-pay" type="button" disabled title="Demo only">Shop Pay</button>
        <button class="gpay" type="button" disabled title="Demo only">G Pay</button>
      </div>

      <!-- Payment form starts -->
      <form id="payment-form" autocomplete="off">
        <div class="checkout-section">
          <label for="contact">Contact</label>
          <input type="text" id="contact" placeholder="Email or mobile phone number" />
          <div style="margin-bottom: 16px;">
            <input type="checkbox" id="news" checked style="margin-right: 6px;" />
            <label for="news" style="font-size:0.97rem; font-weight:400;">Email me with news and offers</label>
          </div>
        </div>

        <div class="checkout-section" id="shipping">
          <label for="country">Country/Region</label>
          <select id="country">
            <option value="LK" selected>Sri Lanka</option>
            <option value="IN">India</option>
            <option value="AE">UAE</option>
          </select>
          <div style="display:flex; gap:12px;">
            <input id="first-name" type="text" placeholder="First name" style="flex:1;" />
            <input id="last-name" type="text" placeholder="Last name" style="flex:1;" />
          </div>
          <input id="addr-line1" type="text" placeholder="Address" />
          <input id="city" type="text" placeholder="City" />
          <input id="postal" type="text" placeholder="Postal code" />
        </div>

        <div class="checkout-section">
          <label for="payment">Payment</label>
          <select id="payment" disabled>
            <option>Credit card (Stripe Test)</option>
          </select>

          <!-- Stripe split elements (replace raw inputs) -->
          <div id="card-number" class="stripe-input"></div>
          <div style="display:flex; gap:12px;">
            <div id="card-expiry" class="stripe-input" style="flex:1;"></div>
            <div id="card-cvc" class="stripe-input" style="flex:1;"></div>
          </div>

          <input id="cardholder-name" type="text" placeholder="Name on card" />
          <div id="card-errors" role="alert"></div>

          <div style="margin-bottom: 16px;">
            <input type="checkbox" id="billing" checked style="margin-right: 6px;" />
            <label for="billing" class="smallnote">Use shipping address as billing address</label>
          </div>
        </div>

        <div class="checkout-section">
          <input type="checkbox" id="remember" checked style="margin-right: 6px;" />
          <label for="remember" style="font-size:0.97rem; font-weight:400;">Save my information for a faster checkout</label>
          <input id="phone" type="text" placeholder="Mobile phone number" style="margin-top: 8px;" />
        </div>

        <button id="pay-btn" class="checkout-btn" type="submit">Pay Now</button>
        <div id="status" class="smallnote" style="margin-top:8px;"></div>
      </form>
      <!-- Payment form ends -->
    </div>

    <div class="checkout-right">
      <div class="order-summary">
        <?php if (!empty($cart)): foreach ($cart as $item): ?>
          <div class="order-summary-item">
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" />
            <div class="order-summary-details">
              <div class="order-summary-title"><?= htmlspecialchars($item['name']) ?></div>
              <div class="order-summary-meta">
                <?php if (!empty($item['size']) || !empty($item['color'])): ?>
                  <?= !empty($item['size']) ? htmlspecialchars($item['size']) : '' ?><?= (!empty($item['size']) && !empty($item['color'])) ? ' / ' : '' ?><?= !empty($item['color']) ? htmlspecialchars($item['color']) : '' ?>
                <?php else: ?>
                  Quantity: <?= htmlspecialchars($item['quantity']) ?>
                <?php endif; ?>
              </div>
            </div>
            <div class="order-summary-price">LKR <?= number_format($item['total'], 2) ?></div>
          </div>
        <?php endforeach; endif; ?>

        <div class="order-summary-total">
          Subtotal · <?= $itemCount ?> item<?= $itemCount == 1 ? '' : 's' ?><br />
          <span style="font-size:1.3rem; font-weight:800; color:#fff;">LKR <?= number_format($subtotal, 2) ?></span><br />
          Shipping <span style="font-weight:400; color:#aaa;">TBD</span><br />
          <span style="font-size:1.4rem; font-weight:900; color:#fff;">Total LKR <?= number_format($subtotal, 2) ?></span>
        </div>
      </div>

      <input type="text" placeholder="Discount code or gift card" style="width:100%; margin-bottom:12px;" />
      <button class="checkout-btn" type="button" disabled>Apply</button>
    </div>
  </div>

  <script>
    // ---------- Stripe Elements (split, like events page) ----------
    const stripe = Stripe('pk_test_51Rz9cXAlz3HCjY3AhQpzZw6R1ClKN70jhIKqDhXLcNwzV6SqiQZ8Uf04zWl6DzUigacmJOZCLyjIUTm6p2aVQi1t00zJEks9Bn');
    const elements = stripe.elements();
    const style = { base: { fontSize: '16px', color: '#fff', '::placeholder': { color: '#999' } } };
    const cardNumber = elements.create('cardNumber', { style, showIcon: true });
    const cardExpiry = elements.create('cardExpiry', { style });
    const cardCvc    = elements.create('cardCvc',    { style });
    cardNumber.mount('#card-number');
    cardExpiry.mount('#card-expiry');
    cardCvc.mount('#card-cvc');

    const errorEl = document.getElementById('card-errors');
    [cardNumber, cardExpiry, cardCvc].forEach(el => {
      el.on('change', (e) => errorEl.textContent = e.error ? e.error.message : '');
    });

    function setBusy(busy, msg='') {
      const btn = document.getElementById('pay-btn');
      const st  = document.getElementById('status');
      btn.disabled = busy;
      btn.textContent = busy ? 'Processing…' : 'Pay Now';
      st.textContent = msg || '';
    }

    // ---------- Submit handler (like events page) ----------
    document.getElementById('payment-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      setBusy(true, 'Creating payment…');

      const name = (document.getElementById('cardholder-name').value || '').trim();
      const email = (document.getElementById('contact').value || '').trim();
      const container = document.querySelector('.checkout-container');
      const subtotalLkrStr = container.getAttribute('data-subtotal-lkr') || '0';
      const subtotalLkr = parseFloat(subtotalLkrStr) || 0;
      const amountCents = Math.round((subtotalLkr / 300) * 100); // Convert LKR to USD cents (test mode)

      try {
        // 1) Create payment method
        const { paymentMethod, error } = await stripe.createPaymentMethod({
          type: 'card',
          card: cardNumber,
          billing_details: { name, email },
        });
        if (error) {
          errorEl.textContent = error.message;
          setBusy(false);
          return;
        }

        // 2) Send payment method to backend
        const res = await fetch('YCMerch-createpayment.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            payment_method_id: paymentMethod.id,
            amount: amountCents,
            currency: 'usd',
            email
          })
        });
        const data = await res.json();
        if (!data.client_secret) throw new Error(data.error || 'No client secret from server');

        setBusy(true, 'Confirming with Stripe…');

        // 3) Confirm card payment
        const { paymentIntent, error: confirmError } = await stripe.confirmCardPayment(data.client_secret);
        if (confirmError) {
          errorEl.textContent = confirmError.message;
          setBusy(false);
          return;
        }

        if (paymentIntent && paymentIntent.status === 'succeeded') {
          setBusy(false, 'Payment succeeded! Redirecting…');
          alert('Thank you for your purchase! Your payment was successful.');
          sessionStorage.setItem('merchCartCount', '0');
          document.querySelectorAll('.cart-count').forEach(function(el) { el.textContent = '0'; });
          fetch('YCMerch-cartproducts.php?clear=1', {method: 'GET'});
          setTimeout(function() { window.location.href = 'YCMerch-merch1.php'; }, 3000);
        } else {
          setBusy(false, 'Payment status: ' + (paymentIntent?.status || 'unknown'));
        }
      } catch (err) {
        errorEl.textContent = err.message || 'Unexpected error';
        setBusy(false);
      }
    });
  </script>

