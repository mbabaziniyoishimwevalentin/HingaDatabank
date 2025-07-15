<!-- sidebar -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
    <!-- Sidebar Header -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 p-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <i data-feather="user" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <h3 class="text-white font-semibold">Farmer Panel</h3>
                <p class="text-green-100 text-sm">Welcome back!</p>
            </div>
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="flex flex-col">
        <!-- Dashboard -->
        <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 border-b border-gray-100 transition duration-200 group">
            <i data-feather="home" class="w-5 h-5 group-hover:text-green-600"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Profile -->
        <a href="profile.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 border-b border-gray-100 transition duration-200 group">
            <i data-feather="user-check" class="w-5 h-5 group-hover:text-green-600"></i>
            <span class="font-medium">Profile</span>
        </a>

        <!-- Farm Management Dropdown -->
        <div class="border-b border-gray-100">
            <button onclick="toggleDropdown('farmDropdown')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 group">
                <div class="flex items-center space-x-3">
                    <i data-feather="leaf" class="w-5 h-5 group-hover:text-green-600"></i>
                    <span class="font-medium">Farm Management</span>
                </div>
                <i data-feather="chevron-right" class="w-4 h-4 transition-transform duration-200" id="farmDropdownIcon"></i>
            </button>
            <div id="farmDropdown" class="hidden bg-gray-50">
                <a href="crops.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-green-100 hover:text-green-700 transition duration-200">
                    <i data-feather="sprout" class="w-4 h-4"></i>
                    <span>Crops</span>
                </a>
                <a href="harvest.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-green-100 hover:text-green-700 transition duration-200">
                    <i data-feather="package" class="w-4 h-4"></i>
                    <span>Harvest</span>
                </a>
                <a href="livestock.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-green-100 hover:text-green-700 transition duration-200">
                    <i data-feather="heart" class="w-4 h-4"></i>
                    <span>Livestock</span>
                </a>
            </div>
        </div>

        <!-- Financial Services -->
        <div class="border-b border-gray-100">
            <a href="loan_request.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 border-b border-gray-100 transition duration-200 group">
                <i data-feather="dollar-sign" class="w-5 h-5 group-hover:text-green-600"></i>
                <span class="font-medium">Request Loan</span>
            </a>
            <a href="loan_status.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 border-b border-gray-100 transition duration-200 group">
                <i data-feather="bar-chart-2" class="w-5 h-5 group-hover:text-green-600"></i>
                <span class="font-medium">Loan Status</span>
            </a>
            <a href="transaction_history.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 border-b border-gray-100 transition duration-200 group">
                <i data-feather="file-text" class="w-5 h-5 group-hover:text-green-600"></i>
                <span class="font-medium">Transaction History</span>
            </a>
        </div>

        <!-- Reports Dropdown -->
        <div class="border-b border-gray-100">
            <button onclick="toggleDropdown('reportsDropdown')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 group">
                <div class="flex items-center space-x-3">
                    <i data-feather="bar-chart-2" class="w-5 h-5 group-hover:text-green-600"></i>
                    <span class="font-medium">Reports</span>
                </div>
                <i data-feather="chevron-right" class="w-4 h-4 transition-transform duration-200" id="reportsDropdownIcon"></i>
            </button>
            <div id="reportsDropdown" class="hidden bg-gray-50">
                <a href="#" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-purple-100 hover:text-purple-700 transition duration-200">
                    <i data-feather="pie-chart" class="w-4 h-4"></i>
                    <span>Crop Analytics</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-purple-100 hover:text-purple-700 transition duration-200">
                    <i data-feather="activity" class="w-4 h-4"></i>
                    <span>Performance</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-purple-100 hover:text-purple-700 transition duration-200">
                    <i data-feather="calendar" class="w-4 h-4"></i>
                    <span>Seasonal Report</span>
                </a>
            </div>
        </div>

        <!-- Settings -->
        <a href="change_password.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 border-b border-gray-100 transition duration-200 group">
            <i data-feather="settings" class="w-5 h-5 group-hover:text-green-600"></i>
            <span class="font-medium">Settings</span>
        </a>

        <!-- Support -->
        <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 border-b border-gray-100 transition duration-200 group">
            <i data-feather="help-circle" class="w-5 h-5 group-hover:text-green-600"></i>
            <span class="font-medium">Support</span>
        </a>

        <!-- Logout -->
        <a href="../logout.php" class="flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 hover:text-red-700 transition duration-200 group">
            <i data-feather="log-out" class="w-5 h-5 group-hover:text-red-700"></i>
            <span class="font-medium">Logout</span>
        </a>
    </div>
</div>

<script>
    function toggleDropdown(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        const icon = document.getElementById(dropdownId + 'Icon');
        
        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('hidden');
            icon.style.transform = 'rotate(90deg)';
        } else {
            dropdown.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    }
    
    // Initialize Feather icons after DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();
    });
</script>