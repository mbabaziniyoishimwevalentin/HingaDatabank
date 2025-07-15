<?php
require_once '../includes/auth_check.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../config/database.php';

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current'];
    $new = $_POST['new'];
    $confirm = $_POST['confirm'];

    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($current, $user['password'])) {
        $msg = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Incorrect current password.</div>";
    } elseif ($new !== $confirm) {
        $msg = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4'>Passwords do not match.</div>";
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $user_id]);
        $msg = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4'>Password updated successfully.</div>";
    }
}
?>

<div class="container mx-auto px-4 mt-4">
    <div class="flex flex-wrap">
        <!-- Sidebar -->
        <div class="w-full md:w-1/4 pr-4 mb-4">
            <?php include 'farmer_sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="w-full md:w-3/4">
            <h4 class="text-xl font-semibold mb-4">Change Password</h4>
            <?= $msg ?>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <form method="POST" class="space-y-4">
                    <div>
                        <label for="current" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input type="password" name="current" id="current" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter your current password" required>
                    </div>
                    
                    <div>
                        <label for="new" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="new" id="new" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter new password" required>
                    </div>
                    
                    <div>
                        <label for="confirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" name="confirm" id="confirm" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Confirm new password" required>
                    </div>
                    
                    <div class="pt-4">
                        <button class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-md transition duration-200">Change Password</button>
                    </div>
                </form>
            </div>

            <!-- Password Requirements Info -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-4">
                <h6 class="text-sm font-medium text-blue-800 mb-2">Password Requirements:</h6>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Use a strong password with at least 8 characters</li>
                    <li>• Include a mix of uppercase and lowercase letters</li>
                    <li>• Include numbers and special characters</li>
                    <li>• Avoid using personal information</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>