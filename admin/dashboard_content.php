<?php
require_once '../config/database.php';
$db = (new Database())->getConnection();
$total_institutions = $db->query('SELECT COUNT(*) FROM financial_institutions')->fetchColumn();
$total_users = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
$total_loans = $db->query('SELECT COUNT(*) FROM loan_requests')->fetchColumn();
$institutions = $db->query('SELECT * FROM financial_institutions ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
    <h1 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
        <i data-feather="home" class="w-6 h-6 mr-2 text-blue-500"></i>
        Admin Dashboard
    </h1>
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-green-400 to-green-600 text-white rounded-2xl shadow-xl p-6 flex flex-col items-center hover:scale-105 transition-transform duration-200">
            <i data-feather="building" class="w-10 h-10 mb-2"></i>
            <div class="text-4xl font-extrabold"><?php echo $total_institutions; ?></div>
            <div class="text-lg font-semibold mt-1">Total Institutions</div>
        </div>
        <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 text-white rounded-2xl shadow-xl p-6 flex flex-col items-center hover:scale-105 transition-transform duration-200">
            <i data-feather="users" class="w-10 h-10 mb-2"></i>
            <div class="text-4xl font-extrabold"><?php echo $total_users; ?></div>
            <div class="text-lg font-semibold mt-1">Total Users</div>
        </div>
        <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white rounded-2xl shadow-xl p-6 flex flex-col items-center hover:scale-105 transition-transform duration-200">
            <i data-feather="bar-chart-2" class="w-10 h-10 mb-2"></i>
            <div class="text-4xl font-extrabold"><?php echo $total_loans; ?></div>
            <div class="text-lg font-semibold mt-1">Total Loans</div>
        </div>
    </div>
    <!-- Add Institution Button -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
        <button id="openAddInstitutionModal" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
            <i data-feather="plus" class="w-5 h-5"></i>
            <span>Add Institution</span>
        </button>
        <div>
            <label for="typeFilter" class="mr-2 font-medium text-gray-700">Filter by Type:</label>
            <select id="typeFilter" class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none">
                <option value="">All</option>
                <option value="bank">Bank</option>
                <option value="microfinance">Microfinance</option>
                <option value="cooperative">Cooperative</option>
                <option value="other">Other</option>
            </select>
        </div>
    </div>
    <!-- Institution Table -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-x-auto">
        <table id="institutionTable" class="min-w-full table-auto">
            <thead class="bg-green-200">
                <tr>
                    <th class="px-6 py-3 border-b font-semibold">Name</th>
                    <th class="px-6 py-3 border-b font-semibold">Type</th>
                    <th class="px-6 py-3 border-b font-semibold">Min Loan</th>
                    <th class="px-6 py-3 border-b font-semibold">Max Loan</th>
                    <th class="px-6 py-3 border-b font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($institutions as $inst): ?>
                <tr class="hover:bg-green-50 transition-colors" data-id="<?php echo $inst['id']; ?>">
                    <td class="px-6 py-4 border-b"><?php echo htmlspecialchars($inst['institution_name']); ?></td>
                    <td class="px-6 py-4 border-b"><?php echo htmlspecialchars($inst['institution_type']); ?></td>
                    <td class="px-6 py-4 border-b"><?php echo number_format($inst['min_loan_amount']); ?> RWF</td>
                    <td class="px-6 py-4 border-b"><?php echo number_format($inst['max_loan_amount']); ?> RWF</td>
                    <td class="px-6 py-4 border-b">
                        <button class="view-inst-btn text-blue-600 hover:underline mr-2" data-inst='<?php echo json_encode($inst); ?>'>View</button>
                        <button class="edit-inst-btn text-green-600 hover:underline mr-2" data-inst='<?php echo json_encode($inst); ?>'>Edit</button>
                        <button class="delete-inst-btn text-red-600 hover:underline" data-id="<?php echo $inst['id']; ?>">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Add/Edit/View/Delete Modals and scripts can be included here if needed -->
</div>
<!-- Add Institution Modal -->
<div id="addInstitutionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md relative">
        <button id="closeAddInstitutionModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
        <h2 class="text-2xl font-bold text-center text-green-800 mb-6 flex items-center justify-center">
            <i data-feather="plus" class="w-6 h-6 mr-2 text-green-500"></i>
            Add Institution
        </h2>
        <form id="addInstitutionForm" class="space-y-4 mt-4">
            <input name="institution_name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="Institution Name" required>
            <select name="institution_type" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" required>
                <option value="">Select Type</option>
                <option value="bank">Bank</option>
                <option value="microfinance">Microfinance</option>
                <option value="cooperative">Cooperative</option>
                <option value="other">Other</option>
            </select>
            <input name="license_number" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="License Number">
            <input name="contact_person" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="Contact Person">
            <input name="office_location" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="Office Location">
            <div class="flex gap-2">
                <input name="min_loan" type="number" min="0" class="w-1/2 px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="Min Loan">
                <input name="max_loan" type="number" min="0" class="w-1/2 px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="Max Loan">
            </div>
            <div id="addInstitutionError" class="text-red-600 text-sm"></div>
            <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <span>Add Institution</span>
            </button>
            <button type="button" id="backToDashboardBtn" class="w-full mt-2 bg-gray-200 hover:bg-gray-300 text-green-800 font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2">
                <span>&larr; Back to Dashboard</span>
            </button>
        </form>
    </div>
</div>
<!-- View Institution Modal -->
<div id="viewInstitutionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md relative">
        <button id="closeViewInstitutionModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
        <h2 class="text-2xl font-bold text-center text-blue-800 mb-6 flex items-center justify-center">
            <i data-feather="eye" class="w-6 h-6 mr-2 text-blue-500"></i>
            View Institution
        </h2>
        <div id="viewInstitutionContent" class="space-y-2"></div>
    </div>
</div>
<!-- Edit Institution Modal -->
<div id="editInstitutionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md relative">
        <button id="closeEditInstitutionModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
        <h2 class="text-2xl font-bold text-center text-yellow-800 mb-6 flex items-center justify-center">
            <i data-feather="edit" class="w-6 h-6 mr-2 text-yellow-500"></i>
            Edit Institution
        </h2>
        <form id="editInstitutionForm" class="space-y-4 mt-4">
            <input type="hidden" name="id">
            <input name="institution_name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Institution Name" required>
            <select name="institution_type" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" required>
                <option value="">Select Type</option>
                <option value="bank">Bank</option>
                <option value="microfinance">Microfinance</option>
                <option value="cooperative">Cooperative</option>
                <option value="other">Other</option>
            </select>
            <input name="license_number" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="License Number">
            <input name="contact_person" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Contact Person">
            <input name="office_location" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Office Location">
            <div class="flex gap-2">
                <input name="min_loan" type="number" min="0" class="w-1/2 px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Min Loan">
                <input name="max_loan" type="number" min="0" class="w-1/2 px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Max Loan">
            </div>
            <div id="editInstitutionError" class="text-red-600 text-sm"></div>
            <button type="submit" class="w-full bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                <span>Save Changes</span>
            </button>
        </form>
    </div>
</div>
<!-- Delete Institution Modal -->
<div id="deleteInstitutionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md relative flex flex-col items-center">
        <button id="closeDeleteInstitutionModal" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
        <i data-feather="alert-triangle" class="w-12 h-12 text-red-500 mb-4"></i>
        <h2 class="text-xl font-bold text-red-700 mb-2">Delete Institution?</h2>
        <p class="text-gray-700 mb-6 text-center">Are you sure you want to delete this institution? This action cannot be undone.</p>
        <form id="deleteInstitutionForm" class="w-full flex flex-col items-center">
            <input type="hidden" name="id">
            <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1"><i data-feather="trash-2" class="w-5 h-5"></i><span>Delete</span></button>
        </form>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#institutionTable').DataTable({
        dom: 'Bfrtip',
        buttons: [ 'copy', 'csv', 'excel', 'print' ],
        responsive: true,
        initComplete: function() {
            if(window.feather) feather.replace();
        }
    });
    $('#typeFilter').on('change', function() {
        var val = $(this).val();
        if (val) table.column(1).search('^'+val+'$', true, false).draw();
        else table.column(1).search('').draw();
    });
    // TODO: Add modal logic for Add, View, Edit, Delete
});

// Modal open/close logic
$(document).ready(function() {
    $('#openAddInstitutionModal').on('click', function() {
        $('#addInstitutionModal').removeClass('hidden');
        if(window.feather) feather.replace();
    });
    $('#closeAddInstitutionModal, #backToDashboardBtn').on('click', function() {
        $('#addInstitutionModal').addClass('hidden');
    });
    // AJAX form submission
    $('#addInstitutionForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=add';
        $('#addInstitutionError').text('');
        $.post('institution_crud.php', formData, function(res) {
            if (res.success) {
                $('#addInstitutionModal').addClass('hidden');
                if(window.showNotification) showNotification('Institution added successfully!');
                if(window.loadPage) loadPage('dashboard_content.php', false);
            } else {
                $('#addInstitutionError').text(res.message);
                if(window.showNotification) showNotification('Error: ' + res.message);
            }
        }, 'json').fail(function(xhr) {
            $('#addInstitutionError').text('Server error. Please try again.');
        });
    });
    // View Institution
    $(document).on('click', '.view-inst-btn', function() {
        var inst = $(this).data('inst');
        var html = '';
        html += `<div><b>Name:</b> ${inst.institution_name}</div>`;
        html += `<div><b>Type:</b> ${inst.institution_type}</div>`;
        html += `<div><b>License Number:</b> ${inst.license_number || ''}</div>`;
        html += `<div><b>Contact Person:</b> ${inst.contact_person || ''}</div>`;
        html += `<div><b>Office Location:</b> ${inst.office_location || ''}</div>`;
        html += `<div><b>Min Loan:</b> ${inst.min_loan_amount || 0} RWF</div>`;
        html += `<div><b>Max Loan:</b> ${inst.max_loan_amount || 0} RWF</div>`;
        $('#viewInstitutionContent').html(html);
        $('#viewInstitutionModal').removeClass('hidden');
        if(window.feather) feather.replace();
    });
    $('#closeViewInstitutionModal').on('click', function() {
        $('#viewInstitutionModal').addClass('hidden');
    });
    // Edit Institution
    $(document).on('click', '.edit-inst-btn', function() {
        var inst = $(this).data('inst');
        var form = $('#editInstitutionForm')[0];
        form.id.value = inst.id;
        form.institution_name.value = inst.institution_name;
        form.institution_type.value = inst.institution_type;
        form.license_number.value = inst.license_number || '';
        form.contact_person.value = inst.contact_person || '';
        form.office_location.value = inst.office_location || '';
        form.min_loan.value = inst.min_loan_amount || '';
        form.max_loan.value = inst.max_loan_amount || '';
        $('#editInstitutionError').text('');
        $('#editInstitutionModal').removeClass('hidden');
        if(window.feather) feather.replace();
    });
    $('#closeEditInstitutionModal').on('click', function() {
        $('#editInstitutionModal').addClass('hidden');
    });
    $('#editInstitutionForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=edit';
        $('#editInstitutionError').text('');
        $.post('institution_crud.php', formData, function(res) {
            if (res.success) {
                $('#editInstitutionModal').addClass('hidden');
                if(window.showNotification) showNotification('Institution updated successfully!');
                if(window.loadPage) loadPage('dashboard_content.php', false);
            } else {
                $('#editInstitutionError').text(res.message);
                if(window.showNotification) showNotification('Error: ' + res.message);
            }
        }, 'json').fail(function(xhr) {
            $('#editInstitutionError').text('Server error. Please try again.');
        });
    });
    // Delete Institution
    $(document).on('click', '.delete-inst-btn', function() {
        var id = $(this).data('id');
        $('#deleteInstitutionForm input[name=id]').val(id);
        $('#deleteInstitutionModal').removeClass('hidden');
        if(window.feather) feather.replace();
    });
    $('#closeDeleteInstitutionModal').on('click', function() {
        $('#deleteInstitutionModal').addClass('hidden');
    });
    $('#deleteInstitutionForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=delete';
        $.post('institution_crud.php', formData, function(res) {
            if (res.success) {
                $('#deleteInstitutionModal').addClass('hidden');
                if(window.showNotification) showNotification('Institution deleted successfully!');
                if(window.loadPage) loadPage('dashboard_content.php', false);
            } else {
                if(window.showNotification) showNotification('Error: ' + res.message);
            }
        }, 'json');
    });
});
</script>