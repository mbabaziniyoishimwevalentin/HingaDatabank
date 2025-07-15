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
    $name = trim($_POST['institution_name'] ?? '');
    $type = trim($_POST['institution_type'] ?? '');
    $license = trim($_POST['license_number'] ?? '');
    $contact = trim($_POST['contact_person'] ?? '');
    $location = trim($_POST['office_location'] ?? '');
    $min_loan = trim($_POST['min_loan'] ?? '');
    $max_loan = trim($_POST['max_loan'] ?? '');
    // For demo: generate email and password
    $email = strtolower(preg_replace('/\s+/', '', $name)) . '@institution.com';
    $password = 'password123';
    if (!$name || !$type) {
        echo json_encode(['success' => false, 'message' => 'Name and type are required.']);
        exit();
    }
    // Check for duplicate institution name
    $check = $db->prepare('SELECT * FROM financial_institutions WHERE institution_name = ?');
    $check->execute([$name]);
    if ($check->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Institution already exists.']);
        exit();
    }
    // Insert into users
    $checkUser = $db->prepare('SELECT * FROM users WHERE email = ?');
    $checkUser->execute([$email]);
    if ($checkUser->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Institution email already exists.']);
        exit();
    }
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, "institution")');
    if ($stmt->execute([$name, $email, $hashed])) {
        $user_id = $db->lastInsertId();
        $stmt2 = $db->prepare('INSERT INTO financial_institutions (user_id, institution_name, institution_type, license_number, contact_person, office_location, min_loan_amount, max_loan_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt2->execute([$user_id, $name, $type, $license, $contact, $location, $min_loan ?: 0, $max_loan ?: 0]);
        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
        exit();
    }
}

if ($action === 'view') {
    $id = $_POST['id'] ?? 0;
    $stmt = $db->prepare('SELECT * FROM financial_institutions WHERE id = ?');
    $stmt->execute([$id]);
    $inst = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($inst) respond(true, 'Institution found.', $inst);
    respond(false, 'Institution not found.');
}

if ($action === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['institution_name'] ?? '');
    $type = trim($_POST['institution_type'] ?? '');
    $license = trim($_POST['license_number'] ?? '');
    $contact = trim($_POST['contact_person'] ?? '');
    $location = trim($_POST['office_location'] ?? '');
    $min_loan = trim($_POST['min_loan'] ?? '');
    $max_loan = trim($_POST['max_loan'] ?? '');
    if (!$id || !$name || !$type) {
        echo json_encode(['success' => false, 'message' => 'ID, name, and type are required.']);
        exit();
    }
    $stmt = $db->prepare('UPDATE financial_institutions SET institution_name=?, institution_type=?, license_number=?, contact_person=?, office_location=?, min_loan_amount=?, max_loan_amount=? WHERE id=?');
    if ($stmt->execute([$name, $type, $license, $contact, $location, $min_loan ?: 0, $max_loan ?: 0, $id])) {
        echo json_encode(['success' => true]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
        exit();
    }
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID required.']);
        exit();
    }
    // Get user_id first
    $stmt = $db->prepare('SELECT user_id FROM financial_institutions WHERE id=?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Institution not found.']);
        exit();
    }
    $user_id = $row['user_id'];
    // Delete from financial_institutions
    $stmt = $db->prepare('DELETE FROM financial_institutions WHERE id=?');
    $stmt->execute([$id]);
    // Delete from users
    $stmt2 = $db->prepare('DELETE FROM users WHERE id=?');
    $stmt2->execute([$user_id]);
    echo json_encode(['success' => true]);
    exit();
}

respond(false, 'Invalid action.'); 