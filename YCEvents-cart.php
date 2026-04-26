<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Your Cart | Yaka Crew</title>
  <meta name="description" content="Review and manage your selected event tickets before checkout.">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="css/YCEvents.css" />
  <link rel="stylesheet" href="css/YCEvents-cart.css" />
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

  <main class="cart-container">
    <h1>Your Shopping Cart</h1>
    <div class="cart-items" id="cart-items-container">
      <p class="empty-cart-message">Your cart is empty. Add some tickets!</p>
    </div>
    <div class="cart-summary">
      <span>Total:</span>
      <span id="cart-total">LKR 0.00</span>
    </div>
    <div class="cart-actions">
      <a href="YCEvents.php"><button class="btn btn-outline">Back to Events</button></a>
      <button class="btn btn-primary" id="checkout-btn" disabled>Proceed to Checkout</button>
    </div>
  </main>

  <!-- Toast Notification -->
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
    document.addEventListener('DOMContentLoaded', function() {
        const cartItemsContainer = document.getElementById('cart-items-container');
        const cartTotalElement = document.getElementById('cart-total');
        const checkoutBtn = document.getElementById('checkout-btn');

        async function fetchCartItemsDetails(cartItems) {
            try {
                const response = await fetch('admin/YCEvents-get-cart-items.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ ids: cartItems.map(item => item.id) })
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();
                
                if (data.success && data.data) {
                    // Create a map of event details by ID
                    const detailsMap = {};
                    data.data.forEach(event => {
                        detailsMap[event.id] = event;
                    });
                    
                    return detailsMap;
                }
                return {};
            } catch (error) {
                console.error('Error fetching cart items:', error);
                return {};
            }
        }

        async function renderCart() {
            const cart = JSON.parse(localStorage.getItem('yakaCrewCart')) || [];
            cartItemsContainer.innerHTML = '';
            let total = 0;

            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '<p class="empty-cart-message">Your cart is empty. Add some tickets!</p>';
                checkoutBtn.disabled = true;
            } else {
                checkoutBtn.disabled = false;
                // Fetch the latest details for all items in cart
                const detailsMap = await fetchCartItemsDetails(cart);

                const PLACEHOLDER_IMG = 'assets/images/image6.JPG';
                
                const cartForCheckout = [];

                cart.forEach(item => {
                    const eventDetails = detailsMap[item.id] || item;
                    const rawImg = eventDetails.image || '';
                    const isString = typeof rawImg === 'string' && rawImg.length > 0;
                    let resolved = PLACEHOLDER_IMG;
                    if (isString) {
                        if (rawImg.includes('/')) {
                            resolved = rawImg;
                        } else if (rawImg.includes('.')) {
                            const filename = rawImg.split('/').pop();
                            resolved = `uploads/Events/${filename}`;
                        }
                    }
                    const imgSrc = encodeURI(resolved);

                    const name = eventDetails.name || 'Event';
                    const date = eventDetails.date || '';
                    const location = eventDetails.location || '';
                    const priceNum = Number(eventDetails.price) || 0;

                    const itemElement = document.createElement('div');
                    itemElement.className = 'cart-item';
                    itemElement.innerHTML = `
                        <div class="item-image">
                            <img src="${imgSrc}" alt="${name}" onerror="this.onerror=null;this.src='${PLACEHOLDER_IMG}'">
                        </div>
                        <div class="item-details">
                            <h3>${name}</h3>
                            <p>${date} - ${location}</p>
                            <p>Price: LKR ${priceNum.toFixed(2)}</p>
                        </div>
                        <div class="item-quantity">
                            <button data-id="${item.id}" data-action="decrease">-</button>
                            <input type="number" value="${item.quantity}" min="1" data-id="${item.id}" class="quantity-input">
                            <button data-id="${item.id}" data-action="increase">+</button>
                        </div>
                        <div class="item-price">LKR ${(priceNum * item.quantity).toFixed(2)}</div>
                        <button class="remove-item" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                    `;
                    cartItemsContainer.appendChild(itemElement);
                    total += priceNum * item.quantity;

                    cartForCheckout.push({
                        ...item,
                        image: imgSrc,
                        name: name,
                        date: date,
                        location: location,
                        price: priceNum
                    });
                });

                sessionStorage.setItem('checkoutCart', JSON.stringify(cartForCheckout));
            }
            cartTotalElement.textContent = `LKR ${total.toFixed(2)}`;
            attachCartListeners();
        }

        function attachCartListeners() {
            document.querySelectorAll('.item-quantity button').forEach(btn => {
                btn.addEventListener('click', () => updateQuantity(btn.dataset.id, btn.dataset.action));
            });

            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', () => updateQuantity(input.dataset.id, null, parseInt(input.value)));
            });

            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.addEventListener('click', () => removeItem(btn.dataset.id));
            });
        }

        function updateQuantity(id, action, quantity = null) {
            let cart = JSON.parse(localStorage.getItem('yakaCrewCart')) || [];
            const index = cart.findIndex(item => item.id === id);
            if (index !== -1) {
                if (quantity !== null) cart[index].quantity = quantity;
                else if (action === 'increase') cart[index].quantity++;
                else if (action === 'decrease') cart[index].quantity--;
                if (cart[index].quantity <= 0) cart.splice(index, 1);
                localStorage.setItem('yakaCrewCart', JSON.stringify(cart));
                renderCart();
                updateCartCount();
            }
        }

        function removeItem(id) {
            let cart = JSON.parse(localStorage.getItem('yakaCrewCart')) || [];
            cart = cart.filter(item => item.id !== id);
            localStorage.setItem('yakaCrewCart', JSON.stringify(cart));
            renderCart();
            updateCartCount();
        }

        function updateCartCount() {
            const cart = JSON.parse(localStorage.getItem('yakaCrewCart')) || [];
            const count = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.querySelectorAll('.cart-count').forEach(el => el.textContent = count);
        }

        // Handle checkout button click
        checkoutBtn.addEventListener('click', function() {
            const cart = JSON.parse(localStorage.getItem('yakaCrewCart')) || [];
            if (cart.length === 0) {
                showToast('Your cart is empty. Add tickets before checkout.', true);
                return;
            }
            
            // Redirect to payment page
            window.location.href = 'YCEvents-payment.php';
        });

        // Initial render
        renderCart();
        updateCartCount();
    });
  </script>

<?php include_once 'footer.php'; ?>
</body>
</html>