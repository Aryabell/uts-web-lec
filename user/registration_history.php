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

// Fetch archived event history for the user
$stmt = $conn->prepare("
    SELECT events.name, events.event_date, archived_events.status, archived_events.created_at
    FROM archived_events
    INNER JOIN events ON archived_events.event_id = events.id 
    WHERE archived_events.user_id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$archived_events = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events History</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex items-center justify-center h-screen bg-gray-100">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
        <h2 class="text-2xl font-bold text-center mb-6">Events History</h2>

        <?php if ($archived_events->num_rows > 0): ?>
            <ul class="space-y-2">
                <?php while ($event = $archived_events->fetch_assoc()): ?>
                    <li class="bg-gray-100 p-3 rounded-lg">
                        <strong>Event :</strong> <?php echo htmlspecialchars($event['name']); ?> <br>
                        <strong>Date :</strong> <?php echo htmlspecialchars($event['event_date']); ?><br>
                        <strong>Canceled Time :</strong> <?php echo htmlspecialchars($event['created_at']); ?><br>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <div class="bg-yellow-100 text-yellow-700 text-center p-3 rounded mb-4">
                <p>No events history found.</p>
            </div>
        <?php endif; ?>

        <div class="mt-6">
            <a href="profile.php" class="w-full bg-gray-600 text-white py-3 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 text-center block">Back to Profile</a>
        </div>
    </div>
</body>

</html>
