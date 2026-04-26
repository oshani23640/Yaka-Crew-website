<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Force a JSON response for initial testing
if (isset($_GET['test'])) {
  header('Content-Type: application/json');
  echo json_encode(['status' => 'php is running']);
  exit;
}

// Use the same vendor path as events payment
require __DIR__ . '/YCEvent-vendor/autoload.php';
\Stripe\Stripe::setApiKey('sk_test_51Rz9cXAlz3HCjY3AehelwnEGyJe19GrsqAsiup4Y6saRx79bz3sDNtF5yAlcGA7xWxtgHwgACSROnyMH9LDoO3yH00ph8D0JF6');
header('Content-Type: application/json');

try {
  $input = json_decode(file_get_contents('php://input'), true) ?: [];
  $paymentMethodId = $input['payment_method_id'] ?? null;
  $amount = $input['amount'] ?? null;
  $currency = 'usd'; // Stripe test mode only supports USD
  $email = $input['email'] ?? null;

  if (!$paymentMethodId || !$amount) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing payment method or amount.']);
    exit;
  }

  $paymentIntent = \Stripe\PaymentIntent::create([
    'payment_method' => $paymentMethodId,
    'amount' => $amount,
    'currency' => $currency,
    'confirmation_method' => 'manual',
    'confirm' => true,
    'receipt_email' => $email,
    'metadata' => [
      'source' => 'yaka_crew_merch',
    ],
    'payment_method_types' => ['card'],
  ]);

  // Send confirmation email if payment succeeded
  if ($paymentIntent->status === 'succeeded' && $email) {
    // PHPMailer setup (using new sender email)
    require_once __DIR__ . '/YCEvent-vendor/phpmailer/Exception.php';
    require_once __DIR__ . '/YCEvent-vendor/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/YCEvent-vendor/phpmailer/SMTP.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
      $mail->isSMTP();
      $mail->Host       = 'smtp.gmail.com';
      $mail->SMTPAuth   = true;
  $mail->Username   = 'oshaniherath15277@gmail.com';
  $mail->Password   = 'yecs lpfk oyea znmo';
  $mail->setFrom('oshaniherath15277@gmail.com', 'Yaka Crew Merch');
      $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = 587;
      $mail->SMTPOptions = array(
        'ssl' => array(
          'verify_peer' => false,
          'verify_peer_name' => false,
          'allow_self_signed' => true
        )
      );
      $mail->setFrom('sithummachado@gmail.com', 'Yaka Crew Merch');
      $mail->addAddress($email);
      $mail->isHTML(true);
      $mail->Subject = 'Payment Confirmation - Yaka Crew Merchandise';
      $mail->Body    = 'Thank you for your purchase! Your payment was successful and your order is being processed.';
      $mail->send();
    } catch (Exception $e) {
      error_log('Merch Mailer Error: ' . $mail->ErrorInfo);
    }
  }

  $response = [
    'client_secret' => $paymentIntent->client_secret,
    'status' => $paymentIntent->status
  ];

  echo json_encode($response);
  exit;
} catch (\Stripe\Exception\ApiErrorException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
  exit;
}

// Fallback: if nothing was output, always return JSON
if (!headers_sent()) {
  http_response_code(500);
  echo json_encode(['error' => 'Unknown server error. No output from payment processor.']);
  exit;
}



