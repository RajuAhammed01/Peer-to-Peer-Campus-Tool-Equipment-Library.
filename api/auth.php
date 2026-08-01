<?php
// api/auth.php - Authentication API endpoint
require_once __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'check';

if ($action === 'check') {
    $user = getCurrentUser();
    sendJsonResponse(['logged_in' => $user !== null, 'user' => $user]);
}

if ($action === 'login') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        sendJsonResponse(['error' => 'Email and password are required.'], 400);
    }

    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']);
        $_SESSION['user'] = $user;
        sendJsonResponse(['message' => 'Login successful', 'user' => $user]);
    } else {
        sendJsonResponse(['error' => 'Invalid email or password.'], 401);
    }
}

if ($action === 'register') {
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $studentId = sanitizeInput($_POST['student_id'] ?? '');
    $department = sanitizeInput($_POST['department'] ?? 'Computer Science & Engineering');
    $role = sanitizeInput($_POST['role'] ?? 'Student');
    $password = $_POST['password'] ?? '';

    if (empty($fullName) || empty($email) || empty($studentId) || empty($password)) {
        sendJsonResponse(['error' => 'All fields (Name, Email, Student/Faculty ID, Password) are required.'], 400);
    }

    $pdo = getDBConnection();
    // Check if email or student_id exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR student_id = ? LIMIT 1");
    $stmt->execute([$email, $studentId]);
    if ($stmt->fetch()) {
        sendJsonResponse(['error' => 'A user with this Email or Student/Faculty ID already exists.'], 400);
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, student_id, password_hash, role, department) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fullName, $email, $studentId, $passwordHash, $role, $department]);

    $userId = $pdo->lastInsertId();
    $user = [
        'id' => $userId,
        'full_name' => $fullName,
        'email' => $email,
        'student_id' => $studentId,
        'role' => $role,
        'department' => $department,
        'reputation_score' => 5.0
    ];
    $_SESSION['user'] = $user;

    sendJsonResponse(['message' => 'Registration successful', 'user' => $user], 201);
}

if ($action === 'logout') {
    unset($_SESSION['user']);
    session_destroy();
    sendJsonResponse(['message' => 'Logged out successfully']);
}

sendJsonResponse(['error' => 'Invalid action parameter.'], 400);
