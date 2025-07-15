<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
header('Content-Type: application/json');

$db = (new Database())->getConnection();
$farmer_id = $_SESSION['user_id'];

$action = $_POST['action'] ?? '';

if ($action === 'edit') {
    // Validate required fields
    $required = ['id', 'crop_name', 'date_harvested', 'yield', 'yield_unit', 'price_kg'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => 'Missing required field: ' . $field]);
            exit;
        }
    }
    $id = $_POST['id'];
    $crop_name = $_POST['crop_name'];
    $date_harvested = $_POST['date_harvested'];
    $yield = $_POST['yield'];
    $yield_unit = $_POST['yield_unit'];
    $price_kg = $_POST['price_kg'];
    $actual_income = $_POST['actual_income'] ?? null;
    $quality = $_POST['quality'] ?? null;
    $harvest_notes = $_POST['harvest_notes'] ?? null;

    // Update the harvest record (validate ownership)
    $stmt = $db->prepare("UPDATE harvests SET crop_name=?, date_harvested=?, yield=?, yield_unit=?, price_kg=?, actual_income=?, quality=?, harvest_notes=? WHERE id=? AND farmer_id=?");
    $success = $stmt->execute([
        $crop_name, $date_harvested, $yield, $yield_unit, $price_kg, $actual_income, $quality, $harvest_notes, $id, $farmer_id
    ]);
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Harvest record updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update harvest record.']);
    }
    exit;
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Missing harvest ID.']);
        exit;
    }
    // Delete the harvest record (validate ownership)
    $stmt = $db->prepare("DELETE FROM harvests WHERE id=? AND farmer_id=?");
    $success = $stmt->execute([$id, $farmer_id]);
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Harvest record deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete harvest record.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']); 