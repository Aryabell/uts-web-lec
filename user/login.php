<?php
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include database connection
include '../config/database.php';

// Initialize variables
$email = "";
$password = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check user
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if email is found
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session user_id
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name']; 
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['success'] = "Login successful!";
            
            if ($user['role'] == 'admin') {
                header("Location: ../admin/dashboard.php");  
            } else {
                header("Location: index.php"); 
            }
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="flex items-center justify-center h-screen bg-gray-100">

<div class="bg-white p-8 rounded-lg shadow-lg max-w-sm w-full">
    <h2 class="text-2xl font-bold text-center mb-6">Login</h2>

    <?php if (!empty($error)): ?>
        <div class="bg-red-100 text-red-700 text-center p-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="space-y-4">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email :</label>
            <input type="email" name="email" class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password :</label>
            <input type="password" name="password" class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
        </div>

        <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300">Login</button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
        Don't have an account? <a href="register.php" class="text-indigo-600 hover:underline">Sign up here</a>
    </p>

    <p class="mt-4 text-center text-sm text-gray-600">
        Forgot your password? <a href="forgot_password.php" class="text-indigo-600 hover:underline">Reset it here</a>
    </p>
</div>

</body>
</html>
