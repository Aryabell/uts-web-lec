<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../user/login.php");
    exit();
}
include '../config/database.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_info = $user_result->fetch_assoc();
$profile_picture = $user_info['profile_picture'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = htmlspecialchars($_POST['location']);
    $max_participants = intval($_POST['max_participants']);
    $featured = isset($_POST['featured']) ? 1 : 0;

    // File upload handling
    $banner_image = $_FILES['banner_image']['name'];
    $banner_tmp = $_FILES['banner_image']['tmp_name'];
    $banner_size = $_FILES['banner_image']['size'];
    $banner_error = $_FILES['banner_image']['error'];

    // Check if file is an image
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
    $banner_ext = strtolower(pathinfo($banner_image, PATHINFO_EXTENSION));

    if (in_array($banner_ext, $allowed_ext) && $banner_error === 0 && $banner_size <= 2000000) {
        $banner_new_name = uniqid('', true) . "." . $banner_ext;
        if (!is_dir('../uploads')) {
            mkdir('../uploads', 0777, true);
        }
        if (move_uploaded_file($banner_tmp, "../uploads/$banner_new_name")) {
            $stmt = $conn->prepare("INSERT INTO events (name, description, event_date, event_time, location, max_participants, banner_image, featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open')");
            $stmt->bind_param("sssssisi", $name, $description, $event_date, $event_time, $location, $max_participants, $banner_new_name, $featured);

            if ($stmt->execute()) {
                header("Location: dashboard.php");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "Error moving file.";
        }
    } else {
        echo "Invalid file type or size too large.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-gray-800 shadow-md">
        <div class="container mx-auto flex items-center justify-between p-4">
            <a class="text-2xl font-bold text-white hover:text-gray-200 transition duration-300" href="dashboard.php">Eventify</a>
            <!-- Desktop menu -->
            <div class="hidden md:flex space-x-4 items-center">
                <a class="text-gray-200 hover:text-white transition duration-300 flex items-center space-x-2" href="../user/profile.php">
                    <?php if (!empty($user_info['profile_picture'])): ?>
                        <img src="../profile/<?php echo htmlspecialchars($user_info['profile_picture']); ?>" alt="Profile Picture" class="w-8 h-8 rounded-full">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($user_name); ?></span>
                </a>
                <a class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 transition duration-300" href="../user/logout.php">
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
            <a class="block px-4 py-2 text-gray-200 hover:bg-gray-700 flex items-center space-x-2" href="../user/profile.php">
                <?php if (!empty($user_info['profile_picture'])): ?>
                    <img src="../profile/<?php echo htmlspecialchars($user_info['profile_picture']); ?>" alt="Profile Picture" class="w-8 h-8 rounded-full">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($user_name); ?></span>
            </a>
            <a class="block px-4 py-2 bg-red-500 text-white hover:bg-red-600" href="../user/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container mx-auto my-10 px-4">
        <h1 class="text-4xl font-bold text-center mb-8 text-blue-700">Create New Event</h1>

        <div class="bg-white p-6 rounded-lg shadow-lg">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Event Name</label>
                    <input type="text" name="name" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required></textarea>
                </div>
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label for="event_date" class="block text-sm font-medium text-gray-700">Event Date</label>
                        <input type="date" name="event_date" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div class="flex-1">
                        <label for="event_time" class="block text-sm font-medium text-gray-700">Event Time</label>
                        <input type="time" name="event_time" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="max_participants" class="block text-sm font-medium text-gray-700">Max Participants</label>
                    <input type="number" name="max_participants" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label for="banner_image" class="block text-sm font-medium text-gray-700">Event Banner</label>
                    <input type="file" name="banner_image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border file:border-gray-300 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="featured" id="featured" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="featured" class="ml-2 block text-sm text-gray-900">Feature this event</label>
                </div>
                <div class="flex space-x-4 mt-4">
                    <a href="dashboard.php" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-all">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-all">Create Event</button>
                </div>
            </form>
        </div>
    </div>
</body>
<script>
    document.getElementById("navbar-toggle").onclick = function() {
        var menu = document.getElementById("navbar-menu");
        menu.classList.toggle("hidden");
    };
</script>
</html>
