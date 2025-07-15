<!-- agrifinance-platform/institution/loan_calculation.php -->
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

// Get institution information
$stmt = $db->prepare("SELECT * FROM financial_institutions WHERE user_id = ?");
$stmt->execute([$user_id]);
$institution = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$institution) {
    header('Location: institution_info.php');
    exit();
}

$institution_id = $institution['id'];

// Handle form submission for adding/updating interest rates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        if (isset($_POST['add_rate'])) {
            // Add new interest rate calculation
            $stmt = $db->prepare("INSERT INTO loan_interest_calculations 
                                  (financial_institutions_id, min_amount, max_amount, interest_rate_permonth) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $institution_id,
                $_POST['min_amount'],
                $_POST['max_amount'],
                $_POST['interest_rate']
            ]);
            $success_message = 'Interest rate calculation added successfully!';
        } 
        elseif (isset($_POST['update_rate'])) {
            // Update existing interest rate calculation
            $stmt = $db->prepare("UPDATE loan_interest_calculations 
                                  SET min_amount = ?, max_amount = ?, interest_rate_permonth = ? 
                                  WHERE id = ? AND financial_institutions_id = ?");
            $stmt->execute([
                $_POST['min_amount'],
                $_POST['max_amount'],
                $_POST['interest_rate'],
                $_POST['rate_id'],
                $institution_id
            ]);
            $success_message = 'Interest rate calculation updated successfully!';
        }
        elseif (isset($_POST['delete_rate'])) {
            // Delete interest rate calculation
            $stmt = $db->prepare("DELETE FROM loan_interest_calculations 
                                  WHERE id = ? AND financial_institutions_id = ?");
            $stmt->execute([$_POST['rate_id'], $institution_id]);
            $success_message = 'Interest rate calculation deleted successfully!';
        }
        
        $db->commit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $error_message = 'Error processing request. Please try again.';
    }
}

// Fetch existing interest rate calculations
$stmt = $db->prepare("SELECT * FROM loan_interest_calculations 
                      WHERE financial_institutions_id = ? 
                      ORDER BY min_amount ASC");
$stmt->execute([$institution_id]);
$interest_rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate loan function
function calculateLoan($amount, $interest_rate, $months) {
    $monthly_rate = $interest_rate / 100;
    $monthly_payment = $amount * $monthly_rate;
    $total_amount = $amount + ($monthly_payment * $months);
    
    return [
        'monthly_payment' => $monthly_payment,
        'total_amount' => $total_amount,
        'total_interest' => $total_amount - $amount
    ];
}
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

                <!-- Page Header -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2 flex items-center">
                        <i data-feather="calculator" class="w-8 h-8 mr-3 text-blue-500"></i>
                        Loan Interest Calculator
                    </h1>
                    <p class="text-gray-600">Manage interest rates and calculate loan payments for different amount ranges</p>
                    <div class="mt-4 text-sm text-gray-500">
                        Institution: <span class="font-semibold"><?= htmlspecialchars($institution['institution_name']) ?></span>
                    </div>
                </div>

                <!-- Quick Calculator -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center">
                            <i data-feather="zap" class="w-6 h-6 mr-3 text-yellow-500"></i>
                            Quick Loan Calculator
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Loan Amount (RWF)</label>
                                <input type="number" id="calc_amount" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Enter amount" step="1000" min="0">
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Interest Rate (%/month)</label>
                                <input type="number" id="calc_rate" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Enter rate" step="0.01" min="0" max="100">
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Repayment Period (months)</label>
                                <input type="number" id="calc_months" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Enter months" min="1" max="120" value="12">
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                                <button onclick="calculateQuickLoan()" 
                                        class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                    Calculate
                                </button>
                            </div>
                        </div>
                        
                        <!-- Calculation Results -->
                        <div id="calc_results" class="hidden">
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                <h3 class="font-semibold text-blue-800 mb-3">Calculation Results</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="text-blue-600">Monthly Payment:</span>
                                        <div class="font-bold text-blue-800" id="monthly_payment">-</div>
                                    </div>
                                    <div>
                                        <span class="text-blue-600">Total Amount:</span>
                                        <div class="font-bold text-blue-800" id="total_amount">-</div>
                                    </div>
                                    <div>
                                        <span class="text-blue-600">Total Interest:</span>
                                        <div class="font-bold text-blue-800" id="total_interest">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interest Rate Management -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center">
                            <i data-feather="settings" class="w-6 h-6 mr-3 text-green-500"></i>
                            Interest Rate Management
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <!-- Add New Rate Form -->
                        <form method="POST" class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Min Amount (RWF)</label>
                                    <input type="number" name="min_amount" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Minimum amount" step="1000" min="0" required>
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Max Amount (RWF)</label>
                                    <input type="number" name="max_amount" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Maximum amount" step="1000" min="0" required>
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Interest Rate (%/month)</label>
                                    <input type="number" name="interest_rate" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Rate per month" step="0.01" min="0" max="100" required>
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">&nbsp;</label>
                                    <button type="submit" name="add_rate" 
                                            class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center">
                                        <i data-feather="plus" class="w-4 h-4 mr-2"></i>
                                        Add Rate
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Existing Rates Table -->
                        <?php if (!empty($interest_rates)): ?>
                            <div class="overflow-x-auto">
                                <table class="w-full table-auto">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Amount Range</th>
                                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Interest Rate</th>
                                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Created</th>
                                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <?php foreach ($interest_rates as $rate): ?>
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm text-gray-900">
                                                    <?= number_format($rate['min_amount']) ?> - <?= number_format($rate['max_amount']) ?> RWF
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900">
                                                    <?= $rate['interest_rate_permonth'] ?>% per month
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-500">
                                                    <?= date('M j, Y', strtotime($rate['created_at'])) ?>
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    <div class="flex space-x-2">
                                                        <button onclick="editRate(<?= $rate['id'] ?>, <?= $rate['min_amount'] ?>, <?= $rate['max_amount'] ?>, <?= $rate['interest_rate_permonth'] ?>)" 
                                                                class="text-blue-600 hover:text-blue-800 transition-colors">
                                                            <i data-feather="edit" class="w-4 h-4"></i>
                                                        </button>
                                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this rate?')">
                                                            <input type="hidden" name="rate_id" value="<?= $rate['id'] ?>">
                                                            <button type="submit" name="delete_rate" 
                                                                    class="text-red-600 hover:text-red-800 transition-colors">
                                                                <i data-feather="trash-2" class="w-4 h-4"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <i data-feather="info" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                                <p class="text-gray-500">No interest rates configured yet. Add your first rate above.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Institution Loan Limits -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center">
                            <i data-feather="bar-chart-2" class="w-6 h-6 mr-3 text-purple-500"></i>
                            Your Loan Limits
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-green-600">Minimum Loan Amount</div>
                                        <div class="text-2xl font-bold text-green-800">
                                            <?= number_format($institution['min_loan_amount']) ?> RWF
                                        </div>
                                    </div>
                                    <i data-feather="trending-down" class="w-8 h-8 text-green-500"></i>
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-blue-600">Maximum Loan Amount</div>
                                        <div class="text-2xl font-bold text-blue-800">
                                            <?= number_format($institution['max_loan_amount']) ?> RWF
                                        </div>
                                    </div>
                                    <i data-feather="trending-up" class="w-8 h-8 text-blue-500"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 text-sm text-gray-600">
                            <p><i data-feather="info" class="w-4 h-4 inline mr-1"></i> 
                               These limits are set in your institution profile. 
                               <a href="institution_info.php" class="text-blue-600 hover:text-blue-800">Update limits</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Rate Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 m-4 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Edit Interest Rate</h3>
        <form method="POST" id="editForm">
            <input type="hidden" name="rate_id" id="edit_rate_id">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Min Amount (RWF)</label>
                    <input type="number" name="min_amount" id="edit_min_amount" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           step="1000" min="0" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Amount (RWF)</label>
                    <input type="number" name="max_amount" id="edit_max_amount" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           step="1000" min="0" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (%/month)</label>
                    <input type="number" name="interest_rate" id="edit_interest_rate" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           step="0.01" min="0" max="100" required>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeEditModal()" 
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" name="update_rate" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    Update Rate
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Initialize Feather icons
    feather.replace();

    // Quick loan calculator
    function calculateQuickLoan() {
        const amount = parseFloat(document.getElementById('calc_amount').value);
        const rate = parseFloat(document.getElementById('calc_rate').value);
        const months = parseInt(document.getElementById('calc_months').value);
        
        if (!amount || !rate || !months) {
            alert('Please fill in all fields');
            return;
        }
        
        const monthlyRate = rate / 100;
        const monthlyPayment = amount * monthlyRate;
        const totalAmount = amount + (monthlyPayment * months);
        const totalInterest = totalAmount - amount;
        
        document.getElementById('monthly_payment').textContent = 
            new Intl.NumberFormat('en-RW', {style: 'currency', currency: 'RWF'}).format(monthlyPayment);
        document.getElementById('total_amount').textContent = 
            new Intl.NumberFormat('en-RW', {style: 'currency', currency: 'RWF'}).format(totalAmount);
        document.getElementById('total_interest').textContent = 
            new Intl.NumberFormat('en-RW', {style: 'currency', currency: 'RWF'}).format(totalInterest);
        
        document.getElementById('calc_results').classList.remove('hidden');
    }

    // Edit rate modal functions
    function editRate(id, minAmount, maxAmount, interestRate) {
        document.getElementById('edit_rate_id').value = id;
        document.getElementById('edit_min_amount').value = minAmount;
        document.getElementById('edit_max_amount').value = maxAmount;
        document.getElementById('edit_interest_rate').value = interestRate;
        
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }

    // Close modal when clicking outside
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditModal();
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

    // Form validation
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const minAmount = parseFloat(form.querySelector('input[name="min_amount"]')?.value);
                const maxAmount = parseFloat(form.querySelector('input[name="max_amount"]')?.value);
                
                if (minAmount && maxAmount && minAmount >= maxAmount) {
                    e.preventDefault();
                    alert('Maximum amount must be greater than minimum amount.');
                }
            });
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>