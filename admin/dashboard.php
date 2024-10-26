<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../user/login.php");
    exit();
}
include '../config/database.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$events = $conn->query("SELECT * FROM events");

$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_info = $user_result->fetch_assoc();
$profile_picture = $user_info['profile_picture'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

    <!-- Dashboard Content -->
    <div class="container mx-auto my-10 px-4 sm:px-6 lg:px-8">
        <h1 class="text-center text-4xl font-bold mb-10 text-gray-800">
            <i class="fas fa-chart-line"></i> Admin Dashboard
        </h1>

        <!-- Event Management -->
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-4 text-center">
                <i class="fas fa-calendar-alt"></i> Event Management
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white divide-y divide-gray-200 rounded-md shadow-lg">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Banner</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Event Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Max Participants</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Registrants</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Featured</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php while ($event = $events->fetch_assoc()) {
                            $event_id = $event['id'];
                            $registrants = $conn->query("SELECT COUNT(*) as total FROM registrations WHERE event_id = $event_id");
                            $total_registrants = $registrants->fetch_assoc()['total'];
                        ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <img src="../uploads/<?php echo htmlspecialchars($event['banner_image']); ?>" alt="Banner" class="w-32 h-32 object-fit rounded-lg">
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800"><?= htmlspecialchars($event['name']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-800"><?= htmlspecialchars($event['max_participants']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-800"><?= htmlspecialchars($total_registrants) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-800"><?= ucfirst(htmlspecialchars($event['status'])) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-800">
                                    <?php if ($event['featured'] == 1): ?>
                                        <span class="text-blue-600 font-medium transition-all duration-300">
                                            <i class="fas fa-star"></i> Featured
                                        </span>
                                    <?php else: ?>
                                        <span class="text-gray-500 font-medium transition-all duration-300">
                                            <i class="fas fa-star"></i> Not Featured
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <a href="view_registrations.php?event_id=<?= $event['id'] ?>" class="text-yellow-600 hover:text-yellow-800 font-medium transition-all duration-300">
                                        <i class="fas fa-eye"></i> View
                                    </a> |
                                    <a href="edit_event.php?id=<?= $event['id'] ?>" class="text-blue-600 hover:text-blue-800 font-medium transition-all duration-300">
                                        <i class="fas fa-edit"></i> Edit
                                    </a> |
                                    <button onclick="confirmDelete('<?= $event['id'] ?>')" class="text-red-600 hover:text-red-800 font-medium transition-all duration-300">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <a href="create_event.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-all duration-300">
                    <i class="fas fa-plus"></i> Create New Event
                </a>
            </div>
        </div>

        <!-- User Management -->
        <div class="mt-10">
            <h2 class="text-2xl font-semibold mb-4">
                <i class="fas fa-users"></i> User Management
            </h2>
            <a href="manage_users.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-all duration-300">
                <i class="fas fa-user-cog"></i> View and Manage Users
            </a>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm">
            <h2 class="text-xl font-semibold mb-4">Are you sure?</h2>
            <p class="text-gray-700 mb-6">Do you really want to delete this event? This action cannot be undone.</p>
            <div class="flex justify-end space-x-4">
                <button onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cancel</button>
                <a id="confirmDeleteLink" href="#" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">Delete</a>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(eventId) {
            document.getElementById('confirmDeleteLink').setAttribute('href', 'delete_event.php?id=' + eventId);
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Toggle navbar menu on mobile
        document.getElementById("navbar-toggle").addEventListener("click", function() {
            var menu = document.getElementById("navbar-menu");
            menu.classList.toggle("hidden");
        });
    </script>
</body>

</html>
