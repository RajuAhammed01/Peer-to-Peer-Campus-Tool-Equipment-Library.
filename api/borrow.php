<?php
// api/borrow.php - Borrow Request and Peer-to-Peer Transactions API
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

$user = requireLogin();

// Submit new borrow request
if ($action === 'create') {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $startDate = sanitizeInput($_POST['start_date'] ?? '');
    $endDate = sanitizeInput($_POST['end_date'] ?? '');
    $purpose = sanitizeInput($_POST['purpose'] ?? '');

    if ($itemId <= 0 || empty($startDate) || empty($endDate) || empty($purpose)) {
        sendJsonResponse(['error' => 'All fields (Start Date, End Date, Purpose) are required.'], 400);
    }

    // Check item existence and availability
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();

    if (!$item) {
        sendJsonResponse(['error' => 'Equipment item not found.'], 404);
    }

    if ($item['status'] !== 'available') {
        sendJsonResponse(['error' => 'Equipment is currently unavailable or already borrowed.'], 400);
    }

    if ((int)$item['owner_id'] === (int)$user['id']) {
        sendJsonResponse(['error' => 'You cannot borrow your own equipment listing.'], 400);
    }

    // Calculate total fee based on days
    $d1 = new DateTime($startDate);
    $d2 = new DateTime($endDate);
    $days = max(1, $d2->diff($d1)->days);
    $totalCost = (float)$item['daily_fee'] * $days;

    $stmt = $pdo->prepare("
        INSERT INTO borrow_requests (item_id, borrower_id, start_date, end_date, purpose, status, total_cost)
        VALUES (?, ?, ?, ?, ?, 'pending', ?)
    ");
    $stmt->execute([$itemId, $user['id'], $startDate, $endDate, $purpose, $totalCost]);

    sendJsonResponse([
        'message' => 'Borrow request submitted successfully! Awaiting owner approval.',
        'request_id' => $pdo->lastInsertId()
    ], 201);
}

// Get user's borrow requests (as Borrower)
if ($action === 'my_borrows') {
    $stmt = $pdo->prepare("
        SELECT br.*, 
               i.title AS item_title, i.image_icon, i.e_waste_kg, i.location,
               u.full_name AS owner_name, u.email AS owner_email, u.phone AS owner_phone
        FROM borrow_requests br
        JOIN items i ON br.item_id = i.id
        JOIN users u ON i.owner_id = u.id
        WHERE br.borrower_id = ?
        ORDER BY br.id DESC
    ");
    $stmt->execute([$user['id']]);
    sendJsonResponse(['requests' => $stmt->fetchAll()]);
}

// Get incoming requests for owner's items (as Owner)
if ($action === 'incoming_requests') {
    $stmt = $pdo->prepare("
        SELECT br.*, 
               i.title AS item_title, i.image_icon,
               u.full_name AS borrower_name, u.email AS borrower_email, u.student_id AS borrower_student_id, u.department AS borrower_department, u.reputation_score AS borrower_rating
        FROM borrow_requests br
        JOIN items i ON br.item_id = i.id
        JOIN users u ON br.borrower_id = u.id
        WHERE i.owner_id = ?
        ORDER BY br.id DESC
    ");
    $stmt->execute([$user['id']]);
    sendJsonResponse(['requests' => $stmt->fetchAll()]);
}

// Owner approves or rejects a request
if ($action === 'update_status') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $newStatus = sanitizeInput($_POST['status'] ?? '');

    if (!in_array($newStatus, ['approved', 'rejected', 'active', 'returned', 'cancelled'])) {
        sendJsonResponse(['error' => 'Invalid status transition.'], 400);
    }

    // Verify user owns the item associated with this request
    $stmt = $pdo->prepare("
        SELECT br.*, i.owner_id, i.id AS item_id
        FROM borrow_requests br
        JOIN items i ON br.item_id = i.id
        WHERE br.id = ?
    ");
    $stmt->execute([$requestId]);
    $req = $stmt->fetch();

    if (!$req) {
        sendJsonResponse(['error' => 'Request not found.'], 404);
    }

    $isOwner = ((int)$req['owner_id'] === (int)$user['id']);
    $isBorrower = ((int)$req['borrower_id'] === (int)$user['id']);

    if (!$isOwner && !$isBorrower) {
        sendJsonResponse(['error' => 'Unauthorized operation.'], 403);
    }

    // Update request status
    $stmt = $pdo->prepare("UPDATE borrow_requests SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$newStatus, $requestId]);

    // Update item status based on request status
    if ($newStatus === 'approved' || $newStatus === 'active') {
        $pdo->prepare("UPDATE items SET status = 'borrowed' WHERE id = ?")->execute([$req['item_id']]);
    } else if ($newStatus === 'returned' || $newStatus === 'rejected' || $newStatus === 'cancelled') {
        $pdo->prepare("UPDATE items SET status = 'available' WHERE id = ?")->execute([$req['item_id']]);
    }

    sendJsonResponse(['message' => "Borrow request status updated to {$newStatus}."]);
}

sendJsonResponse(['error' => 'Invalid action.'], 400);
