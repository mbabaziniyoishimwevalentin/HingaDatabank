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
    $fields = ['farmer_id','crop_name','date_harvested','yield','yield_unit','price_kg'];
    foreach ($fields as $f) {
        if (empty($_POST[$f]) && $_POST[$f] !== '0') respond(false, 'All fields are required.');
    }
    try {
        $stmt = $db->prepare("INSERT INTO harvests (farmer_id, crop_name, date_harvested, yield, yield_unit, price_kg) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['farmer_id'],
            $_POST['crop_name'],
            $_POST['date_harvested'],
            $_POST['yield'],
            $_POST['yield_unit'],
            $_POST['price_kg']
        ]);
        respond(true, 'Harvest added successfully.');
    } catch (Exception $e) {
        respond(false, 'Error adding harvest: ' . $e->getMessage());
    }
}

if ($action === 'view') {
    $id = $_POST['id'] ?? 0;
    $stmt = $db->prepare('SELECT h.*, u.username AS farmer_name FROM harvests h LEFT JOIN users u ON h.farmer_id = u.id WHERE h.id = ?');
    $stmt->execute([$id]);
    $harvest = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($harvest) respond(true, 'Harvest found.', $harvest);
    respond(false, 'Harvest not found.');
}

if ($action === 'edit') {
    $id = $_POST['id'] ?? 0;
    $fields = ['farmer_id','crop_name','date_harvested','yield','yield_unit','price_kg'];
    foreach ($fields as $f) {
        if (empty($_POST[$f]) && $_POST[$f] !== '0') respond(false, 'All fields are required.');
    }
    try {
        $stmt = $db->prepare("UPDATE harvests SET farmer_id=?, crop_name=?, date_harvested=?, yield=?, yield_unit=?, price_kg=? WHERE id=?");
        $stmt->execute([
            $_POST['farmer_id'],
            $_POST['crop_name'],
            $_POST['date_harvested'],
            $_POST['yield'],
            $_POST['yield_unit'],
            $_POST['price_kg'],
            $id
        ]);
        respond(true, 'Harvest updated successfully.');
    } catch (Exception $e) {
        respond(false, 'Error updating harvest: ' . $e->getMessage());
    }
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? 0;
    try {
        $stmt = $db->prepare('DELETE FROM harvests WHERE id = ?');
        $stmt->execute([$id]);
        respond(true, 'Harvest deleted successfully.');
    } catch (Exception $e) {
        respond(false, 'Error deleting harvest: ' . $e->getMessage());
    }
}

respond(false, 'Invalid action.'); 