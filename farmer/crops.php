<!-- Enhanced Crop Management Page -->
<?php
require_once '../includes/auth_check.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../config/database.php';

$db = (new Database())->getConnection();
$farmer_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("INSERT INTO crops (
        farmer_id, crop_name, crop_type, area_planted, planting_date,
        expected_harvest_date, actual_harvest_date, expected_yield, actual_yield,
        yield_unit, season, farming_method, expected_income, actual_income
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $farmer_id,
        $_POST['crop_name'],
        $_POST['crop_type'],
        $_POST['area_planted'],
        $_POST['planting_date'],
        $_POST['expected_harvest_date'],
        $_POST['actual_harvest_date'],
        $_POST['expected_yield'],
        $_POST['actual_yield'],
        $_POST['yield_unit'],
        $_POST['season'],
        $_POST['farming_method'],
        $_POST['expected_income'],
        $_POST['actual_income']
    ]);

    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 shadow-lg'>
            <div class='flex items-center'>
                <i data-feather='check-circle' class='w-5 h-5 mr-2'></i>
                <span class='font-medium'>Success!</span>
                <span class='ml-2'>Crop added successfully to your farm records.</span>
            </div>
          </div>";
}

// Fetch crops
$stmt = $db->prepare("SELECT * FROM crops WHERE farmer_id = ? ORDER BY planting_date DESC");
$stmt->execute([$farmer_id]);
$crops = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50">
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-wrap lg:flex-nowrap gap-6">
            <!-- Sidebar -->
            <div class="w-full lg:w-1/4">
                <div class="sticky top-24">
                    <?php include 'farmer_sidebar.php'; ?>
                </div>
            </div>

            <!-- Main Content -->
            <div class="w-full lg:w-3/4">
                <!-- Page Header -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <i data-feather="leaf" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">Crop Management</h1>
                                <p class="text-gray-600">Track and manage your crop production</p>
                            </div>
                        </div>
                        <div class="hidden md:block">
                            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 rounded-lg">
                                <div class="text-sm opacity-90">Total Crops</div>
                                <div class="font-bold text-lg"><?php echo count($crops); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add New Crop Form -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i data-feather="plus-circle" class="w-5 h-5 mr-2 text-green-500"></i>
                        Add New Crop
                    </h2>
                    
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="tag" class="w-4 h-4 inline mr-1"></i>
                                        Crop Name *
                                    </label>
                                    <select name="crop_name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white appearance-none bg-no-repeat bg-right-4 bg-center" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-size: 1.5em 1.5em; padding-right: 2.5rem;" required>
                                        <option value="">Select crop type...</option>
                                        <option value="Maize">🌽 Maize</option>
                                        <option value="Rice">🌾 Rice</option>
                                        <option value="Beans">🫘 Beans</option>
                                        <option value="Sorghum">🌾 Sorghum</option>
                                        <option value="Cassava">🥔 Cassava</option>
                                        <option value="Sweet Potato">🍠 Sweet Potato</option>
                                        <option value="Irish Potato">🥔 Irish Potato</option>
                                        <option value="Banana">🍌 Banana</option>
                                        <option value="Tomato">🍅 Tomato</option>
                                        <option value="Cabbage">🥬 Cabbage</option>
                                        <option value="Onion">🧅 Onion</option>
                                        <option value="Carrot">🥕 Carrot</option>
                                        <option value="Coffee">☕ Coffee</option>
                                        <option value="Tea">🍃 Tea</option>
                                        <option value="Other">🌱 Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="layers" class="w-4 h-4 inline mr-1"></i>
                                        Crop Category
                                    </label>
                                    <select name="crop_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white appearance-none bg-no-repeat bg-right-4 bg-center" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                                        <option value="">Select category...</option>
                                        <option value="Cereal">🌾 Cereal Crops</option>
                                        <option value="Legume">🫘 Legume Crops</option>
                                        <option value="Root Tuber">🥔 Root & Tuber Crops</option>
                                        <option value="Vegetable">🥬 Vegetable Crops</option>
                                        <option value="Fruit">🍌 Fruit Crops</option>
                                        <option value="Cash Crop">💰 Cash Crops</option>
                                        <option value="Fodder">🌿 Fodder Crops</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="maximize" class="w-4 h-4 inline mr-1"></i>
                                        Area Planted *
                                    </label>
                                    <div class="relative">
                                        <input name="area_planted" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 pr-16" placeholder="e.g., 2.5" required>
                                        <span class="absolute right-3 top-3 text-gray-500 text-sm">hectares</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="calendar" class="w-4 h-4 inline mr-1"></i>
                                        Planting Date *
                                    </label>
                                    <input name="planting_date" type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="calendar" class="w-4 h-4 inline mr-1"></i>
                                        Expected Harvest Date
                                    </label>
                                    <input name="expected_harvest_date" type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="check-circle" class="w-4 h-4 inline mr-1"></i>
                                        Actual Harvest Date
                                    </label>
                                    <input name="actual_harvest_date" type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="sun" class="w-4 h-4 inline mr-1"></i>
                                        Growing Season
                                    </label>
                                    <select name="season" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white appearance-none bg-no-repeat bg-right-4 bg-center" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                                        <option value="">Select season...</option>
                                        <option value="Season A">🌧️ Season A (Sep-Jan)</option>
                                        <option value="Season B">☀️ Season B (Feb-Jun)</option>
                                        <option value="Season C">🌤️ Season C (Jul-Aug)</option>
                                        <option value="Year Round">🔄 Year Round</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="trending-up" class="w-4 h-4 inline mr-1"></i>
                                        Expected Yield
                                    </label>
                                    <input name="expected_yield" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="e.g., 1500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="package" class="w-4 h-4 inline mr-1"></i>
                                        Actual Yield
                                    </label>
                                    <input name="actual_yield" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" placeholder="e.g., 1800">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="box" class="w-4 h-4 inline mr-1"></i>
                                        Yield Unit
                                    </label>
                                    <select name="yield_unit" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white appearance-none bg-no-repeat bg-right-4 bg-center" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                                        <option value="">Select unit...</option>
                                        <option value="Kg">⚖️ Kilograms (Kg)</option>
                                        <option value="Tons">🏗️ Tons</option>
                                        <option value="Bags">📦 Bags</option>
                                        <option value="Pieces">🔢 Pieces</option>
                                        <option value="Bunches">🍌 Bunches</option>
                                        <option value="Liters">🥛 Liters</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="settings" class="w-4 h-4 inline mr-1"></i>
                                        Farming Method
                                    </label>
                                    <select name="farming_method" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white appearance-none bg-no-repeat bg-right-4 bg-center" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                                        <option value="">Select method...</option>
                                        <option value="Organic">🌿 Organic Farming</option>
                                        <option value="Conventional">🚜 Conventional Farming</option>
                                        <option value="Mixed">🔄 Mixed Farming</option>
                                        <option value="Intensive">⚡ Intensive Farming</option>
                                        <option value="Sustainable">♻️ Sustainable Farming</option>
                                        <option value="Hydroponic">💧 Hydroponic</option>
                                        <option value="Greenhouse">🏠 Greenhouse</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="dollar-sign" class="w-4 h-4 inline mr-1"></i>
                                        Expected Income
                                    </label>
                                    <div class="relative">
                                        <input name="expected_income" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 pl-12" placeholder="e.g., 750000">
                                        <span class="absolute left-3 top-3 text-gray-500">RWF</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="trending-up" class="w-4 h-4 inline mr-1"></i>
                                        Actual Income
                                    </label>
                                    <div class="relative">
                                        <input name="actual_income" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 pl-12" placeholder="e.g., 900000">
                                        <span class="absolute left-3 top-3 text-gray-500">RWF</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i data-feather="plus" class="w-5 h-5"></i>
                                <span>Add Crop to Farm</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Analytics Cards -->
                <?php
                $total_area = 0;
                $total_expected_yield = 0;
                $total_actual_yield = 0;
                $total_expected_income = 0;
                $total_actual_income = 0;
                $yield_by_crop = [];
                $income_by_crop = [];
                foreach ($crops as $crop) {
                    $total_area += (float)$crop['area_planted'];
                    $total_expected_yield += (float)$crop['expected_yield'];
                    $total_actual_yield += (float)$crop['actual_yield'];
                    $total_expected_income += (float)$crop['expected_income'];
                    $total_actual_income += (float)$crop['actual_income'];
                    $yield_by_crop[$crop['crop_name']] = ($yield_by_crop[$crop['crop_name']] ?? 0) + (float)$crop['actual_yield'];
                    $income_by_crop[$crop['crop_name']] = ($income_by_crop[$crop['crop_name']] ?? 0) + (float)$crop['actual_income'];
                }
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                    <div class="bg-gradient-to-br from-green-400 to-green-600 text-white rounded-2xl shadow-xl p-4 flex flex-col items-center">
                        <div class="text-lg font-semibold">Total Area</div>
                        <div class="text-2xl font-bold"><?php echo number_format($total_area, 2); ?> ha</div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white rounded-2xl shadow-xl p-4 flex flex-col items-center">
                        <div class="text-lg font-semibold">Expected Yield</div>
                        <div class="text-2xl font-bold"><?php echo number_format($total_expected_yield); ?></div>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 text-white rounded-2xl shadow-xl p-4 flex flex-col items-center">
                        <div class="text-lg font-semibold">Actual Yield</div>
                        <div class="text-2xl font-bold"><?php echo number_format($total_actual_yield); ?></div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-400 to-purple-600 text-white rounded-2xl shadow-xl p-4 flex flex-col items-center">
                        <div class="text-lg font-semibold">Expected Income</div>
                        <div class="text-2xl font-bold"><?php echo number_format($total_expected_income); ?> RWF</div>
                    </div>
                    <div class="bg-gradient-to-br from-pink-400 to-pink-600 text-white rounded-2xl shadow-xl p-4 flex flex-col items-center">
                        <div class="text-lg font-semibold">Actual Income</div>
                        <div class="text-2xl font-bold"><?php echo number_format($total_actual_income); ?> RWF</div>
                    </div>
                </div>
                <!-- Yield/Income by Crop Chart -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <h4 class="text-lg font-bold text-green-700 mb-4">Yield & Income by Crop</h4>
                    <canvas id="cropBarChart" height="120"></canvas>
                </div>

                <!-- Crops List -->
                <?php if (!empty($crops)): ?>
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <i data-feather="list" class="w-5 h-5 mr-2 text-green-500"></i>
                            Your Crops (<?php echo count($crops); ?>)
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table id="cropsTable" class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Planted</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Season</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Yield</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Yield</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected Income</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Income</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($crops as $crop): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                <i data-feather="leaf" class="w-5 h-5 text-green-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($crop['crop_name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($crop['crop_type']); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($crop['farming_method']); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $crop['area_planted']; ?> ha</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M j, Y', strtotime($crop['planting_date'])); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($crop['season']); ?></td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <?php if ($crop['actual_harvest_date']): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i data-feather="check-circle" class="w-3 h-3 mr-1"></i>
                                                Harvested
                                            </span>
                                        <?php elseif ($crop['expected_harvest_date'] && strtotime($crop['expected_harvest_date']) < strtotime(date('Y-m-d'))): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i data-feather="alert-circle" class="w-3 h-3 mr-1"></i>
                                                Missed
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i data-feather="clock" class="w-3 h-3 mr-1"></i>
                                                Growing
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $crop['expected_yield'] ? $crop['expected_yield'] . ' ' . $crop['yield_unit'] : '<span class="text-gray-400">Not set</span>'; ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $crop['actual_yield'] ? $crop['actual_yield'] . ' ' . $crop['yield_unit'] : '<span class="text-gray-400">Not set</span>'; ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $crop['expected_income'] ? number_format($crop['expected_income']) . ' RWF' : '<span class="text-gray-400">Not set</span>'; ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $crop['actual_income'] ? number_format($crop['actual_income']) . ' RWF' : '<span class="text-gray-400">Not set</span>'; ?>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <button class="view-crop-btn text-blue-600 hover:underline mr-2" data-crop-id="<?php echo $crop['id']; ?>">View</button>
                                        <button class="edit-crop-btn text-yellow-600 hover:underline mr-2" data-crop-id="<?php echo $crop['id']; ?>">Edit</button>
                                        <button class="delete-crop-btn text-red-600 hover:underline" data-crop-id="<?php echo $crop['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- View Crop Modal -->
                <div id="viewCropModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-lg relative">
                        <button id="closeViewCropModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">&times;</button>
                        <h3 class="text-xl font-bold mb-4 text-green-700">Crop Details</h3>
                        <div id="viewCropDetails" class="space-y-2">
                            <!-- Details will be populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Edit Crop Modal -->
                <div id="editCropModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-lg relative">
                        <button id="closeEditCropModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">&times;</button>
                        <h3 class="text-xl font-bold mb-4 text-yellow-700">Edit Crop</h3>
                        <form id="editCropForm" class="space-y-4">
                            <input type="hidden" name="id" id="edit_crop_id">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Crop Name</label>
                                    <input type="text" name="crop_name" id="edit_crop_name" class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Category</label>
                                    <input type="text" name="crop_type" id="edit_crop_type" class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Farming Method</label>
                                    <input type="text" name="farming_method" id="edit_farming_method" class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Area Planted</label>
                                    <input type="number" step="0.01" name="area_planted" id="edit_area_planted" class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Planting Date</label>
                                    <input type="date" name="planting_date" id="edit_planting_date" class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Season</label>
                                    <input type="text" name="season" id="edit_season" class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Expected Harvest Date</label>
                                    <input type="date" name="expected_harvest_date" id="edit_expected_harvest_date" class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Actual Harvest Date</label>
                                    <input type="date" name="actual_harvest_date" id="edit_actual_harvest_date" class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Expected Yield</label>
                                    <input type="number" step="0.01" name="expected_yield" id="edit_expected_yield" class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Actual Yield</label>
                                    <input type="number" step="0.01" name="actual_yield" id="edit_actual_yield" class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Yield Unit</label>
                                    <input type="text" name="yield_unit" id="edit_yield_unit" class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Expected Income</label>
                                    <input type="number" step="0.01" name="expected_income" id="edit_expected_income" class="w-full border rounded px-3 py-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Actual Income</label>
                                    <input type="number" step="0.01" name="actual_income" id="edit_actual_income" class="w-full border rounded px-3 py-2">
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Crop Modal -->
                <div id="deleteCropModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-sm relative">
                        <button id="closeDeleteCropModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">&times;</button>
                        <h3 class="text-xl font-bold mb-4 text-red-700">Delete Crop</h3>
                        <p>Are you sure you want to delete this crop? This action cannot be undone.</p>
                        <div class="flex justify-end mt-6 gap-2">
                            <button id="confirmDeleteCrop" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Delete</button>
                            <button id="cancelDeleteCrop" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancel</button>
                        </div>
                    </div>
                </div>

                <!-- Notification -->
                <div id="cropNotification" class="fixed top-6 right-6 z-50 hidden px-6 py-4 rounded shadow-lg font-semibold"></div>
                <?php else: ?>
                <div class="bg-white rounded-xl shadow-lg p-12 text-center border border-gray-200">
                    <div class="bg-gray-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-feather="leaf" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No crops added yet</h3>
                    <p class="text-gray-500 mb-4">Start by adding your first crop to track your agricultural activities.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add DataTables JS and CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    $('#cropsTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[4, 'desc']],
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'print', 'colvis'
        ]
    });
    feather.replace();

    // View Crop Modal logic
    $(document).on('click', '.view-crop-btn', function() {
        var row = $(this).closest('tr');
        var details = '';
        details += '<div><b>Crop Name:</b> ' + row.find('td:eq(0)').text().trim() + '</div>';
        details += '<div><b>Category:</b> ' + row.find('td:eq(1)').text().trim() + '</div>';
        details += '<div><b>Farming Method:</b> ' + row.find('td:eq(2)').text().trim() + '</div>';
        details += '<div><b>Area Planted:</b> ' + row.find('td:eq(3)').text().trim() + '</div>';
        details += '<div><b>Planting Date:</b> ' + row.find('td:eq(4)').text().trim() + '</div>';
        details += '<div><b>Season:</b> ' + row.find('td:eq(5)').text().trim() + '</div>';
        details += '<div><b>Status:</b> ' + row.find('td:eq(6)').text().trim() + '</div>';
        details += '<div><b>Expected Yield:</b> ' + row.find('td:eq(7)').text().trim() + '</div>';
        details += '<div><b>Actual Yield:</b> ' + row.find('td:eq(8)').text().trim() + '</div>';
        details += '<div><b>Expected Income:</b> ' + row.find('td:eq(9)').text().trim() + '</div>';
        details += '<div><b>Actual Income:</b> ' + row.find('td:eq(10)').text().trim() + '</div>';
        $('#viewCropDetails').html(details);
        $('#viewCropModal').removeClass('hidden');
    });
    $('#closeViewCropModal').on('click', function() {
        $('#viewCropModal').addClass('hidden');
    });
    $(window).on('click', function(e) {
        if ($(e.target).is('#viewCropModal')) {
            $('#viewCropModal').addClass('hidden');
        }
    });

    // Edit Crop Modal logic
    var editingRow = null;
    $(document).on('click', '.edit-crop-btn', function() {
        editingRow = $(this).closest('tr');
        var tds = editingRow.find('td');
        $('#edit_crop_id').val($(this).data('crop-id'));
        $('#edit_crop_name').val(tds.eq(0).text().trim());
        $('#edit_crop_type').val(tds.eq(1).text().trim());
        $('#edit_farming_method').val(tds.eq(2).text().trim());
        $('#edit_area_planted').val(parseFloat(tds.eq(3).text()));
        $('#edit_planting_date').val(tds.eq(4).text().trim() ? new Date(tds.eq(4).text().trim()).toISOString().split('T')[0] : '');
        $('#edit_season').val(tds.eq(5).text().trim());
        $('#edit_expected_harvest_date').val('');
        $('#edit_actual_harvest_date').val('');
        $('#edit_expected_yield').val(parseFloat(tds.eq(7).text()));
        $('#edit_actual_yield').val(parseFloat(tds.eq(8).text()));
        $('#edit_yield_unit').val('');
        $('#edit_expected_income').val(parseFloat(tds.eq(9).text().replace(/[^\d.]/g, '')));
        $('#edit_actual_income').val(parseFloat(tds.eq(10).text().replace(/[^\d.]/g, '')));
        $('#editCropModal').removeClass('hidden');
    });
    $('#closeEditCropModal').on('click', function() {
        $('#editCropModal').addClass('hidden');
    });
    $('#editCropForm').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize() + '&action=edit_crop';
        $.post('crops.php', formData, function(response) {
            if (response.success) {
                // Update the row in the table
                var updated = response.updated;
                editingRow.find('td').eq(0).text(updated.crop_name);
                editingRow.find('td').eq(1).text(updated.crop_type);
                editingRow.find('td').eq(2).text(updated.farming_method);
                editingRow.find('td').eq(3).text(updated.area_planted + ' ha');
                editingRow.find('td').eq(4).text(updated.planting_date);
                editingRow.find('td').eq(5).text(updated.season);
                // Status badge, yields, incomes
                editingRow.find('td').eq(6).html(updated.status_html);
                editingRow.find('td').eq(7).text(updated.expected_yield ? updated.expected_yield + ' ' + updated.yield_unit : 'Not set');
                editingRow.find('td').eq(8).text(updated.actual_yield ? updated.actual_yield + ' ' + updated.yield_unit : 'Not set');
                editingRow.find('td').eq(9).text(updated.expected_income ? updated.expected_income + ' RWF' : 'Not set');
                editingRow.find('td').eq(10).text(updated.actual_income ? updated.actual_income + ' RWF' : 'Not set');
                showCropNotification('Crop updated successfully!', 'success');
                $('#editCropModal').addClass('hidden');
            } else {
                showCropNotification('Failed to update crop.', 'error');
            }
        }, 'json');
    });

    // Delete Crop Modal logic
    var deletingRow = null;
    var deletingId = null;
    $(document).on('click', '.delete-crop-btn', function() {
        deletingRow = $(this).closest('tr');
        deletingId = $(this).data('crop-id');
        $('#deleteCropModal').removeClass('hidden');
    });
    $('#closeDeleteCropModal, #cancelDeleteCrop').on('click', function() {
        $('#deleteCropModal').addClass('hidden');
    });
    $('#confirmDeleteCrop').on('click', function() {
        $.post('crops.php', { action: 'delete_crop', id: deletingId }, function(response) {
            if (response.success) {
                deletingRow.remove();
                showCropNotification('Crop deleted successfully!', 'success');
            } else {
                showCropNotification('Failed to delete crop.', 'error');
            }
            $('#deleteCropModal').addClass('hidden');
        }, 'json');
    });

    // Notification function
    function showCropNotification(msg, type) {
        var notif = $('#cropNotification');
        notif.text(msg).removeClass('hidden');
        notif.removeClass('bg-green-100 bg-red-100 text-green-700 text-red-700');
        if (type === 'success') {
            notif.addClass('bg-green-100 text-green-700');
        } else {
            notif.addClass('bg-red-100 text-red-700');
        }
        setTimeout(function() { notif.addClass('hidden'); }, 2500);
    }

    // Crop Bar Chart
    var ctx = document.getElementById('cropBarChart').getContext('2d');
    var cropLabels = <?php echo json_encode(array_keys($yield_by_crop)); ?>;
    var yieldData = <?php echo json_encode(array_values($yield_by_crop)); ?>;
    var incomeData = <?php echo json_encode(array_values($income_by_crop)); ?>;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: cropLabels,
            datasets: [
                {
                    label: 'Yield',
                    data: yieldData,
                    backgroundColor: '#34d399',
                },
                {
                    label: 'Income',
                    data: incomeData,
                    backgroundColor: '#60a5fa',
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>