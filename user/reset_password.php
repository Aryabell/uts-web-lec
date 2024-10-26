<?php
// Include database connection
include '../config/database.php';

$error = "";
$success = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Cek apakah token ada dan belum kedaluwarsa di tabel password_resets
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $expires_at = $row['expires_at'];

        // Cek apakah token belum kedaluwarsa
        if (strtotime($expires_at) > time()) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $new_password = $_POST['password'];
                $confirm_password = $_POST['confirm_password'];

                // Validasi panjang password
                if (strlen($new_password) < 8) {
                    $error = "Password harus memiliki panjang minimal 8 karakter.";
                } elseif ($new_password === $confirm_password) {
                    // Hash password baru
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    // Update password di tabel users
                    $email = $row['email'];
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                    $stmt->bind_param("ss", $hashed_password, $email);
                    if ($stmt->execute()) {
                        $success = "Password berhasil diubah. Anda dapat <a href='login.php' class='text-indigo-600 hover:underline'>login</a> dengan password baru Anda.";

                        // Hapus token setelah digunakan
                        $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
                        $stmt->bind_param("s", $token);
                        $stmt->execute();
                    } else {
                        $error = "Failed to reset password, try again.";
                    }
                } else {
                    $error = "Password and confirm password are not the same.";
                }
            }
        } else {
            $error = "Token has expired. Please resubmit the password reset request.";
        }
    } else {
        $error = "Token is invalid or already used.";
    }
} else {
    $error = "Token not found.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #f3f4f6;
        }
    </style>
</head>

<body class="flex items-center justify-center h-screen bg-gray-100">

    <div class="bg-white p-8 rounded-lg shadow-lg max-w-sm w-full">
        <h2 class="text-2xl font-bold text-center mb-6">Reset Password</h2>

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
            <form action="" method="POST" class="space-y-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password :</label>
                    <input type="password" name="password" class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password :</label>
                    <input type="password" name="confirm_password" class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>

</body>

</html>