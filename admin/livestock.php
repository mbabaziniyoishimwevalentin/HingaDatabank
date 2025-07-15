<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$db = (new Database())->getConnection();
// Fetch all livestock with farmer info
$stmt = $db->prepare("SELECT l.*, u.username AS farmer_name FROM livestock l LEFT JOIN users u ON l.farmer_id = u.id ORDER BY l.id DESC");
$stmt->execute();
$livestock = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="bg-white rounded-xl shadow-lg border border-green-200 p-8">
  <h2 class="text-2xl font-bold text-center text-green-800 mb-6 flex items-center justify-center">
    <i data-feather="zap" class="w-6 h-6 mr-2 text-amber-500"></i>
    Manage Livestock
  </h2>
  <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
    <button id="openAddLivestockModal" class="bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
      <i data-feather="plus" class="w-5 h-5"></i>
      <span>Add Livestock</span>
    </button>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full table-auto" id="livestockTable">
      <thead class="bg-amber-200">
        <tr>
          <th class="px-6 py-3 border-b font-semibold">ID</th>
          <th class="px-6 py-3 border-b font-semibold">Farmer</th>
          <th class="px-6 py-3 border-b font-semibold">Animal Type</th>
          <th class="px-6 py-3 border-b font-semibold">Quantity</th>
          <th class="px-6 py-3 border-b font-semibold">Value</th>
          <th class="px-6 py-3 border-b font-semibold">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($livestock as $l): ?>
        <tr class="hover:bg-amber-50 transition-colors">
          <td class="px-6 py-4 border-b"><?= $l['id'] ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($l['farmer_name']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($l['animal_type']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($l['quantity']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($l['value_all_animals']) ?></td>
          <td class="px-6 py-4 border-b">
            <button class="view-livestock-btn text-blue-600 hover:underline mr-2" data-livestock='<?= json_encode($l) ?>'>View</button>
            <button class="edit-livestock-btn text-yellow-600 hover:underline mr-2" data-livestock='<?= json_encode($l) ?>'>Edit</button>
            <button class="delete-livestock-btn text-red-600 hover:underline" data-id="<?= $l['id'] ?>">Delete</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <!-- CRUD Modals for Livestock -->
  <div id="addLivestockModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
      <button onclick="closeModal('addLivestockModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <h2 class="text-2xl font-bold text-amber-800 mb-6 flex items-center"><i data-feather="plus" class="w-6 h-6 mr-2 text-amber-500"></i>Add Livestock</h2>
      <form id="addLivestockForm" class="space-y-4">
        <input name="farmer_id" type="number" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-400 focus:outline-none" placeholder="Farmer ID" required>
        <input name="animal_type" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-400 focus:outline-none" placeholder="Animal Type" required>
        <input name="quantity" type="number" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-400 focus:outline-none" placeholder="Quantity" required>
        <input name="value_all_animals" type="number" step="0.01" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-amber-400 focus:outline-none" placeholder="Total Value" required>
        <div id="addLivestockError" class="text-red-600 text-sm"></div>
        <button type="submit" class="w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1"><i data-feather="plus" class="w-5 h-5"></i><span>Add Livestock</span></button>
      </form>
    </div>
  </div>
  <div id="viewLivestockModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
      <button onclick="closeModal('viewLivestockModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <h2 class="text-2xl font-bold text-blue-800 mb-6 flex items-center"><i data-feather="eye" class="w-6 h-6 mr-2 text-blue-500"></i>View Livestock</h2>
      <div id="viewLivestockContent" class="space-y-2"></div>
    </div>
  </div>
  <div id="editLivestockModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
      <button onclick="closeModal('editLivestockModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <h2 class="text-2xl font-bold text-yellow-800 mb-6 flex items-center"><i data-feather="edit" class="w-6 h-6 mr-2 text-yellow-500"></i>Edit Livestock</h2>
      <form id="editLivestockForm" class="space-y-4">
        <input type="hidden" name="id">
        <input name="farmer_id" type="number" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Farmer ID" required>
        <input name="animal_type" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Animal Type" required>
        <input name="quantity" type="number" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Quantity" required>
        <input name="value_all_animals" type="number" step="0.01" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Total Value" required>
        <div id="editLivestockError" class="text-red-600 text-sm"></div>
        <button type="submit" class="w-full bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1"><i data-feather="edit" class="w-5 h-5"></i><span>Save Changes</span></button>
      </form>
    </div>
  </div>
  <div id="deleteLivestockModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md relative flex flex-col items-center">
      <button onclick="closeModal('deleteLivestockModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <i data-feather="alert-triangle" class="w-12 h-12 text-red-500 mb-4"></i>
      <h2 class="text-xl font-bold text-red-700 mb-2">Delete Livestock?</h2>
      <p class="text-gray-700 mb-6 text-center">Are you sure you want to delete this livestock record? This action cannot be undone.</p>
      <form id="deleteLivestockForm" class="w-full flex flex-col items-center">
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

  function initLivestockPage() {
    var table = $('#livestockTable').DataTable({
      dom: 'Bfrtip',
      buttons: [ 'copy', 'csv', 'excel', 'print' ],
      responsive: true,
      initComplete: function() { if(window.feather) feather.replace(); }
    });
    $('#openAddLivestockModal').on('click', function() { openModal('addLivestockModal'); });
    $('#addLivestockForm').on('submit', function(e) {
      e.preventDefault();
      var data = $(this).serialize() + '&action=add';
      $.post('livestock_crud.php', data, function(res) {
        if (res.success) {
          if(window.loadPage) loadPage('livestock.php', false);
          if(window.showNotification) showNotification('Livestock added successfully!');
        } else {
          $('#addLivestockError').text(res.message);
          if(window.showNotification) showNotification('Error: ' + res.message);
        }
      }, 'json');
    });
    $('.view-livestock-btn').on('click', function() {
      var l = $(this).data('livestock');
      var html = '';
      html += `<div class='flex flex-col gap-2'>`;
      html += `<div><b>Farmer:</b> ${l.farmer_name}</div>`;
      html += `<div><b>Animal Type:</b> ${l.animal_type}</div>`;
      html += `<div><b>Quantity:</b> ${l.quantity}</div>`;
      html += `<div><b>Total Value:</b> ${l.value_all_animals}</div>`;
      html += `</div>`;
      $('#viewLivestockContent').html(html);
      openModal('viewLivestockModal');
    });
    $('.edit-livestock-btn').on('click', function() {
      var l = $(this).data('livestock');
      var form = $('#editLivestockForm')[0];
      form.id.value = l.id;
      form.farmer_id.value = l.farmer_id;
      form.animal_type.value = l.animal_type;
      form.quantity.value = l.quantity;
      form.value_all_animals.value = l.value_all_animals;
      $('#editLivestockError').text('');
      openModal('editLivestockModal');
    });
    $('#editLivestockForm').on('submit', function(e) {
      e.preventDefault();
      var data = $(this).serialize() + '&action=edit';
      $.post('livestock_crud.php', data, function(res) {
        if (res.success) {
          if(window.loadPage) loadPage('livestock.php', false);
          if(window.showNotification) showNotification('Livestock updated successfully!');
        } else {
          $('#editLivestockError').text(res.message);
          if(window.showNotification) showNotification('Error: ' + res.message);
        }
      }, 'json');
    });
    $('.delete-livestock-btn').on('click', function() {
      var id = $(this).data('id');
      $('#deleteLivestockForm input[name=id]').val(id);
      openModal('deleteLivestockModal');
    });
    $('#deleteLivestockForm').on('submit', function(e) {
      e.preventDefault();
      var data = $(this).serialize() + '&action=delete';
      $.post('livestock_crud.php', data, function(res) {
        if (res.success) {
          if(window.loadPage) loadPage('livestock.php', false);
          if(window.showNotification) showNotification('Livestock deleted successfully!');
        } else {
          if(window.showNotification) showNotification('Error: ' + res.message);
        }
      }, 'json');
    });
  }

  $(document).ready(function() { initLivestockPage(); });
  </script>
</div> 