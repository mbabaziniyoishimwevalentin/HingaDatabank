<!-- farmer dashboard -->
<?php 
require_once '../includes/auth_check.php'; 
require_once '../includes/header.php'; 
require_once '../includes/navbar.php'; 
require_once '../config/database.php';
$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// Fetch the logged-in farmer's profile and username
$stmt = $db->prepare("SELECT u.username, fp.id_number, fp.district, fp.sector, fp.cell, fp.bank_account, fp.profile_image FROM users u LEFT JOIN farmer_profiles fp ON u.id = fp.user_id WHERE u.id = ?");
$stmt->execute([$user_id]);
$farmer = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch recent activity for the logged-in farmer
$recent_activity = [];
$activity_limit = 10;

// Fetch recent crops
$stmt = $db->prepare("SELECT 'Crop Added' AS type, crop_name AS detail, planting_date AS date FROM crops WHERE farmer_id = ? ORDER BY planting_date DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_activity = array_merge($recent_activity, $stmt->fetchAll(PDO::FETCH_ASSOC));

// Fetch recent harvests
$stmt = $db->prepare("SELECT 'Harvest Recorded' AS type, crop_name AS detail, date_harvested AS date FROM harvests WHERE farmer_id = ? ORDER BY date_harvested DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_activity = array_merge($recent_activity, $stmt->fetchAll(PDO::FETCH_ASSOC));

// Fetch recent livestock
$stmt = $db->prepare("SELECT 'Livestock Added' AS type, animal_type AS detail, NULL AS date FROM livestock WHERE farmer_id = ? ORDER BY id DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_activity = array_merge($recent_activity, $stmt->fetchAll(PDO::FETCH_ASSOC));

// Fetch recent loan requests
$stmt = $db->prepare("SELECT 'Loan Requested' AS type, loan_type AS detail, approval_date AS date FROM loan_requests WHERE farmer_id = ? ORDER BY approval_date DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_activity = array_merge($recent_activity, $stmt->fetchAll(PDO::FETCH_ASSOC));

// Sort all activities by date (descending), null dates last
usort($recent_activity, function($a, $b) {
    if ($a['date'] == $b['date']) return 0;
    if ($a['date'] === null) return 1;
    if ($b['date'] === null) return -1;
    return strtotime($b['date']) - strtotime($a['date']);
});

// Limit to 10 most recent
$show_activity = array_slice($recent_activity, 0, $activity_limit);
$has_more_activity = count($recent_activity) > $activity_limit;

// Notifications logic
$notifications = [];
// 1. Upcoming harvests (within 7 days)
$stmt = $db->prepare("SELECT crop_name, expected_harvest_date FROM crops WHERE farmer_id = ? AND expected_harvest_date >= CURDATE() AND expected_harvest_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$stmt->execute([$user_id]);
$upcoming_harvests = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($upcoming_harvests as $harvest) {
    $notifications[] = "Upcoming harvest for <b>" . htmlspecialchars($harvest['crop_name']) . "</b> on <b>" . date('M d, Y', strtotime($harvest['expected_harvest_date'])) . "</b>.";
}
// 2. Loan repayments due (loans approved, with approval_date + 30 days <= today)
$stmt = $db->prepare("SELECT loan_type, approval_date FROM loan_requests WHERE farmer_id = ? AND status = 'approved' AND approval_date IS NOT NULL AND DATE_ADD(approval_date, INTERVAL 30 DAY) <= CURDATE()");
$stmt->execute([$user_id]);
$due_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($due_loans as $loan) {
    $notifications[] = "Loan repayment due for <b>" . htmlspecialchars($loan['loan_type']) . "</b> (approved on <b>" . date('M d, Y', strtotime($loan['approval_date'])) . "</b>).";
}
// 3. Profile incomplete
$stmt = $db->prepare("SELECT id_number, district, sector, cell, bank_account FROM farmer_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_fields = ['id_number', 'district', 'sector', 'cell', 'bank_account'];
$incomplete = false;
foreach ($profile_fields as $field) {
    if (empty($profile[$field])) {
        $incomplete = true;
        break;
    }
}
if ($incomplete) {
    $notifications[] = "Your profile is incomplete. <a href='profile.php' class='text-blue-600 underline'>Complete your profile</a> for better services.";
}
$notifications = array_slice($notifications, 0, 5);

// Fetch dynamic counts for dashboard cards
$stmt = $db->prepare("SELECT COUNT(*) FROM crops WHERE farmer_id = ?");
$stmt->execute([$user_id]);
$total_crops = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM harvests WHERE farmer_id = ?");
$stmt->execute([$user_id]);
$total_harvests = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM livestock WHERE farmer_id = ?");
$stmt->execute([$user_id]);
$total_livestock = (int)$stmt->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM loan_requests WHERE farmer_id = ? AND status = 'approved'");
$stmt->execute([$user_id]);
$total_loans = (int)$stmt->fetchColumn();

// Fetch upcoming tasks for the logged-in farmer
$stmt = $db->prepare("SELECT t.*, c.crop_name, l.animal_type FROM tasks t
    LEFT JOIN crops c ON t.related_crop_id = c.id
    LEFT JOIN livestock l ON t.related_livestock_id = l.id
    WHERE t.farmer_id = ? AND t.status = 'pending' AND (t.due_date IS NULL OR t.due_date >= CURDATE())
    ORDER BY t.due_date ASC, t.id DESC");
$stmt->execute([$user_id]);
$upcoming_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch completed tasks for the logged-in farmer
$stmt = $db->prepare("SELECT t.*, c.crop_name, l.animal_type FROM tasks t
    LEFT JOIN crops c ON t.related_crop_id = c.id
    LEFT JOIN livestock l ON t.related_livestock_id = l.id
    WHERE t.farmer_id = ? AND t.status = 'done'
    ORDER BY t.due_date DESC, t.id DESC");
$stmt->execute([$user_id]);
$completed_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50">
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-wrap lg:flex-nowrap gap-6">
            <!-- Sidebar -->
            <div class="w-full lg:w-1/5">
                <div class="sticky top-24">
                    <?php include 'farmer_sidebar.php'; ?>
                </div>
            </div>

            <!-- Main Content -->
            <div class="w-full lg:w-3/5">
                <!-- Dashboard Header with Avatar -->
                <div class="flex items-center mb-8">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-r from-green-400 to-blue-400 flex items-center justify-center text-white text-3xl font-bold shadow-lg mr-6 border-4 border-white">
                        <?php
                        // Use profile image if available, else first letter of username
                        $avatar_img = !empty($farmer['profile_image']) ? $farmer['profile_image'] : '';
                        $avatar_letter = isset($farmer['username']) ? strtoupper(substr($farmer['username'], 0, 1)) : 'F';
                        ?>
                        <?php if ($avatar_img): ?>
                            <img src="<?= htmlspecialchars($avatar_img) ?>" alt="Profile" class="w-20 h-20 rounded-full object-cover">
                        <?php else: ?>
                            <?= $avatar_letter ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="text-4xl font-extrabold text-gray-800 mb-1">Welcome back, <span class="text-green-600"><?= htmlspecialchars($farmer['username'] ?? 'Farmer') ?></span>!</h1>
                        <p class="text-lg text-gray-500">Here’s a snapshot of your farm’s progress and activities.</p>
                    </div>
                </div>

                <!-- Enhanced Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-green-400 to-green-600 text-white rounded-2xl shadow-xl p-6 flex flex-col items-center hover:scale-105 transition-transform duration-200">
                        <i data-feather="leaf" class="w-10 h-10 mb-2"></i>
                        <div class="text-4xl font-extrabold"><?php echo $total_crops; ?></div>
                        <div class="text-lg font-semibold mt-1">Total Crops</div>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 text-white rounded-2xl shadow-xl p-6 flex flex-col items-center hover:scale-105 transition-transform duration-200">
                        <i data-feather="package" class="w-10 h-10 mb-2"></i>
                        <div class="text-4xl font-extrabold"><?php echo $total_harvests; ?></div>
                        <div class="text-lg font-semibold mt-1">Harvests</div>
                    </div>
                    <div class="bg-gradient-to-br from-red-400 to-red-600 text-white rounded-2xl shadow-xl p-6 flex flex-col items-center hover:scale-105 transition-transform duration-200">
                        <i data-feather="heart" class="w-10 h-10 mb-2"></i>
                        <div class="text-4xl font-extrabold"><?php echo $total_livestock; ?></div>
                        <div class="text-lg font-semibold mt-1">Livestock</div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-400 to-blue-600 text-white rounded-2xl shadow-xl p-6 flex flex-col items-center hover:scale-105 transition-transform duration-200">
                        <i data-feather="dollar-sign" class="w-10 h-10 mb-2"></i>
                        <div class="text-4xl font-extrabold"><?php echo $total_loans; ?></div>
                        <div class="text-lg font-semibold mt-1">Active Loans</div>
                    </div>
                </div>

                <!-- Farmer Profile Summary Table -->
                <?php if ($farmer): ?>
                <div class="bg-gradient-to-r from-green-100 to-blue-100 rounded-xl shadow-lg p-6 mb-6 border border-green-300">
                    <h2 class="text-2xl font-bold text-center text-green-800 mb-6 flex items-center justify-center">
                        <i data-feather="user" class="w-6 h-6 mr-2 text-green-500"></i>
                        Your Profile Summary
                    </h2>
                    <div class="overflow-x-auto flex justify-center">
                        <table class="min-w-fit bg-white border border-gray-200 rounded-lg text-center">
                            <thead class="bg-green-200">
                                <tr>
                                    <th class="px-6 py-3 border-b font-semibold">Name</th>
                                    <th class="px-6 py-3 border-b font-semibold">ID Number</th>
                                    <th class="px-6 py-3 border-b font-semibold">District</th>
                                    <th class="px-6 py-3 border-b font-semibold">Sector</th>
                                    <th class="px-6 py-3 border-b font-semibold">Cell</th>
                                    <th class="px-6 py-3 border-b font-semibold">Bank Account</th>
                                    <th class="px-6 py-3 border-b font-semibold">Profile Image</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="hover:bg-green-50 transition-colors">
                                    <td class="px-6 py-4 border-b"><?php echo htmlspecialchars($farmer['username']); ?></td>
                                    <td class="px-6 py-4 border-b"><?php echo htmlspecialchars($farmer['id_number']); ?></td>
                                    <td class="px-6 py-4 border-b"><?php echo htmlspecialchars($farmer['district']); ?></td>
                                    <td class="px-6 py-4 border-b"><?php echo htmlspecialchars($farmer['sector']); ?></td>
                                    <td class="px-6 py-4 border-b"><?php echo htmlspecialchars($farmer['cell']); ?></td>
                                    <td class="px-6 py-4 border-b"><?php echo htmlspecialchars($farmer['bank_account']); ?></td>
                                    <td class="px-6 py-4 border-b">
                                        <?php if (!empty($farmer['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($farmer['profile_image']); ?>" alt="Profile Image" class="w-12 h-12 rounded-full object-cover mx-auto">
                                        <?php else: ?>
                                            <span class="text-gray-400">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Recent Activity -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-blue-200">
                    <h3 class="text-lg font-bold text-blue-800 mb-4 flex items-center">
                        <i data-feather="activity" class="w-5 h-5 mr-2 text-blue-500"></i>
                        Recent Activity
                    </h3>
                    <ul class="divide-y divide-blue-100">
                        <?php if (count($show_activity) > 0): ?>
                            <?php foreach ($show_activity as $activity): ?>
                                <li class="py-3 flex items-center justify-between">
                                    <span>
                                        <span class="font-semibold text-blue-700"><?php echo htmlspecialchars($activity['type']); ?></span>
                                        <?php if (!empty($activity['detail'])): ?>
                                            : <span class="text-gray-700"><?php echo htmlspecialchars($activity['detail']); ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <?php if (!empty($activity['date'])): ?>
                                        <span class="text-sm text-gray-500"><?php echo date('M d, Y', strtotime($activity['date'])); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="py-3 text-gray-500">No recent activity found.</li>
                        <?php endif; ?>
                    </ul>
                    <?php if ($has_more_activity): ?>
                        <div class="mt-4 text-right">
                            <a href="activity_log.php" class="text-blue-600 hover:underline font-semibold">View All</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Notifications -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-yellow-200">
                    <h3 class="text-lg font-bold text-yellow-800 mb-4 flex items-center">
                        <i data-feather="bell" class="w-5 h-5 mr-2 text-yellow-500"></i>
                        Notifications
                    </h3>
                    <ul class="list-disc pl-6 space-y-2">
                        <?php if (count($notifications) > 0): ?>
                            <?php foreach ($notifications as $note): ?>
                                <li class="text-gray-700"><?= $note ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-gray-500">No notifications at this time.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Analytics/Charts Section: Pie chart smaller, with explanation on right, and add bar chart below -->
                <?php
                // PIE CHART: Get total counts for Crops, Harvests, Livestock
                $stmt = $db->prepare("SELECT COUNT(*) FROM crops WHERE farmer_id = ?");
                $stmt->execute([$user_id]);
                $total_crops = (int)$stmt->fetchColumn();
                $stmt = $db->prepare("SELECT COUNT(*) FROM harvests WHERE farmer_id = ?");
                $stmt->execute([$user_id]);
                $total_harvests = (int)$stmt->fetchColumn();
                $stmt = $db->prepare("SELECT COUNT(*) FROM livestock WHERE farmer_id = ?");
                $stmt->execute([$user_id]);
                $total_livestock = (int)$stmt->fetchColumn();
                $pie_labels = ['Crops', 'Harvests', 'Livestock'];
                $pie_counts = [$total_crops, $total_harvests, $total_livestock];

                // BAR CHART: Get counts per month for each asset type for last 12 months
                $months = [];
                $month_keys = [];
                for ($i = 0; $i < 12; $i++) {
                    $m = date('M Y', strtotime("-" . (11 - $i) . " months"));
                    $months[] = $m;
                    $month_keys[] = date('Y-m', strtotime("-" . (11 - $i) . " months"));
                }
                // Crops by month
                $stmt = $db->prepare("SELECT DATE_FORMAT(planting_date, '%Y-%m') as ym, COUNT(*) as count FROM crops WHERE farmer_id = ? AND planting_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY ym");
                $stmt->execute([$user_id]);
                $crops_by_month = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'count', 'ym');
                // Harvests by month
                $stmt = $db->prepare("SELECT DATE_FORMAT(date_harvested, '%Y-%m') as ym, COUNT(*) as count FROM harvests WHERE farmer_id = ? AND date_harvested >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY ym");
                $stmt->execute([$user_id]);
                $harvests_by_month = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'count', 'ym');
                // Livestock by month
                $stmt = $db->prepare("SELECT DATE_FORMAT(NOW(), '%Y-%m') as ym, COUNT(*) as count FROM livestock WHERE farmer_id = ?");
                $stmt->execute([$user_id]);
                $livestock_by_month = array_fill_keys($month_keys, 0);
                $livestock_total = (int)$stmt->fetchColumn();
                // For livestock, if you want to track by date added, you need a date_added column. If not, show total only in the latest month.
                if ($livestock_total > 0) {
                    $livestock_by_month[end($month_keys)] = $livestock_total;
                }
                // Prepare data for chart
                $bar_crops = [];
                $bar_harvests = [];
                $bar_livestock = [];
                foreach ($month_keys as $k) {
                    $bar_crops[] = isset($crops_by_month[$k]) ? (int)$crops_by_month[$k] : 0;
                    $bar_harvests[] = isset($harvests_by_month[$k]) ? (int)$harvests_by_month[$k] : 0;
                    $bar_livestock[] = isset($livestock_by_month[$k]) ? (int)$livestock_by_month[$k] : 0;
                }
                ?>
                <div class="bg-white rounded-xl shadow-lg p-8 mb-8 border border-purple-200 flex flex-col md:flex-row items-center md:items-start gap-8">
                    <div class="w-full md:w-1/2 flex flex-col items-center">
                        <h4 class="text-lg font-bold text-purple-700 mb-2">Farm Asset Distribution</h4>
                        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow w-full flex justify-center">
                            <?php if (array_sum($pie_counts) > 0): ?>
                                <canvas id="pieChart" width="320" height="320" style="cursor:pointer;"></canvas>
                            <?php else: ?>
                                <div class="text-gray-400 text-center py-16">No farm asset data available</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="w-full md:w-1/2 flex flex-col items-center">
                        <h4 class="text-lg font-bold text-blue-700 mb-2">Farm Activity by Month</h4>
                        <div class="bg-white border border-gray-200 rounded-lg p-4 shadow w-full flex justify-center">
                            <?php if (array_sum($bar_crops) + array_sum($bar_harvests) + array_sum($bar_livestock) > 0): ?>
                                <canvas id="barChart" width="400" height="320" style="cursor:pointer;"></canvas>
                            <?php else: ?>
                                <div class="text-gray-400 text-center py-16">No activity data available</div>
                            <?php endif; ?>
                        </div>
                        <p class="text-gray-600 mt-4 text-center">This bar chart shows the number of crops added, harvests recorded, and livestock managed each month. Use it to track your farm’s activity trends.</p>
                    </div>
                </div>
                <!-- Chart.js CDN -->
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    <?php if (array_sum($pie_counts) > 0): ?>
                    // Pie chart
                    const pieCtx = document.getElementById('pieChart').getContext('2d');
                    new Chart(pieCtx, {
                        type: 'pie',
                        data: {
                            labels: <?= json_encode($pie_labels) ?>,
                            datasets: [{
                                data: <?= json_encode($pie_counts) ?>,
                                backgroundColor: [
                                    '#34d399', '#60a5fa', '#fbbf24'
                                ],
                                borderColor: '#fff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { color: '#374151', font: { size: 16 } }
                                },
                                tooltip: {
                                    enabled: true,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            let value = context.parsed || 0;
                                            return label + ': ' + value;
                                        }
                                    }
                                },
                                title: { display: false }
                            },
                            onHover: function(e, activeEls) {
                                e.native.target.style.cursor = activeEls.length ? 'pointer' : 'default';
                            }
                        }
                    });
                    <?php endif; ?>
                    <?php if (array_sum($bar_crops) + array_sum($bar_harvests) + array_sum($bar_livestock) > 0): ?>
                    // Bar chart
                    const barCtx = document.getElementById('barChart').getContext('2d');
                    new Chart(barCtx, {
                        type: 'bar',
                        data: {
                            labels: <?= json_encode($months) ?>,
                            datasets: [
                                {
                                    label: 'Crops',
                                    data: <?= json_encode($bar_crops) ?>,
                                    backgroundColor: '#34d399',
                                    borderColor: '#059669',
                                    borderWidth: 2,
                                    borderRadius: 8,
                                },
                                {
                                    label: 'Harvests',
                                    data: <?= json_encode($bar_harvests) ?>,
                                    backgroundColor: '#60a5fa',
                                    borderColor: '#2563eb',
                                    borderWidth: 2,
                                    borderRadius: 8,
                                },
                                {
                                    label: 'Livestock',
                                    data: <?= json_encode($bar_livestock) ?>,
                                    backgroundColor: '#fbbf24',
                                    borderColor: '#b45309',
                                    borderWidth: 2,
                                    borderRadius: 8,
                                }
                            ]
                        },
                        options: {
                            responsive: false,
                            plugins: {
                                legend: { display: true, position: 'bottom', labels: { color: '#374151', font: { size: 14 } } },
                                tooltip: {
                                    enabled: true,
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y;
                                        }
                                    }
                                },
                                title: { display: false }
                            },
                            scales: {
                                x: { grid: { display: false }, ticks: { color: '#374151', font: { size: 14 } } },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f3f4f6' },
                                    ticks: {
                                        color: '#374151',
                                        font: { size: 14 },
                                        stepSize: 1,
                                        callback: function(value) { return Number.isInteger(value) ? value : null; }
                                    }
                                }
                            },
                            onHover: function(e, activeEls) {
                                e.native.target.style.cursor = activeEls.length ? 'pointer' : 'default';
                            }
                        }
                    });
                    <?php endif; ?>

                    // Modal logic
                    const addTaskBtn = document.getElementById('addTaskBtn');
                    const addTaskModal = document.getElementById('addTaskModal');
                    const closeTaskModal = document.getElementById('closeTaskModal');
                    const addTaskForm = document.getElementById('addTaskForm');
                    const taskModalTitle = document.getElementById('taskModalTitle');

                    if (addTaskBtn && addTaskModal && closeTaskModal) {
                        addTaskBtn.onclick = function() {
                            addTaskModal.classList.remove('hidden');
                            taskModalTitle.textContent = 'Add New Task';
                            addTaskForm.reset();
                            document.getElementById('task_id').value = '';
                            console.log('Add Task button clicked, modal opened');
                        };
                        closeTaskModal.onclick = function() {
                            addTaskModal.classList.add('hidden');
                        };
                        window.onclick = function(e) {
                            if (e.target === addTaskModal) addTaskModal.classList.add('hidden');
                        };
                    }

                    // AJAX: Add/Edit Task
                    addTaskForm.onsubmit = function(e) {
                        e.preventDefault();
                        const formData = new FormData(addTaskForm);
                        formData.append('action', formData.get('task_id') ? 'edit_task' : 'add_task');
                        fetch('task_action.php', {
                            method: 'POST',
                            body: new URLSearchParams(formData)
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.task_html) {
                                if (formData.get('task_id')) {
                                    // Edit: Replace the existing task item
                                    const li = document.querySelector('li[data-task-id="' + formData.get('task_id') + '"]');
                                    if (li) li.outerHTML = data.task_html;
                                } else {
                                    // Add: Insert new task at the top
                                    document.getElementById('tasksList').insertAdjacentHTML('afterbegin', data.task_html);
                                }
                                addTaskModal.classList.add('hidden');
                                addTaskForm.reset();
                                // Re-bind event handlers for new buttons
                                bindMarkDoneBtns();
                                bindDeleteBtns();
                                bindEditBtns();
                            } else {
                                alert('Failed to save task.' + (data.error ? '\n' + data.error : ''));
                            }
                        });
                    };

                    // Function to re-bind event handlers for new buttons
                    function bindMarkDoneBtns() {
                        document.querySelectorAll('.mark-done-btn').forEach(btn => {
                            btn.onclick = function() {
                                const taskId = this.dataset.taskId;
                                if (confirm('Are you sure you want to mark this task as done?')) {
                                    fetch('task_action.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: 'task_id=' + taskId + '&action=mark_done'
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success && data.task_html) {
                                            const li = this.closest('li');
                                            if (li) {
                                                li.remove();
                                                document.getElementById('completedTasksList').insertAdjacentHTML('afterbegin', data.task_html);
                                                bindRestoreBtns();
                                                bindDeleteBtns();
                                            }
                                        } else {
                                            alert('Failed to mark task as done.');
                                        }
                                    });
                                }
                            };
                        });
                    }

                    function bindRestoreBtns() {
                        document.querySelectorAll('.restore-task-btn').forEach(btn => {
                            btn.onclick = function() {
                                const taskId = this.dataset.taskId;
                                if (confirm('Are you sure you want to restore this task?')) {
                                    fetch('task_action.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: 'task_id=' + taskId + '&action=restore_task'
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success && data.task_html) {
                                            const li = this.closest('li');
                                            if (li) {
                                                li.remove();
                                                document.getElementById('tasksList').insertAdjacentHTML('afterbegin', data.task_html);
                                                bindMarkDoneBtns();
                                                bindDeleteBtns();
                                                bindEditBtns();
                                            }
                                        } else {
                                            alert('Failed to restore task.');
                                        }
                                    });
                                }
                            };
                        });
                    }

                    function bindDeleteBtns() {
                        document.querySelectorAll('.delete-task-btn').forEach(btn => {
                            btn.onclick = function() {
                                const taskId = this.dataset.taskId;
                                if (confirm('Are you sure you want to delete this task?')) {
                                    fetch('task_action.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded',
                                        },
                                        body: 'task_id=' + taskId + '&action=delete_task'
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            const li = this.closest('li');
                                            if (li) {
                                                li.remove();
                                                bindMarkDoneBtns(); // Re-bind after update
                                                bindDeleteBtns();
                                                bindEditBtns();
                                            }
                                        } else {
                                            alert('Failed to delete task.');
                                        }
                                    });
                                }
                            };
                        });
                    }

                    function bindEditBtns() {
                        document.querySelectorAll('.edit-task-btn').forEach(btn => {
                            btn.onclick = function() {
                                const taskId = this.dataset.taskId;
                                const taskItem = document.querySelector('li[data-task-id="' + taskId + '"]');
                                if (taskItem) {
                                    const taskData = {
                                        id: taskId,
                                        title: taskItem.querySelector('.font-semibold').textContent.replace('Edit', '').trim(),
                                        description: taskItem.querySelector('.text-gray-500').textContent.replace('Edit', '').trim(),
                                        due_date: taskItem.querySelector('.text-xs').textContent.replace('Due', '').trim(),
                                        related_crop_id: taskItem.querySelector('.text-green-600').textContent.replace('Crop:', '').replace(']', '').trim(),
                                        related_livestock_id: taskItem.querySelector('.text-red-600').textContent.replace('Livestock:', '').replace(']', '').trim()
                                    };
                                    addTaskForm.task_id.value = taskId;
                                    addTaskForm.task_title.value = taskData.title;
                                    addTaskForm.task_description.value = taskData.description;
                                    addTaskForm.task_due_date.value = taskData.due_date;
                                    addTaskForm.task_related_crop_id.value = taskData.related_crop_id;
                                    addTaskForm.task_related_livestock_id.value = taskData.related_livestock_id;
                                    addTaskModal.classList.remove('hidden');
                                    taskModalTitle.textContent = 'Edit Task';
                                }
                            };
                        });
                    }

                    // Initial binding of event handlers
                    bindMarkDoneBtns();
                    bindDeleteBtns();
                    bindEditBtns();
                    bindRestoreBtns();
                });
                </script>

                <!-- Recent Crops -->
                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i data-feather="leaf" class="w-5 h-5 mr-2 text-green-500"></i>
                        Recent Crops
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <span class="text-gray-700">Tomatoes</span>
                            </div>
                            <span class="text-sm text-gray-500">2 days ago</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                <span class="text-gray-700">Maize</span>
                            </div>
                            <span class="text-sm text-gray-500">5 days ago</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <span class="text-gray-700">Beans</span>
                            </div>
                            <span class="text-sm text-gray-500">1 week ago</span>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Tasks Card (Interactive) -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-blue-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-blue-800 flex items-center">
                            <i data-feather="calendar" class="w-5 h-5 mr-2 text-blue-500"></i>
                            Upcoming Tasks
                        </h3>
                        <button id="addTaskBtn" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg shadow transition">+ Add Task</button>
                    </div>
                    <ul id="tasksList" class="space-y-3">
                        <?php if (count($upcoming_tasks) > 0): ?>
                            <?php foreach ($upcoming_tasks as $task): ?>
                                <li class="flex items-center justify-between bg-blue-50 rounded-lg p-3" data-task-id="<?php echo $task['id']; ?>">
                                    <div>
                                        <span class="font-semibold text-blue-700"><?php echo htmlspecialchars($task['title']); ?></span>
                                        <?php if ($task['crop_name']): ?>
                                            <span class="ml-2 text-green-600 text-xs">[Crop: <?php echo htmlspecialchars($task['crop_name']); ?>]</span>
                                        <?php endif; ?>
                                        <?php if ($task['animal_type']): ?>
                                            <span class="ml-2 text-red-600 text-xs">[Livestock: <?php echo htmlspecialchars($task['animal_type']); ?>]</span>
                                        <?php endif; ?>
                                        <div class="text-gray-500 text-sm"><?php echo htmlspecialchars($task['description']); ?></div>
                                        <?php if ($task['due_date']): ?>
                                            <div class="text-xs text-gray-400">Due <?php echo date('M d, Y', strtotime($task['due_date'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex flex-col gap-2 items-end">
                                        <button class="edit-task-btn bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded transition mb-1" data-task-id="<?php echo $task['id']; ?>">Edit</button>
                                        <button class="mark-done-btn bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition mb-1" data-task-id="<?php echo $task['id']; ?>">Mark as Done</button>
                                        <button class="delete-task-btn bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition" data-task-id="<?php echo $task['id']; ?>">Delete</button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-gray-500">No upcoming tasks.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Completed Tasks Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <i data-feather="check-circle" class="w-5 h-5 mr-2 text-green-500"></i>
                            Completed Tasks
                        </h3>
                    </div>
                    <ul id="completedTasksList" class="space-y-3">
                        <?php if (count($completed_tasks) > 0): ?>
                            <?php foreach ($completed_tasks as $task): ?>
                                <li class="flex items-center justify-between bg-green-50 rounded-lg p-3" data-task-id="<?php echo $task['id']; ?>">
                                    <div>
                                        <span class="font-semibold text-green-700 line-through"><?php echo htmlspecialchars($task['title']); ?></span>
                                        <?php if ($task['crop_name']): ?>
                                            <span class="ml-2 text-green-600 text-xs">[Crop: <?php echo htmlspecialchars($task['crop_name']); ?>]</span>
                                        <?php endif; ?>
                                        <?php if ($task['animal_type']): ?>
                                            <span class="ml-2 text-red-600 text-xs">[Livestock: <?php echo htmlspecialchars($task['animal_type']); ?>]</span>
                                        <?php endif; ?>
                                        <div class="text-gray-500 text-sm line-through"><?php echo htmlspecialchars($task['description']); ?></div>
                                        <?php if ($task['due_date']): ?>
                                            <div class="text-xs text-gray-400">Due <?php echo date('M d, Y', strtotime($task['due_date'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex flex-col gap-2 items-end">
                                        <button class="restore-task-btn bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition mb-1" data-task-id="<?php echo $task['id']; ?>">Restore</button>
                                        <button class="delete-task-btn bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition" data-task-id="<?php echo $task['id']; ?>">Delete</button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-gray-500">No completed tasks.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Add/Edit Task Modal -->
                <div id="addTaskModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md relative">
                        <button id="closeTaskModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">&times;</button>
                        <h3 id="taskModalTitle" class="text-xl font-bold mb-4 text-green-700">Add New Task</h3>
                        <form id="addTaskForm">
                            <input type="hidden" name="task_id" id="task_id">
                            <div class="mb-3">
                                <label class="block text-sm font-semibold mb-1">Title</label>
                                <input type="text" name="title" id="task_title" class="w-full border rounded px-3 py-2" required>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-semibold mb-1">Description</label>
                                <textarea name="description" id="task_description" class="w-full border rounded px-3 py-2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-semibold mb-1">Due Date</label>
                                <input type="date" name="due_date" id="task_due_date" class="w-full border rounded px-3 py-2">
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-semibold mb-1">Related Crop</label>
                                <select name="related_crop_id" id="task_related_crop_id" class="w-full border rounded px-3 py-2">
                                    <option value="">None</option>
                                    <?php
                                    $stmt = $db->prepare("SELECT id, crop_name FROM crops WHERE farmer_id = ?");
                                    $stmt->execute([$user_id]);
                                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $crop) {
                                        echo '<option value="' . $crop['id'] . '">' . htmlspecialchars($crop['crop_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="block text-sm font-semibold mb-1">Related Livestock</label>
                                <select name="related_livestock_id" id="task_related_livestock_id" class="w-full border rounded px-3 py-2">
                                    <option value="">None</option>
                                    <?php
                                    $stmt = $db->prepare("SELECT id, animal_type FROM livestock WHERE farmer_id = ?");
                                    $stmt->execute([$user_id]);
                                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $livestock) {
                                        echo '<option value="' . $livestock['id'] . '">' . htmlspecialchars($livestock['animal_type']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Save Task</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Vertical Sidebar (Right) -->
            <div class="w-full lg:w-1/5 flex flex-col items-center">
                <div class="sticky top-24 w-full">
                    <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-200 flex flex-col gap-4">
                        <h3 class="text-lg font-bold text-gray-800 mb-2 flex items-center justify-center">
                            <i data-feather="zap" class="w-5 h-5 mr-2 text-yellow-500"></i>
                            Quick Actions
                        </h3>
                        <a href="profile.php" class="group bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-4 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1 w-full justify-center">
                            <i data-feather="user-check" class="w-5 h-5"></i>
                            <span>Complete Profile</span>
                        </a>
                        <a href="crops.php" class="group bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1 w-full justify-center">
                            <i data-feather="leaf" class="w-5 h-5"></i>
                            <span>Manage Crops</span>
                        </a>
                        <a href="harvest.php" class="group bg-gradient-to-r from-yellow-500 to-yellow-600 text-white px-6 py-4 rounded-lg hover:from-yellow-600 hover:to-yellow-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1 w-full justify-center">
                            <i data-feather="package" class="w-5 h-5"></i>
                            <span>Record Harvest</span>
                        </a>
                        <a href="livestock.php" class="group bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-4 rounded-lg hover:from-red-600 hover:to-red-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1 w-full justify-center">
                            <i data-feather="heart" class="w-5 h-5"></i>
                            <span>Manage Livestock</span>
                        </a>
                        <a href="loan_request.php" class="group bg-gradient-to-r from-purple-500 to-purple-600 text-white px-6 py-4 rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1 w-full justify-center">
                            <i data-feather="dollar-sign" class="w-5 h-5"></i>
                            <span>Request Loan</span>
                        </a>
                        <a href="change_password.php" class="group bg-gradient-to-r from-gray-500 to-gray-600 text-white px-6 py-4 rounded-lg hover:from-gray-600 hover:to-gray-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1 w-full justify-center">
                            <i data-feather="lock" class="w-5 h-5"></i>
                            <span>Change Password</span>
                        </a>
                        <a href="#" class="group bg-gradient-to-r from-indigo-500 to-indigo-600 text-white px-6 py-4 rounded-lg hover:from-indigo-600 hover:to-indigo-700 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl transform hover:-translate-y-1 w-full justify-center">
                            <i data-feather="bar-chart-2" class="w-5 h-5"></i>
                            <span>View Reports</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<!-- Add DataTables JS and Buttons extension -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
$(document).ready(function() {
    // The original DataTables script for the farmersTable is removed as per the edit hint.
    // The table is now static and does not have export/search/pagination features.
});
</script>

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