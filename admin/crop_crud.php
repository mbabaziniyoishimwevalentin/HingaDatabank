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
    $fields = ['farmer_id','crop_name','crop_type','area_planted','planting_date','expected_harvest_date'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) respond(false, 'All fields are required.');
    }
    try {
        $stmt = $db->prepare("INSERT INTO crops (farmer_id, crop_name, crop_type, area_planted, planting_date, expected_harvest_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['farmer_id'],
            $_POST['crop_name'],
            $_POST['crop_type'],
            $_POST['area_planted'],
            $_POST['planting_date'],
            $_POST['expected_harvest_date']
        ]);
        respond(true, 'Crop added successfully.');
    } catch (Exception $e) {
        respond(false, 'Error adding crop: ' . $e->getMessage());
    }
}

if ($action === 'view') {
    $id = $_POST['id'] ?? 0;
    $stmt = $db->prepare('SELECT c.*, u.username AS farmer_name FROM crops c LEFT JOIN users u ON c.farmer_id = u.id WHERE c.id = ?');
    $stmt->execute([$id]);
    $crop = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($crop) respond(true, 'Crop found.', $crop);
    respond(false, 'Crop not found.');
}

if ($action === 'edit') {
    $id = $_POST['id'] ?? 0;
    $fields = ['farmer_id','crop_name','crop_type','area_planted','planting_date','expected_harvest_date'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) respond(false, 'All fields are required.');
    }
    try {
        $stmt = $db->prepare("UPDATE crops SET farmer_id=?, crop_name=?, crop_type=?, area_planted=?, planting_date=?, expected_harvest_date=? WHERE id=?");
        $stmt->execute([
            $_POST['farmer_id'],
            $_POST['crop_name'],
            $_POST['crop_type'],
            $_POST['area_planted'],
            $_POST['planting_date'],
            $_POST['expected_harvest_date'],
            $id
        ]);
        respond(true, 'Crop updated successfully.');
    } catch (Exception $e) {
        respond(false, 'Error updating crop: ' . $e->getMessage());
    }
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? 0;
    try {
        $stmt = $db->prepare('DELETE FROM crops WHERE id = ?');
        $stmt->execute([$id]);
        respond(true, 'Crop deleted successfully.');
    } catch (Exception $e) {
        respond(false, 'Error deleting crop: ' . $e->getMessage());
    }
}

respond(false, 'Invalid action.'); 