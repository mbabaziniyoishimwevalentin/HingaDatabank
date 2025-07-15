<!-- institution dashboard -->
<?php 
require_once '../includes/auth_check.php'; 
require_once '../includes/header.php'; 
require_once '../includes/navbar.php'; 
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50">
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-wrap lg:flex-nowrap gap-6">
            <!-- Sidebar -->
            <div class="w-full lg:w-1/4">
                <div class="sticky top-24">
                    <?php include 'institution_sidebar.php'; ?>
                </div>
            </div>

            <!-- Main Content -->
            <div class="w-full lg:w-3/4">
                <!-- Welcome Header -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 mb-2">Welcome back, Institution! 🏛️</h1>
                            <p class="text-gray-600">Manage your agricultural lending portfolio efficiently</p>
                        </div>
                        <div class="hidden md:block">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-lg">
                                <div class="text-sm">Today's Date</div>
                                <div class="font-semibold" id="currentDate"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Active Loans</p>
                                <p class="text-2xl font-bold text-blue-600">156</p>
                            </div>
                            <div class="bg-blue-100 p-3 rounded-full">
                                <i data-feather="dollar-sign" class="w-6 h-6 text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Clients</p>
                                <p class="text-2xl font-bold text-green-600">324</p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-full">
                                <i data-feather="users" class="w-6 h-6 text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Pending Requests</p>
                                <p class="text-2xl font-bold text-orange-600">28</p>
                            </div>
                            <div class="bg-orange-100 p-3 rounded-full">
                                <i data-feather="file-text" class="w-6 h-6 text-orange-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Portfolio Value</p>
                                <p class="text-2xl font-bold text-purple-600">$2.4M</p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <i data-feather="trending-up" class="w-6 h-6 text-purple-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i data-feather="zap" class="w-5 h-5 mr-2 text-yellow-500"></i>
                        Quick Actions
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="loans.php" class="group bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-4 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <div class="bg-white bg-opacity-20 p-2 rounded-full">
                                <i data-feather="file-text" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="font-semibold">Review Loan Requests</div>
                                <div class="text-sm text-blue-100">Process applications</div>
                            </div>
                        </a>

                        <a href="approved_loans.php" class="group bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <div class="bg-white bg-opacity-20 p-2 rounded-full">
                                <i data-feather="check-circle" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="font-semibold">Manage Active Loans</div>
                                <div class="text-sm text-green-100">Track loan status</div>
                            </div>
                        </a>

                        <a href="farmers.php" class="group bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-4 rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <div class="bg-white bg-opacity-20 p-2 rounded-full">
                                <i data-feather="users" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="font-semibold">Client Management</div>
                                <div class="text-sm text-orange-100">View farmer profiles</div>
                            </div>
                        </a>

                        <a href="credit_scoring.php" class="group bg-gradient-to-r from-purple-500 to-purple-600 text-white px-6 py-4 rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <div class="bg-white bg-opacity-20 p-2 rounded-full">
                                <i data-feather="award" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="font-semibold">Credit Assessment</div>
                                <div class="text-sm text-purple-100">Evaluate creditworthiness</div>
                            </div>
                        </a>

                        <a href="financial_reports.php" class="group bg-gradient-to-r from-indigo-500 to-indigo-600 text-white px-6 py-4 rounded-lg hover:from-indigo-600 hover:to-indigo-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <div class="bg-white bg-opacity-20 p-2 rounded-full">
                                <i data-feather="bar-chart-2" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="font-semibold">Financial Reports</div>
                                <div class="text-sm text-indigo-100">View analytics</div>
                            </div>
                        </a>

                        <a href="loan_calculation.php" class="group bg-gradient-to-r from-teal-500 to-teal-600 text-white px-6 py-4 rounded-lg hover:from-teal-600 hover:to-teal-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <div class="bg-white bg-opacity-20 p-2 rounded-full">
                                <i data-feather="calculator" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="font-semibold">Loan Calculator</div>
                                <div class="text-sm text-teal-100">Calculate terms</div>
                            </div>
                        </a>

                        <a href="risk_assessment.php" class="group bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-4 rounded-lg hover:from-red-600 hover:to-red-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <div class="bg-white bg-opacity-20 p-2 rounded-full">
                                <i data-feather="shield" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="font-semibold">Risk Assessment</div>
                                <div class="text-sm text-red-100">Analyze portfolio risk</div>
                            </div>
                        </a>

                        <a href="change_password.php" class="group bg-gradient-to-r from-gray-500 to-gray-600 text-white px-6 py-4 rounded-lg hover:from-gray-600 hover:to-gray-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            <div class="bg-white bg-opacity-20 p-2 rounded-full">
                                <i data-feather="lock" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="font-semibold">Change Password</div>
                                <div class="text-sm text-gray-100">Update security</div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Recent Loan Applications -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i data-feather="file-text" class="w-5 h-5 mr-2 text-blue-500"></i>
                            Recent Loan Applications
                        </h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                                    <div>
                                        <div class="text-gray-700 font-medium">John Munyaradzi</div>
                                        <div class="text-sm text-gray-500">$15,000 - Crop Expansion</div>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500">2 hours ago</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <div>
                                        <div class="text-gray-700 font-medium">Mary Uwimana</div>
                                        <div class="text-sm text-gray-500">$8,500 - Equipment Purchase</div>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500">1 day ago</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <div>
                                        <div class="text-gray-700 font-medium">Peter Nkurunziza</div>
                                        <div class="text-sm text-gray-500">$12,000 - Livestock</div>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-500">2 days ago</span>
                            </div>
                        </div>
                    </div>

                    <!-- Loan Performance -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i data-feather="trending-up" class="w-5 h-5 mr-2 text-green-500"></i>
                            Loan Performance
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                    <span class="text-gray-700">Performing Loans</span>
                                </div>
                                <span class="text-green-600 font-semibold">92%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: 92%"></div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                    <span class="text-gray-700">At Risk</span>
                                </div>
                                <span class="text-yellow-600 font-semibold">5%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: 5%"></div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                    <span class="text-gray-700">Non-Performing</span>
                                </div>
                                <span class="text-red-600 font-semibold">3%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: 3%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Monthly Disbursements -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i data-feather="dollar-sign" class="w-5 h-5 mr-2 text-blue-500"></i>
                            Monthly Disbursements
                        </h3>
                        <div class="text-3xl font-bold text-blue-600 mb-2">$486,000</div>
                        <div class="flex items-center text-green-600">
                            <i data-feather="trending-up" class="w-4 h-4 mr-1"></i>
                            <span class="text-sm">12% from last month</span>
                        </div>
                    </div>

                    <!-- Collection Rate -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i data-feather="check-circle" class="w-5 h-5 mr-2 text-green-500"></i>
                            Collection Rate
                        </h3>
                        <div class="text-3xl font-bold text-green-600 mb-2">94.2%</div>
                        <div class="flex items-center text-green-600">
                            <i data-feather="trending-up" class="w-4 h-4 mr-1"></i>
                            <span class="text-sm">2% improvement</span>
                        </div>
                    </div>

                    <!-- Average Loan Size -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i data-feather="bar-chart-2" class="w-5 h-5 mr-2 text-purple-500"></i>
                            Average Loan Size
                        </h3>
                        <div class="text-3xl font-bold text-purple-600 mb-2">$15,400</div>
                        <div class="flex items-center text-orange-600">
                            <i data-feather="trending-down" class="w-4 h-4 mr-1"></i>
                            <span class="text-sm">3% from last month</span>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Tasks -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i data-feather="clock" class="w-5 h-5 mr-2 text-orange-500"></i>
                        Upcoming Tasks & Reminders
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border-l-4 border-red-500">
                                <div>
                                    <div class="font-medium text-gray-700">Loan Review - Sarah Mukamana</div>
                                    <div class="text-sm text-gray-500">Due today</div>
                                </div>
                                <i data-feather="alert-circle" class="w-4 h-4 text-red-500"></i>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg border-l-4 border-orange-500">
                                <div>
                                    <div class="font-medium text-gray-700">Credit Committee Meeting</div>
                                    <div class="text-sm text-gray-500">Tomorrow 2:00 PM</div>
                                </div>
                                <i data-feather="users" class="w-4 h-4 text-orange-500"></i>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                                <div>
                                    <div class="font-medium text-gray-700">Monthly Portfolio Review</div>
                                    <div class="text-sm text-gray-500">Due in 3 days</div>
                                </div>
                                <i data-feather="file-text" class="w-4 h-4 text-blue-500"></i>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg border-l-4 border-purple-500">
                                <div>
                                    <div class="font-medium text-gray-700">Compliance Audit</div>
                                    <div class="text-sm text-gray-500">Next week</div>
                                </div>
                                <i data-feather="shield" class="w-4 h-4 text-purple-500"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Display current date
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Initialize Feather icons
    feather.replace();
</script>

<?php require_once '../includes/footer.php'; ?>