<?php
require_once '../includes/auth_check.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../config/database.php';

$db = (new Database())->getConnection();
$farmer_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if updating crop table with actual harvest data
    if (isset($_POST['crop_id']) && !empty($_POST['crop_id'])) {
        // Update the crops table with actual harvest data
        $stmt = $db->prepare("UPDATE crops SET 
            actual_harvest_date = ?, 
            actual_yield = ?,
            actual_income = ?
            WHERE id = ? AND farmer_id = ?");
        
        $stmt->execute([
            $_POST['date_harvested'],
            $_POST['yield'],
            $_POST['actual_income'],
            $_POST['crop_id'],
            $farmer_id
        ]);
    }
    
    // Insert into harvests table
    $stmt = $db->prepare("INSERT INTO harvests (
        farmer_id, crop_name, date_harvested, yield, yield_unit, price_kg
    ) VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $farmer_id,
        $_POST['crop_name'],
        $_POST['date_harvested'],
        $_POST['yield'],
        $_POST['yield_unit'],
        $_POST['price_kg']
    ]);

    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 shadow-lg'>
            <div class='flex items-center'>
                <i data-feather='check-circle' class='w-5 h-5 mr-2'></i>
                <span class='font-medium'>Success!</span>
                <span class='ml-2'>Harvest recorded successfully and crop data updated.</span>
            </div>
          </div>";
}

// Fetch planted crops that haven't been harvested yet
$stmt = $db->prepare("SELECT id, crop_name, crop_type, area_planted, planting_date, expected_harvest_date, expected_yield, yield_unit, season, farming_method 
                      FROM crops 
                      WHERE farmer_id = ? AND actual_harvest_date IS NULL 
                      ORDER BY expected_harvest_date ASC");
$stmt->execute([$farmer_id]);
$ready_crops = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch harvest history
$stmt = $db->prepare("SELECT h.*, c.crop_type, c.area_planted, c.season 
                      FROM harvests h 
                      LEFT JOIN crops c ON h.crop_name = c.crop_name AND h.farmer_id = c.farmer_id 
                      WHERE h.farmer_id = ? 
                      ORDER BY h.date_harvested DESC");
$stmt->execute([$farmer_id]);
$harvests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total harvest value
$total_harvest_value = 0;
foreach ($harvests as $harvest) {
    $total_harvest_value += ($harvest['yield'] * $harvest['price_kg']);
}
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
                            <div class="bg-orange-100 p-3 rounded-full mr-4">
                                <i data-feather="package" class="w-6 h-6 text-orange-600"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">Harvest Management</h1>
                                <p class="text-gray-600">Record and track your harvest activities</p>
                            </div>
                        </div>
                        <div class="hidden md:flex space-x-4">
                            <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-4 py-2 rounded-lg text-center">
                                <div class="text-sm opacity-90">Total Harvests</div>
                                <div class="font-bold text-lg"><?php echo count($harvests); ?></div>
                            </div>
                            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 rounded-lg text-center">
                                <div class="text-sm opacity-90">Total Value</div>
                                <div class="font-bold text-lg"><?php echo number_format($total_harvest_value); ?> RWF</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-2 rounded-full mr-3">
                                <i data-feather="clock" class="w-5 h-5 text-blue-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Ready to Harvest</p>
                                <p class="text-xl font-bold text-gray-800"><?php echo count($ready_crops); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200">
                        <div class="flex items-center">
                            <div class="bg-green-100 p-2 rounded-full mr-3">
                                <i data-feather="trending-up" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Avg. Yield/Ha</p>
                                <p class="text-xl font-bold text-gray-800">
                                    <?php 
                                    $avg_yield = count($harvests) > 0 ? array_sum(array_column($harvests, 'yield')) / count($harvests) : 0;
                                    echo number_format($avg_yield, 1);
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200">
                        <div class="flex items-center">
                            <div class="bg-purple-100 p-2 rounded-full mr-3">
                                <i data-feather="calendar" class="w-5 h-5 text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">This Season</p>
                                <p class="text-xl font-bold text-gray-800">
                                    <?php 
                                    $current_season_harvests = array_filter($harvests, function($h) {
                                        return date('Y', strtotime($h['date_harvested'])) == date('Y');
                                    });
                                    echo count($current_season_harvests);
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Record New Harvest Form -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i data-feather="plus-circle" class="w-5 h-5 mr-2 text-orange-500"></i>
                        Record New Harvest
                    </h2>
                    
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="leaf" class="w-4 h-4 inline mr-1"></i>
                                        Select Crop to Harvest *
                                    </label>
                                    <select name="crop_id" id="crop_select" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white appearance-none bg-no-repeat bg-right-4 bg-center" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                                        <option value="">Select a crop ready for harvest...</option>
                                        <?php foreach ($ready_crops as $crop): ?>
                                            <option value="<?php echo $crop['id']; ?>" 
                                                    data-crop-name="<?php echo htmlspecialchars($crop['crop_name']); ?>"
                                                    data-crop-type="<?php echo htmlspecialchars($crop['crop_type']); ?>"
                                                    data-expected-yield="<?php echo $crop['expected_yield']; ?>"
                                                    data-yield-unit="<?php echo htmlspecialchars($crop['yield_unit']); ?>"
                                                    data-area="<?php echo $crop['area_planted']; ?>"
                                                    data-season="<?php echo htmlspecialchars($crop['season']); ?>">
                                                <?php echo htmlspecialchars($crop['crop_name']); ?> 
                                                (<?php echo htmlspecialchars($crop['crop_type']); ?>) - 
                                                <?php echo $crop['area_planted']; ?> ha - 
                                                Expected: <?php echo date('M j, Y', strtotime($crop['expected_harvest_date'])); ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="manual">🆕 Enter crop manually</option>
                                    </select>
                                </div>

                                <div id="manual_crop_name" style="display: none;">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="edit" class="w-4 h-4 inline mr-1"></i>
                                        Crop Name *
                                    </label>
                                    <input name="crop_name" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" placeholder="Enter crop name (e.g., Maize, Rice, Beans)">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="calendar" class="w-4 h-4 inline mr-1"></i>
                                        Harvest Date *
                                    </label>
                                    <input name="date_harvested" type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" required>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="package" class="w-4 h-4 inline mr-1"></i>
                                        Total Yield Harvested *
                                    </label>
                                    <div class="relative">
                                        <input name="yield" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 pr-20" placeholder="e.g., 1800" required>
                                        <span id="yield_unit_display" class="absolute right-3 top-3 text-gray-500 text-sm">units</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Expected yield: <span id="expected_yield_display">-</span></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="box" class="w-4 h-4 inline mr-1"></i>
                                        Yield Unit *
                                    </label>
                                    <select name="yield_unit" id="yield_unit_select" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-white appearance-none bg-no-repeat bg-right-4 bg-center" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-size: 1.5em 1.5em; padding-right: 2.5rem;" required>
                                        <option value="">Select measurement unit...</option>
                                        <option value="Kg">⚖️ Kilograms (Kg)</option>
                                        <option value="Tons">🏗️ Tons</option>
                                        <option value="Bags">📦 Bags</option>
                                        <option value="Pieces">🔢 Pieces</option>
                                        <option value="Bunches">🍌 Bunches</option>
                                        <option value="Liters">🥛 Liters</option>
                                        <option value="Baskets">🧺 Baskets</option>
                                        <option value="Sacks">📝 Sacks</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="dollar-sign" class="w-4 h-4 inline mr-1"></i>
                                        Price per Kg *
                                    </label>
                                    <div class="relative">
                                        <input name="price_kg" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 pl-12" placeholder="e.g., 500" required>
                                        <span class="absolute left-3 top-3 text-gray-500">RWF</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Current market price per kilogram</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="trending-up" class="w-4 h-4 inline mr-1"></i>
                                        Total Income (Optional)
                                    </label>
                                    <div class="relative">
                                        <input name="actual_income" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 pl-12" placeholder="e.g., 900000">
                                        <span class="absolute left-3 top-3 text-gray-500">RWF</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Total income from this harvest</p>
                                </div>

                                <!-- Harvest Quality Assessment -->
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <h4 class="font-medium text-gray-800 mb-3 flex items-center">
                                        <i data-feather="star" class="w-4 h-4 mr-1"></i>
                                        Harvest Quality (Optional)
                                    </h4>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input type="radio" name="quality" value="excellent" id="excellent" class="mr-2">
                                            <label for="excellent" class="text-sm text-gray-700">🌟 Excellent Quality</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" name="quality" value="good" id="good" class="mr-2">
                                            <label for="good" class="text-sm text-gray-700">👍 Good Quality</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" name="quality" value="average" id="average" class="mr-2">
                                            <label for="average" class="text-sm text-gray-700">⚖️ Average Quality</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" name="quality" value="poor" id="poor" class="mr-2">
                                            <label for="poor" class="text-sm text-gray-700">⚠️ Poor Quality</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Harvest Notes -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="file-text" class="w-4 h-4 inline mr-1"></i>
                                        Harvest Notes (Optional)
                                    </label>
                                    <textarea name="harvest_notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none" placeholder="Add any notes about this harvest (weather conditions, challenges, etc.)"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i data-feather="check" class="w-5 h-5"></i>
                                <span>Record Harvest</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Harvest History -->
                <?php if (!empty($harvests)): ?>
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <i data-feather="clock" class="w-5 h-5 mr-2 text-orange-500"></i>
                            Harvest History (<?php echo count($harvests); ?>)
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harvest Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yield</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price/Kg</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Season</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($harvests as $harvest): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                                <i data-feather="package" class="w-5 h-5 text-orange-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($harvest['crop_name']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($harvest['crop_type'] ?? 'N/A'); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M j, Y', strtotime($harvest['date_harvested'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($harvest['yield'], 2); ?> <?php echo htmlspecialchars($harvest['yield_unit']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo number_format($harvest['price_kg']); ?> RWF</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                        <?php echo number_format($harvest['yield'] * $harvest['price_kg']); ?> RWF
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($harvest['season'] ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <button class="edit-harvest-btn bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold py-1 px-3 rounded mr-2" data-harvest='<?php echo json_encode($harvest); ?>'>Edit</button>
                                        <button class="delete-harvest-btn bg-red-100 hover:bg-red-200 text-red-700 font-semibold py-1 px-3 rounded" data-harvest-id="<?php echo $harvest['id']; ?>">Delete</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-xl shadow-lg p-12 text-center border border-gray-200">
                    <div class="bg-gray-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-feather="package" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No harvests recorded yet</h3>
                    <p class="text-gray-500 mb-4">Start by recording your first harvest to track your agricultural productivity.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Edit Harvest Modal -->
<div id="editHarvestModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-2xl relative">
        <button id="closeEditHarvestModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl">&times;</button>
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            <i data-feather="edit" class="w-5 h-5 mr-2 text-blue-500"></i>
            Edit Harvest Record
        </h2>
        <form id="editHarvestForm" class="space-y-6">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_harvest_id">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Crop Name *</label>
                        <input name="crop_name" id="edit_crop_name" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harvest Date *</label>
                        <input name="date_harvested" id="edit_date_harvested" type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Yield Harvested *</label>
                        <input name="yield" id="edit_yield" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Yield Unit *</label>
                        <input name="yield_unit" id="edit_yield_unit" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price per Kg *</label>
                        <input name="price_kg" id="edit_price_kg" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Income (Optional)</label>
                        <input name="actual_income" id="edit_actual_income" type="number" step="0.01" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harvest Quality (Optional)</label>
                        <input name="quality" id="edit_quality" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Harvest Notes (Optional)</label>
                        <textarea name="harvest_notes" id="edit_harvest_notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg resize-none"></textarea>
                    </div>
                </div>
            </div>
            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <i data-feather="save" class="w-5 h-5"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Initialize Feather icons
    feather.replace();
    
    // Set maximum date to today for harvest date
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="date_harvested"]').setAttribute('max', today);
    
    // Handle crop selection
    document.getElementById('crop_select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const manualCropName = document.getElementById('manual_crop_name');
        const cropNameInput = document.querySelector('input[name="crop_name"]');
        const yieldUnitSelect = document.getElementById('yield_unit_select');
        const yieldUnitDisplay = document.getElementById('yield_unit_display');
        const expectedYieldDisplay = document.getElementById('expected_yield_display');
        
        if (this.value === 'manual') {
            manualCropName.style.display = 'block';
            cropNameInput.required = true;
            yieldUnitDisplay.textContent = 'units';
            expectedYieldDisplay.textContent = '-';
        } else if (this.value !== '') {
            manualCropName.style.display = 'none';
            cropNameInput.required = false;
            cropNameInput.value = selectedOption.dataset.cropName;
            
            // Auto-select yield unit if available
            const yieldUnit = selectedOption.dataset.yieldUnit;
            if (yieldUnit) {
                yieldUnitSelect.value = yieldUnit;
                yieldUnitDisplay.textContent = yieldUnit;
            }
            
            // Display expected yield
            const expectedYield = selectedOption.dataset.expectedYield;
            if (expectedYield) {
                expectedYieldDisplay.textContent = expectedYield + ' ' + yieldUnit;
            }
        } else {
            manualCropName.style.display = 'none';
            cropNameInput.required = false;
            yieldUnitDisplay.textContent = 'units';
            expectedYieldDisplay.textContent = '-';
        }
    });
    
    // Update yield unit display when changed
    document.getElementById('yield_unit_select').addEventListener('change', function() {
        const yieldUnitDisplay = document.getElementById('yield_unit_display');
        yieldUnitDisplay.textContent = this.value || 'units';
    });

    // --- Edit & Delete Harvest Modal Logic ---
    const editHarvestModal = document.getElementById('editHarvestModal');
    const closeEditHarvestModal = document.getElementById('closeEditHarvestModal');
    const editHarvestForm = document.getElementById('editHarvestForm');

    // Open modal and populate fields
    document.querySelectorAll('.edit-harvest-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = JSON.parse(this.getAttribute('data-harvest'));
            document.getElementById('edit_harvest_id').value = data.id;
            document.getElementById('edit_crop_name').value = data.crop_name;
            document.getElementById('edit_date_harvested').value = data.date_harvested;
            document.getElementById('edit_yield').value = data.yield;
            document.getElementById('edit_yield_unit').value = data.yield_unit;
            document.getElementById('edit_price_kg').value = data.price_kg;
            document.getElementById('edit_actual_income').value = data.actual_income || '';
            document.getElementById('edit_quality').value = data.quality || '';
            document.getElementById('edit_harvest_notes').value = data.harvest_notes || '';
            editHarvestModal.classList.remove('hidden');
        });
    });

    // Close modal
    closeEditHarvestModal.addEventListener('click', function() {
        editHarvestModal.classList.add('hidden');
    });
    window.addEventListener('click', function(e) {
        if (e.target === editHarvestModal) {
            editHarvestModal.classList.add('hidden');
        }
    });

    // AJAX update (stub)
    editHarvestForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(editHarvestForm);
        fetch('harvest_action.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload(); // For now, reload to reflect changes
            } else {
                alert(data.message || 'Failed to update harvest.');
            }
        })
        .catch(() => alert('Failed to update harvest.'));
    });

    // AJAX delete (stub)
    document.querySelectorAll('.delete-harvest-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to delete this harvest record?')) return;
            const id = this.getAttribute('data-harvest-id');
            fetch('harvest_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=delete&id=' + encodeURIComponent(id)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete harvest.');
                }
            })
            .catch(() => alert('Failed to delete harvest.'));
        });
    });
</script>

<?php require_once '../includes/footer.php'; ?>