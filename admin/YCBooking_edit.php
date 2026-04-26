<?php
require_once '../YCdb_connection.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$edit_error = '';
$edit_success = false;
$booking = null;

if ($edit_id > 0) {
    // Fetch booking before update
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$edit_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobile = trim($_POST['mobile_number'] ?? '');
        $date = $_POST['booking_date'] ?? '';
        $time = $_POST['booking_time'] ?? '';
        $other_info = trim($_POST['other_info'] ?? '');
        $booking_status = $_POST['booking_status'] ?? '';
        $payment_status = $_POST['payment_status'] ?? '';
        $admin_notes = trim($_POST['admin_notes'] ?? '');

        try {
            // Update booking
            $stmt = $pdo->prepare("UPDATE bookings SET 
                first_name=?, last_name=?, email=?, mobile_number=?, booking_date=?, booking_time=?, 
                other_info=?, booking_status=?, payment_status=?, admin_notes=? WHERE id=?");
            $stmt->execute([
                $first_name, $last_name, $email, $mobile, $date, $time,
                $other_info, $booking_status, $payment_status, $admin_notes, $edit_id
            ]);

            $edit_success = true;

            // Send email if status changed to Confirmed
            if ($booking_status === 'Confirmed' && $booking['booking_status'] !== 'Confirmed') {
                sendConfirmationEmail($email, $first_name, $edit_id, $date, $time);
            }

            // Refresh updated booking
            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
            $stmt->execute([$edit_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $edit_error = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}

/**
 * Send booking confirmation email
 */
function sendConfirmationEmail($toEmail, $name, $bookingId, $date, $time) {
    $mail = new PHPMailer(true);

    try {
        // Gmail SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yakacrew2025@gmail.com';
        $mail->Password   = 'wfgj qrwj kuss jgvz'; // App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('yakacrew2025@gmail.com', 'Yaka Crew Events');
        $mail->addAddress($toEmail, $name);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Your Booking is Confirmed - Yaka Crew Events";

        $fullPaymentLink = "http://localhost/YCBooking/admin/YCBooking-payment.php?id={$bookingId}&type=full";
        $partialPaymentLink = "http://localhost/YCBooking/admin/YCBooking-payment.php?id={$bookingId}&type=partial";

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
                .container { background: #fff; padding: 25px; border-radius: 8px; max-width: 650px; margin: auto; }
                h2 { color: #333; }
                p { font-size: 16px; color: #555; }
                .btn-container { text-align: center; margin-top: 20px; }
                .btn { display: inline-block; padding: 12px 20px; margin: 10px; background-color: #28a745; color: #fff; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 6px; }
                .btn-partial { background-color: #007bff; }
                .price-list { margin-top: 20px; }
                .price-list h3 { color: #444; margin-bottom: 10px; }
                .price-list p { margin: 4px 0; font-size: 14px; color: #555; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Booking Confirmed ðŸŽ‰</h2>
                <p>Hi <strong>{$name}</strong>,</p>
                <p>Your booking is confirmed for <strong>{$date}</strong> at <strong>{$time}</strong>.</p>
                <p>Please complete payment to finalize your booking:</p>
                <div class='btn-container'>
                    <a href='{$fullPaymentLink}' class='btn'>ðŸ’³ Full Payment</a>
                    <a href='{$partialPaymentLink}' class='btn btn-partial'>ðŸ’µ Partial Payment</a>
                </div>

                <div class='price-list'>
                    <h3>ðŸŽ¶ Yaka Crew Band â€“ Sample Price List</h3>
                    <p>ðŸ’ <strong>Weddings & Engagements</strong></p>
                    <p>Full Day Wedding Package (5â€“6 hrs) â€“ Rs. 250,000 â€“ 350,000</p>
                    <p>Half Day Wedding Package (3 hrs) â€“ Rs. 180,000 â€“ 220,000</p>
                    <p>Engagement / Homecoming â€“ Rs. 120,000 â€“ 180,000</p>

                    <p>ðŸŽ‰ <strong>Corporate Events & Private Parties</strong></p>
                    <p>Corporate Function (3â€“4 hrs) â€“ Rs. 200,000 â€“ 280,000</p>
                    <p>Birthday / Anniversary Party (2â€“3 hrs) â€“ Rs. 100,000 â€“ 150,000</p>

                    <p>ðŸŽ¤ <strong>Concerts & Festivals</strong></p>
                    <p>University / School Concerts â€“ Rs. 300,000 â€“ 500,000</p>
                    <p>Public Shows / Festivals â€“ Rs. 400,000+ (depending on scale & location)</p>

                    <p>ðŸ– <strong>Special / Themed Events</strong></p>
                    <p>Beach Parties / Club Nights â€“ Rs. 150,000 â€“ 250,000</p>
                    <p>Seasonal Shows (Xâ€™mas / New Yearâ€™s Eve) â€“ Rs. 200,000 â€“ 300,000</p>
                </div>

                <p>Thank you for choosing Yaka Crew Events.</p>
            </div>
        </body>
        </html>
        ";

        $mail->send();

    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/YCBooking_admin-Edit.css">
    <title>Edit Booking Information</title>
</head>
<body>
<div class="edit-box">
    <h2 class="edit-title">Edit Booking</h2>
    <div class="edit-underline"></div>
    <form method="post" autocomplete="off" class="edit-form-scroll">
        <?php if ($edit_success): ?>
            <div class="edit-success" id="edit-success-msg">Booking updated successfully!</div>
            <script>
                setTimeout(() => document.getElementById('edit-success-msg').style.display = 'none', 1500);
            </script>
        <?php endif; ?>
        <?php if ($edit_error): ?>
            <div class="edit-error"><?= $edit_error ?></div>
        <?php endif; ?>
        <?php if ($booking): ?>
        <div class="edit-field"><label class="edit-label" for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" class="edit-input" value="<?= htmlspecialchars($booking['first_name']) ?>" required>
        </div>
        <div class="edit-field"><label class="edit-label" for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" class="edit-input" value="<?= htmlspecialchars($booking['last_name']) ?>" required>
        </div>
        <div class="edit-field"><label class="edit-label" for="email">Email:</label>
            <input type="email" id="email" name="email" class="edit-input" value="<?= htmlspecialchars($booking['email']) ?>" required>
        </div>
        <div class="edit-field"><label class="edit-label" for="mobile_number">Mobile Number:</label>
            <input type="text" id="mobile_number" name="mobile_number" class="edit-input" value="<?= htmlspecialchars($booking['mobile_number']) ?>" required>
        </div>
        <div class="edit-field"><label class="edit-label" for="booking_date">Booking Date:</label>
            <input type="date" id="booking_date" name="booking_date" class="edit-input" value="<?= htmlspecialchars($booking['booking_date']) ?>" required>
        </div>
        <div class="edit-field"><label class="edit-label" for="booking_time">Booking Time:</label>
            <input type="time" id="booking_time" name="booking_time" class="edit-input" value="<?= htmlspecialchars($booking['booking_time']) ?>" required>
        </div>
        <div class="edit-field"><label class="edit-label" for="other_info">Other Info:</label>
            <input type="text" id="other_info" name="other_info" class="edit-input" value="<?= htmlspecialchars($booking['other_info']) ?>">
        </div>
        <div class="edit-field"><label class="edit-label" for="booking_status">Booking Status:</label>
            <select id="booking_status" name="booking_status" class="edit-select">
                <?php foreach (['Pending','Confirmed','Cancelled'] as $status): ?>
                    <option value="<?= $status ?>" <?= $booking['booking_status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="edit-field"><label class="edit-label" for="payment_status">Payment Status:</label>
            <select id="payment_status" name="payment_status" class="edit-select">
                <?php foreach (['Unpaid','Paid','Refunded'] as $status): ?>
                    <option value="<?= $status ?>" <?= $booking['payment_status'] === $status ? 'selected' : '' ?>><?= $status ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="edit-field"><label class="edit-label" for="admin_notes">Admin Notes:</label>
            <input type="text" id="admin_notes" name="admin_notes" class="edit-input" value="<?= htmlspecialchars($booking['admin_notes'] ?? '') ?>">
        </div>
        <div class="edit-actions">
            <button type="submit" class="edit-save">Save Changes</button>
            <a href="YCBooking_admin.php?page=bookings" class="edit-cancel">Cancel</a>
        </div>
    </form>
    <?php else: ?>
        <div class="edit-error">Booking not found.</div>
    <?php endif; ?>
</div>
</body>
</html>
