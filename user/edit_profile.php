<?php
session_start();
include '../config/database.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data pengguna dari sesi
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Ambil informasi pengguna dari database
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_info = $user_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data yang dikirim dari form
    $name = $_POST['name'];
    $email = $_POST['email'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    $error_message = '';
    $success_message = '';

    // Cek password saat ini jika diubah
    if (!empty($new_password)) {
        // Verifikasi password saat ini
        if (password_verify($current_password, $user_info['password'])) {
            // Hash password baru
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        } else {
            $error_message = "The current password is incorrect.";
        }
    }

    // Update data pengguna di database
    if (empty($error_message)) {
        $update_query = "UPDATE users SET name = ?, email = ?" . (!empty($new_password) ? ", password = ?" : "") . " WHERE id = ?";
        $stmt = $conn->prepare($update_query);

        if (!empty($new_password)) {
            $stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
        } else {
            $stmt->bind_param("ssi", $name, $email, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name; // Update session username
            $success_message = "Profile updated successfully.";
        } else {
            $error_message = "An error occurred while updating the profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Edit Profile</title>
</head>

<body class="bg-gray-100">

    <!-- Navbar -->
    <nav class="bg-gray-800 shadow-md">
        <div class="container mx-auto flex items-center justify-between p-4">
            <a class="text-2xl font-bold text-white hover:text-gray-200 transition duration-300" href="index.php">Eventify</a>
            <!-- Desktop menu -->
            <div class="hidden md:flex space-x-4 items-center">
                <a class="text-gray-200 hover:text-white transition duration-300" href="profile.php">
                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name); ?>
                </a>
                <a class="bg-green-500 text-white px-3 py-2 rounded hover:bg-green-600 transition duration-300" href="registered_events.php">
                    <i class="fas fa-calendar-check"></i> My Events
                </a>
                <a class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 transition duration-300" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            <!-- Burger button -->
            <div class="relative md:hidden">
                <button id="navbar-toggle" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        <!-- Mobile menu (hidden by default) -->
        <div id="navbar-menu" class="hidden md:hidden bg-gray-800">
            <a class="block px-4 py-2 text-gray-200 hover:bg-gray-700" href="profile.php">
                <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_name); ?>
            </a>
            <a class="block px-4 py-2 bg-green-500 text-white hover:bg-green-600" href="registered_events.php">
                <i class="fas fa-calendar-check"></i> My Events
            </a>
            <a class="block px-4 py-2 bg-red-500 text-white hover:bg-red-600" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container mx-auto mt-10 max-w-md">
        <h1 class="text-2xl font-bold mb-6">Edit Profile</h1>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white p-6 rounded-lg shadow">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name :</label>
                <input type="text" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" id="name" name="name" value="<?php echo htmlspecialchars($user_info['name']); ?>" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email :</label>
                <input type="email" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
            </div>
            <div class="mb-4">
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password :</label>
                <input type="password" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" id="current_password" name="current_password" required>
            </div>
            <div class="mb-4">
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password (leave blank if you don't want to change) :</label>
                <input type="password" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" id="new_password" name="new_password">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300">Perbarui Profil</button>
        </form>
        <div class="mt-3">
            <a href="profile.php" class="text-indigo-600 hover:underline">Return to Profile</a>
        </div>
    </div>

    <script>
        document.getElementById("navbar-toggle").onclick = function() {
            var menu = document.getElementById("navbar-menu");
            menu.classList.toggle("hidden");
        };
    </script>

</body>

</html>
