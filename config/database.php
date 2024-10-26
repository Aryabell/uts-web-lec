<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "event_registration_system";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password);

// Mengecek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Membuat database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
$conn->query($sql);

// Menghubungkan ke database
$conn->select_db($dbname);

// Membuat tabel registrations
$sql = "CREATE TABLE IF NOT EXISTS registrations (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    event_id INT(11) NOT NULL,
    status ENUM('active', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Membuat tabel users
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user' NOT NULL
)";
$conn->query($sql);

// Membuat tabel events
$sql = "CREATE TABLE IF NOT EXISTS events (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(100) NOT NULL,
    description TEXT NULL,
    max_participants INT(11) NOT NULL,
    banner_image VARCHAR(255) NULL,
    featured TINYINT(1) DEFAULT 0,
    status ENUM('open', 'closed', 'canceled') DEFAULT 'open',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Membuat tabel events
$sql = "CREATE TABLE IF NOT EXISTS archived_events (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    event_id INT(11) NOT NULL,
    status ENUM('active', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Membuat tabel password_resets
$sql = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

// Menambahkan user admin jika belum ada
$admin_email = 'admin@admin.com';
$admin_name = 'admin';
$admin_password = '$2a$10$vj7UijGHoYH.daN1JgIVyeta0UmiuR0k.lrUfrWabs5uVvRzgWCgu'; // password yang sudah di-hash
$check_admin_query = "SELECT * FROM users WHERE role = 'admin'";
$result = $conn->query($check_admin_query);

// Jika tidak ada admin dalam tabel users, tambahkan admin baru
if ($result->num_rows == 0) {
    $insert_admin_query = "INSERT INTO users (name, email, password, role) VALUES ('$admin_name', '$admin_email', '$admin_password', 'admin')";
    $conn->query($insert_admin_query);
}

?>

