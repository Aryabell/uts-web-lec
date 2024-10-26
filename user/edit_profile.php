<?php
session_start();
include '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data from session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch user information from database
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_info = $user_result->fetch_assoc();

$current_password_hash = $user_info['password']; // Fetch the current password hash
$user_role = $user_info['role']; 

// Only allow profile picture edit for admin
$is_admin = $user_role === 'admin';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $profile_picture = $user_info['profile_picture']; // default if no changes
    $error_message = '';
    $success_message = '';

    if (!$is_admin) {
        // Get form data for non-admin users
        $name = $_POST['name'];
        $email = $_POST['email'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];

        if (!password_verify($current_password, $current_password_hash)) {
            $error_message = "The current password is incorrect.";
        }

        // Verify and hash new password if provided
        if (!empty($new_password)) {
            if (strlen($new_password) < 8) {
                $error_message = "The new password must be at least 8 characters long.";
            } elseif (password_verify($current_password, $current_password_hash)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                $error_message = "The current password is incorrect.";
            }
        }
    }

    // Process profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../profile/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($check !== false) {
            if ($_FILES["profile_picture"]["size"] <= 2000000) {
                if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $new_filename = $user_id . "_" . time() . "." . $imageFileType;
                    $target_file = $target_dir . $new_filename;

                    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                        $profile_picture = $new_filename;
                    } else {
                        $error_message = "Failed to upload the image.";
                    }
                } else {
                    $error_message = "Only JPG, JPEG, PNG & GIF files are allowed.";
                }
            } else {
                $error_message = "The image size should not exceed 2MB.";
            }
        } else {
            $error_message = "The uploaded file is not an image.";
        }
    }

    // Update user data in the database if no errors
    if (empty($error_message)) {
        $update_query = "UPDATE users SET profile_picture = ?" . (!$is_admin ? ", name = ?, email = ?" . (!empty($new_password) ? ", password = ?" : "") : "") . " WHERE id = ?";
        $stmt = $conn->prepare($update_query);

        if (!$is_admin) {
            if (!empty($new_password) && strlen($new_password) >= 8) {
                $stmt->bind_param("ssssi", $profile_picture, $name, $email, $hashed_password, $user_id);
            } else {
                $stmt->bind_param("sssi", $profile_picture, $name, $email, $user_id);
            }
        } else {
            $stmt->bind_param("si", $profile_picture, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['user_name'] = $name ?? $_SESSION['user_name']; // Update session name if set
            $success_message = "Profile updated successfully.";
        } else {
            $error_message = "An error occurred while updating the profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Edit Profile</title>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto mt-10 max-w-md">
        <h1 class="text-2xl font-bold mb-6">Edit Profile</h1>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <form id="profile-form" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700">Name :</label>
                <input type="text" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" id="name" name="name" value="<?php echo htmlspecialchars($user_info['name']); ?>" <?php echo $is_admin ? 'disabled' : ''; ?>>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email :</label>
                <input type="email" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" <?php echo $is_admin ? 'disabled' : ''; ?>>
            </div>
            <div class="mb-4">
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password :</label>
                <input type="password" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" id="current_password" name="current_password" <?php echo $is_admin ? 'disabled' : 'required'; ?>>
            </div>
            <div class="mb-4">
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password (leave blank if you don't want to change) :</label>
                <input type="password" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" id="new_password" name="new_password" <?php echo $is_admin ? 'disabled' : ''; ?>>
            </div>
            <div class="mb-4">
                <label for="profile_picture" class="block text-sm font-medium text-gray-700">Profile Picture</label>
                <input type="file" class="mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" id="profile_picture" name="profile_picture" accept="image/*">
                <?php if (!empty($user_info['profile_picture'])): ?>
                    <div class="flex justify-center mt-4">
                        <img src="../profile/<?php echo htmlspecialchars($user_info['profile_picture']); ?>" alt="Profile Picture" class="w-24 h-24 object-cover rounded-full">
                    </div>
                <?php endif; ?>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300">Update Profile</button>
        </form>
        <div class="mt-3">
            <a href="profile.php" class="text-indigo-600 hover:underline">Return to Profile</a>
        </div>
    </div>
</body>

</html>

<script>
    document.getElementById("navbar-toggle").onclick = function() {
        var menu = document.getElementById("navbar-menu");
        menu.classList.toggle("hidden");
    };
</script>

</body>

</html>