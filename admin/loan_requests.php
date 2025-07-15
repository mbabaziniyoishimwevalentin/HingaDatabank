<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$db = (new Database())->getConnection();
// Fetch all loan requests with farmer and institution info
$stmt = $db->prepare("SELECT lr.*, u.username AS farmer_name, fi.institution_name FROM loan_requests lr LEFT JOIN users u ON lr.farmer_id = u.id LEFT JOIN financial_institutions fi ON lr.institution_id = fi.id ORDER BY lr.id DESC");
$stmt->execute();
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="bg-white rounded-xl shadow-lg border border-green-200 p-8">
  <h2 class="text-2xl font-bold text-center text-green-800 mb-6 flex items-center justify-center">
    <i data-feather="dollar-sign" class="w-6 h-6 mr-2 text-blue-500"></i>
    Manage Loan Requests
  </h2>
  <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
    <div>
      <label for="statusFilter" class="mr-2 font-medium text-gray-700">Filter by Status:</label>
      <select id="statusFilter" class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:outline-none">
        <option value="">All</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
      </select>
    </div>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full table-auto" id="loanTable">
      <thead class="bg-blue-200">
        <tr>
          <th class="px-6 py-3 border-b font-semibold">ID</th>
          <th class="px-6 py-3 border-b font-semibold">Farmer</th>
          <th class="px-6 py-3 border-b font-semibold">Institution</th>
          <th class="px-6 py-3 border-b font-semibold">Loan Type</th>
          <th class="px-6 py-3 border-b font-semibold">Amount Requested</th>
          <th class="px-6 py-3 border-b font-semibold">Status</th>
          <th class="px-6 py-3 border-b font-semibold">Approval Date</th>
          <th class="px-6 py-3 border-b font-semibold">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($loans as $loan): ?>
        <tr class="hover:bg-blue-50 transition-colors">
          <td class="px-6 py-4 border-b"><?= $loan['id'] ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($loan['farmer_name']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($loan['institution_name']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($loan['loan_type']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($loan['amount_requested']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($loan['status']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($loan['approval_date']) ?></td>
          <td class="px-6 py-4 border-b">
            <button class="view-loan-btn text-blue-600 hover:underline mr-2" data-loan='<?= json_encode($loan) ?>'>View</button>
            <button class="edit-loan-btn text-yellow-600 hover:underline mr-2" data-loan='<?= json_encode($loan) ?>'>Edit</button>
            <button class="delete-loan-btn text-red-600 hover:underline" data-id="<?= $loan['id'] ?>">Delete</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <!-- CRUD Modals for Loan Requests -->
  <div id="viewLoanModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
      <button onclick="closeModal('viewLoanModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <h2 class="text-2xl font-bold text-blue-800 mb-6 flex items-center"><i data-feather="eye" class="w-6 h-6 mr-2 text-blue-500"></i>View Loan Request</h2>
      <div id="viewLoanContent" class="space-y-2"></div>
    </div>
  </div>
  <div id="editLoanModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
      <button onclick="closeModal('editLoanModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <h2 class="text-2xl font-bold text-yellow-800 mb-6 flex items-center"><i data-feather="edit" class="w-6 h-6 mr-2 text-yellow-500"></i>Edit Loan Request</h2>
      <form id="editLoanForm" class="space-y-4">
        <input type="hidden" name="id">
        <select name="status" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" required>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        <input name="approval_date" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Approval Date">
        <input name="amount_approved" type="number" step="0.01" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Amount Approved">
        <div id="editLoanError" class="text-red-600 text-sm"></div>
        <button type="submit" class="w-full bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1"><i data-feather="edit" class="w-5 h-5"></i><span>Save Changes</span></button>
      </form>
    </div>
  </div>
  <div id="deleteLoanModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md relative flex flex-col items-center">
      <button onclick="closeModal('deleteLoanModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <i data-feather="alert-triangle" class="w-12 h-12 text-red-500 mb-4"></i>
      <h2 class="text-xl font-bold text-red-700 mb-2">Delete Loan Request?</h2>
      <p class="text-gray-700 mb-6 text-center">Are you sure you want to delete this loan request? This action cannot be undone.</p>
      <form id="deleteLoanForm" class="w-full flex flex-col items-center">
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
function openModal(id) { document.getElementById(id).classList.remove('hidden'); if(window.feather) feather.replace(); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function initLoanRequestsPage() {
  var table = $('#loanTable').DataTable({
    dom: 'Bfrtip',
    buttons: [ 'copy', 'csv', 'excel', 'print' ],
    responsive: true,
    initComplete: function() { if(window.feather) feather.replace(); }
  });
  $('#statusFilter').on('change', function() {
    var val = $(this).val();
    if (val) table.column(5).search('^'+val+'$', true, false).draw();
    else table.column(5).search('').draw();
  });
  $('.view-loan-btn').on('click', function() {
    var loan = $(this).data('loan');
    var html = '';
    html += `<div class='flex flex-col gap-2'>`;
    html += `<div><b>Farmer:</b> ${loan.farmer_name}</div>`;
    html += `<div><b>Institution:</b> ${loan.institution_name}</div>`;
    html += `<div><b>Loan Type:</b> ${loan.loan_type}</div>`;
    html += `<div><b>Amount Requested:</b> ${loan.amount_requested}</div>`;
    html += `<div><b>Status:</b> ${loan.status}</div>`;
    html += `<div><b>Approval Date:</b> ${loan.approval_date}</div>`;
    html += `<div><b>Amount Approved:</b> ${loan.amount_approved}</div>`;
    html += `</div>`;
    $('#viewLoanContent').html(html);
    openModal('viewLoanModal');
  });
  $('.edit-loan-btn').on('click', function() {
    var loan = $(this).data('loan');
    var form = $('#editLoanForm')[0];
    form.id.value = loan.id;
    form.status.value = loan.status;
    form.approval_date.value = loan.approval_date;
    form.amount_approved.value = loan.amount_approved;
    $('#editLoanError').text('');
    openModal('editLoanModal');
  });
  $('#editLoanForm').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize() + '&action=edit';
    $.post('loan_crud.php', data, function(res) {
      if (res.success) {
        if(window.loadPage) loadPage('loan_requests.php', false);
        if(window.showNotification) showNotification('Loan request updated successfully!');
      } else {
        $('#editLoanError').text(res.message);
        if(window.showNotification) showNotification('Error: ' + res.message);
      }
    }, 'json');
  });
  $('.delete-loan-btn').on('click', function() {
    var id = $(this).data('id');
    $('#deleteLoanForm input[name=id]').val(id);
    openModal('deleteLoanModal');
  });
  $('#deleteLoanForm').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize() + '&action=delete';
    $.post('loan_crud.php', data, function(res) {
      if (res.success) {
        if(window.loadPage) loadPage('loan_requests.php', false);
        if(window.showNotification) showNotification('Loan request deleted successfully!');
      } else {
        if(window.showNotification) showNotification('Error: ' + res.message);
      }
    }, 'json');
  });
}

$(document).ready(function() { initLoanRequestsPage(); });
</script>
</div> 