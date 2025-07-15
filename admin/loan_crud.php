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

if ($action === 'view') {
    $id = $_POST['id'] ?? 0;
    $stmt = $db->prepare('SELECT lr.*, u.username AS farmer_name, fi.institution_name FROM loan_requests lr LEFT JOIN users u ON lr.farmer_id = u.id LEFT JOIN financial_institutions fi ON lr.institution_id = fi.id WHERE lr.id = ?');
    $stmt->execute([$id]);
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($loan) respond(true, 'Loan found.', $loan);
    respond(false, 'Loan not found.');
}

if ($action === 'edit') {
    $id = $_POST['id'] ?? 0;
    $fields = ['status'];
    foreach ($fields as $f) {
        if (!isset($_POST[$f]) || $_POST[$f] === '') respond(false, 'Status is required.');
    }
    $approval_date = $_POST['approval_date'] ?? null;
    $amount_approved = $_POST['amount_approved'] ?? null;
    try {
        $stmt = $db->prepare("UPDATE loan_requests SET status=?, approval_date=?, amount_approved=? WHERE id=?");
        $stmt->execute([
            $_POST['status'],
            $approval_date,
            $amount_approved,
            $id
        ]);
        respond(true, 'Loan updated successfully.');
    } catch (Exception $e) {
        respond(false, 'Error updating loan: ' . $e->getMessage());
    }
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? 0;
    try {
        $stmt = $db->prepare('DELETE FROM loan_requests WHERE id = ?');
        $stmt->execute([$id]);
        respond(true, 'Loan deleted successfully.');
    } catch (Exception $e) {
        respond(false, 'Error deleting loan: ' . $e->getMessage());
    }
}

respond(false, 'Invalid action.'); 