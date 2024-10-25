<?php
session_start();
include '../config/database.php';

// Cek apakah pengguna sudah login sebagai admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $max_participants = $_POST['max_participants'];
    $banner = ""; // Variabel untuk menyimpan nama file banner

    // Logika upload gambar
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] == 0) {
        $target_dir = "../uploads/";
        $original_file_name = basename($_FILES["banner"]["name"]);
        $file_extension = strtolower(pathinfo($original_file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid() . '.' . $file_extension; // Generate nama file unik
        $target_file = $target_dir . $new_file_name;

        // Cek apakah file adalah gambar
        $check = getimagesize($_FILES["banner"]["tmp_name"]);
        if ($check !== false) {
            // Pindahkan file yang di-upload ke folder uploads
            if (move_uploaded_file($_FILES["banner"]["tmp_name"], $target_file)) {
                $banner = $new_file_name; // Simpan nama file gambar untuk dimasukkan ke database
            } else {
                echo "Terjadi kesalahan saat mengunggah gambar.";
            }
        } else {
            echo "File yang diunggah bukan gambar.";
        }
    }

    // Simpan event baru ke database, termasuk gambar jika ada
    $stmt = $conn->prepare("INSERT INTO events (name, event_date, event_time, location, description, max_participants, banner_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $event_name, $event_date, $event_time, $location, $description, $max_participants, $banner_image);

    if ($stmt->execute()) {
        echo "Event berhasil dibuat!";
    } else {
        echo "Terjadi kesalahan saat menyimpan event.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Event Baru</title>
</head>
<body>
    <div class="container mt-5">
        <h2>Buat Event Baru</h2>
        <form action="create_event.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="event_name" class="form-label">Nama Event</label>
                <input type="text" class="form-control" id="event_name" name="event_name" required>
            </div>
            <div class="mb-3">
                <label for="event_date" class="form-label">Tanggal Event</label>
                <input type="date" class="form-control" id="event_date" name="event_date" required>
            </div>
            <div class="mb-3">
                <label for="event_time" class="form-label">Waktu Event</label>
                <input type="time" class="form-control" id="event_time" name="event_time" required>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Lokasi</label>
                <input type="text" class="form-control" id="location" name="location" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
            </div>
            <div class="mb-3">
                <label for="max_participants" class="form-label">Maks. Peserta</label>
                <input type="number" class="form-control" id="max_participants" name="max_participants" required>
            </div>
            <div class="mb-3">
                <label for="banner" class="form-label">Banner Gambar (opsional)</label>
                <input type="file" class="form-control" id="banner" name="banner" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Buat Event</button>
        </form>
        <a href="index.php" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</body>
</html>
