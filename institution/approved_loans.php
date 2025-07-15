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

// Handle loan status updates (mark as disbursed, completed, etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $loan_id = $_POST['loan_id'];
        $action = $_POST['action'];
        
        if ($action === 'disburse') {
            $disbursement_date = $_POST['disbursement_date'];
            $disbursement_method = $_POST['disbursement_method'];
            $disbursement_notes = $_POST['disbursement_notes'] ?? '';
            
            // Update loan with disbursement information
            $stmt = $db->prepare("UPDATE loan_requests SET 
                                  disbursement_date = ?, 
                                  disbursement_method = ?, 
                                  disbursement_notes = ?
                                  WHERE id = ? AND institution_id = ? AND status = 'approved'");
            $stmt->execute([$disbursement_date, $disbursement_method, $disbursement_notes, $loan_id, $institution_id]);
            
            $success_message = 'Loan disbursement recorded successfully!';
            
        } elseif ($action === 'mark_completed') {
            $completion_date = $_POST['completion_date'];
            $completion_notes = $_POST['completion_notes'] ?? '';
            
            // Update loan as completed
            $stmt = $db->prepare("UPDATE loan_requests SET 
                                  completion_date = ?, 
                                  completion_notes = ?
                                  WHERE id = ? AND institution_id = ? AND status = 'approved'");
            $stmt->execute([$completion_date, $completion_notes, $loan_id, $institution_id]);
            
            $success_message = 'Loan marked as completed successfully!';
        }
        
    } catch (Exception $e) {
        $error_message = 'Error updating loan status.';
    }
}

// Get approved loans only
$stmt = $db->prepare("SELECT lr.*, u.username, u.email 
                      FROM loan_requests lr 
                      JOIN users u ON lr.farmer_id = u.id 
                      WHERE lr.institution_id = ? AND lr.status = 'approved'
                      ORDER BY lr.approval_date DESC");
$stmt->execute([$institution_id]);
$approved_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate loan statistics
$total_approved = count($approved_loans);
$total_amount_approved = array_sum(array_column($approved_loans, 'amount_approved'));
$total_amount_to_collect = array_sum(array_column($approved_loans, 'amount_to_pay'));
$disbursed_loans = array_filter($approved_loans, fn($l) => !empty($l['disbursement_date']));
$completed_loans = array_filter($approved_loans, fn($l) => !empty($l['completion_date']));

// Check if we need to add new columns to the database (for disbursement tracking)
try {
    $stmt = $db->prepare("SELECT disbursement_date FROM loan_requests LIMIT 1");
    $stmt->execute();
} catch (Exception $e) {
    // If columns don't exist, we'll show a note to add them
    $missing_columns = true;
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

                <?php if (isset($missing_columns)): ?>
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-6">
                        <strong>Database Update Required:</strong> To track disbursements, add these columns to loan_requests table:
                        <code>ALTER TABLE loan_requests ADD COLUMN disbursement_date DATE, ADD COLUMN disbursement_method VARCHAR(100), ADD COLUMN disbursement_notes TEXT, ADD COLUMN completion_date DATE, ADD COLUMN completion_notes TEXT;</code>
                    </div>
                <?php endif; ?>

                <!-- Page Header -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 mb-2">Approved Loans</h1>
                            <p class="text-gray-600">Manage and track your approved loan portfolio</p>
                        </div>
                        <div class="text-right">
                            <a href="loans.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                                Back to All Loans
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Loan Portfolio Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-lg font-semibold text-gray-800">Total Approved</h3>
                        <p class="text-3xl font-bold text-green-600"><?= $total_approved ?></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-lg font-semibold text-gray-800">Amount Approved</h3>
                        <p class="text-2xl font-bold text-blue-600"><?= number_format($total_amount_approved) ?> RWF</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-lg font-semibold text-gray-800">Expected Returns</h3>
                        <p class="text-2xl font-bold text-purple-600"><?= number_format($total_amount_to_collect) ?> RWF</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <h3 class="text-lg font-semibold text-gray-800">Disbursed</h3>
                        <p class="text-3xl font-bold text-orange-600"><?= count($disbursed_loans) ?></p>
                    </div>
                </div>

                <!-- Approved Loans Table -->
                <div class="bg-white rounded-xl shadow-lg">
                    <div class="p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-800">Approved Loan Details</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Farmer</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Loan Details</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Approved Amount</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Monthly Payment</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Total Expected</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Approval Date</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (empty($approved_loans)): ?>
                                    <tr>
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <p class="text-lg font-medium">No approved loans yet</p>
                                                <p class="text-sm">Approved loans will appear here once you approve loan applications</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($approved_loans as $loan): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($loan['username']) ?></div>
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars($loan['email']) ?></div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($loan['loan_type']) ?></div>
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars($loan['purpose']) ?></div>
                                                <div class="text-xs text-gray-400">
                                                    Collateral: <?= htmlspecialchars($loan['collateral_type']) ?> 
                                                    (<?= number_format($loan['collateral_value']) ?> RWF)
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-green-600"><?= number_format($loan['amount_approved']) ?> RWF</div>
                                                <div class="text-xs text-gray-500">
                                                    Requested: <?= number_format($loan['amount_requested']) ?> RWF
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-blue-600"><?= number_format($loan['monthly_payment']) ?> RWF</div>
                                                <div class="text-xs text-gray-500">
                                                    Rate: <?= $loan['interest_rate'] ?>%/month
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-purple-600"><?= number_format($loan['amount_to_pay']) ?> RWF</div>
                                                <div class="text-xs text-gray-500">
                                                    Profit: <?= number_format($loan['amount_to_pay'] - $loan['amount_approved']) ?> RWF
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="text-sm text-gray-900">
                                                    <?= date('M j, Y', strtotime($loan['approval_date'])) ?>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    <?= date('D', strtotime($loan['approval_date'])) ?>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <?php if (!empty($loan['completion_date'])): ?>
                                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">Completed</span>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        <?= date('M j, Y', strtotime($loan['completion_date'])) ?>
                                                    </div>
                                                <?php elseif (!empty($loan['disbursement_date'])): ?>
                                                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Disbursed</span>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        <?= date('M j, Y', strtotime($loan['disbursement_date'])) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Approved</span>
                                                    <div class="text-xs text-gray-500 mt-1">Pending disbursement</div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3">
                                                <?php if (!isset($missing_columns)): ?>
                                                    <?php if (empty($loan['completion_date'])): ?>
                                                        <div class="flex flex-col space-y-1">
                                                            <?php if (empty($loan['disbursement_date'])): ?>
                                                                <button onclick="openDisbursementModal(<?= $loan['id'] ?>)" 
                                                                        class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600">
                                                                    Mark Disbursed
                                                                </button>
                                                            <?php else: ?>
                                                                <button onclick="openCompletionModal(<?= $loan['id'] ?>)" 
                                                                        class="px-3 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600">
                                                                    Mark Completed
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-xs text-gray-500">
                                                            Loan Completed
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="text-xs text-gray-500">
                                                        Update DB schema
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Disbursement Modal -->
<div id="disbursementModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 m-4 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Record Loan Disbursement</h3>
        <form method="POST" id="disbursementForm">
            <input type="hidden" name="loan_id" id="disbursement_loan_id">
            <input type="hidden" name="action" value="disburse">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Disbursement Date</label>
                    <input type="date" name="disbursement_date" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Disbursement Method</label>
                    <select name="disbursement_method" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Select method...</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Mobile Money">Mobile Money</option>
                        <option value="Cash">Cash</option>
                        <option value="Check">Check</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea name="disbursement_notes" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Any additional notes about the disbursement..."></textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeDisbursementModal()" 
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    Record Disbursement
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Completion Modal -->
<div id="completionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 m-4 max-w-md w-full">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Mark Loan as Completed</h3>
        <form method="POST" id="completionForm">
            <input type="hidden" name="loan_id" id="completion_loan_id">
            <input type="hidden" name="action" value="mark_completed">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Completion Date</label>
                    <input type="date" name="completion_date" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="<?= date('Y-m-d') ?>" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Completion Notes</label>
                    <textarea name="completion_notes" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Notes about loan completion, final payments, etc..."></textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" onclick="closeCompletionModal()" 
                        class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    Mark as Completed
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDisbursementModal(loanId) {
    document.getElementById('disbursement_loan_id').value = loanId;
    document.getElementById('disbursementModal').classList.remove('hidden');
    document.getElementById('disbursementModal').classList.add('flex');
}

function closeDisbursementModal() {
    document.getElementById('disbursementModal').classList.add('hidden');
    document.getElementById('disbursementModal').classList.remove('flex');
}

function openCompletionModal(loanId) {
    document.getElementById('completion_loan_id').value = loanId;
    document.getElementById('completionModal').classList.remove('hidden');
    document.getElementById('completionModal').classList.add('flex');
}

function closeCompletionModal() {
    document.getElementById('completionModal').classList.add('hidden');
    document.getElementById('completionModal').classList.remove('flex');
}

// Auto-hide messages
setTimeout(() => {
    const messages = document.querySelectorAll('.bg-green-100, .bg-red-100, .bg-yellow-100');
    messages.forEach(message => {
        message.style.transition = 'opacity 0.5s ease-out';
        message.style.opacity = '0';
        setTimeout(() => message.remove(), 500);
    });
}, 5000);
</script>

<?php require_once '../includes/footer.php'; ?>