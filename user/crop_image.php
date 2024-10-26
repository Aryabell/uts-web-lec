<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Mengambil gambar profil pengguna dari database
include '../config/database.php';
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

$profile_picture = $user['profile_picture'] ?? 'default.png';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Profile Picture</title>
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">

    <div class="bg-white p-8 rounded-lg shadow-lg max-w-lg w-full">
        <h2 class="text-2xl font-bold text-center mb-6">Crop Profile Picture</h2>

        <div class="flex justify-center mb-4">
            <div class="w-64 h-64 rounded-full border-4 border-black overflow-hidden">
                <img id="image" src="../profile/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="object-cover w-full h-full">
            </div>
        </div>

        <div class="flex justify-center gap-4 mt-4">
            <button id="cropButton" class="bg-indigo-600 text-white py-2 px-6 rounded hover:bg-indigo-700">Crop & Save</button>
            <a href="profile.php" class="bg-gray-600 text-white py-2 px-6 rounded hover:bg-gray-700">Cancel</a>
        </div>
    </div>

    <script>
        const image = document.getElementById('image');
        const cropper = new Cropper(image, {
            aspectRatio: 1,
            viewMode: 1,
            autoCropArea: 1,
            responsive: true
        });

        document.getElementById('cropButton').addEventListener('click', () => {
            const canvas = cropper.getCroppedCanvas({
                width: 300,
                height: 300,
            });

            canvas.toBlob(blob => {
                const formData = new FormData();
                formData.append('croppedImage', blob);

                fetch('upload_cropped_image.php', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (response.ok) {
                        window.location.href = 'profile.php';
                    } else {
                        alert('Failed to upload cropped image.');
                    }
                });
            });
        });
    </script>
</body>
</html>
