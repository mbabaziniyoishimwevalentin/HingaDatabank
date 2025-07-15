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

// Handle loan approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $loan_id = $_POST['loan_id'];
        $action = $_POST['action'];
        
        if ($action === 'approve') {
            $amount_approved = $_POST['amount_approved'];
            $interest_rate = $_POST['interest_rate'];
            $months = $_POST['months'] ?? 12;
            
            // Calculate payment details
            $monthly_payment = ($amount_approved * $interest_rate) / 100;
            $amount_to_pay = $amount_approved + ($monthly_payment * $months);
            
            $stmt = $db->prepare("UPDATE loan_requests SET 
                                  status = 'approved', 
                                  amount_approved = ?, 
                                  interest_rate = ?, 
                                  monthly_payment = ?, 
                                  amount_to_pay = ?, 
                                  approval_date = CURDATE() 
                                  WHERE id = ? AND institution_id = ?");
            $stmt->execute([$amount_approved, $interest_rate, $monthly_payment, $amount_to_pay, $loan_id, $institution_id]);
            
            $success_message = 'Loan approved successfully!';
            
        } elseif ($action === 'reject') {
            $rejection_reason = $_POST['rejection_reason'];
            
            $stmt = $db->prepare("UPDATE loan_requests SET 
                                  status = 'rejected', 
                                  rejection_reason = ? 
                                  WHERE id = ? AND institution_id = ?");
            $stmt->execute([$rejection_reason, $loan_id, $institution_id]);
            
            $success_message = 'Loan rejected successfully!';
        }
        
    } catch (Exception $e) {
        $error_message = 'Error processing loan request.';
    }
}

// Get loan requests
$stmt = $db->prepare("SELECT lr.*, u.username, u.email 
                      FROM loan_requests lr 
                      JOIN users u ON lr.farmer_id = u.id 
                      WHERE lr.institution_id = ? 
                      ORDER BY lr.id DESC");
$stmt->execute([$institution_id]);
$loan_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get interest rates for this institution
$stmt = $db->prepare("SELECT * FROM loan_interest_calculations 
                      WHERE financial_institutions_id = ? 
                      ORDER BY min_amount ASC");
$stmt->execute([$institution_id]);
$interest_rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <!-- Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                        <?= $success_message ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <!-- Page Header -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Loan Management</h1>
                    <p class="text-gray-600">Review and process loan applications</p>
                </div>

                <!-- Loan Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <?php
                    $pending = array_filter($loan_requests, fn($l) => $l['status'] === 'pending');
                    $approved = array_filter($loan_requests, fn($l) => $l['status'] === 'approved');
                    $rejected = array_filter($loan_requests, fn($l) => $l['status'] === 'rejected');
                    ?>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-lg font-semibold text-gray-800">Total Requests</h3>
                        <p class="text-3xl font-bold text-blue-600"><?= count($loan_requests) ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-lg font-semibold text-gray-800">Pending</h3>
                        <p class="text-3xl font-bold text-yellow-600"><?= count($pending) ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-lg font-semibold text-gray-800">Approved</h3>
                        <p class="text-3xl font-bold text-green-600"><?= count($approved) ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-lg font-semibold text-gray-800">Rejected</h3>
                        <p class="text-3xl font-bold text-red-600"><?= count($rejected) ?></p>
                    </div>
                </div>

                <!-- Loan Requests Table -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-800">Loan Applications</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Farmer</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Amount</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Purpose</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Collateral</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Monthly Payment</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Total to Pay</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($loan_requests as $loan): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($loan['username']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($loan['email']) ?></div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-sm text-gray-900"><?= number_format($loan['amount_requested']) ?> RWF</div>
                                            <?php if ($loan['status'] === 'approved'): ?>
                                                <div class="text-sm text-green-600">Approved: <?= number_format($loan['amount_approved']) ?> RWF</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <div><?= htmlspecialchars($loan['loan_type']) ?></div>
                                            <div class="text-gray-500"><?= htmlspecialchars($loan['purpose']) ?></div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <div><?= htmlspecialchars($loan['collateral_type']) ?></div>
                                            <div class="text-gray-500"><?= number_format($loan['collateral_value']) ?> RWF</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <?php if ($loan['status'] === 'approved' && $loan['monthly_payment']): ?>
                                                <div class="text-green-600 font-medium">
                                                    <?= number_format($loan['monthly_payment']) ?> RWF
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    Rate: <?= $loan['interest_rate'] ?>%/month
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <?php if ($loan['status'] === 'approved' && $loan['amount_to_pay']): ?>
                                                <div class="text-blue-600 font-medium">
                                                    <?= number_format($loan['amount_to_pay']) ?> RWF
                                                </div>
                                                <?php if ($loan['approval_date']): ?>
                                                    <div class="text-xs text-gray-500">
                                                        Approved: <?= date('M j, Y', strtotime($loan['approval_date'])) ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-gray-400">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php if ($loan['status'] === 'pending'): ?>
                                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                                            <?php elseif ($loan['status'] === 'approved'): ?>
                                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Approved</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php if ($loan['status'] === 'pending'): ?>
                                                <div class="flex space-x-2">
                                                    <button onclick="openApprovalModal(<?= $loan['id'] ?>, <?= $loan['amount_requested'] ?>)" 
                                                            class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600">
                                                        Approve
                                                    </button>
                                                    <button onclick="openRejectionModal(<?= $loan['id'] ?>)" 
                                                            class="px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">
                                                        Reject
                                                    </button>
                                                </div>
                                            <?php elseif ($loan['status'] === 'rejected'): ?>
                                                <div class="text-xs text-gray-500">
                                                    <?= htmlspecialchars($loan['rejection_reason']) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-xs text-green-600">
                                                    Loan Active
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 m-4 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Approve Loan</h3>
        <form method="POST" id="approvalForm">
            <input type="hidden" name="loan_id" id="approval_loan_id">
            <input type="hidden" name="action" value="approve">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount to Approve (RWF)</label>
                    <input type="number" name="amount_approved" id="amount_approved" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           step="1000" min="0" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Interest Rate (%/month)</label>
                    <select name="interest_rate" id="interest_rate" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select rate...</option>
                        <?php foreach ($interest_rates as $rate): ?>
                            <option value="<?= $rate['interest_rate_permonth'] ?>" 
                                    data-min="<?= $rate['min_amount'] ?>" 
                                    data-max="<?= $rate['max_amount'] ?>">
                                <?= $rate['interest_rate_permonth'] ?>% (<?= number_format($rate['min_amount']) ?> - <?= number_format($rate['max_amount']) ?> RWF)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Repayment Period (months)</label>
                    <input type="number" name="months" value="12" min="1" max="120" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div id="calculation_preview" class="bg-blue-50 p-3 rounded-lg hidden">
                    <h4 class="font-semibold text-blue-800 mb-2">Loan Preview</h4>
                    <div class="text-sm text-blue-700">
                        <div>Monthly Payment: <span id="preview_monthly">-</span> RWF</div>
                        <div>Total to Pay: <span id="preview_total">-</span> RWF</div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeApprovalModal()" 
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    Approve Loan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 m-4 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Reject Loan</h3>
        <form method="POST">
            <input type="hidden" name="loan_id" id="rejection_loan_id">
            <input type="hidden" name="action" value="reject">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason</label>
                <textarea name="rejection_reason" rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Please provide a reason for rejection..." required></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeRejectionModal()" 
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                    Reject Loan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openApprovalModal(loanId, requestedAmount) {
    document.getElementById('approval_loan_id').value = loanId;
    document.getElementById('amount_approved').value = requestedAmount;
    document.getElementById('approvalModal').classList.remove('hidden');
    document.getElementById('approvalModal').classList.add('flex');
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    document.getElementById('approvalModal').classList.remove('flex');
}

function openRejectionModal(loanId) {
    document.getElementById('rejection_loan_id').value = loanId;
    document.getElementById('rejectionModal').classList.remove('hidden');
    document.getElementById('rejectionModal').classList.add('flex');
}

function closeRejectionModal() {
    document.getElementById('rejectionModal').classList.add('hidden');
    document.getElementById('rejectionModal').classList.remove('flex');
}

// Update loan calculation preview
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount_approved');
    const rateSelect = document.getElementById('interest_rate');
    const monthsInput = document.querySelector('input[name="months"]');
    
    function updatePreview() {
        const amount = parseFloat(amountInput.value);
        const rate = parseFloat(rateSelect.value);
        const months = parseInt(monthsInput.value);
        
        if (amount && rate && months) {
            const monthlyPayment = (amount * rate) / 100;
            const totalAmount = amount + (monthlyPayment * months);
            
            document.getElementById('preview_monthly').textContent = monthlyPayment.toLocaleString();
            document.getElementById('preview_total').textContent = totalAmount.toLocaleString();
            document.getElementById('calculation_preview').classList.remove('hidden');
        } else {
            document.getElementById('calculation_preview').classList.add('hidden');
        }
    }
    
    amountInput.addEventListener('input', updatePreview);
    rateSelect.addEventListener('change', updatePreview);
    monthsInput.addEventListener('input', updatePreview);
});

// Auto-hide messages
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