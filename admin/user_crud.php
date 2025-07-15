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
    $fields = ['username','email','password','role'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) respond(false, 'All fields are required.');
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) respond(false, 'Invalid email.');
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$_POST['email']]);
    if ($stmt->fetch()) respond(false, 'Email already exists.');
    $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
    try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['username'],
            $_POST['email'],
            $hash,
            $_POST['role']
        ]);
        respond(true, 'User added successfully.');
    } catch (Exception $e) {
        respond(false, 'Error adding user: ' . $e->getMessage());
    }
}

if ($action === 'view') {
    $id = $_POST['id'] ?? 0;
    $stmt = $db->prepare('SELECT id, username, email, role FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) respond(true, 'User found.', $user);
    respond(false, 'User not found.');
}

if ($action === 'edit') {
    $id = $_POST['id'] ?? 0;
    $fields = ['username','email','role'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) respond(false, 'All fields are required.');
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) respond(false, 'Invalid email.');
    // Check for email uniqueness (exclude self)
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
    $stmt->execute([$_POST['email'], $id]);
    if ($stmt->fetch()) respond(false, 'Email already exists.');
    $params = [$_POST['username'], $_POST['email'], $_POST['role'], $id];
    $sql = "UPDATE users SET username=?, email=?, role=? WHERE id=?";
    // If password is set and not empty, update it
    if (!empty($_POST['password'])) {
        $sql = "UPDATE users SET username=?, email=?, role=?, password=? WHERE id=?";
        $params = [$_POST['username'], $_POST['email'], $_POST['role'], password_hash($_POST['password'], PASSWORD_BCRYPT), $id];
    }
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        respond(true, 'User updated successfully.');
    } catch (Exception $e) {
        respond(false, 'Error updating user: ' . $e->getMessage());
    }
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? 0;
    // Prevent deleting self or last admin
    if ($id == $_SESSION['user_id']) respond(false, 'You cannot delete your own account.');
    $stmt = $db->prepare('SELECT role FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && $user['role'] === 'admin') {
        $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE role = "admin"');
        $stmt->execute();
        if ($stmt->fetchColumn() <= 1) respond(false, 'At least one admin must remain.');
    }
    try {
        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        respond(true, 'User deleted successfully.');
    } catch (Exception $e) {
        respond(false, 'Error deleting user: ' . $e->getMessage());
    }
}

respond(false, 'Invalid action.'); 