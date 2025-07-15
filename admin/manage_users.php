<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$db = (new Database())->getConnection();

if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
}

$stmt = $db->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 py-8">
    <div class="container mx-auto px-4">
        <div class="bg-white rounded-xl shadow-lg border border-green-200 p-8">
            <h2 class="text-2xl font-bold text-center text-green-800 mb-6 flex items-center justify-center">
                <i data-feather="users" class="w-6 h-6 mr-2 text-green-500"></i>
                Manage Users
            </h2>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
                <button id="openAddUserModal" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <i data-feather="plus" class="w-5 h-5"></i>
                    <span>Add User</span>
                </button>
                <div>
                    <label for="roleFilter" class="mr-2 font-medium text-gray-700">Filter by Role:</label>
                    <select id="roleFilter" class="px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none">
                        <option value="">All</option>
                        <option value="farmer">Farmer</option>
                        <option value="institution">Institution</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto" id="userTable">
                    <thead class="bg-green-200">
                        <tr>
                            <th class="px-6 py-3 border-b font-semibold">ID</th>
                            <th class="px-6 py-3 border-b font-semibold">Username</th>
                            <th class="px-6 py-3 border-b font-semibold">Email</th>
                            <th class="px-6 py-3 border-b font-semibold">Role</th>
                            <th class="px-6 py-3 border-b font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-green-50 transition-colors">
                            <td class="px-6 py-4 border-b"><?= $u['id'] ?></td>
                            <td class="px-6 py-4 border-b"><?= $u['username'] ?></td>
                            <td class="px-6 py-4 border-b"><?= $u['email'] ?></td>
                            <td class="px-6 py-4 border-b"><?= $u['role'] ?></td>
                            <td class="px-6 py-4 border-b">
                                <button class="view-user-btn text-blue-600 hover:underline mr-2" data-user='<?= json_encode(["id"=>$u["id"],"username"=>$u["username"],"email"=>$u["email"],"role"=>$u["role"]]) ?>'>View</button>
                                <button class="edit-user-btn text-yellow-600 hover:underline mr-2" data-user='<?= json_encode(["id"=>$u["id"],"username"=>$u["username"],"email"=>$u["email"],"role"=>$u["role"]]) ?>'>Edit</button>
                                <?php if ($u['role'] !== 'admin'): ?>
                                <button class="delete-user-btn text-red-600 hover:underline" data-id="<?= $u['id'] ?>">Delete</button>
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

<!-- CRUD Modals for Users -->
<div id="addUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
        <button onclick="closeModal('addUserModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
        <h2 class="text-2xl font-bold text-green-800 mb-6 flex items-center"><i data-feather="plus" class="w-6 h-6 mr-2 text-green-500"></i>Add User</h2>
        <form id="addUserForm" class="space-y-4">
            <input name="username" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="Username" required>
            <input name="email" type="email" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="Email" required>
            <input name="password" type="password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" placeholder="Password" required>
            <select name="role" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none" required>
                <option value="">Select Role</option>
                <option value="farmer">Farmer</option>
                <option value="institution">Institution</option>
                <option value="admin">Admin</option>
            </select>
            <div id="addUserError" class="text-red-600 text-sm"></div>
            <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1"><i data-feather="plus" class="w-5 h-5"></i><span>Add User</span></button>
        </form>
    </div>
</div>
<div id="viewUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
        <button onclick="closeModal('viewUserModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
        <h2 class="text-2xl font-bold text-blue-800 mb-6 flex items-center"><i data-feather="eye" class="w-6 h-6 mr-2 text-blue-500"></i>View User</h2>
        <div id="viewUserContent" class="space-y-2"></div>
    </div>
</div>
<div id="editUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-lg relative">
        <button onclick="closeModal('editUserModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
        <h2 class="text-2xl font-bold text-yellow-800 mb-6 flex items-center"><i data-feather="edit" class="w-6 h-6 mr-2 text-yellow-500"></i>Edit User</h2>
        <form id="editUserForm" class="space-y-4">
            <input type="hidden" name="id">
            <input name="username" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Username" required>
            <input name="email" type="email" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="Email" required>
            <input name="password" type="password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" placeholder="New Password (leave blank to keep current)">
            <select name="role" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-yellow-400 focus:outline-none" required>
                <option value="">Select Role</option>
                <option value="farmer">Farmer</option>
                <option value="institution">Institution</option>
                <option value="admin">Admin</option>
            </select>
            <div id="editUserError" class="text-red-600 text-sm"></div>
            <button type="submit" class="w-full bg-gradient-to-r from-yellow-400 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1"><i data-feather="edit" class="w-5 h-5"></i><span>Save Changes</span></button>
        </form>
    </div>
</div>
<div id="deleteUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
    <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md relative flex flex-col items-center">
        <button onclick="closeModal('deleteUserModal')" class="absolute top-4 right-4 text-gray-400 hover:text-red-500"><i data-feather="x"></i></button>
        <i data-feather="alert-triangle" class="w-12 h-12 text-red-500 mb-4"></i>
        <h2 class="text-xl font-bold text-red-700 mb-2">Delete User?</h2>
        <p class="text-gray-700 mb-6 text-center">Are you sure you want to delete this user? This action cannot be undone.</p>
        <form id="deleteUserForm" class="w-full flex flex-col items-center">
            <input type="hidden" name="id">
            <button type="submit" class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1"><i data-feather="trash-2" class="w-5 h-5"></i><span>Delete</span></button>
        </form>
    </div>
</div>
<script>
function openModal(id) { document.getElementById(id).classList.remove('hidden'); if(window.feather) feather.replace(); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function initUsersPage() {
  var table = $('#userTable').DataTable({
    dom: 'Bfrtip',
    buttons: [ 'copy', 'csv', 'excel', 'print' ],
    responsive: true,
    initComplete: function() { if(window.feather) feather.replace(); }
  });
  $('#roleFilter').on('change', function() {
    var val = $(this).val();
    if (val) table.column(3).search('^'+val+'$', true, false).draw();
    else table.column(3).search('').draw();
  });
  $('#openAddUserModal').on('click', function() { openModal('addUserModal'); });
  $('#addUserForm').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize() + '&action=add';
    $.post('user_crud.php', data, function(res) {
      if (res.success) {
        if(window.loadPage) loadPage('manage_users.php', false);
        if(window.showNotification) showNotification('User added successfully!');
      } else {
        $('#addUserError').text(res.message);
        if(window.showNotification) showNotification('Error: ' + res.message);
      }
    }, 'json');
  });
  $('.view-user-btn').on('click', function() {
    var user = $(this).data('user');
    var html = '';
    html += `<div class='flex flex-col gap-2'>`;
    html += `<div><b>Username:</b> ${user.username}</div>`;
    html += `<div><b>Email:</b> ${user.email}</div>`;
    html += `<div><b>Role:</b> ${user.role}</div>`;
    html += `</div>`;
    $('#viewUserContent').html(html);
    openModal('viewUserModal');
  });
  $('.edit-user-btn').on('click', function() {
    var user = $(this).data('user');
    var form = $('#editUserForm')[0];
    form.id.value = user.id;
    form.username.value = user.username;
    form.email.value = user.email;
    form.password.value = '';
    form.role.value = user.role;
    $('#editUserError').text('');
    openModal('editUserModal');
  });
  $('#editUserForm').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize() + '&action=edit';
    $.post('user_crud.php', data, function(res) {
      if (res.success) {
        if(window.loadPage) loadPage('manage_users.php', false);
        if(window.showNotification) showNotification('User updated successfully!');
      } else {
        $('#editUserError').text(res.message);
        if(window.showNotification) showNotification('Error: ' + res.message);
      }
    }, 'json');
  });
  $('.delete-user-btn').on('click', function() {
    var id = $(this).data('id');
    $('#deleteUserForm input[name=id]').val(id);
    openModal('deleteUserModal');
  });
  $('#deleteUserForm').on('submit', function(e) {
    e.preventDefault();
    var data = $(this).serialize() + '&action=delete';
    $.post('user_crud.php', data, function(res) {
      if (res.success) {
        if(window.loadPage) loadPage('manage_users.php', false);
        if(window.showNotification) showNotification('User deleted successfully!');
      } else {
        if(window.showNotification) showNotification('Error: ' + res.message);
      }
    }, 'json');
  });
}

$(document).ready(function() { initUsersPage(); });
</script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" />
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script>
$(document).ready(function() { initUsersPage(); });
</script>

