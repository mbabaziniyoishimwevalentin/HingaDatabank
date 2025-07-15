<!--  institution info-->
<?php 
require_once '../includes/auth_check.php'; 
require_once '../config/database.php';
require_once '../includes/header.php'; 
require_once '../includes/navbar.php'; 

// Check if user is an institution
if ($_SESSION['role'] !== 'institution') {
    header('Location: ../index.php');
    exit();
}

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        // Update users table
        $stmt = $db->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([
            $_POST['username'],
            $_POST['email'],
            $user_id
        ]);
        
        // Update or insert financial institution record
        $stmt = $db->prepare("INSERT INTO financial_institutions 
                              (user_id, institution_name, institution_type, license_number, contact_person, office_location, min_loan_amount, max_loan_amount)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                              ON DUPLICATE KEY UPDATE
                              institution_name=VALUES(institution_name), institution_type=VALUES(institution_type),
                              license_number=VALUES(license_number), contact_person=VALUES(contact_person),
                              office_location=VALUES(office_location), min_loan_amount=VALUES(min_loan_amount),
                              max_loan_amount=VALUES(max_loan_amount)");
        $stmt->execute([
            $user_id,
            $_POST['institution_name'],
            $_POST['institution_type'],
            $_POST['license_number'],
            $_POST['contact_person'],
            $_POST['office_location'],
            $_POST['min_loan_amount'],
            $_POST['max_loan_amount']
        ]);
        
        $db->commit();
        $success_message = 'Institution information updated successfully!';
        
    } catch (Exception $e) {
        $db->rollBack();
        $error_message = 'Error updating information. Please try again.';
    }
}

// Fetch current institution data
$stmt = $db->prepare("
    SELECT u.username, u.email, fi.institution_name, fi.institution_type, 
           fi.license_number, fi.contact_person, fi.office_location, 
           fi.min_loan_amount, fi.max_loan_amount
    FROM users u 
    LEFT JOIN financial_institutions fi ON u.id = fi.user_id 
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$institution_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate profile completion percentage
$fields = ['institution_name', 'institution_type', 'license_number', 'contact_person', 'office_location', 'min_loan_amount', 'max_loan_amount'];
$completed_fields = 0;
foreach ($fields as $field) {
    if (!empty($institution_data[$field])) {
        $completed_fields++;
    }
}
$completion_percentage = ($completed_fields / count($fields)) * 100;
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50">
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-wrap lg:flex-nowrap gap-6">
            <!-- Sidebar -->
            <div class="w-full lg:w-1/4">
                <div class="sticky top-24">
                    <?php include 'institution_sidebar.php'; ?>
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
                        <!-- Institution Logo/Avatar -->
                        <div class="relative">
                            <div class="w-24 h-24 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                                <?= strtoupper(substr($institution_data['institution_name'] ?? $institution_data['username'], 0, 2)) ?>
                            </div>
                            <div class="absolute -bottom-2 -right-2 bg-white rounded-full p-2 shadow-lg">
                                <i data-feather="building" class="w-4 h-4 text-gray-600"></i>
                            </div>
                        </div>

                        <!-- Institution Info -->
                        <div class="flex-1 text-center md:text-left">
                            <h1 class="text-3xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($institution_data['institution_name'] ?? $institution_data['username']) ?></h1>
                            <p class="text-gray-600 mb-4"><?= htmlspecialchars($institution_data['email']) ?></p>
                            
                            <!-- Profile Completion -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Profile Completion</span>
                                    <span class="text-sm font-medium text-gray-700"><?= round($completion_percentage) ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300" style="width: <?= $completion_percentage ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Institution Form -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i data-feather="building" class="w-6 h-6 mr-3 text-blue-500"></i>
                            Institution Information
                        </h2>
                        <p class="text-gray-600 mt-2">Update your institution profile and lending parameters</p>
                    </div>

                    <form method="POST" class="p-6">
                        <!-- Basic Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i data-feather="user" class="w-5 h-5 mr-2 text-gray-500"></i>
                                Basic Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Username -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i data-feather="user" class="w-4 h-4 mr-2 text-gray-500"></i>
                                        Username
                                    </label>
                                    <input type="text" name="username" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                           placeholder="Enter username" 
                                           value="<?= htmlspecialchars($institution_data['username'] ?? '') ?>" 
                                           required>
                                </div>

                                <!-- Email -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i data-feather="mail" class="w-4 h-4 mr-2 text-gray-500"></i>
                                        Email Address
                                    </label>
                                    <input type="email" name="email" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                           placeholder="Enter email address" 
                                           value="<?= htmlspecialchars($institution_data['email'] ?? '') ?>" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Institution Details -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i data-feather="building" class="w-5 h-5 mr-2 text-gray-500"></i>
                                Institution Details
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Institution Name -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i data-feather="tag" class="w-4 h-4 mr-2 text-gray-500"></i>
                                        Institution Name
                                    </label>
                                    <input type="text" name="institution_name" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                           placeholder="Enter institution name" 
                                           value="<?= htmlspecialchars($institution_data['institution_name'] ?? '') ?>" 
                                           required>
                                </div>

                                <!-- Institution Type -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i data-feather="layers" class="w-4 h-4 mr-2 text-gray-500"></i>
                                        Institution Type
                                    </label>
                                    <select name="institution_type" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            required>
                                        <option value="">Select Institution Type</option>
                                        <option value="bank" <?= ($institution_data['institution_type'] ?? '') === 'bank' ? 'selected' : '' ?>>Bank</option>
                                        <option value="microfinance" <?= ($institution_data['institution_type'] ?? '') === 'microfinance' ? 'selected' : '' ?>>Microfinance</option>
                                        <option value="cooperative" <?= ($institution_data['institution_type'] ?? '') === 'cooperative' ? 'selected' : '' ?>>Cooperative</option>
                                        <option value="other" <?= ($institution_data['institution_type'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>

                                <!-- License Number -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i data-feather="file-text" class="w-4 h-4 mr-2 text-gray-500"></i>
                                        License Number
                                    </label>
                                    <input type="text" name="license_number" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                           placeholder="Enter license number" 
                                           value="<?= htmlspecialchars($institution_data['license_number'] ?? '') ?>" 
                                           required>
                                </div>

                                <!-- Contact Person -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i data-feather="user-check" class="w-4 h-4 mr-2 text-gray-500"></i>
                                        Contact Person
                                    </label>
                                    <input type="text" name="contact_person" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                           placeholder="Enter contact person name" 
                                           value="<?= htmlspecialchars($institution_data['contact_person'] ?? '') ?>" 
                                           required>
                                </div>

                                <!-- Office Location -->
                                <div class="space-y-2 md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i data-feather="map-pin" class="w-4 h-4 mr-2 text-gray-500"></i>
                                        Office Location
                                    </label>
                                    <input type="text" name="office_location" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                           placeholder="Enter office location" 
                                           value="<?= htmlspecialchars($institution_data['office_location'] ?? '') ?>" 
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Lending Parameters -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i data-feather="dollar-sign" class="w-5 h-5 mr-2 text-green-500"></i>
                                Lending Parameters
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Minimum Loan Amount -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i data-feather="trending-down" class="w-4 h-4 mr-2 text-gray-500"></i>
                                        Minimum Loan Amount (RWF)
                                    </label>
                                    <input type="number" name="min_loan_amount" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                           placeholder="Enter minimum loan amount" 
                                           value="<?= htmlspecialchars($institution_data['min_loan_amount'] ?? '') ?>" 
                                           step="1000" min="0"
                                           required>
                                </div>

                                <!-- Maximum Loan Amount -->
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 flex items-center">
                                        <i data-feather="trending-up" class="w-4 h-4 mr-2 text-gray-500"></i>
                                        Maximum Loan Amount (RWF)
                                    </label>
                                    <input type="number" name="max_loan_amount" 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                           placeholder="Enter maximum loan amount" 
                                           value="<?= htmlspecialchars($institution_data['max_loan_amount'] ?? '') ?>" 
                                           step="1000" min="0"
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4 mt-8">
                            <button type="submit" 
                                    class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i data-feather="save" class="w-5 h-5"></i>
                                <span>Save Information</span>
                            </button>
                            
                            <a href="dashboard.php" 
                               class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i data-feather="arrow-left" class="w-5 h-5"></i>
                                <span>Back to Dashboard</span>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Institution Tips -->
                <div class="bg-white rounded-xl shadow-lg p-6 mt-6 border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i data-feather="lightbulb" class="w-5 h-5 mr-2 text-yellow-500"></i>
                        Institution Tips
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg">
                            <i data-feather="info" class="w-5 h-5 text-blue-500 mt-0.5"></i>
                            <div>
                                <div class="font-medium text-blue-800">Complete Profile</div>
                                <div class="text-sm text-blue-600">Complete your profile to attract more borrowers</div>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                            <i data-feather="shield" class="w-5 h-5 text-green-500 mt-0.5"></i>
                            <div>
                                <div class="font-medium text-green-800">Secure Data</div>
                                <div class="text-sm text-green-600">Your institution data is encrypted and secure</div>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3 p-3 bg-yellow-50 rounded-lg">
                            <i data-feather="edit" class="w-5 h-5 text-yellow-500 mt-0.5"></i>
                            <div>
                                <div class="font-medium text-yellow-800">Keep Updated</div>
                                <div class="text-sm text-yellow-600">Update loan parameters regularly</div>
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
        const requiredFields = ['username', 'email', 'institution_name', 'institution_type', 'license_number', 'contact_person', 'office_location', 'min_loan_amount', 'max_loan_amount'];
        let isValid = true;
        
        requiredFields.forEach(field => {
            const input = document.querySelector(`input[name="${field}"], select[name="${field}"]`);
            if (!input.value.trim()) {
                input.classList.add('border-red-500');
                isValid = false;
            } else {
                input.classList.remove('border-red-500');
            }
        });
        
        // Validate loan amounts
        const minAmount = parseFloat(document.querySelector('input[name="min_loan_amount"]').value);
        const maxAmount = parseFloat(document.querySelector('input[name="max_loan_amount"]').value);
        
        if (minAmount >= maxAmount) {
            e.preventDefault();
            alert('Maximum loan amount must be greater than minimum loan amount.');
            return false;
        }
        
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

    // Auto-format number inputs
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function() {
            if (this.value) {
                this.value = Math.abs(this.value);
            }
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>