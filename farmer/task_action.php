<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

function error_response($msg) {
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        if ($action === 'mark_done' && isset($_POST['task_id'])) {
            $task_id = intval($_POST['task_id']);
            $stmt = $db->prepare('UPDATE tasks SET status = "done" WHERE id = ? AND farmer_id = ?');
            $stmt->execute([$task_id, $user_id]);
            // Fetch the updated task for completed section
            $stmt = $db->prepare('SELECT t.*, c.crop_name, l.animal_type FROM tasks t LEFT JOIN crops c ON t.related_crop_id = c.id LEFT JOIN livestock l ON t.related_livestock_id = l.id WHERE t.id = ? AND t.farmer_id = ?');
            $stmt->execute([$task_id, $user_id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            ob_start(); ?>
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
            <?php $task_html = ob_get_clean();
            echo json_encode(['success' => true, 'task_html' => $task_html]);
            exit;
        }
        if ($action === 'add_task') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $due_date = $_POST['due_date'] ?? null;
            $related_crop_id = !empty($_POST['related_crop_id']) ? intval($_POST['related_crop_id']) : null;
            $related_livestock_id = !empty($_POST['related_livestock_id']) ? intval($_POST['related_livestock_id']) : null;
            if ($title === '') {
                error_response('Title required');
            }
            $stmt = $db->prepare('INSERT INTO tasks (farmer_id, title, description, due_date, related_crop_id, related_livestock_id) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$user_id, $title, $description, $due_date ?: null, $related_crop_id, $related_livestock_id]);
            $task_id = $db->lastInsertId();
            // Fetch crop/livestock names for display
            $crop_name = '';
            $animal_type = '';
            if ($related_crop_id) {
                $stmt = $db->prepare('SELECT crop_name FROM crops WHERE id = ?');
                $stmt->execute([$related_crop_id]);
                $crop_name = $stmt->fetchColumn();
            }
            if ($related_livestock_id) {
                $stmt = $db->prepare('SELECT animal_type FROM livestock WHERE id = ?');
                $stmt->execute([$related_livestock_id]);
                $animal_type = $stmt->fetchColumn();
            }
            ob_start(); ?>
            <li class="flex items-center justify-between bg-blue-50 rounded-lg p-3" data-task-id="<?php echo $task_id; ?>">
                <div>
                    <span class="font-semibold text-blue-700"><?php echo htmlspecialchars($title); ?></span>
                    <?php if ($crop_name): ?>
                        <span class="ml-2 text-green-600 text-xs">[Crop: <?php echo htmlspecialchars($crop_name); ?>]</span>
                    <?php endif; ?>
                    <?php if ($animal_type): ?>
                        <span class="ml-2 text-red-600 text-xs">[Livestock: <?php echo htmlspecialchars($animal_type); ?>]</span>
                    <?php endif; ?>
                    <div class="text-gray-500 text-sm"><?php echo htmlspecialchars($description); ?></div>
                    <?php if ($due_date): ?>
                        <div class="text-xs text-gray-400">Due <?php echo date('M d, Y', strtotime($due_date)); ?></div>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col gap-2 items-end">
                    <button class="edit-task-btn bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded transition mb-1" data-task-id="<?php echo $task_id; ?>">Edit</button>
                    <button class="mark-done-btn bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition mb-1" data-task-id="<?php echo $task_id; ?>">Mark as Done</button>
                    <button class="delete-task-btn bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition" data-task-id="<?php echo $task_id; ?>">Delete</button>
                </div>
            </li>
            <?php $task_html = ob_get_clean();
            echo json_encode(['success' => true, 'task_html' => $task_html]);
            exit;
        }
        if ($action === 'edit_task' && isset($_POST['task_id'])) {
            $task_id = intval($_POST['task_id']);
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $due_date = $_POST['due_date'] ?? null;
            $related_crop_id = !empty($_POST['related_crop_id']) ? intval($_POST['related_crop_id']) : null;
            $related_livestock_id = !empty($_POST['related_livestock_id']) ? intval($_POST['related_livestock_id']) : null;
            if ($title === '') {
                error_response('Title required');
            }
            $stmt = $db->prepare('UPDATE tasks SET title=?, description=?, due_date=?, related_crop_id=?, related_livestock_id=? WHERE id=? AND farmer_id=?');
            $stmt->execute([$title, $description, $due_date ?: null, $related_crop_id, $related_livestock_id, $task_id, $user_id]);
            // Fetch crop/livestock names for display
            $crop_name = '';
            $animal_type = '';
            if ($related_crop_id) {
                $stmt = $db->prepare('SELECT crop_name FROM crops WHERE id = ?');
                $stmt->execute([$related_crop_id]);
                $crop_name = $stmt->fetchColumn();
            }
            if ($related_livestock_id) {
                $stmt = $db->prepare('SELECT animal_type FROM livestock WHERE id = ?');
                $stmt->execute([$related_livestock_id]);
                $animal_type = $stmt->fetchColumn();
            }
            ob_start(); ?>
            <li class="flex items-center justify-between bg-blue-50 rounded-lg p-3" data-task-id="<?php echo $task_id; ?>">
                <div>
                    <span class="font-semibold text-blue-700"><?php echo htmlspecialchars($title); ?></span>
                    <?php if ($crop_name): ?>
                        <span class="ml-2 text-green-600 text-xs">[Crop: <?php echo htmlspecialchars($crop_name); ?>]</span>
                    <?php endif; ?>
                    <?php if ($animal_type): ?>
                        <span class="ml-2 text-red-600 text-xs">[Livestock: <?php echo htmlspecialchars($animal_type); ?>]</span>
                    <?php endif; ?>
                    <div class="text-gray-500 text-sm"><?php echo htmlspecialchars($description); ?></div>
                    <?php if ($due_date): ?>
                        <div class="text-xs text-gray-400">Due <?php echo date('M d, Y', strtotime($due_date)); ?></div>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col gap-2 items-end">
                    <button class="edit-task-btn bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded transition mb-1" data-task-id="<?php echo $task_id; ?>">Edit</button>
                    <button class="mark-done-btn bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded transition mb-1" data-task-id="<?php echo $task_id; ?>">Mark as Done</button>
                    <button class="delete-task-btn bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition" data-task-id="<?php echo $task_id; ?>">Delete</button>
                </div>
            </li>
            <?php $task_html = ob_get_clean();
            echo json_encode(['success' => true, 'task_html' => $task_html]);
            exit;
        }
        if ($action === 'delete_task' && isset($_POST['task_id'])) {
            $task_id = intval($_POST['task_id']);
            $stmt = $db->prepare('DELETE FROM tasks WHERE id = ? AND farmer_id = ?');
            $stmt->execute([$task_id, $user_id]);
            echo json_encode(['success' => true]);
            exit;
        }
        if ($action === 'restore_task' && isset($_POST['task_id'])) {
            $task_id = intval($_POST['task_id']);
            $stmt = $db->prepare('UPDATE tasks SET status = "pending" WHERE id = ? AND farmer_id = ?');
            $stmt->execute([$task_id, $user_id]);
            // Fetch the updated task for upcoming section
            $stmt = $db->prepare('SELECT t.*, c.crop_name, l.animal_type FROM tasks t LEFT JOIN crops c ON t.related_crop_id = c.id LEFT JOIN livestock l ON t.related_livestock_id = l.id WHERE t.id = ? AND t.farmer_id = ?');
            $stmt->execute([$task_id, $user_id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            ob_start(); ?>
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
            <?php $task_html = ob_get_clean();
            echo json_encode(['success' => true, 'task_html' => $task_html]);
            exit;
        }
    }
} catch (Exception $e) {
    error_response($e->getMessage());
}
echo json_encode(['success' => false, 'error' => 'Invalid request']); 