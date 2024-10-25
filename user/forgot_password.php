<?php
// Include database connection
include '../config/database.php';
require '../vendor/autoload.php'; // Pastikan path ini sesuai dengan lokasi PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Cek apakah email ada di tabel users
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate token
        $token = bin2hex(random_bytes(50)); // Token unik

        // Set expiration time (1 hour from now)
        $expires_at = date("Y-m-d H:i:s", strtotime('+15 minutes'));

        // Insert token into password_resets table
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires_at);
        $stmt->execute();

        // Link reset password
        $reset_link = "http://localhost:8000/user/reset_password.php?token=" . $token;

        // Konfigurasi pengiriman email menggunakan PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Host SMTP Anda
            $mail->SMTPAuth = true;
            $mail->Username = 'eventify.noreplys@gmail.com'; // Email SMTP Anda
            $mail->Password = 'hprd peco syck cldj'; // Password SMTP Anda
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // Penerima dan konten email
            $mail->setFrom('noreply@example.com', 'Eventify');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Eventify: Reset Your Password Request';
            $mail->Body = "
            <html>
            <body>
            <p>Hello,</p>
            
            <p>We received a request to reset the password for your Eventify account. Please use the link below to reset your password:</p>
            
            <p><a href='$reset_link'>Reset Password Link</a></p>
            
            <p>This link will expire in 15 minutes. If you did not request a password reset, please ignore this email.</p>
            
            <p>Thank you,<br>
            The Eventify Team
            </body>
            </html>
            ";
            $mail->isHTML(true);

            $mail->send();
            $success = "Password reset link has been sent to your email.";
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #f3f4f6;
        }
    </style>
</head>

<body class="flex items-center justify-center h-screen bg-gray-100">

    <div class="bg-white p-8 rounded-lg shadow-lg max-w-sm w-full">
        <h2 class="text-2xl font-bold text-center mb-6">Forgot Password</h2>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 text-center p-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 text-green-700 text-center p-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php else: ?>
            <form action="forgot_password.php" method="POST" class="space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email :</label>
                    <input type="email" name="email" class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300">Send Reset Link</button>
            </form>
        <?php endif; ?>

        <p class="mt-4 text-center text-sm text-gray-600">
            Remembered your password? <a href="login.php" class="text-indigo-600 hover:underline">Login here</a>
        </p>
    </div>

</body>

</html>