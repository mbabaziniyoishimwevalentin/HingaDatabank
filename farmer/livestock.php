<!-- Enhanced Livestock Management Page -->
<?php
require_once '../includes/auth_check.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';
require_once '../config/database.php';

$db = (new Database())->getConnection();
$farmer_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("INSERT INTO livestock (
        farmer_id, animal_type, quantity, value_all_animals
    ) VALUES (?, ?, ?, ?)");

    $stmt->execute([
        $farmer_id,
        $_POST['animal_type'],
        $_POST['quantity'],
        $_POST['value_all_animals']
    ]);

    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 shadow-lg'>
            <div class='flex items-center'>
                <i data-feather='check-circle' class='w-5 h-5 mr-2'></i>
                <span class='font-medium'>Success!</span>
                <span class='ml-2'>Livestock added successfully to your farm records.</span>
            </div>
          </div>";
}

// Fetch livestock
$stmt = $db->prepare("SELECT * FROM livestock WHERE farmer_id = ? ORDER BY id DESC");
$stmt->execute([$farmer_id]);
$animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total value
$totalValue = array_sum(array_column($animals, 'value_all_animals'));
$totalAnimals = array_sum(array_column($animals, 'quantity'));
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
                            <div class="bg-amber-100 p-3 rounded-full mr-4">
                                <i data-feather="zap" class="w-6 h-6 text-amber-600"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">Livestock Management</h1>
                                <p class="text-gray-600">Track and manage your livestock inventory</p>
                            </div>
                        </div>
                        <div class="hidden md:flex space-x-4">
                            <div class="bg-gradient-to-r from-amber-500 to-amber-600 text-white px-4 py-2 rounded-lg text-center">
                                <div class="text-sm opacity-90">Total Animals</div>
                                <div class="font-bold text-lg"><?php echo $totalAnimals; ?></div>
                            </div>
                            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 rounded-lg text-center">
                                <div class="text-sm opacity-90">Total Value</div>
                                <div class="font-bold text-lg"><?php echo number_format($totalValue); ?> RWF</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add New Livestock Form -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i data-feather="plus-circle" class="w-5 h-5 mr-2 text-amber-500"></i>
                        Add New Livestock
                    </h2>
                    
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Left Column -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="zap" class="w-4 h-4 inline mr-1"></i>
                                        Animal Type *
                                    </label>
                                    <select name="animal_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white appearance-none bg-no-repeat bg-right-4 bg-center" style="background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 20 20\'%3e%3cpath stroke=\'%236b7280\' stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'1.5\' d=\'M6 8l4 4 4-4\'/%3e%3c/svg%3e'); background-size: 1.5em 1.5em; padding-right: 2.5rem;" required>
                                        <option value="">Select animal type...</option>
                                        <option value="Cattle">🐄 Cattle</option>
                                        <option value="Goats">🐐 Goats</option>
                                        <option value="Sheep">🐑 Sheep</option>
                                        <option value="Pigs">🐷 Pigs</option>
                                        <option value="Chickens">🐔 Chickens</option>
                                        <option value="Ducks">🦆 Ducks</option>
                                        <option value="Rabbits">🐰 Rabbits</option>
                                        <option value="Fish">🐟 Fish</option>
                                        <option value="Bees">🐝 Bees (Hives)</option>
                                        <option value="Turkeys">🦃 Turkeys</option>
                                        <option value="Guinea Fowl">🐦 Guinea Fowl</option>
                                        <option value="Donkeys">🐴 Donkeys</option>
                                        <option value="Other">🦎 Other</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="hash" class="w-4 h-4 inline mr-1"></i>
                                        Quantity *
                                    </label>
                                    <div class="relative">
                                        <input name="quantity" type="number" min="1" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 pr-16" placeholder="e.g., 5" required>
                                        <span class="absolute right-3 top-3 text-gray-500 text-sm">animals</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i data-feather="dollar-sign" class="w-4 h-4 inline mr-1"></i>
                                        Total Value *
                                    </label>
                                    <div class="relative">
                                        <input name="value_all_animals" type="number" step="0.01" min="0" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 pl-12" placeholder="e.g., 1500000" required>
                                        <span class="absolute left-3 top-3 text-gray-500">RWF</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-1">Combined value of all animals of this type</p>
                                </div>

                                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                    <h4 class="font-medium text-amber-800 mb-2 flex items-center">
                                        <i data-feather="info" class="w-4 h-4 mr-2"></i>
                                        Quick Tips
                                    </h4>
                                    <ul class="text-sm text-amber-700 space-y-1">
                                        <li>• Record current market value of your livestock</li>
                                        <li>• Include all animals of the same type together</li>
                                        <li>• Update values regularly for accurate records</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-semibold py-3 px-8 rounded-lg transition-all duration-300 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <i data-feather="plus" class="w-5 h-5"></i>
                                <span>Add Livestock</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Livestock List -->
                <?php if (!empty($animals)): ?>
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <i data-feather="list" class="w-5 h-5 mr-2 text-amber-500"></i>
                            Your Livestock (<?php echo count($animals); ?> types)
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Animal Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value per Animal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($animals as $animal): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center mr-3">
                                                <i data-feather="zap" class="w-5 h-5 text-amber-600"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($animal['animal_type']); ?></div>
                                                <div class="text-sm text-gray-500">Livestock</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo number_format($animal['quantity']); ?></div>
                                        <div class="text-sm text-gray-500">animals</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo number_format($animal['value_all_animals']); ?> RWF</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php 
                                            $valuePerAnimal = $animal['quantity'] > 0 ? $animal['value_all_animals'] / $animal['quantity'] : 0;
                                            echo number_format($valuePerAnimal);
                                            ?> RWF
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i data-feather="check-circle" class="w-3 h-3 mr-1"></i>
                                            Active
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Summary Footer -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                Total: <?php echo $totalAnimals; ?> animals across <?php echo count($animals); ?> types
                            </div>
                            <div class="text-lg font-semibold text-gray-900">
                                Total Value: <?php echo number_format($totalValue); ?> RWF
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-xl shadow-lg p-12 text-center border border-gray-200">
                    <div class="bg-gray-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-feather="zap" class="w-8 h-8 text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No livestock recorded yet</h3>
                    <p class="text-gray-500 mb-4">Start by adding your first livestock to track your animal inventory.</p>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 max-w-md mx-auto">
                        <h4 class="font-medium text-amber-800 mb-2">Popular livestock in Rwanda:</h4>
                        <p class="text-sm text-amber-700">Cattle, Goats, Chickens, Pigs, and Fish farming are common sources of income.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Feather icons
    feather.replace();
    
    // Add some interactivity for better UX
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-calculate value per animal when quantity or total value changes
        const quantityInput = document.querySelector('input[name="quantity"]');
        const valueInput = document.querySelector('input[name="value_all_animals"]');
        
        function updateCalculations() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const totalValue = parseFloat(valueInput.value) || 0;
            
            if (quantity > 0 && totalValue > 0) {
                const valuePerAnimal = totalValue / quantity;
                // You could add a display element here to show value per animal
            }
        }
        
        quantityInput.addEventListener('input', updateCalculations);
        valueInput.addEventListener('input', updateCalculations);
    });
</script>

<?php require_once '../includes/footer.php'; ?>