<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$db = (new Database())->getConnection();
$action = $_POST['action'] ?? '';

function respond($success, $message, $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit();
}

if ($action === 'add') {
    $fields = ['farmer_id','animal_type','quantity','value_all_animals'];
    foreach ($fields as $f) {
        if (empty($_POST[$f]) && $_POST[$f] !== '0') respond(false, 'All fields are required.');
    }
    try {
        $stmt = $db->prepare("INSERT INTO livestock (farmer_id, animal_type, quantity, value_all_animals) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['farmer_id'],
            $_POST['animal_type'],
            $_POST['quantity'],
            $_POST['value_all_animals']
        ]);
        respond(true, 'Livestock added successfully.');
    } catch (Exception $e) {
        respond(false, 'Error adding livestock: ' . $e->getMessage());
    }
}

if ($action === 'view') {
    $id = $_POST['id'] ?? 0;
    $stmt = $db->prepare('SELECT l.*, u.username AS farmer_name FROM livestock l LEFT JOIN users u ON l.farmer_id = u.id WHERE l.id = ?');
    $stmt->execute([$id]);
    $livestock = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($livestock) respond(true, 'Livestock found.', $livestock);
    respond(false, 'Livestock not found.');
}

if ($action === 'edit') {
    $id = $_POST['id'] ?? 0;
    $fields = ['farmer_id','animal_type','quantity','value_all_animals'];
    foreach ($fields as $f) {
        if (empty($_POST[$f]) && $_POST[$f] !== '0') respond(false, 'All fields are required.');
    }
    try {
        $stmt = $db->prepare("UPDATE livestock SET farmer_id=?, animal_type=?, quantity=?, value_all_animals=? WHERE id=?");
        $stmt->execute([
            $_POST['farmer_id'],
            $_POST['animal_type'],
            $_POST['quantity'],
            $_POST['value_all_animals'],
            $id
        ]);
        respond(true, 'Livestock updated successfully.');
    } catch (Exception $e) {
        respond(false, 'Error updating livestock: ' . $e->getMessage());
    }
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? 0;
    try {
        $stmt = $db->prepare('DELETE FROM livestock WHERE id = ?');
        $stmt->execute([$id]);
        respond(true, 'Livestock deleted successfully.');
    } catch (Exception $e) {
        respond(false, 'Error deleting livestock: ' . $e->getMessage());
    }
}

respond(false, 'Invalid action.'); 