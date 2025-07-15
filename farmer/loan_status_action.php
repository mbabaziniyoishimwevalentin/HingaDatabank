<?php
require_once '../includes/auth_check.php';
require_once '../config/database.php';
header('Content-Type: application/json');

$db = (new Database())->getConnection();
$farmer_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'edit') {
    $id = $_POST['id'] ?? null;
    $loan_type = $_POST['loan_type'] ?? '';
    $amount_requested = $_POST['amount_requested'] ?? 0;
    $months = $_POST['months'] ?? 0;
    $collateral_type = $_POST['collateral_type'] ?? '';
    $collateral_value = $_POST['collateral_value'] ?? 0;
    $purpose = $_POST['purpose'] ?? '';

    // Validate ownership and status
    $stmt = $db->prepare("SELECT * FROM loan_requests WHERE id=? AND farmer_id=? AND status='pending'");
    $stmt->execute([$id, $farmer_id]);
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$loan) {
        echo json_encode(['success' => false, 'message' => 'Loan not found or not editable.']);
        exit;
    }

    // Recalculate interest and payments
    $stmt = $db->prepare("SELECT interest_rate_permonth FROM loan_interest_calculations WHERE financial_institutions_id = ? AND ? >= min_amount AND ? <= max_amount ORDER BY min_amount ASC LIMIT 1");
    $stmt->execute([$loan['institution_id'], $amount_requested, $amount_requested]);
    $interest_rate_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$interest_rate_data) {
        echo json_encode(['success' => false, 'message' => 'No interest rate found for this amount range.']);
        exit;
    }
    $interest_rate = $interest_rate_data['interest_rate_permonth'];
    $monthly_rate = $interest_rate / 100;
    $monthly_payment = $amount_requested * $monthly_rate;
    $total_amount = $amount_requested + ($monthly_payment * $months);

    // Update loan
    $stmt = $db->prepare("UPDATE loan_requests SET loan_type=?, amount_requested=?, months=?, collateral_type=?, collateral_value=?, purpose=?, interest_rate=?, monthly_payment=?, amount_to_pay=? WHERE id=? AND farmer_id=? AND status='pending'");
    $success = $stmt->execute([
        $loan_type, $amount_requested, $months, $collateral_type, $collateral_value, $purpose, $interest_rate, $monthly_payment, $total_amount, $id, $farmer_id
    ]);
    if ($success) {
        echo json_encode(['success' => true, 'monthly_payment' => $monthly_payment]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update loan.']);
    }
    exit;
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? null;
    // Validate ownership and status
    $stmt = $db->prepare("SELECT * FROM loan_requests WHERE id=? AND farmer_id=? AND status='pending'");
    $stmt->execute([$id, $farmer_id]);
    $loan = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$loan) {
        echo json_encode(['success' => false, 'message' => 'Loan not found or not deletable.']);
        exit;
    }
    $stmt = $db->prepare("DELETE FROM loan_requests WHERE id=? AND farmer_id=? AND status='pending'");
    $success = $stmt->execute([$id, $farmer_id]);
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete loan.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']); 