<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include koneksi database
include '../config/database.php';

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Ambil event_id dari parameter GET
$event_id = $_GET['event_id'];

// Verifikasi apakah pengguna terdaftar untuk event tersebut
$registration = $conn->query("SELECT * FROM registrations WHERE user_id = $user_id AND event_id = $event_id")->fetch_assoc();

if ($registration) {
    // Masukkan data ke tabel archived_events
    $stmt_archive = $conn->prepare("INSERT INTO archived_events (user_id, event_id) VALUES (?, ?)");
    $stmt_archive->bind_param("ii", $user_id, $event_id);
    
    if ($stmt_archive->execute()) {
        // Batalkan pendaftaran setelah data berhasil diarsipkan
        $stmt_delete = $conn->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
        $stmt_delete->bind_param("ii", $user_id, $event_id);
        
        if ($stmt_delete->execute()) {
            $_SESSION['success'] = "You have successfully canceled your registration for this event.";
        } else {
            $_SESSION['error'] = "An error occurred while canceling the registration.";
        }
    } else {
        $_SESSION['error'] = "An error occurred while archiving registration data.";
    }
} else {
    $_SESSION['error'] = "You are not registered for this event.";
}

// Arahkan kembali ke daftar acara yang sudah didaftarkan
header("Location: registered_events.php");
exit();
?>
