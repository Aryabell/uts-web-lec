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

// Fetch all users
$users = $conn->query("SELECT * FROM users WHERE role != 'admin'");

// Fetch all events related to users
$events = $conn->query("SELECT users.id as user_id, events.name as event_name, events.banner_image 
    FROM registrations 
    INNER JOIN events ON registrations.event_id = events.id 
    INNER JOIN users ON registrations.user_id = users.id");

$events_per_user = [];
while ($event = $events->fetch_assoc()) {
    $events_per_user[$event['user_id']][] = $event;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Manage Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <h1 class="text-center text-4xl font-bold mb-10">
            <i class="fas fa-users"></i> Manage Users
        </h1>

        <div class="bg-white shadow-md rounded-lg p-6 overflow-x-auto">
            <table class="min-w-full bg-white divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">
                            <i class="fas fa-user"></i> Name
                        </th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">
                            <i class="fas fa-envelope"></i> Email
                        </th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">
                            <i class="fas fa-calendar"></i> Event
                        </th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">
                            <i class="fas fa-cog"></i> Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($user = $users->fetch_assoc()) { ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-800">
                                <?= htmlspecialchars($user['name']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800">
                                <?= htmlspecialchars($user['email']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800">
                                <?php if (isset($events_per_user[$user['id']])) {
                                    foreach ($events_per_user[$user['id']] as $event) { ?>
                                        <p><?= htmlspecialchars($event['event_name']) ?></p>
                                    <?php }
                                } else { ?>
                                    <p>No event registered</p>
                                <?php } ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="delete_user.php?id=<?= $user['id'] ?>" class="text-red-600 hover:text-red-800 font-medium" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <a href="dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

</body>
<script>
    document.getElementById("navbar-toggle").onclick = function () {
        var menu = document.getElementById("navbar-menu");
        menu.classList.toggle("hidden");
    };
</script>

</html>
