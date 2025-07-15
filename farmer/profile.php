<!-- farmer profile -->
<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $db->prepare("INSERT INTO farmer_profiles (user_id, id_number, district, sector, cell, bank_account, profile_image)
                              VALUES (?, ?, ?, ?, ?, ?, ?)
                              ON DUPLICATE KEY UPDATE
                              id_number=VALUES(id_number), district=VALUES(district), sector=VALUES(sector),
                              cell=VALUES(cell), bank_account=VALUES(bank_account), profile_image=VALUES(profile_image)");
        $stmt->execute([
            $user_id,
            $_POST['id_number'],
            $_POST['district'],
            $_POST['sector'],
            $_POST['cell'],
            $_POST['bank_account'],
            $_POST['profile_image']
        ]);
        $success_message = "Profile updated successfully!";
    } catch (Exception $e) {
        $error_message = "Error updating profile. Please try again.";
    }
}

// Fetch current profile
$stmt = $db->prepare("SELECT * FROM farmer_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user information
$stmt = $db->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate profile completion percentage
$fields = ['id_number', 'district', 'sector', 'cell', 'bank_account'];
$completed_fields = 0;
foreach ($fields as $field) {
    if (!empty($profile[$field])) {
        $completed_fields++;
    }
}
$completion_percentage = ($completed_fields / count($fields)) * 100;
?>

<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50">
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-wrap lg:flex-nowrap gap-6">
            <!-- Sidebar -->
            <div class="w-full lg:w-1/4">
                <div class="sticky top-24">
                    <?php include 'farmer_sidebar.php'; ?>
                </div>
            </div>

            <!-- Main Content -->
            <div class="w-full lg:w-3/4">
                <!-- Success/Error Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                        <i data-feather="check-circle" class="w-5 h-5 mr-2"></i>
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                        <i data-feather="alert-circle" class="w-5 h-5 mr-2"></i>
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <!-- Profile Header -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <div class="flex flex-col md:flex-row items-center md:items-start space-y-4 md:space-y-0 md:space-x-6">
                        <!-- Profile Image -->
                        <div class="relative">
                            <div class="w-24 h-24 rounded-full bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                                <?php if (!empty($profile['profile_image'])): ?>
                                    <img src="<?= htmlspecialchars($profile['profile_image']) ?>" alt="Profile" class="w-24 h-24 rounded-full object-cover">
                                <?php else: ?>
                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                <?php endif; ?>
                            </div>
                            <div class="absolute -bottom-2 -right-2 bg-white rounded-full p-2 shadow-lg">
                                <i data-feather="camera" class="w-4 h-4 text-gray-600"></i>
                            </div>
                        </div>

                        <!-- Profile Info -->
                        <div class="flex-1 text-center md:text-left">
                            <h1 class="text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($user['username']) ?></h1>
                            <p class="text-gray-600 mb-4"><?= htmlspecialchars($user['email']) ?></p>
                            
                            <!-- Profile Completion -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Profile Completion</span>
                                    <span class="text-sm font-medium text-gray-700"><?= round($completion_percentage) ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-green-500 to-green-600 h-2 rounded-full transition-all duration-300" style="width: <?= $completion_percentage ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Form -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i data-feather="user-check" class="w-6 h-6 mr-3 text-green-500"></i>
                            Profile Information
                        </h2>
                        <p class="text-gray-600 mt-2">Update your personal and location details</p>
                    </div>

                    <form method="POST" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- ID Number -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i data-feather="credit-card" class="w-4 h-4 mr-2 text-gray-500"></i>
                                    ID Number
                                </label>
                                <input type="text" name="id_number" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                       placeholder="Enter your ID number" 
                                       value="<?= htmlspecialchars($profile['id_number'] ?? '') ?>" 
                                       required>
                            </div>

                            <!-- District -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i data-feather="map-pin" class="w-4 h-4 mr-2 text-gray-500"></i>
                                    District
                                </label>
                                <input type="text" name="district" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                       placeholder="Enter your district" 
                                       value="<?= htmlspecialchars($profile['district'] ?? '') ?>" 
                                       required>
                            </div>

                            <!-- Sector -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i data-feather="map" class="w-4 h-4 mr-2 text-gray-500"></i>
                                    Sector
                                </label>
                                <input type="text" name="sector" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                       placeholder="Enter your sector" 
                                       value="<?= htmlspecialchars($profile['sector'] ?? '') ?>" 
                                       required>
                            </div>

                            <!-- Cell -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i data-feather="navigation" class="w-4 h-4 mr-2 text-gray-500"></i>
                                    Cell
                                </label>
                                <input type="text" name="cell" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                       placeholder="Enter your cell" 
                                       value="<?= htmlspecialchars($profile['cell'] ?? '') ?>" 
                                       required>
                            </div>

                            <!-- Bank Account -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i data-feather="dollar-sign" class="w-4 h-4 mr-2 text-gray-500"></i>
                                    Bank Account
                                </label>
                                <input type="text" name="bank_account" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                       placeholder="Enter your bank account number" 
                                       value="<?= htmlspecialchars($profile['bank_account'] ?? '') ?>" 
                                       required>
                            </div>

                            <!-- Profile Image -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700 flex items-center">
                                    <i data-feather="image" class="w-4 h-4 mr-2 text-gray-500"></i>
                                    Profile Image URL
                                </label>
                                <input type="url" name="profile_image" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                       placeholder="Enter image URL (optional)" 
                                       value="<?= htmlspecialchars($profile['profile_image'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 mt-8">
                            <button type="submit" 
                                    class="flex-1 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-medium py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i data-feather="save" class="w-5 h-5"></i>
                                <span>Save Profile</span>
                            </button>
                            
                            <a href="dashboard.php" 
                               class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i data-feather="arrow-left" class="w-5 h-5"></i>
                                <span>Back to Dashboard</span>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Profile Tips -->
                <div class="bg-white rounded-xl shadow-lg p-6 mt-6 border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i data-feather="lightbulb" class="w-5 h-5 mr-2 text-yellow-500"></i>
                        Profile Tips
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg">
                            <i data-feather="info" class="w-5 h-5 text-blue-500 mt-0.5"></i>
                            <div>
                                <div class="font-medium text-blue-800">Complete Profile</div>
                                <div class="text-sm text-blue-600">Complete your profile to improve loan approval chances</div>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                            <i data-feather="shield" class="w-5 h-5 text-green-500 mt-0.5"></i>
                            <div>
                                <div class="font-medium text-green-800">Secure Information</div>
                                <div class="text-sm text-green-600">Your data is encrypted and secure</div>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3 p-3 bg-yellow-50 rounded-lg">
                            <i data-feather="edit" class="w-5 h-5 text-yellow-500 mt-0.5"></i>
                            <div>
                                <div class="font-medium text-yellow-800">Keep Updated</div>
                                <div class="text-sm text-yellow-600">Update your profile regularly for better services</div>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3 p-3 bg-purple-50 rounded-lg">
                            <i data-feather="phone" class="w-5 h-5 text-purple-500 mt-0.5"></i>
                            <div>
                                <div class="font-medium text-purple-800">Need Help?</div>
                                <div class="text-sm text-purple-600">Contact support for assistance</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Feather icons
    feather.replace();

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const requiredFields = ['id_number', 'district', 'sector', 'cell', 'bank_account'];
        let isValid = true;
        
        requiredFields.forEach(field => {
            const input = document.querySelector(`input[name="${field}"]`);
            if (!input.value.trim()) {
                input.classList.add('border-red-500');
                isValid = false;
            } else {
                input.classList.remove('border-red-500');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });

    // Auto-hide success/error messages
    setTimeout(() => {
        const messages = document.querySelectorAll('.bg-green-100, .bg-red-100');
        messages.forEach(message => {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 500);
        });
    }, 5000);
</script>

<?php require_once '../includes/footer.php'; ?>