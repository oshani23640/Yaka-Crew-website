<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/stripe-php/init.php';
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use Stripe\Stripe;
use Stripe\PaymentIntent;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$stripeSecretKey = 'sk_test_51Rz9w0Plji5Rb0KWlAFgOznDb3aR2gbZ2woXhhKSd3eROoIkV5UBUIS6dQVhP3kntP3ZQcps4d5bTmQXaJd99iaF00OYVFZznp';
Stripe::setApiKey($stripeSecretKey);

$smtpUser = 'yakacrew2025@gmail.com';
$smtpPass = 'wfgj qrwj kuss jgvz'; // app password

header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input) {
    echo json_encode(['error' => 'Invalid request body']);
    exit;
}

$action = $input['action'] ?? 'create';

try {
    if ($action === 'create') {
        $paymentMethodId = $input['payment_method_id'] ?? null;
        $amount = intval($input['amount'] ?? 0);
        $currency = strtolower(trim($input['currency'] ?? 'lkr'));
        $email = $input['email'] ?? null;
        $name = $input['name'] ?? '';

        if (!$paymentMethodId || !$amount || !$email) {
            echo json_encode(['error' => 'Missing required parameters.']);
            exit;
        }

        $pi = PaymentIntent::create([
            'payment_method' => $paymentMethodId,
            'amount' => $amount,
            'currency' => $currency,
            'confirmation_method' => 'manual',
            'confirm' => true,
            'receipt_email' => $email,
            'payment_method_types' => ['card'],
            'metadata' => ['customer_name' => $name],
        ]);

        if ($pi->status === 'requires_action' && isset($pi->next_action) && $pi->next_action->type === 'use_stripe_sdk') {
            echo json_encode([
                'requires_action' => true,
                'payment_intent_client_secret' => $pi->client_secret
            ]);
            exit;
        } elseif ($pi->status === 'succeeded') {
            sendConfirmationEmail($email, $name, '', '', '', $amount, $currency, $smtpUser, $smtpPass);
            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['error' => 'Unexpected PaymentIntent status: ' . $pi->status]);
            exit;
        }

    } elseif ($action === 'verify') {
        $paymentIntentId = $input['payment_intent_id'] ?? null;
        $email = $input['email'] ?? null;
        $name = $input['name'] ?? '';

        if (!$paymentIntentId || !$email) {
            echo json_encode(['error' => 'Missing required parameters for verification.']);
            exit;
        }

        $pi = PaymentIntent::retrieve($paymentIntentId);

        if ($pi->status === 'succeeded') {
            sendConfirmationEmail($email, $name, '', '', '', $pi->amount, $pi->currency, $smtpUser, $smtpPass);
            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['error' => 'Payment not completed. Status: ' . $pi->status]);
            exit;
        }
    } else {
        echo json_encode(['error' => 'Unknown action']);
        exit;
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Stripe API error: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    exit;
}

function sendConfirmationEmail($toEmail, $name, $bookingId = '', $date = '', $time = '', $amount, $currency, $smtpUser, $smtpPass) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($smtpUser, 'Yaka Crew Events');
        $mail->addAddress($toEmail, $name);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Your Payment is Confirmed - Yaka Crew Events";

        $amountFormatted = number_format($amount / 100, 2);
        $currencyUpper = strtoupper($currency);

        $mail->Body = "
            <html>
            <body>
                <h2>Payment Confirmed</h2>
                <p>Hi " . htmlspecialchars($name) . ",</p>
                <p>Thank you for your payment. Amount paid: <strong>{$amountFormatted} {$currencyUpper}</strong></p>
                <p>We look forward to seeing you — Yaka Crew Events.</p>
                <hr>
                <p style='font-size:12px;color:#666'>If you did not make this payment, please contact us immediately.</p>
            </body>
            </html>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
    }
}
