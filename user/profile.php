<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include the database connection
include '../config/database.php';

// Ensure the database connection is established
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Retrieve user data from the session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? null;

// Fetch user data from the database
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $error = "User data not found.";
} else {
    // Fetch registration history for the user
    $stmt2 = $conn->prepare("SELECT events.* FROM registrations INNER JOIN events ON registrations.event_id = events.id WHERE registrations.user_id = ?");
    $stmt2->bind_param("i", $user['id']);
    $stmt2->execute();
    $registrations = $stmt2->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex items-center justify-center h-screen bg-gray-100">

    <div class="bg-white p-8 rounded-lg shadow-lg max-w-sm w-full">
        <h2 class="text-2xl font-bold text-center mb-6">Profile</h2>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 text-center p-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($user): ?>
            <div class="space-y-4">
                <!-- Display the profile picture if available -->
                <?php if (!empty($user['profile_picture'])): ?>
                    <div class="flex justify-center">
                        <a href="crop_image.php">
                            <div class="w-28 h-28 rounded-full border-4 border-black overflow-hidden transition-transform duration-300 transform hover:scale-110 active:scale-90 hover:shadow-lg">
                                <img src="../profile/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="w-full h-full object-cover">
                            </div>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="flex justify-center">
                        <a href="crop_image.php">
                            <div class="w-28 h-28 rounded-full border-4 border-black overflow-hidden transition-transform duration-300 transform hover:scale-110 active:scale-90 hover:shadow-lg">
                                <img src="../profile/default.png" alt="Default Profile Picture" class="w-full h-full object-cover">
                            </div>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Display user details -->
                <p><strong>Name :</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        <?php else: ?>
            <div class="bg-yellow-100 text-yellow-700 text-center p-3 rounded mb-4">
                <p>User data could not be loaded.</p>
            </div>
        <?php endif; ?>

        <div class="mt-6">
            <a href="edit_profile.php" class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300 text-center block">Edit Profile</a>
            <a href="registration_history.php" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 text-center block mt-2">View Events History</a>
            <a href="<?php echo ($_SESSION['role'] == 'admin') ? '../admin/dashboard.php' : 'index.php'; ?>" class="w-full bg-gray-600 text-white py-3 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 text-center block mt-2">
                Back to Home
            </a>
            <a href="logout.php" class="w-full bg-red-600 text-white py-3 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300 text-center block mt-2">Logout</a>
        </div>
    </div>

</body>

</html>