<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$db = (new Database())->getConnection();
// Fetch all harvests with farmer info
$stmt = $db->prepare("SELECT h.*, u.username AS farmer_name FROM harvests h LEFT JOIN users u ON h.farmer_id = u.id ORDER BY h.id DESC");
$stmt->execute();
$harvests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="bg-white rounded-xl shadow-lg border border-green-200 p-8">
  <h2 class="text-2xl font-bold text-center text-green-800 mb-6 flex items-center justify-center">
    <i data-feather="package" class="w-6 h-6 mr-2 text-orange-500"></i>
    Manage Harvests
  </h2>
  <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
    <button id="openAddHarvestModal" class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
      <i data-feather="plus" class="w-5 h-5"></i>
      <span>Add Harvest</span>
    </button>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full table-auto" id="harvestTable">
      <thead class="bg-orange-200">
        <tr>
          <th class="px-6 py-3 border-b font-semibold">ID</th>
          <th class="px-6 py-3 border-b font-semibold">Farmer</th>
          <th class="px-6 py-3 border-b font-semibold">Crop Name</th>
          <th class="px-6 py-3 border-b font-semibold">Date Harvested</th>
          <th class="px-6 py-3 border-b font-semibold">Yield</th>
          <th class="px-6 py-3 border-b font-semibold">Unit</th>
          <th class="px-6 py-3 border-b font-semibold">Price per Kg</th>
          <th class="px-6 py-3 border-b font-semibold">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($harvests as $h): ?>
        <tr class="hover:bg-orange-50 transition-colors">
          <td class="px-6 py-4 border-b"><?= $h['id'] ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($h['farmer_name']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($h['crop_name']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($h['date_harvested']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($h['yield']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($h['yield_unit']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($h['price_kg']) ?></td>
          <td class="px-6 py-4 border-b">
            <button class="view-harvest-btn text-blue-600 hover:underline mr-2" data-harvest='<?= json_encode($h) ?>'>View</button>
            <button class="edit-harvest-btn text-yellow-600 hover:underline mr-2" data-harvest='<?= json_encode($h) ?>'>Edit</button>
            <button class="delete-harvest-btn text-red-600 hover:underline" data-id="<?= $h['id'] ?>">Delete</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <!-- CRUD Modals for Harvests -->
  <div id="addHarvestModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
      <button onclick="closeModal('addHarvestModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <h2 class="text-2xl font-bold text-orange-800 mb-6 flex items-center"><i data-feather="plus" class="w-6 h-6 mr-2 text-orange-500"></i>Add Harvest</h2>
      <form id="addHarvestForm" class="space-y-4">
        <input name="farmer_id" type="number" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:outline-none" placeholder="Farmer ID" required>
        <input name="crop_name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:outline-none" placeholder="Crop Name" required>
        <input name="date_harvested" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:outline-none" required>
        <input name="yield" type="number" step="0.01" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:outline-none" placeholder="Yield" required>
        <input name="yield_unit" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:outline-none" placeholder="Yield Unit (e.g. Kg)" required>
        <input name="price_kg" type="number" step="0.01" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-orange-400 focus:outline-none" placeholder="Price per Kg" required>
        <div id="addHarvestError" class="text-red-600 text-sm"></div>
        <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1"><i data-feather="plus" class="w-5 h-5"></i><span>Add Harvest</span></button>
      </form>
    </div>
  </div>
  <div id="viewHarvestModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
      <button onclick="closeModal('viewHarvestModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <h2 class="text-2xl font-bold text-blue-800 mb-6 flex items-center"><i data-feather="eye" class="w-6 h-6 mr-2 text-blue-500"></i>View Harvest</h2>
      <div id="viewHarvestContent" class="space-y-2"></div>
    </div>
  </div>
  <div id="editHarvestModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
      <button onclick="closeModal('editHarvestModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <h2 class="text-2xl font-bold text-yellow-800 mb-6 flex items-center"><i data-feather="edit" class="w-6 h-6 mr-2 text-yellow-500"></i>Edit Harvest</h2>
      <form id="editHarvestForm" class="space-y-4">
        <input type="hidden" name="id">
        <input name="farmer_id" type="number" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Farmer ID" required>
        <input name="crop_name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Crop Name" required>
        <input name="date_harvested" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" required>
        <input name="yield" type="number" step="0.01" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Yield" required>
        <input name="yield_unit" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Yield Unit (e.g. Kg)" required>
        <input name="price_kg" type="number" step="0.01" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Price per Kg" required>
        <div id="editHarvestError" class="text-red-600 text-sm"></div>
        <button type="submit" class="w-full bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1"><i data-feather="edit" class="w-5 h-5"></i><span>Save Changes</span></button>
      </form>
    </div>
  </div>
  <div id="deleteHarvestModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md relative flex flex-col items-center">
      <button onclick="closeModal('deleteHarvestModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <i data-feather="alert-triangle" class="w-12 h-12 text-red-500 mb-4"></i>
      <h2 class="text-xl font-bold text-red-700 mb-2">Delete Harvest?</h2>
      <p class="text-gray-700 mb-6 text-center">Are you sure you want to delete this harvest? This action cannot be undone.</p>
      <form id="deleteHarvestForm" class="w-full flex flex-col items-center">
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

function initHarvestsPage() {
  var table = $('#harvestTable').DataTable({
    dom: 'Bfrtip',
    buttons: [ 'copy', 'csv', 'excel', 'print' ],
    responsive: true,
    initComplete: function() { if(window.feather) feather.replace(); }
  });
  $('#openAddHarvestModal').on('click', function() { openModal('addHarvestModal'); });
  $('#addHarvestForm').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize() + '&action=add';
    $.post('harvest_crud.php', data, function(res) {
      if (res.success) {
        if(window.loadPage) loadPage('harvests.php', false);
        if(window.showNotification) showNotification('Harvest added successfully!');
      } else {
        $('#addHarvestError').text(res.message);
        if(window.showNotification) showNotification('Error: ' + res.message);
      }
    }, 'json');
  });
  $('.view-harvest-btn').on('click', function() {
    var h = $(this).data('harvest');
    var html = '';
    html += `<div class='flex flex-col gap-2'>`;
    html += `<div><b>Farmer:</b> ${h.farmer_name}</div>`;
    html += `<div><b>Crop Name:</b> ${h.crop_name}</div>`;
    html += `<div><b>Date Harvested:</b> ${h.date_harvested}</div>`;
    html += `<div><b>Yield:</b> ${h.yield} ${h.yield_unit}</div>`;
    html += `<div><b>Price per Kg:</b> ${h.price_kg}</div>`;
    html += `</div>`;
    $('#viewHarvestContent').html(html);
    openModal('viewHarvestModal');
  });
  $('.edit-harvest-btn').on('click', function() {
    var h = $(this).data('harvest');
    var form = $('#editHarvestForm')[0];
    form.id.value = h.id;
    form.farmer_id.value = h.farmer_id;
    form.crop_name.value = h.crop_name;
    form.date_harvested.value = h.date_harvested;
    form.yield.value = h.yield;
    form.yield_unit.value = h.yield_unit;
    form.price_kg.value = h.price_kg;
    $('#editHarvestError').text('');
    openModal('editHarvestModal');
  });
  $('#editHarvestForm').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize() + '&action=edit';
    $.post('harvest_crud.php', data, function(res) {
      if (res.success) {
        if(window.loadPage) loadPage('harvests.php', false);
        if(window.showNotification) showNotification('Harvest updated successfully!');
      } else {
        $('#editHarvestError').text(res.message);
        if(window.showNotification) showNotification('Error: ' + res.message);
      }
    }, 'json');
  });
  $('.delete-harvest-btn').on('click', function() {
    var id = $(this).data('id');
    $('#deleteHarvestForm input[name=id]').val(id);
    openModal('deleteHarvestModal');
  });
  $('#deleteHarvestForm').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize() + '&action=delete';
    $.post('harvest_crud.php', data, function(res) {
      if (res.success) {
        if(window.loadPage) loadPage('harvests.php', false);
        if(window.showNotification) showNotification('Harvest deleted successfully!');
      } else {
        if(window.showNotification) showNotification('Error: ' + res.message);
      }
    }, 'json');
  });
}

$(document).ready(function() { initHarvestsPage(); });
  </script>
</div> 