<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$db = (new Database())->getConnection();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['institution_name'] ?? '');
    $email = trim($_POST['institution_email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$name || !$email || !$password) {
        $msg = "<div class='alert alert-danger'>All fields are required.</div>";
    } else {
        $check = $db->prepare("SELECT * FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            $msg = "<div class='alert alert-danger'>Email already exists.</div>";
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'institution')");
            if ($stmt->execute([$name, $email, $hashed])) {
                $user_id = $db->lastInsertId();
                // Insert into financial_institutions
                $stmt2 = $db->prepare("INSERT INTO financial_institutions (user_id, institution_name) VALUES (?, ?)");
                $stmt2->execute([$user_id, $name]);
                $msg = "<div class='alert alert-success'>Institution user created successfully and added to dashboard.</div>";
            } else {
                $msg = "<div class='alert alert-danger'>Database error. Please try again.</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Institution</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gradient-to-br from-green-50 to-blue-50 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg border border-green-200 p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-center text-green-800 mb-6 flex items-center justify-center">
            <svg xmlns='http://www.w3.org/2000/svg' class='w-6 h-6 mr-2 text-green-500' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 21v-2a4 4 0 014-4h10a4 4 0 014 4v2M16 3.13a4 4 0 010 7.75M12 7v4m0 0l-2-2m2 2l2-2'/></svg>
            Add Financial Institution
        </h2>
        <?php if ($msg) echo $msg; ?>
        <form method="POST" class="space-y-4 mt-4">
            <input name="institution_name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none mb-2" placeholder="Institution Name" required>
            <input name="institution_email" type="email" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none mb-2" placeholder="Email" required>
            <input name="password" type="password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none mb-2" placeholder="Password" required>
            <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <span>Add Institution</span>
            </button>
        </form>
        <div class="mt-6 text-center">
            <a href="dashboard.php" class="text-green-700 hover:underline">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
