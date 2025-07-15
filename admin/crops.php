<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$db = (new Database())->getConnection();
// Fetch all crops with farmer info
$stmt = $db->prepare("SELECT c.*, u.username AS farmer_name FROM crops c LEFT JOIN users u ON c.farmer_id = u.id ORDER BY c.id DESC");
$stmt->execute();
$crops = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="bg-white rounded-xl shadow-lg border border-green-200 p-8">
  <h2 class="text-2xl font-bold text-center text-green-800 mb-6 flex items-center justify-center">
    <i data-feather="leaf" class="w-6 h-6 mr-2 text-green-500"></i>
    Manage Crops
  </h2>
  <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
    <button id="openAddCropModal" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
      <i data-feather="plus" class="w-5 h-5"></i>
      <span>Add Crop</span>
    </button>
    <div>
      <label for="typeFilter" class="mr-2 font-medium text-gray-700">Filter by Type:</label>
      <select id="typeFilter" class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none">
        <option value="">All</option>
        <option value="Fruit">Fruit</option>
        <option value="Vegetable">Vegetable</option>
        <option value="Cereal">Cereal</option>
        <option value="Legume">Legume</option>
        <option value="Other">Other</option>
      </select>
    </div>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full table-auto" id="cropTable">
      <thead class="bg-green-200">
        <tr>
          <th class="px-6 py-3 border-b font-semibold">ID</th>
          <th class="px-6 py-3 border-b font-semibold">Farmer</th>
          <th class="px-6 py-3 border-b font-semibold">Crop Name</th>
          <th class="px-6 py-3 border-b font-semibold">Type</th>
          <th class="px-6 py-3 border-b font-semibold">Area (ha)</th>
          <th class="px-6 py-3 border-b font-semibold">Planting Date</th>
          <th class="px-6 py-3 border-b font-semibold">Expected Harvest</th>
          <th class="px-6 py-3 border-b font-semibold">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($crops as $crop): ?>
        <tr class="hover:bg-green-50 transition-colors">
          <td class="px-6 py-4 border-b"><?= $crop['id'] ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($crop['farmer_name']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($crop['crop_name']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($crop['crop_type']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($crop['area_planted']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($crop['planting_date']) ?></td>
          <td class="px-6 py-4 border-b"><?= htmlspecialchars($crop['expected_harvest_date']) ?></td>
          <td class="px-6 py-4 border-b">
            <button class="view-crop-btn text-blue-600 hover:underline mr-2" data-crop='<?= json_encode($crop) ?>'>View</button>
            <button class="edit-crop-btn text-yellow-600 hover:underline mr-2" data-crop='<?= json_encode($crop) ?>'>Edit</button>
            <button class="delete-crop-btn text-red-600 hover:underline" data-id="<?= $crop['id'] ?>">Delete</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <!-- CRUD Modals for Crops -->
  <div id="addCropModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
      <button onclick="closeModal('addCropModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <h2 class="text-2xl font-bold text-green-800 mb-6 flex items-center"><i data-feather="plus" class="w-6 h-6 mr-2 text-green-500"></i>Add Crop</h2>
      <form id="addCropForm" class="space-y-4">
        <input name="farmer_id" type="number" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="Farmer ID" required>
        <input name="crop_name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="Crop Name" required>
        <input name="crop_type" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="Crop Type (e.g. Fruit, Vegetable)" required>
        <input name="area_planted" type="number" step="0.01" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="Area Planted (ha)" required>
        <input name="planting_date" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" required>
        <input name="expected_harvest_date" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" required>
        <div id="addCropError" class="text-red-600 text-sm"></div>
        <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1"><i data-feather="plus" class="w-5 h-5"></i><span>Add Crop</span></button>
      </form>
    </div>
  </div>
  <div id="viewCropModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
      <button onclick="closeModal('viewCropModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <h2 class="text-2xl font-bold text-blue-800 mb-6 flex items-center"><i data-feather="eye" class="w-6 h-6 mr-2 text-blue-500"></i>View Crop</h2>
      <div id="viewCropContent" class="space-y-2"></div>
    </div>
  </div>
  <div id="editCropModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
      <button onclick="closeModal('editCropModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <h2 class="text-2xl font-bold text-yellow-800 mb-6 flex items-center"><i data-feather="edit" class="w-6 h-6 mr-2 text-yellow-500"></i>Edit Crop</h2>
      <form id="editCropForm" class="space-y-4">
        <input type="hidden" name="id">
        <input name="farmer_id" type="number" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Farmer ID" required>
        <input name="crop_name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Crop Name" required>
        <input name="crop_type" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Crop Type (e.g. Fruit, Vegetable)" required>
        <input name="area_planted" type="number" step="0.01" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Area Planted (ha)" required>
        <input name="planting_date" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" required>
        <input name="expected_harvest_date" type="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" required>
        <div id="editCropError" class="text-red-600 text-sm"></div>
        <button type="submit" class="w-full bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1"><i data-feather="edit" class="w-5 h-5"></i><span>Save Changes</span></button>
      </form>
    </div>
  </div>
  <div id="deleteCropModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md relative flex flex-col items-center">
      <button onclick="closeModal('deleteCropModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
      <i data-feather="alert-triangle" class="w-12 h-12 text-red-500 mb-4"></i>
      <h2 class="text-xl font-bold text-red-700 mb-2">Delete Crop?</h2>
      <p class="text-gray-700 mb-6 text-center">Are you sure you want to delete this crop? This action cannot be undone.</p>
      <form id="deleteCropForm" class="w-full flex flex-col items-center">
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

function initCropsPage() {
  var table = $('#cropTable').DataTable({
    dom: 'Bfrtip',
    buttons: [ 'copy', 'csv', 'excel', 'print' ],
    responsive: true,
    initComplete: function() { if(window.feather) feather.replace(); }
  });
  $('#typeFilter').on('change', function() {
    var val = $(this).val();
    if (val) table.column(3).search('^'+val+'$', true, false).draw();
    else table.column(3).search('').draw();
  });
  $('#openAddCropModal').on('click', function() { openModal('addCropModal'); });
  $('#addCropForm').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize() + '&action=add';
    $.post('crop_crud.php', data, function(res) {
      if (res.success) {
        if(window.loadPage) loadPage('crops.php', false);
        if(window.showNotification) showNotification('Crop added successfully!');
      } else {
        $('#addCropError').text(res.message);
        if(window.showNotification) showNotification('Error: ' + res.message);
      }
    }, 'json');
  });
  $('.view-crop-btn').on('click', function() {
    var crop = $(this).data('crop');
    var html = '';
    html += `<div class='flex flex-col gap-2'>`;
    html += `<div><b>Farmer:</b> ${crop.farmer_name}</div>`;
    html += `<div><b>Crop Name:</b> ${crop.crop_name}</div>`;
    html += `<div><b>Type:</b> ${crop.crop_type}</div>`;
    html += `<div><b>Area:</b> ${crop.area_planted} ha</div>`;
    html += `<div><b>Planting Date:</b> ${crop.planting_date}</div>`;
    html += `<div><b>Expected Harvest:</b> ${crop.expected_harvest_date}</div>`;
    html += `</div>`;
    $('#viewCropContent').html(html);
    openModal('viewCropModal');
  });
  $('.edit-crop-btn').on('click', function() {
    var crop = $(this).data('crop');
    var form = $('#editCropForm')[0];
    form.id.value = crop.id;
    form.farmer_id.value = crop.farmer_id;
    form.crop_name.value = crop.crop_name;
    form.crop_type.value = crop.crop_type;
    form.area_planted.value = crop.area_planted;
    form.planting_date.value = crop.planting_date;
    form.expected_harvest_date.value = crop.expected_harvest_date;
    $('#editCropError').text('');
    openModal('editCropModal');
  });
  $('#editCropForm').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize() + '&action=edit';
    $.post('crop_crud.php', data, function(res) {
      if (res.success) {
        if(window.loadPage) loadPage('crops.php', false);
        if(window.showNotification) showNotification('Crop updated successfully!');
      } else {
        $('#editCropError').text(res.message);
        if(window.showNotification) showNotification('Error: ' + res.message);
      }
    }, 'json');
  });
  $('.delete-crop-btn').on('click', function() {
    var id = $(this).data('id');
    $('#deleteCropForm input[name=id]').val(id);
    openModal('deleteCropModal');
  });
  $('#deleteCropForm').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize() + '&action=delete';
    $.post('crop_crud.php', data, function(res) {
      if (res.success) {
        if(window.loadPage) loadPage('crops.php', false);
        if(window.showNotification) showNotification('Crop deleted successfully!');
      } else {
        if(window.showNotification) showNotification('Error: ' + res.message);
      }
    }, 'json');
  });
}

$(document).ready(function() { initCropsPage(); });
  </script>
</div> 