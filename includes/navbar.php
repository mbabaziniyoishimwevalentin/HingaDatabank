<!-- navbar -->
<nav class="gradient-bg shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <!-- Brand/Logo -->
            <a href="#" class="text-white text-2xl font-bold hover:text-green-100 transition duration-300 flex items-center space-x-2 hover-scale">
                <div class="bg-white bg-opacity-20 p-2 rounded-full">
                    <i data-feather="activity" class="w-6 h-6"></i>
                </div>
                <span class="text-shadow">HingaDatabank</span>
            </a>
            
            <!-- Navigation Links (Desktop) -->
            <div class="hidden lg:flex space-x-8">
                <!-- Quick Actions Dropdown -->
                <div class="relative group">
                    <button class="text-white hover:text-green-100 font-medium py-2 px-4 rounded-md transition duration-300 flex items-center space-x-1 glass-effect">
                        <i data-feather="zap" class="w-4 h-4"></i>
                        <span>Quick Actions</span>
                        <i data-feather="chevron-down" class="w-4 h-4 group-hover:rotate-180 transition-transform duration-300"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform group-hover:translate-y-0 translate-y-2">
                        <div class="py-2">
                            <a href="crops.php" class="block px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 flex items-center space-x-2">
                                <i data-feather="leaf" class="w-4 h-4"></i>
                                <span>Add Crop</span>
                            </a>
                            <a href="harvest.php" class="block px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 flex items-center space-x-2">
                                <i data-feather="box" class="w-4 h-4"></i>
                                <span>Record Harvest</span>
                            </a>
                            <a href="livestock.php" class="block px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 flex items-center space-x-2">
                                <i data-feather="heart" class="w-4 h-4"></i>
                                <span>Manage Livestock</span>
                            </a>
                            <hr class="my-2">
                            <a href="loan_request.php" class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition duration-200 flex items-center space-x-2">
                                <i data-feather="dollar-sign" class="w-4 h-4"></i>
                                <span>Request Loan</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Reports Dropdown -->
                <div class="relative group">
                    <button class="text-white hover:text-green-100 font-medium py-2 px-4 rounded-md transition duration-300 flex items-center space-x-1 glass-effect">
                        <i data-feather="bar-chart-2" class="w-4 h-4"></i>
                        <span>Reports</span>
                        <i data-feather="chevron-down" class="w-4 h-4 group-hover:rotate-180 transition-transform duration-300"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform group-hover:translate-y-0 translate-y-2">
                        <div class="py-2">
                            <a href="#" class="block px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 flex items-center space-x-2">
                                <i data-feather="trending-up" class="w-4 h-4"></i>
                                <span>Crop Analytics</span>
                            </a>
                            <a href="#" class="block px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 flex items-center space-x-2">
                                <i data-feather="pie-chart" class="w-4 h-4"></i>
                                <span>Financial Summary</span>
                            </a>
                            <a href="#" class="block px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 flex items-center space-x-2">
                                <i data-feather="calendar" class="w-4 h-4"></i>
                                <span>Seasonal Report</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help -->
                <a href="#" class="text-white hover:text-green-100 font-medium py-2 px-4 rounded-md transition duration-300 flex items-center space-x-1 glass-effect">
                    <i data-feather="help-circle" class="w-4 h-4"></i>
                    <span>Help</span>
                </a>
            </div>

            <!-- User Profile & Mobile Menu -->
            <div class="flex items-center space-x-4">
                <!-- User Dropdown -->
                <div class="relative group">
                    <button class="flex items-center space-x-2 text-white hover:text-green-100 transition duration-300 glass-effect px-3 py-2 rounded-lg">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <i data-feather="user" class="w-4 h-4"></i>
                        </div>
                        <span class="hidden md:block">Profile</span>
                        <i data-feather="chevron-down" class="w-4 h-4 group-hover:rotate-180 transition-transform duration-300"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform group-hover:translate-y-0 translate-y-2">
                        <div class="py-2">
                            <a href="profile.php" class="block px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 flex items-center space-x-2">
                                <i data-feather="user" class="w-4 h-4"></i>
                                <span>My Profile</span>
                            </a>
                            <a href="change_password.php" class="block px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 transition duration-200 flex items-center space-x-2">
                                <i data-feather="lock" class="w-4 h-4"></i>
                                <span>Change Password</span>
                            </a>
                            <hr class="my-2">
                            <a href="../logout.php" class="block px-4 py-3 text-red-600 hover:bg-red-50 transition duration-200 flex items-center space-x-2">
                                <i data-feather="log-out" class="w-4 h-4"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button class="lg:hidden text-white hover:text-green-100 transition duration-300" onclick="toggleMobileMenu()">
                    <i data-feather="menu" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="lg:hidden hidden bg-green-700 bg-opacity-95 backdrop-blur-sm">
        <div class="px-4 py-2 space-y-1">
            <a href="dashboard.php" class="block px-3 py-2 text-white hover:bg-green-600 rounded-md transition duration-200">Dashboard</a>
            <a href="crops.php" class="block px-3 py-2 text-white hover:bg-green-600 rounded-md transition duration-200">Crops</a>
            <a href="harvest.php" class="block px-3 py-2 text-white hover:bg-green-600 rounded-md transition duration-200">Harvest</a>
            <a href="livestock.php" class="block px-3 py-2 text-white hover:bg-green-600 rounded-md transition duration-200">Livestock</a>
            <a href="loan_request.php" class="block px-3 py-2 text-white hover:bg-green-600 rounded-md transition duration-200">Request Loan</a>
            <hr class="my-2 border-green-600">
            <a href="../logout.php" class="block px-3 py-2 text-red-300 hover:bg-red-600 hover:text-white rounded-md transition duration-200">Logout</a>
        </div>
    </div>
</nav>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
    }
    
    // Initialize Feather icons
    feather.replace();
</script>