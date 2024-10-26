<?php
session_start();

if ($_SESSION['role'] != 'admin') {
    header("Location: ../user/login.php");
    exit();
}
include '../config/database.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_info = $user_result->fetch_assoc();
$profile_picture = $user_info['profile_picture'];

// Check if event_id is set and valid
if (isset($_GET['event_id']) && is_numeric($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);

    // Get event details
    $event_query = $conn->query("SELECT * FROM events WHERE id = $event_id");

    if ($event_query->num_rows > 0) {
        $event = $event_query->fetch_assoc();

        // Fetch registrants for the event
        $registrants = $conn->query("SELECT users.name, users.email 
                                     FROM registrations 
                                     INNER JOIN users ON registrations.user_id = users.id 
                                     WHERE registrations.event_id = $event_id");

        // Export to CSV logic using PhpSpreadsheet
        if (isset($_GET['export']) && $_GET['export'] == 'csv') {
            // Create a new Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set header row
            $sheet->setCellValue('A1', 'Name');
            $sheet->setCellValue('B1', 'Email');
            $sheet->setCellValue('C1', 'Event Name');

            // Fetch registrants and populate rows
            $row = 2; // Start from the second row after headers
            while ($registrant = $registrants->fetch_assoc()) {
                $sheet->setCellValue('A' . $row, $registrant['name']);
                $sheet->setCellValue('B' . $row, $registrant['email']);
                $sheet->setCellValue('C' . $row, $event['name']);
                $row++;
            }

            // Output as CSV file
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment;filename="registrants_' . $event['name'] . '.csv"');
            header('Cache-Control: max-age=0');

            $writer = new Csv($spreadsheet);
            $writer->save('php://output');
            exit();
        }
    } else {
        $error_message = "Event not found.";
    }
} else {
    $error_message = "Invalid event ID.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>View Registrations</title>
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
        <h1 class="text-center text-2xl sm:text-3xl font-bold mb-6">Registrants for <?= $event['name'] ?></h1>

        <div class="bg-white shadow-md rounded-lg p-4 overflow-x-auto">
            <table class="min-w-full bg-white divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Event Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Banner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($registrant = $registrants->fetch_assoc()) { ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-800"><?= $registrant['name'] ?></td>
                            <td class="px-6 py-4 text-sm text-gray-800"><?= $registrant['email'] ?></td>
                            <td class="px-6 py-4 text-sm text-gray-800"><?= $event['name'] ?></td>
                            <td class="px-6 py-4">
                                <img src="../uploads/<?php echo htmlspecialchars($event['banner_image']); ?>" alt="Banner" class="w-32 h-24 object-cover rounded-lg mx-auto">
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap space-y-4 md:space-y-0 md:space-x-4 mt-4">
            <a href="dashboard.php" class="w-full md:w-auto px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-all">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="view_registrations.php?event_id=<?= $event_id ?>&export=csv" class="w-full md:w-auto px-6 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-all">
                Export to CSV
            </a>
        </div>
</body>
<script>
    document.getElementById("navbar-toggle").onclick = function() {
        var menu = document.getElementById("navbar-menu");
        menu.classList.toggle("hidden");
    };
</script>

</html>