<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header("Location: ../user/login.php");
    exit();
}
include '../config/database.php';

$id = intval($_GET['id']);

// Delete event query
if ($conn->query("DELETE FROM events WHERE id = $id")) {
    header("Location: dashboard.php");
} else {
    echo "Error: " . $conn->error;
}
?>
