<?php
// api/items.php - Equipment & Tool Catalog API Endpoint
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// List equipment with search and filter
if ($action === 'list') {
    $search = sanitizeInput($_GET['search'] ?? '');
    $categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
    $status = sanitizeInput($_GET['status'] ?? '');
    $ownerId = isset($_GET['owner_id']) ? (int)$_GET['owner_id'] : null;

    $query = "
        SELECT i.*, 
               c.name AS category_name, 
               c.icon AS category_icon, 
               u.full_name AS owner_name, 
               u.department AS owner_department,
               u.reputation_score AS owner_rating
        FROM items i
        JOIN categories c ON i.category_id = c.id
        JOIN users u ON i.owner_id = u.id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($search)) {
        $query .= " AND (i.title LIKE ? OR i.description LIKE ? OR i.location LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($categoryId !== null && $categoryId > 0) {
        $query .= " AND i.category_id = ?";
        $params[] = $categoryId;
    }

    if (!empty($status)) {
        $query .= " AND i.status = ?";
        $params[] = $status;
    }

    if ($ownerId !== null) {
        $query .= " AND i.owner_id = ?";
        $params[] = $ownerId;
    }

    $query .= " ORDER BY i.id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    sendJsonResponse(['items' => $items, 'count' => count($items)]);
}

// Get single item detail
if ($action === 'detail') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("
        SELECT i.*, c.name AS category_name, c.icon AS category_icon, u.full_name AS owner_name, u.email AS owner_email, u.department AS owner_department, u.reputation_score AS owner_rating
        FROM items i
        JOIN categories c ON i.category_id = c.id
        JOIN users u ON i.owner_id = u.id
        WHERE i.id = ?
    ");
    $stmt->execute([$id]);
    $item = $stmt->fetch();

    if (!$item) {
        sendJsonResponse(['error' => 'Equipment item not found.'], 404);
    }

    sendJsonResponse(['item' => $item]);
}

// Add new equipment item
if ($action === 'create') {
    $user = requireLogin();

    $title = sanitizeInput($_POST['title'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 1);
    $description = sanitizeInput($_POST['description'] ?? '');
    $condition = sanitizeInput($_POST['item_condition'] ?? 'Good');
    $dailyFee = (float)($_POST['daily_fee'] ?? 0.0);
    $deposit = (float)($_POST['security_deposit'] ?? 0.0);
    $eWasteKg = (float)($_POST['e_waste_kg'] ?? 1.5);
    $location = sanitizeInput($_POST['location'] ?? '');
    $icon = sanitizeInput($_POST['image_icon'] ?? 'fa-microchip');

    if (empty($title) || empty($description) || empty($location)) {
        sendJsonResponse(['error' => 'Title, description, and pickup location are required.'], 400);
    }

    $stmt = $pdo->prepare("
        INSERT INTO items (owner_id, category_id, title, description, item_condition, daily_fee, security_deposit, status, e_waste_kg, location, image_icon)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'available', ?, ?, ?)
    ");
    $stmt->execute([$user['id'], $categoryId, $title, $description, $condition, $dailyFee, $deposit, $eWasteKg, $location, $icon]);

    sendJsonResponse(['message' => 'Equipment listed successfully!', 'item_id' => $pdo->lastInsertId()], 201);
}

// Toggle availability / delete
if ($action === 'toggle_status') {
    $user = requireLogin();
    $itemId = (int)($_POST['item_id'] ?? 0);
    $newStatus = sanitizeInput($_POST['status'] ?? 'available');

    $stmt = $pdo->prepare("SELECT owner_id FROM items WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();

    if (!$item || (int)$item['owner_id'] !== (int)$user['id']) {
        sendJsonResponse(['error' => 'You do not have permission to modify this item.'], 403);
    }

    $stmt = $pdo->prepare("UPDATE items SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $itemId]);

    sendJsonResponse(['message' => 'Equipment status updated successfully.']);
}

// Get categories
if ($action === 'categories') {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    sendJsonResponse(['categories' => $stmt->fetchAll()]);
}

sendJsonResponse(['error' => 'Invalid action.'], 400);
