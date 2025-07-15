<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Fetch all loan requests for this farmer
$stmt = $db->prepare("SELECT lr.*, fi.institution_name FROM loan_requests lr JOIN financial_institutions fi ON lr.institution_id = fi.id WHERE lr.farmer_id = ? ORDER BY lr.id DESC");
$stmt->execute([$user_id]);
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <h1 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                        <i data-feather="file-text" class="w-6 h-6 mr-2 text-green-500"></i>
                        My Loan Requests
                    </h1>
                    <p class="text-gray-600 mb-4">Track the status of your submitted loan applications.</p>
                    <div class="overflow-x-auto">
                        <table id="loanTable" class="min-w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Institution</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loan Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Repayment</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Collateral</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($loans as $loan): ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900"><?php echo htmlspecialchars($loan['institution_name']); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-900"><?php echo htmlspecialchars($loan['loan_type']); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-900"><?php echo number_format($loan['amount_requested']); ?> RWF</td>
                                    <td class="px-4 py-3 text-sm text-gray-900"><?php echo number_format($loan['monthly_payment']); ?> RWF x <?php echo htmlspecialchars($loan['months'] ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-900"><?php echo htmlspecialchars($loan['collateral_type']); ?> (<?php echo number_format($loan['collateral_value']); ?> RWF)</td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php
                                            $status = $loan['status'] ?? 'pending';
                                            $badge = 'bg-yellow-100 text-yellow-800';
                                            if ($status === 'approved') $badge = 'bg-green-100 text-green-800';
                                            if ($status === 'rejected') $badge = 'bg-red-100 text-red-800';
                                        ?>
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold <?php echo $badge; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <button class="view-loan-btn text-blue-600 hover:underline mr-2" data-loan='<?php echo json_encode($loan); ?>'>View</button>
                                        <?php if (($loan['status'] ?? 'pending') === 'pending'): ?>
                                            <button class="edit-loan-btn text-green-600 hover:underline mr-2" data-loan='<?php echo json_encode($loan); ?>'>Edit</button>
                                            <button class="delete-loan-btn text-red-600 hover:underline" data-loan-id="<?php echo $loan['id']; ?>">Delete</button>
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
<!-- View Loan Modal -->
<div id="viewLoanModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-lg relative">
        <button id="closeViewLoanModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            <i data-feather="file-text" class="w-5 h-5 mr-2 text-blue-500"></i>
            Loan Request Details
        </h2>
        <div id="viewLoanDetails" class="space-y-3 text-sm">
            <!-- Populated by JS -->
        </div>
    </div>
</div>
<!-- Edit Loan Modal -->
<div id="editLoanModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-lg relative">
        <button id="closeEditLoanModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            <i data-feather="edit" class="w-5 h-5 mr-2 text-green-500"></i>
            Edit Loan Request
        </h2>
        <form id="editLoanForm" class="space-y-4">
            <input type="hidden" name="id" id="edit_loan_id">
            <div>
                <label class="block text-sm font-medium text-gray-700">Loan Type</label>
                <input name="loan_type" id="edit_loan_type" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Amount (RWF)</label>
                <input name="amount_requested" id="edit_amount_requested" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Repayment Period (months)</label>
                <input name="months" id="edit_months" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Collateral Type</label>
                <input name="collateral_type" id="edit_collateral_type" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Collateral Value (RWF)</label>
                <input name="collateral_value" id="edit_collateral_value" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Purpose</label>
                <textarea name="purpose" id="edit_purpose" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required></textarea>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-6 rounded-lg flex items-center">
                    <i data-feather="save" class="w-5 h-5 mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
<!-- Delete Loan Modal -->
<div id="deleteLoanModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md relative">
        <button id="closeDeleteLoanModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            <i data-feather="trash-2" class="w-5 h-5 mr-2 text-red-500"></i>
            Delete Loan Request
        </h2>
        <p class="mb-6 text-gray-700">Are you sure you want to delete this loan request? This action cannot be undone.</p>
        <div class="flex justify-end gap-4">
            <button id="confirmDeleteLoan" class="bg-red-500 hover:bg-red-600 text-white font-medium py-2 px-6 rounded-lg">Delete</button>
            <button id="cancelDeleteLoan" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-6 rounded-lg">Cancel</button>
        </div>
    </div>
</div>
<!-- DataTables & Feather -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
<script>
$(document).ready(function() {
    $('#loanTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'print'
        ]
    });
    feather.replace();

    // View Modal Logic
    const viewLoanModal = document.getElementById('viewLoanModal');
    const closeViewLoanModal = document.getElementById('closeViewLoanModal');
    const viewLoanDetails = document.getElementById('viewLoanDetails');
    document.querySelectorAll('.view-loan-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = JSON.parse(this.getAttribute('data-loan'));
            viewLoanDetails.innerHTML = `
                <div><span class='font-semibold text-gray-700'>Institution:</span> ${data.institution_name}</div>
                <div><span class='font-semibold text-gray-700'>Loan Type:</span> ${data.loan_type}</div>
                <div><span class='font-semibold text-gray-700'>Amount:</span> ${Number(data.amount_requested).toLocaleString()} RWF</div>
                <div><span class='font-semibold text-gray-700'>Repayment:</span> ${Number(data.monthly_payment).toLocaleString()} RWF x ${data.months || '-'} months</div>
                <div><span class='font-semibold text-gray-700'>Collateral:</span> ${data.collateral_type} (${Number(data.collateral_value).toLocaleString()} RWF)</div>
                <div><span class='font-semibold text-gray-700'>Purpose:</span> ${data.purpose}</div>
                <div><span class='font-semibold text-gray-700'>Status:</span> <span class='px-2 py-1 rounded-full text-xs font-semibold ${data.status === 'approved' ? 'bg-green-100 text-green-800' : data.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'}'>${(data.status||'pending').charAt(0).toUpperCase()+(data.status||'pending').slice(1)}</span></div>
                <div><span class='font-semibold text-gray-700'>Interest Rate:</span> ${data.interest_rate}%/month</div>
                <div><span class='font-semibold text-gray-700'>Total Repayable:</span> ${Number(data.amount_to_pay).toLocaleString()} RWF</div>
            `;
            viewLoanModal.classList.remove('hidden');
        });
    });
    closeViewLoanModal.addEventListener('click', function() {
        viewLoanModal.classList.add('hidden');
    });
    window.addEventListener('click', function(e) {
        if (e.target === viewLoanModal) viewLoanModal.classList.add('hidden');
    });

    // --- Edit Modal Logic ---
    const editLoanModal = document.getElementById('editLoanModal');
    const closeEditLoanModal = document.getElementById('closeEditLoanModal');
    const editLoanForm = document.getElementById('editLoanForm');
    let editingRow = null;
    document.querySelectorAll('.edit-loan-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = JSON.parse(this.getAttribute('data-loan'));
            editingRow = this.closest('tr');
            document.getElementById('edit_loan_id').value = data.id;
            document.getElementById('edit_loan_type').value = data.loan_type;
            document.getElementById('edit_amount_requested').value = data.amount_requested;
            document.getElementById('edit_months').value = data.months;
            document.getElementById('edit_collateral_type').value = data.collateral_type;
            document.getElementById('edit_collateral_value').value = data.collateral_value;
            document.getElementById('edit_purpose').value = data.purpose;
            editLoanModal.classList.remove('hidden');
        });
    });
    closeEditLoanModal.addEventListener('click', function() {
        editLoanModal.classList.add('hidden');
    });
    window.addEventListener('click', function(e) {
        if (e.target === editLoanModal) editLoanModal.classList.add('hidden');
    });
    editLoanForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(editLoanForm);
        formData.append('action', 'edit');
        fetch('loan_status_action.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update table row in real time
                const cells = editingRow.children;
                cells[1].textContent = formData.get('loan_type');
                cells[2].textContent = Number(formData.get('amount_requested')).toLocaleString() + ' RWF';
                cells[3].textContent = Number(data.monthly_payment).toLocaleString() + ' RWF x ' + formData.get('months');
                cells[4].textContent = formData.get('collateral_type') + ' (' + Number(formData.get('collateral_value')).toLocaleString() + ' RWF)';
                cells[5].innerHTML = '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Pending</span>';
                editLoanModal.classList.add('hidden');
                showAjaxMessage('Loan updated successfully.', 'success');
            } else {
                showAjaxMessage(data.message || 'Failed to update loan.', 'error');
            }
        })
        .catch(() => showAjaxMessage('Network error. Please try again.', 'error'));
    });
    // --- Delete Modal Logic ---
    const deleteLoanModal = document.getElementById('deleteLoanModal');
    const closeDeleteLoanModal = document.getElementById('closeDeleteLoanModal');
    const confirmDeleteLoan = document.getElementById('confirmDeleteLoan');
    const cancelDeleteLoan = document.getElementById('cancelDeleteLoan');
    let deletingRow = null;
    let deletingId = null;
    document.querySelectorAll('.delete-loan-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            deletingRow = this.closest('tr');
            deletingId = this.getAttribute('data-loan-id');
            deleteLoanModal.classList.remove('hidden');
        });
    });
    closeDeleteLoanModal.addEventListener('click', function() {
        deleteLoanModal.classList.add('hidden');
    });
    cancelDeleteLoan.addEventListener('click', function() {
        deleteLoanModal.classList.add('hidden');
    });
    window.addEventListener('click', function(e) {
        if (e.target === deleteLoanModal) deleteLoanModal.classList.add('hidden');
    });
    confirmDeleteLoan.addEventListener('click', function() {
        fetch('loan_status_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=delete&id=' + encodeURIComponent(deletingId)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                deletingRow.remove();
                deleteLoanModal.classList.add('hidden');
                showAjaxMessage('Loan deleted successfully.', 'success');
            } else {
                showAjaxMessage(data.message || 'Failed to delete loan.', 'error');
            }
        })
        .catch(() => showAjaxMessage('Network error. Please try again.', 'error'));
    });
    // --- AJAX Message Helper ---
    function showAjaxMessage(msg, type) {
        document.querySelectorAll('.ajax-message').forEach(el => el.remove());
        const div = document.createElement('div');
        div.className = 'ajax-message ' + (type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700') + ' px-4 py-3 rounded-lg mb-6 flex items-center';
        div.innerHTML = msg;
        document.querySelector('.bg-white.rounded-xl.shadow-lg').insertAdjacentElement('beforebegin', div);
        feather.replace();
        setTimeout(() => div.remove(), 5000);
    }
});
</script>
<?php require_once '../includes/footer.php'; ?> 