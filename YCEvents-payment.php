<?php
// Use Composer's vendor autoload for Stripe
require_once __DIR__ . '/YCEvent-vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/YCEvent-vendor/phpmailer/Exception.php';
require __DIR__ . '/YCEvent-vendor/phpmailer/PHPMailer.php';
require __DIR__ . '/YCEvent-vendor/phpmailer/SMTP.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
require_once __DIR__ . '/YCdb_connection.php';

// Handle payment processing if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: Replace this with your actual Stripe secret key from your Stripe dashboard.
    // This key is used for server-side operations and should be kept confidential.
    $stripeSecretKey = 'sk_test_51RzHBlDZKJyc4R9PEkyYtS2uTCrPt5J0039b4Y7FXkdoeiRA87s5lTMrTxGaImaVRbrzAYDOkOPmbVbzxVxYpLiF00OXUzS6kT'; // Your test secret key
    
    try {
        // Initialize the Stripe client
        \Stripe\Stripe::setApiKey($stripeSecretKey);

        // Retrieve the payment method ID and amount from the request
        $input = json_decode(file_get_contents('php://input'), true);
        $paymentMethodId = $input['payment_method_id'];
        $amount = $input['amount'];
        // For test mode, force currency to USD (Stripe test mode does not support LKR)
        $currency = 'usd';
        $email = $input['email'];
        $cardholderName = $input['cardholder_name'];
        $cartData = $input['cart_data'];

        // Create a PaymentIntent to charge the user
        $paymentIntent = \Stripe\PaymentIntent::create([
            'payment_method' => $paymentMethodId,
            'amount' => $amount,
            'currency' => $currency,
            'confirmation_method' => 'manual',
            'confirm' => true,
            'return_url' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'YCEvents-payment.php',
            'receipt_email' => $email,
        ]);

        // Send the client secret to the client
        $response = [
            'client_secret' => $paymentIntent->client_secret,
            'status' => $paymentIntent->status
        ];

        if ($paymentIntent->status === 'succeeded') {
            // Save each event purchase to the database
            foreach ($cartData as $item) {
                $eventId = $item['id'];
                $quantity = $item['quantity'];
                $unitPrice = $item['price'];
                $totalPrice = $unitPrice * $quantity;
                
                // Insert into event_sales table
                $stmt = $pdo->prepare("INSERT INTO event_sales (event_id, quantity, unit_price, total_price, buyer_name, buyer_email) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$eventId, $quantity, $unitPrice, $totalPrice, $cardholderName, $email]);
                
                // Debug: Check if insertion was successful
                if ($stmt->rowCount() > 0) {
                    error_log("Successfully inserted sale for event ID: $eventId");
                } else {
                    error_log("Failed to insert sale for event ID: $eventId");
                }
            }

            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'oshaniherath15277@gmail.com'; // TODO: Replace with your Gmail address
                $mail->Password   = 'yecs lpfk oyea znmo'; // TODO: Replace with your Gmail password or App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // IMPORTANT: The following option is for debugging purposes only on a local machine.
                // It disables SSL certificate verification, which is a security risk.
                // Do NOT use this in a production environment.
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                //Recipients
                $mail->setFrom('oshaniherath15277@gmail.com', 'Yaka Crew Events');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Payment Confirmation - Yaka Crew Events';
                $mail->Body    = 'Thank you for your payment. Your ticket booking is confirmed.';

                $mail->send();
            } catch (Exception $e) {
                // Don't block the payment confirmation, just log the email error
                error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        }

    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Handle any errors from the Stripe API
        http_response_code(500);
        $response = ['error' => $e->getMessage()];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Yaka Crew Events</title>
    <link rel="stylesheet" href="./css/YCEvents-payment.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h1>YakaCrewPay</h1>
            <div class="timer">05:00</div>
        </div>

        <div class="payment-body">
            <div class="payment-form-section">
                <h2>Payment Details</h2>
                
                <form id="payment-form">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <p class="sub-label">Enter the 16-digit card number on the card</p>
                        <div class="input-with-icon stripe-element-container">
                            <i class="icon-card"></i>
                            <div id="card-number" class="stripe-input"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="card_cvc">CW Number</label>
                            <p class="sub-label">Enter the 3 or 4 digit number on the card</p>
                            <div id="card-cvc" class="stripe-input"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="card_expiry">Expiry Date</label>
                            <p class="sub-label">Enter the expiration date of the card</p>
                            <div id="card-expiry" class="stripe-input"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="cardholder-name">Name on Card</label>
                        <p class="sub-label">Enter the name as displayed on your card</p>
                        <input type="text" id="cardholder-name" placeholder="John Doe" required autocomplete="cc-name">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <p class="sub-label">Enter your email address</p>
                        <input type="email" id="email" placeholder="you@example.com" required autocomplete="email">
                    </div>
                    
                    <hr class="divider">
                    
                    <div class="payment-summary">
                        <div class="summary-header">
                            <h3>You have to Pay</h3>
                            <div class="amount" id="payment-total">0.00 LKR</div>
                        </div>
                        
                        <div class="summary-details" id="payment-details">
                            <!-- Payment details will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div id="card-errors" role="alert" class="error-message"></div>
                    
                    <button type="submit" class="pay-now-btn">Pay Now</button>
                </form>
            </div>
            
            <div class="event-summary-section">
                <div id="event-summary-cards">
                    <!-- Event cards will be populated by JavaScript -->
                </div>
                
                <div class="price-breakdown">
                    <h4>Price Breakdown</h4>
                    <div class="breakdown-item">
                        <span>Ticket Price</span>
                        <span id="ticket-price">0.00 LKR</span>
                    </div>
                    
                    <div class="breakdown-item total">
                        <span>Total</span>
                        <span id="breakdown-total">0.00 LKR</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Stripe
            // TODO: Replace this with your actual Stripe publishable key from your Stripe dashboard.
            // This key is used for client-side operations.
            const stripe = Stripe('pk_test_51RzHBlDZKJyc4R9PGAxYmrKsUemJeyhnDF4SfePosYkE8cEokPdoYCIQRa3pBS8mtDhpUFWtih028Pijmu2Vr8tp00IYsJketm'); // Test key
            
            // Create Stripe elements
            const elements = stripe.elements();
            const style = {
                base: {
               
                    fontSize: '16px',
                    color: '#fff',
                    '::placeholder': {
                        color: '#aaa'
                    },
                    iconColor: '#956E2F'
                }
            };
            
            const cardNumber = elements.create('cardNumber', { 
                style,
                showIcon: true,
                placeholder: '2412 7512 3412 3456'
            });
            
            const cardExpiry = elements.create('cardExpiry', { 
                style,
                placeholder: 'MM/YY'
            });
            
            const cardCvc = elements.create('cardCvc', { 
                style,
                placeholder: '123'
            });
            
            // Mount Stripe elements
            cardNumber.mount('#card-number');
            cardExpiry.mount('#card-expiry');
            cardCvc.mount('#card-cvc');
            
            // Get cart data from sessionStorage
            const checkoutCart = JSON.parse(sessionStorage.getItem('checkoutCart') || '[]');
            
            if (checkoutCart.length === 0) {
                // Redirect back to cart if no items
                window.location.href = 'YCEvents-cart.php';
                return;
            }
            
            let totalAmount = 0;
            let eventDetailsHtml = '';
            let eventCardsHtml = '';
            
            // Process each item in cart
            checkoutCart.forEach(item => {
                const itemTotal = (parseFloat(item.price) || 0) * (parseInt(item.quantity) || 1);
                totalAmount += itemTotal;
                
                // Add to event details
                eventDetailsHtml += `
                    <div class="detail-item">
                        <span>${item.name} (x${item.quantity})</span>
                        <span>LKR ${itemTotal.toFixed(2)}</span>
                    </div>
                `;
                
                // Add to event cards
                eventCardsHtml += `
                    <div class="event-card">
                        <img src="${item.image || 'assets/images/image6.JPG'}" alt="${item.name}">
                        <div class="event-details">
                            <h3>${item.name}</h3>
                            <p class="event-date">${item.date}</p>
                            <p class="event-location">${item.location}</p>
                            <p class="event-quantity">Quantity: ${item.quantity}</p>
                        </div>
                    </div>
                `;
            });
            
            // Update payment summary
            document.getElementById('payment-total').textContent = `LKR ${totalAmount.toFixed(2)}`;
            document.getElementById('payment-details').innerHTML = eventDetailsHtml;
            
            // Update event summary
            document.getElementById('event-summary-cards').innerHTML = eventCardsHtml;
            
            // Update price breakdown
            document.getElementById('ticket-price').textContent = `LKR ${totalAmount.toFixed(2)}`;
            document.getElementById('breakdown-total').textContent = `LKR ${totalAmount.toFixed(2)}`;
            
            // Handle form submission
            const form = document.getElementById('payment-form');
            const errorEl = document.getElementById('card-errors');
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const cardholderName = document.getElementById('cardholder-name');
                const email = document.getElementById('email');
                
                if (!cardholderName.value.trim()) {
                    errorEl.textContent = 'Please enter the name on your card';
                    return;
                }

                if (!email.value.trim()) {
                    errorEl.textContent = 'Please enter your email address';
                    return;
                }
                
                // Disable submit button to prevent repeated submissions
                const submitButton = form.querySelector('button');
                submitButton.disabled = true;
                submitButton.textContent = 'Processing...';
                
                try {
                    // Create payment method using card elements
                    const { paymentMethod, error } = await stripe.createPaymentMethod({
                        type: 'card',
                        card: cardNumber,
                        billing_details: {
                            name: cardholderName.value.trim(),
                            email: email.value.trim(),
                        },
                    });
                    
                    if (error) {
                        errorEl.textContent = error.message;
                        submitButton.disabled = false;
                        submitButton.textContent = 'Pay Now';
                        return;
                    }
                    
                    // Send payment method ID to our server
                    const response = await fetch('YCEvents-payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            payment_method_id: paymentMethod.id,
                            amount: Math.round(totalAmount * 100), // Convert to cents
                            currency: 'usd',
                            email: email.value.trim(),
                            cardholder_name: cardholderName.value.trim(),
                            cart_data: checkoutCart // Send cart data to server
                        }),
                    });
                    
                    const paymentIntent = await response.json();
                    
                    if (paymentIntent.error) {
                        errorEl.textContent = paymentIntent.error;
                        submitButton.disabled = false;
                        submitButton.textContent = 'Pay Now';
                        return;
                    }
                    
                    // Confirm the payment
                    const { paymentIntent: confirmedPaymentIntent, error: confirmError } = await stripe.confirmCardPayment(
                        paymentIntent.client_secret
                    );
                    
                    if (confirmError) {
                        errorEl.textContent = confirmError.message;
                        submitButton.disabled = false;
                        submitButton.textContent = 'Pay Now';
                    } else {
                        // Payment successful
                        alert('Payment successful! Thank you for your purchase.');
                        
                        // Clear cart and redirect
                        sessionStorage.removeItem('checkoutCart');
                        localStorage.removeItem('yakaCrewCart');
                        window.location.href = 'YCEvents.php';
                    }
                } catch (err) {
                    console.error('Error:', err);
                    errorEl.textContent = 'An unexpected error occurred. Please try again.';
                    submitButton.disabled = false;
                    submitButton.textContent = 'Pay Now';
                }
            });
            
            // Timer countdown
            let timeLeft = 300; // 5 minutes in seconds
            const timerElement = document.querySelector('.timer');
            
            const timerInterval = setInterval(() => {
                timeLeft--;
                
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    alert('Payment session expired. Please try again.');
                    window.location.href = 'YCEvents-cart.php';
                    return;
                }
                
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }, 1000);
            
            // Handle real-time validation errors from Stripe
            [cardNumber, cardExpiry, cardCvc].forEach(element => {
                element.on('change', (event) => {
                    if (event.error) {
                        errorEl.textContent = event.error.message;
                    } else {
                        errorEl.textContent = '';
                    }
                });
            });
        });
    </script>
</body>
</html>