<!-- Institution Sidebar -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200 max-w-sm">
    <!-- Sidebar Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <i data-feather="building" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <h3 class="text-white font-semibold">Institution Panel</h3>
                <p class="text-blue-100 text-sm">Financial Services</p>
            </div>
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="flex flex-col">
        <!-- Dashboard -->
        <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 border-b border-gray-100 transition duration-200 group">
            <i data-feather="home" class="w-5 h-5 group-hover:text-blue-600"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Institution Profile -->
        <a href="update_info.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 border-b border-gray-100 transition duration-200 group">
            <i data-feather="building-2" class="w-5 h-5 group-hover:text-blue-600"></i>
            <span class="font-medium">Institution Info</span>
        </a>

        <!-- Loan Management Dropdown -->
        <div class="border-b border-gray-100">
            <button onclick="toggleDropdown('loanDropdown')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition duration-200 group">
                <div class="flex items-center space-x-3">
                    <i data-feather="dollar-sign" class="w-5 h-5 group-hover:text-blue-600"></i>
                    <span class="font-medium">Loan Management</span>
                </div>
                <i data-feather="chevron-right" class="w-4 h-4 transition-transform duration-200" id="loanDropdownIcon"></i>
            </button>
            <div id="loanDropdown" class="hidden bg-gray-50">
                <a href="loans.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-green-100 hover:text-green-700 transition duration-200">
                    <i data-feather="file-text" class="w-4 h-4"></i>
                    <span>Loan Requests</span>
                </a>
                <a href="approved_loans.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-green-100 hover:text-green-700 transition duration-200">
                    <i data-feather="check-circle" class="w-4 h-4"></i>
                    <span>Approved Loans</span>
                </a>
                <a href="loan_calculation.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-green-100 hover:text-green-700 transition duration-200">
                    <i data-feather="calculator" class="w-4 h-4"></i>
                    <span>Loan Calculator</span>
                </a>
                <a href="loan_history.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-green-100 hover:text-green-700 transition duration-200">
                    <i data-feather="clock" class="w-4 h-4"></i>
                    <span>Loan History</span>
                </a>
            </div>
        </div>

        <!-- Client Management Dropdown -->
        <div class="border-b border-gray-100">
            <button onclick="toggleDropdown('clientDropdown')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition duration-200 group">
                <div class="flex items-center space-x-3">
                    <i data-feather="users" class="w-5 h-5 group-hover:text-blue-600"></i>
                    <span class="font-medium">Client Management</span>
                </div>
                <i data-feather="chevron-right" class="w-4 h-4 transition-transform duration-200" id="clientDropdownIcon"></i>
            </button>
            <div id="clientDropdown" class="hidden bg-gray-50">
                <a href="farmers.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-orange-100 hover:text-orange-700 transition duration-200">
                    <i data-feather="user" class="w-4 h-4"></i>
                    <span>Farmers</span>
                </a>
                <a href="client_profiles.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-orange-100 hover:text-orange-700 transition duration-200">
                    <i data-feather="user-check" class="w-4 h-4"></i>
                    <span>Client Profiles</span>
                </a>
                <a href="credit_scoring.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-orange-100 hover:text-orange-700 transition duration-200">
                    <i data-feather="award" class="w-4 h-4"></i>
                    <span>Credit Scoring</span>
                </a>
            </div>
        </div>

        <!-- Financial Reports Dropdown -->
        <div class="border-b border-gray-100">
            <button onclick="toggleDropdown('reportsDropdown')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition duration-200 group">
                <div class="flex items-center space-x-3">
                    <i data-feather="bar-chart-2" class="w-5 h-5 group-hover:text-blue-600"></i>
                    <span class="font-medium">Reports & Analytics</span>
                </div>
                <i data-feather="chevron-right" class="w-4 h-4 transition-transform duration-200" id="reportsDropdownIcon"></i>
            </button>
            <div id="reportsDropdown" class="hidden bg-gray-50">
                <a href="financial_reports.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-purple-100 hover:text-purple-700 transition duration-200">
                    <i data-feather="pie-chart" class="w-4 h-4"></i>
                    <span>Financial Reports</span>
                </a>
                <a href="portfolio_analysis.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-purple-100 hover:text-purple-700 transition duration-200">
                    <i data-feather="trending-up" class="w-4 h-4"></i>
                    <span>Portfolio Analysis</span>
                </a>
                <a href="risk_assessment.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-purple-100 hover:text-purple-700 transition duration-200">
                    <i data-feather="shield" class="w-4 h-4"></i>
                    <span>Risk Assessment</span>
                </a>
                <a href="compliance_reports.php" class="flex items-center space-x-3 px-8 py-2 text-gray-600 hover:bg-purple-100 hover:text-purple-700 transition duration-200">
                    <i data-feather="file-check" class="w-4 h-4"></i>
                    <span>Compliance Reports</span>
                </a>
            </div>
        </div>

        <!-- Notifications -->
        <a href="notifications.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 border-b border-gray-100 transition duration-200 group">
            <i data-feather="bell" class="w-5 h-5 group-hover:text-blue-600"></i>
            <span class="font-medium">Notifications</span>
            <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">3</span>
        </a>

        <!-- Settings -->
        <a href="change_password.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 border-b border-gray-100 transition duration-200 group">
            <i data-feather="settings" class="w-5 h-5 group-hover:text-blue-600"></i>
            <span class="font-medium">Settings</span>
        </a>

        <!-- Support -->
        <a href="support.php" class="flex items-center space-x-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 border-b border-gray-100 transition duration-200 group">
            <i data-feather="help-circle" class="w-5 h-5 group-hover:text-blue-600"></i>
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