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

$id = $_GET['id'];
$event = $conn->query("SELECT * FROM events WHERE id = $id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = htmlspecialchars($_POST['location']);
    $max_participants = intval($_POST['max_participants']);
    $status = $_POST['status'];
    $featured = isset($_POST['featured']) ? 1 : 0;

    // Banner image handling
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === 0) {
        $banner_image = $_FILES['banner_image']['name'];
        $banner_tmp = $_FILES['banner_image']['tmp_name'];
        $banner_size = $_FILES['banner_image']['size'];
        $banner_ext = strtolower(pathinfo($banner_image, PATHINFO_EXTENSION));

        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($banner_ext, $allowed_ext) && $banner_size <= 2000000) {
            $banner_new_name = uniqid('', true) . "." . $banner_ext;
            if (!is_dir('../uploads')) {
                mkdir('../uploads', 0777, true);
            }
            if (move_uploaded_file($banner_tmp, "../uploads/$banner_new_name")) {
                // Remove old banner image if exists
                if (!empty($event['banner_image']) && file_exists("../uploads/" . $event['banner_image'])) {
                    unlink("../uploads/" . $event['banner_image']);
                }
                // Update banner image in the database
                $stmt = $conn->prepare("UPDATE events SET banner_image = ? WHERE id = ?");
                $stmt->bind_param("si", $banner_new_name, $id);
                $stmt->execute();
            } else {
                echo "Error moving file.";
            }
        } else {
            echo "Invalid file type or size too large.";
        }
    }

    // Update event details
    $stmt = $conn->prepare("UPDATE events SET name = ?, description = ?, event_date = ?, event_time = ?, location = ?, max_participants = ?, status = ?, featured = ? WHERE id = ?");
    $stmt->bind_param("sssssisii", $name, $description, $event_date, $event_time, $location, $max_participants, $status, $featured, $id);

    if ($stmt->execute()) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
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
        <h1 class="text-4xl font-bold text-center mb-8 text-blue-700">Edit Event</h1>

        <div class="bg-white p-6 rounded-lg shadow-lg">
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Event Name</label>
                    <input type="text" name="name" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="<?= $event['name'] ?>" required>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required><?= $event['description'] ?></textarea>
                </div>

                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label for="event_date" class="block text-sm font-medium text-gray-700">Event Date</label>
                        <input type="date" name="event_date" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="<?= $event['event_date'] ?>" required>
                    </div>
                    <div class="flex-1">
                        <label for="event_time" class="block text-sm font-medium text-gray-700">Event Time</label>
                        <input type="time" name="event_time" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="<?= $event['event_time'] ?>" required>
                    </div>
                </div>

                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="<?= $event['location'] ?>" required>
                </div>

                <div>
                    <label for="max_participants" class="block text-sm font-medium text-gray-700">Max Participants</label>
                    <input type="number" name="max_participants" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="<?= $event['max_participants'] ?>" required>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Event Status</label>
                    <select name="status" class="mt-1 p-2 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="open" <?= $event['status'] == 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="closed" <?= $event['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="canceled" <?= $event['status'] == 'canceled' ? 'selected' : '' ?>>Canceled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Banner</label>
                    <?php if (!empty($event['banner_image'])): ?>
                        <img src="../uploads/<?= $event['banner_image'] ?>" alt="Event Banner" class="mt-2 h-32 object-cover">
                    <?php endif; ?>
                </div>

                <div>
                    <label for="banner_image" class="block text-sm font-medium text-gray-700">Upload New Banner (optional)</label>
                    <input type="file" name="banner_image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="featured" id="featured" <?= $event['featured'] ? 'checked' : '' ?> class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="featured" class="ml-2 block text-sm text-gray-700">Feature this event</label>
                </div>

                <div class="flex space-x-4 mt-4">
                    <a href="dashboard.php" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-all">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="submit" class="px-6 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-all">Edit Event</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('navbar-toggle').addEventListener('click', function() {
            var menu = document.getElementById('navbar-menu');
            menu.classList.toggle('hidden');
        });
    </script>
</body>

</html>