<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Payment - Yaka Crew Events</title>
<link rel="stylesheet" href="css/YCBooking-payment.css">
<script src="https://js.stripe.com/v3/"></script>
<style>
    body { font-family: Arial, sans-serif; background:#f4f4f4; }
    .payment-container { max-width: 520px; margin: 50px auto; background:#fff; padding:22px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.08); }
    h1 { text-align:center; color:#956E2F; margin-bottom:18px; }
    .form-group { margin-bottom:14px; }
    .stripe-input { padding:10px; border:1px solid #ddd; border-radius:6px; background:#fff; }
    .pay-now-btn { background:#956E2F; color:#fff; border:none; padding:12px; width:100%; border-radius:8px; font-size:16px; cursor:pointer; }
    .pay-now-btn:disabled { background:#ccc; }
    .error-message { color:#c62828; margin-top:10px; min-height:18px; }
    .success-popup { background:#2e7d32; color:#fff; padding:18px; border-radius:8px; text-align:center; margin-top:16px; display:none; }
    label { display:block; margin-bottom:6px; font-weight:600; font-size:14px; }
    input[type="text"], input[type="email"] { width:100%; padding:10px; border-radius:6px; border:1px solid #ddd; }
</style>
</head>
<body>
<div class="payment-container">
    <h1>YakaCrewPay</h1>

    <form id="payment-form">
        <div class="form-group">
            <label>Name</label>
            <input type="text" id="customer-name" placeholder="John Doe" required />
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" id="email" placeholder="you@example.com" required />
        </div>

        <hr style="margin:14px 0; border:none; border-top:1px solid #eee" />

        <div class="form-group">
            <label>Card Number</label>
            <div id="card-number" class="stripe-input"></div>
        </div>

        <div class="form-row" style="display:flex; gap:10px;">
            <div class="form-group" style="flex:1;">
                <label>CVC</label>
                <div id="card-cvc" class="stripe-input"></div>
            </div>
            <div class="form-group" style="flex:1;">
                <label>Expiry Date</label>
                <div id="card-expiry" class="stripe-input"></div>
            </div>
        </div>

        <div id="card-errors" class="error-message"></div>
        <button type="submit" class="pay-now-btn">Pay Now</button>
    </form>

    <div class="success-popup" id="success-popup">✅ Payment Successful! A confirmation email has been sent.</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stripe = Stripe('pk_test_51Rz9w0Plji5Rb0KW9KRkYFGvAA7qMz4BBHf4CJx6gXNR0AcgAygBWnrM9LciTGj4VOzfHQy9dB97iONbcvdymIhY00FxclFsMN');
    const elements = stripe.elements();
    const style = { base: { fontSize: '16px', color: '#000', '::placeholder': { color: '#aaa' } } };

    const cardNumber = elements.create('cardNumber', { style, showIcon: true });
    const cardExpiry = elements.create('cardExpiry', { style });
    const cardCvc = elements.create('cardCvc', { style });

    cardNumber.mount('#card-number');
    cardExpiry.mount('#card-expiry');
    cardCvc.mount('#card-cvc');

    const form = document.getElementById('payment-form');
    const errorEl = document.getElementById('card-errors');
    const successPopup = document.getElementById('success-popup');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorEl.textContent = '';

        const name = document.getElementById('customer-name').value.trim();
        const email = document.getElementById('email').value.trim();

        if (!name || !email) {
            errorEl.textContent = 'Please fill all required fields.';
            return;
        }

        const btn = form.querySelector('button');
        btn.disabled = true;
        btn.textContent = 'Processing...';

        try {
            const { paymentMethod, error } = await stripe.createPaymentMethod({
                type: 'card',
                card: cardNumber,
                billing_details: { name, email }
            });

            if (error) {
                errorEl.textContent = error.message;
                btn.disabled = false;
                btn.textContent = 'Pay Now';
                return;
            }

            const createResp = await fetch('YCBooking-payment-booking.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'create',
                    payment_method_id: paymentMethod.id,
                    amount: 1000 * 100, // LKR 1000
                    currency: 'lkr',
                    email,
                    name
                })
            });

            const data = await createResp.json();

            if (data.error) {
                errorEl.textContent = data.error;
                btn.disabled = false;
                btn.textContent = 'Pay Now';
                return;
            }

            if (data.requires_action) {
                const confirmResult = await stripe.confirmCardPayment(data.payment_intent_client_secret);
                if (confirmResult.error) {
                    errorEl.textContent = confirmResult.error.message || 'Authentication failed';
                    btn.disabled = false;
                    btn.textContent = 'Pay Now';
                    return;
                }
                if (confirmResult.paymentIntent && confirmResult.paymentIntent.status === 'succeeded') {
                    await fetch('YCBooking-payment-booking.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'verify',
                            payment_intent_id: confirmResult.paymentIntent.id,
                            email,
                            name
                        })
                    });
                    successPopup.style.display = 'block';
                    form.reset();
                }
            } else if (data.success) {
                successPopup.style.display = 'block';
                form.reset();
            } else {
                errorEl.textContent = 'Unexpected response from server.';
            }

        } catch (err) {
            console.error(err);
            errorEl.textContent = 'Unexpected error occurred.';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Pay Now';
        }
    });

    [cardNumber, cardExpiry, cardCvc].forEach(el => el.on('change', e => {
        errorEl.textContent = e.error ? e.error.message : '';
    }));
});
</script>
</body>
</html>
