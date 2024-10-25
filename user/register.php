<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include database connection
include '../config/database.php';

// Initialize variables
$name = "";
$email = "";
$password = "";
$confirm_password = "";
$error = "";
$success = "";
$role = "user"; // Set default role to 'user'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password length
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Password and confirm password do not match!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email is already registered!";
        } else {
            // Hash password and save new user with role
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                // After successful registration, save user details in session
                $success = "Registration successful! Please log in.";
            } else {
                $error = "An error occurred during registration. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">

<div class="bg-white p-8 rounded-lg shadow-lg max-w-sm w-full">
    <h2 class="text-2xl font-bold text-center mb-6">Sign Up</h2>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 text-red-700 text-center p-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="bg-green-100 text-green-700 text-center p-3 rounded mb-4">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST" class="space-y-4" onsubmit="return validatePassword()">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name :</label>
            <input type="text" name="name" class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email :</label>
            <input type="email" name="email" class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password :</label>
            <input type="password" name="password" id="password" class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
        </div>

        <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password :</label>
            <input type="password" name="confirm_password" id="confirm_password" class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300">Sign Up</button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
        Already have an account? <a href="login.php" class="text-indigo-600 hover:underline">Login here</a>
    </p>
</div>

</body>
</html>
