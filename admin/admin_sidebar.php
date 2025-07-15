<div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 max-w-xs min-h-screen flex flex-col">
    <!-- Sidebar Header -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 p-4 flex items-center space-x-3">
        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
            <i data-feather="activity" class="w-5 h-5 text-white"></i>
        </div>
        <div>
            <h3 class="text-white font-semibold">Admin Panel</h3>
            <p class="text-green-100 text-xs">System Management</p>
        </div>
    </div>
    <!-- Navigation Links -->
    <nav class="flex-1 flex flex-col py-4">
        <a href="#" data-page="dashboard_content.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 group sidebar-link">
            <i data-feather="home" class="w-5 h-5 group-hover:text-green-600"></i>
            <span class="font-medium">Dashboard</span>
        </a>
        <a href="add_institution.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 group">
            <i data-feather="building" class="w-5 h-5 group-hover:text-green-600"></i>
            <span class="font-medium">Add Institution</span>
        </a>
        <a href="#" data-page="manage_users.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 group sidebar-link">
            <i data-feather="users" class="w-5 h-5 group-hover:text-green-600"></i>
            <span class="font-medium">Manage Users</span>
        </a>
        <a href="#" data-page="crops.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 group sidebar-link">
            <i data-feather="leaf" class="w-5 h-5 group-hover:text-green-600"></i>
            <span class="font-medium">Crops</span>
        </a>
        <a href="#" data-page="harvests.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 group sidebar-link">
            <i data-feather="shopping-bag" class="w-5 h-5 group-hover:text-green-600"></i>
            <span class="font-medium">Harvests</span>
        </a>
        <a href="#" data-page="livestock.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 group sidebar-link">
            <i data-feather="feather" class="w-5 h-5 group-hover:text-green-600"></i>
            <span class="font-medium">Livestock</span>
        </a>
        <a href="#" data-page="loan_requests.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 group sidebar-link">
            <i data-feather="credit-card" class="w-5 h-5 group-hover:text-green-600"></i>
            <span class="font-medium">Loan Requests</span>
        </a>
        <a href="#" data-page="reports.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 group sidebar-link">
            <i data-feather="bar-chart-2" class="w-5 h-5 group-hover:text-green-600"></i>
            <span class="font-medium">Reports</span>
        </a>
    </nav>
    <!-- Logout -->
    <div class="border-t border-gray-100 p-4">
        <a href="../logout.php" class="flex items-center space-x-3 text-red-600 hover:bg-red-50 hover:text-red-700 px-4 py-3 rounded-lg transition duration-200 group">
            <i data-feather="log-out" class="w-5 h-5 group-hover:text-red-700"></i>
            <span class="font-medium">Logout</span>
        </a>
    </div>
</div>
<script>feather.replace();</script>
