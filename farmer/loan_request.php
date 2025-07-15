<?php 
require_once '../includes/auth_check.php'; 
require_once '../config/database.php';
require_once '../includes/header.php'; 
require_once '../includes/navbar.php'; 

// Check if user is a farmer
if ($_SESSION['role'] !== 'farmer') {
    header('Location: ../index.php');
    exit();
}

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Detect AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Get farmer information
$stmt = $db->prepare("SELECT * FROM farmer_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$farmer_profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Get all financial institutions
$stmt = $db->prepare("SELECT * FROM financial_institutions ORDER BY institution_name ASC");
$stmt->execute();
$institutions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['submit_request']) || $isAjax)) {
    try {
        $db->beginTransaction();
        
        // Get the selected institution's interest rate for the amount
        $institution_id = $_POST['institution_id'];
        $amount_requested = $_POST['amount_requested'];
        $months = $_POST['months'];
        
        // Find the appropriate interest rate
        $stmt = $db->prepare("SELECT interest_rate_permonth FROM loan_interest_calculations 
                              WHERE financial_institutions_id = ? 
                              AND ? >= min_amount AND ? <= max_amount 
                              ORDER BY min_amount ASC LIMIT 1");
        $stmt->execute([$institution_id, $amount_requested, $amount_requested]);
        $interest_rate_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$interest_rate_data) {
            throw new Exception("No interest rate found for this amount range.");
        }
        
        $interest_rate = $interest_rate_data['interest_rate_permonth'];
        
        // Calculate loan details
        $monthly_rate = $interest_rate / 100;
        $monthly_payment = $amount_requested * $monthly_rate;
        $total_amount = $amount_requested + ($monthly_payment * $months);
        
        // Insert loan request
        $stmt = $db->prepare("INSERT INTO loan_requests 
                              (farmer_id, institution_id, loan_type, amount_requested, purpose, 
                               collateral_type, collateral_value, interest_rate, monthly_payment, amount_to_pay) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $institution_id,
            $_POST['loan_type'],
            $amount_requested,
            $_POST['purpose'],
            $_POST['collateral_type'],
            $_POST['collateral_value'],
            $interest_rate,
            $monthly_payment,
            $total_amount
        ]);
        
        $db->commit();
        $success_message = 'Loan request submitted successfully!';
        
    } catch (Exception $e) {
        $db->rollBack();
        $error_message = $e->getMessage();
    }
    // AJAX: Return JSON and exit
    if ($isAjax) {
        if (isset($success_message)) {
            echo json_encode(['success' => true, 'message' => $success_message]);
        } else {
            echo json_encode(['success' => false, 'message' => $error_message ?? 'Unknown error.']);
        }
        exit;
    }
}
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

                <!-- Page Header -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2 flex items-center">
                        <i data-feather="file-text" class="w-8 h-8 mr-3 text-green-500"></i>
                        Request Agricultural Loan
                    </h1>
                    <p class="text-gray-600">Apply for farming loans from registered financial institutions</p>
                </div>

                <!-- Loan Application Form -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 mb-6">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center">
                            <i data-feather="edit" class="w-6 h-6 mr-3 text-blue-500"></i>
                            Loan Application Details
                        </h2>
                    </div>
                    
                    <form id="loanRequestForm" method="POST" class="p-6" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Financial Institution Selection -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Select Financial Institution <span class="text-red-500">*</span>
                                </label>
                                <select name="institution_id" id="institution_id" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" 
                                        required onchange="loadInstitutionRates()">
                                    <option value="">Choose an institution...</option>
                                    <?php foreach ($institutions as $institution): ?>
                                        <option value="<?= $institution['id'] ?>" 
                                                data-min="<?= $institution['min_loan_amount'] ?>" 
                                                data-max="<?= $institution['max_loan_amount'] ?>">
                                            <?= htmlspecialchars($institution['institution_name']) ?> 
                                            (<?= ucfirst($institution['institution_type']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Loan Type -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Loan Type <span class="text-red-500">*</span>
                                </label>
                                <select name="loan_type" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" 
                                        required>
                                    <option value="">Select loan type...</option>
                                    <option value="Crop Production">Crop Production</option>
                                    <option value="Equipment Purchase">Equipment Purchase</option>
                                    <option value="Livestock">Livestock</option>
                                    <option value="Land Improvement">Land Improvement</option>
                                    <option value="Seasonal Farming">Seasonal Farming</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <!-- Loan Amount -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Loan Amount (RWF) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="amount_requested" id="amount_requested" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="Enter loan amount" step="1000" min="0" required 
                                       onchange="calculateLoan()">
                                <div id="amount_range_info" class="text-sm text-gray-500 hidden">
                                    <i data-feather="info" class="w-4 h-4 inline mr-1"></i>
                                    <span id="range_text"></span>
                                </div>
                            </div>

                            <!-- Repayment Period -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Repayment Period (months) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="months" id="months" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="Enter number of months" min="1" max="120" value="12" required 
                                       onchange="calculateLoan()">
                            </div>

                            <!-- Collateral Type -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Collateral Type <span class="text-red-500">*</span>
                                </label>
                                <select name="collateral_type" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" 
                                        required>
                                    <option value="">Select collateral type...</option>
                                    <option value="Land Title">Land Title</option>
                                    <option value="Livestock">Livestock</option>
                                    <option value="Farm Equipment">Farm Equipment</option>
                                    <option value="Harvest/Crops">Harvest/Crops</option>
                                    <option value="Bank Guarantee">Bank Guarantee</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <!-- Collateral Value -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Collateral Value (RWF) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="collateral_value" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="Enter collateral value" step="1000" min="0" required>
                            </div>
                        </div>

                        <!-- Purpose -->
                        <div class="mt-6 space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Purpose of Loan <span class="text-red-500">*</span>
                            </label>
                            <textarea name="purpose" rows="4" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                      placeholder="Describe the purpose of this loan and how it will be used..." required></textarea>
                        </div>

                        <!-- Loan Calculation Results -->
                        <div id="loan_calculation" class="hidden mt-6">
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                <h3 class="font-semibold text-blue-800 mb-4 flex items-center">
                                    <i data-feather="calculator" class="w-5 h-5 mr-2"></i>
                                    Loan Calculation Results
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                                    <div class="bg-white rounded-lg p-3">
                                        <span class="text-blue-600 block">Interest Rate:</span>
                                        <div class="font-bold text-blue-800 text-lg" id="interest_rate_display">-</div>
                                    </div>
                                    <div class="bg-white rounded-lg p-3">
                                        <span class="text-blue-600 block">Monthly Payment:</span>
                                        <div class="font-bold text-blue-800 text-lg" id="monthly_payment_display">-</div>
                                    </div>
                                    <div class="bg-white rounded-lg p-3">
                                        <span class="text-blue-600 block">Total Amount:</span>
                                        <div class="font-bold text-blue-800 text-lg" id="total_amount_display">-</div>
                                    </div>
                                    <div class="bg-white rounded-lg p-3">
                                        <span class="text-blue-600 block">Total Interest:</span>
                                        <div class="font-bold text-blue-800 text-lg" id="total_interest_display">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-6 flex justify-end">
                            <button type="submit" name="submit_request" 
                                    class="bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-6 rounded-lg transition-colors flex items-center">
                                <i data-feather="send" class="w-5 h-5 mr-2"></i>
                                Submit Loan Request
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Interest Rate Information -->
                <div id="interest_rates_info" class="bg-white rounded-xl shadow-lg border border-gray-200 hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center">
                            <i data-feather="percent" class="w-6 h-6 mr-3 text-purple-500"></i>
                            Interest Rates for Selected Institution
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="w-full table-auto">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Amount Range</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Interest Rate</th>
                                    </tr>
                                </thead>
                                <tbody id="interest_rates_table" class="divide-y divide-gray-200">
                                    <!-- Interest rates will be loaded here -->
                                </tbody>
                            </table>
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

    // Store interest rates for selected institution
    let currentInterestRates = [];

    // Load interest rates for selected institution
    function loadInstitutionRates() {
        const institutionId = document.getElementById('institution_id').value;
        const selectedOption = document.getElementById('institution_id').selectedOptions[0];
        
        if (!institutionId) {
            document.getElementById('interest_rates_info').classList.add('hidden');
            document.getElementById('amount_range_info').classList.add('hidden');
            return;
        }

        // Show institution limits
        const minAmount = selectedOption.getAttribute('data-min');
        const maxAmount = selectedOption.getAttribute('data-max');
        
        document.getElementById('range_text').textContent = 
            `Institution limits: ${formatCurrency(minAmount)} - ${formatCurrency(maxAmount)}`;
        document.getElementById('amount_range_info').classList.remove('hidden');

        // Fetch interest rates via AJAX
        fetch('../api/get_interest_rates.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'institution_id=' + institutionId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentInterestRates = data.rates;
                displayInterestRates(data.rates);
                document.getElementById('interest_rates_info').classList.remove('hidden');
            } else {
                document.getElementById('interest_rates_info').classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('interest_rates_info').classList.add('hidden');
        });
    }

    // Display interest rates in table
    function displayInterestRates(rates) {
        const tbody = document.getElementById('interest_rates_table');
        tbody.innerHTML = '';
        
        rates.forEach(rate => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            row.innerHTML = `
                <td class="px-4 py-3 text-sm text-gray-900">
                    ${formatCurrency(rate.min_amount)} - ${formatCurrency(rate.max_amount)}
                </td>
                <td class="px-4 py-3 text-sm text-gray-900">
                    ${rate.interest_rate_permonth}% per month
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Calculate loan based on amount and months
    function calculateLoan() {
        const amount = parseFloat(document.getElementById('amount_requested').value);
        const months = parseInt(document.getElementById('months').value);
        
        if (!amount || !months || currentInterestRates.length === 0) {
            document.getElementById('loan_calculation').classList.add('hidden');
            return;
        }

        // Find the appropriate interest rate
        const applicableRate = currentInterestRates.find(rate => 
            amount >= rate.min_amount && amount <= rate.max_amount
        );

        if (!applicableRate) {
            document.getElementById('loan_calculation').classList.add('hidden');
            return;
        }

        const interestRate = parseFloat(applicableRate.interest_rate_permonth);
        const monthlyRate = interestRate / 100;
        const monthlyPayment = amount * monthlyRate;
        const totalAmount = amount + (monthlyPayment * months);
        const totalInterest = totalAmount - amount;

        // Display results
        document.getElementById('interest_rate_display').textContent = `${interestRate}%/month`;
        document.getElementById('monthly_payment_display').textContent = formatCurrency(monthlyPayment);
        document.getElementById('total_amount_display').textContent = formatCurrency(totalAmount);
        document.getElementById('total_interest_display').textContent = formatCurrency(totalInterest);
        
        document.getElementById('loan_calculation').classList.remove('hidden');
    }

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-RW', {
            style: 'currency',
            currency: 'RWF',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    // Auto-hide success/error messages
    setTimeout(() => {
        const messages = document.querySelectorAll('.bg-green-100, .bg-red-100');
        messages.forEach(message => {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 500);
        });
    }, 5000);

    // --- Enhanced Client-Side Validation ---
    const loanRequestForm = document.getElementById('loanRequestForm');
    const submitBtn = loanRequestForm.querySelector('button[type="submit"]');

    function clearFieldErrors() {
        loanRequestForm.querySelectorAll('.field-error').forEach(e => e.remove());
        loanRequestForm.querySelectorAll('.border-red-500').forEach(e => e.classList.remove('border-red-500'));
    }

    function showFieldError(input, message) {
        input.classList.add('border-red-500');
        const error = document.createElement('div');
        error.className = 'field-error text-red-500 text-xs mt-1';
        error.textContent = message;
        input.parentNode.appendChild(error);
    }

    loanRequestForm.addEventListener('submit', function(e) {
        e.preventDefault();
        clearFieldErrors();
        let valid = true;

        // Validate required fields
        const requiredFields = [
            'institution_id', 'loan_type', 'amount_requested', 'months',
            'collateral_type', 'collateral_value', 'purpose'
        ];
        requiredFields.forEach(name => {
            const input = loanRequestForm.querySelector(`[name="${name}"]`);
            if (input && !input.value.trim()) {
                showFieldError(input, 'This field is required.');
                valid = false;
            }
        });

        // Validate loan amount
        const amountInput = document.getElementById('amount_requested');
        const institutionId = document.getElementById('institution_id').value;
        const selectedOption = document.getElementById('institution_id').selectedOptions[0];
        if (amountInput && institutionId) {
            const minAmount = parseFloat(selectedOption.getAttribute('data-min'));
            const maxAmount = parseFloat(selectedOption.getAttribute('data-max'));
            const amount = parseFloat(amountInput.value);
            if (amount < minAmount || amount > maxAmount) {
                showFieldError(amountInput, `Amount must be between ${formatCurrency(minAmount)} and ${formatCurrency(maxAmount)}.`);
                valid = false;
            }
        }

        // Validate collateral value
        const collateralInput = loanRequestForm.querySelector('[name="collateral_value"]');
        if (collateralInput && parseFloat(collateralInput.value) <= 0) {
            showFieldError(collateralInput, 'Collateral value must be positive.');
            valid = false;
        }

        // Validate repayment period
        const monthsInput = document.getElementById('months');
        if (monthsInput && (parseInt(monthsInput.value) < 1 || parseInt(monthsInput.value) > 120)) {
            showFieldError(monthsInput, 'Repayment period must be between 1 and 120 months.');
            valid = false;
        }

        // Validate interest rate range
        const amount = parseFloat(amountInput.value);
        const applicableRate = currentInterestRates.find(rate => amount >= rate.min_amount && amount <= rate.max_amount);
        if (!applicableRate) {
            showFieldError(amountInput, 'No interest rate is available for this loan amount.');
            valid = false;
        }

        if (!valid) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i data-feather="send" class="w-5 h-5 mr-2"></i>Submit Loan Request';
            feather.replace();
            return;
        }

        // Proceed with AJAX if valid
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="animate-spin mr-2">⏳</span>Submitting...';
        document.querySelectorAll('.ajax-message').forEach(el => el.remove());
        const formData = new FormData(loanRequestForm);
        fetch('loan_request.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            let message = '';
            if (data.success) {
                message = `<div class='ajax-message bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center'>${data.message}</div>`;
                loanRequestForm.reset();
                // Prevent form resubmission dialog on refresh/back
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
            } else {
                message = `<div class='ajax-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center'>${data.message}</div>`;
            }
            loanRequestForm.insertAdjacentHTML('beforebegin', message);
            feather.replace();
        })
        .catch(() => {
            loanRequestForm.insertAdjacentHTML('beforebegin', `<div class='ajax-message bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center'>Network error. Please try again.</div>`);
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i data-feather="send" class="w-5 h-5 mr-2"></i>Submit Loan Request';
            feather.replace();
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>