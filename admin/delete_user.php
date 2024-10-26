<?php
session_start();

if ($_SESSION['role'] != 'admin') {
    header("Location: ../user/login.php");
    exit();
}

include '../config/database.php';

// Check if ID is set and is a valid number
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    // Execute the statement
    if ($stmt->execute()) {
        // Successful deletion
        $_SESSION['message'] = "User deleted successfully.";
    } else {
        // Error occurred during deletion
        $_SESSION['message'] = "Error deleting user: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
} else {
    // Invalid ID
    $_SESSION['message'] = "Invalid user ID.";
}

// Redirect back to manage users page
header("Location: manage_users.php");
exit();
?>
