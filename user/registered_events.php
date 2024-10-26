<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include '../config/database.php';

$user_id = intval($_SESSION['user_id']); // Cast to integer to prevent SQL injection
$user_name = $_SESSION['user_name'];

$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_info = $user_result->fetch_assoc();
$profile_picture = $user_info['profile_picture'];

// Prepare the SQL query to prevent SQL injection
$stmt = $conn->prepare("SELECT events.* FROM registrations INNER JOIN events ON registrations.event_id = events.id WHERE registrations.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$registrations = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Registered Events</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive viewport -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-gray-800 shadow-md">
        <div class="container mx-auto flex items-center justify-between p-4">
            <a class="text-2xl font-bold text-white hover:text-gray-200 transition duration-300" href="index.php">Eventify</a>
            <!-- Desktop menu -->
            <div class="hidden md:flex space-x-4 items-center">
                <a class="text-gray-200 hover:text-white transition duration-300 flex items-center space-x-2" href="profile.php">
                    <?php if (!empty($user_info['profile_picture'])): ?>
                        <img src="../profile/<?php echo htmlspecialchars($user_info['profile_picture']); ?>" alt="Profile Picture" class="w-8 h-8 rounded-full">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($user_name); ?></span>
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
            <a class="block px-4 py-2 text-gray-200 hover:bg-gray-700 flex items-center space-x-2" href="profile.php">
                <?php if (!empty($user_info['profile_picture'])): ?>
                    <img src="../profile/<?php echo htmlspecialchars($user_info['profile_picture']); ?>" alt="Profile Picture" class="w-8 h-8 rounded-full">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($user_name); ?></span>
            </a>
            <a class="block px-4 py-2 bg-green-500 text-white hover:bg-green-600" href="registered_events.php">
                <i class="fas fa-calendar-check"></i> My Events
            </a>
            <a class="block px-4 py-2 bg-red-500 text-white hover:bg-red-600" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container mx-auto my-10 px-4">
        <h1 class="text-center text-3xl font-bold mb-6">
            <i class="fas fa-list-alt"></i> Your Registered Events
        </h1>

        <!-- Back Button -->
        <div class="mb-4">
            <a href="index.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow-md">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="py-3 px-4"><i class="fas fa-calendar"></i> Event Name</th>
                        <th class="py-3 px-4"><i class="fas fa-info-circle"></i> Description</th>
                        <th class="py-3 px-4"><i class="fas fa-calendar-day"></i> Date</th>
                        <th class="py-3 px-4"><i class="fas fa-clock"></i> Time</th>
                        <th class="py-3 px-4"><i class="fas fa-map-marker-alt"></i> Location</th>
                        <th class="py-3 px-4"><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($event = $registrations->fetch_assoc()) { ?>
                        <tr class="border-b">
                            <td class="py-3 px-4"><?= htmlspecialchars($event['name']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($event['description']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($event['event_date']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($event['event_time']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($event['location']) ?></td>
                            <td class="py-3 px-4">
                                <button onclick="openModal(<?= $event['id'] ?>)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                    <i class="fas fa-times-circle"></i> Cancel
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Confirmation -->
    <div id="modal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h2 class="text-xl font-bold mb-4">Confirm Cancellation</h2>
            <p>Are you sure you want to cancel this event registration?</p>
            <div class="mt-6 flex justify-end space-x-4">
                <button onclick="closeModal()" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
                    No, Keep it
                </button>
                <a id="confirm-cancel" href="#" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    Yes, Cancel
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("navbar-toggle").onclick = function() {
            var menu = document.getElementById("navbar-menu");
            menu.classList.toggle("hidden");
        };

        function openModal(eventId) {
            const modal = document.getElementById('modal');
            const confirmCancel = document.getElementById('confirm-cancel');
            confirmCancel.href = 'cancel_registration.php?event_id=' + eventId;
            modal.classList.remove('hidden');
        }

        function closeModal() {
            const modal = document.getElementById('modal');
            modal.classList.add('hidden');
        }
    </script>
</body>

</html>