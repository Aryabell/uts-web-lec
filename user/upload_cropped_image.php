<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['croppedImage'])) {
    $file = $_FILES['croppedImage'];
    $filePath = '../profile/' . 'profile_' . $user_id . '.png';

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        $stmt->bind_param("si", $filePath, $user_id);
        $stmt->execute();
        header("Location: profile.php");
        exit();
    } else {
        echo "Failed to save cropped image.";
    }
}
?>
