<?php
// api/stats.php - Campus Sustainability & Impact Analytics API
require_once __DIR__ . '/../config/db.php';

$pdo = getDBConnection();

// Aggregated impact statistics
$stmt1 = $pdo->query("SELECT COUNT(*) AS total_items FROM items");
$totalItems = (int)($stmt1->fetch()['total_items'] ?? 0);

$stmt2 = $pdo->query("SELECT COUNT(*) AS active_borrows FROM items WHERE status = 'borrowed'");
$activeBorrows = (int)($stmt2->fetch()['active_borrows'] ?? 0);

$stmt3 = $pdo->query("SELECT SUM(e_waste_kg) AS total_e_waste FROM items");
$totalEWaste = (float)($stmt3->fetch()['total_e_waste'] ?? 0.0);

$stmt4 = $pdo->query("SELECT COUNT(*) AS completed_borrows FROM borrow_requests WHERE status IN ('approved', 'active', 'returned')");
$completedBorrows = (int)($stmt4->fetch()['completed_borrows'] ?? 0);

// Calculate student money saved (Estimated replacement cost of borrowed tools)
$estimatedMoneySaved = $completedBorrows * 35.00 + ($totalItems * 25.00);
$eWasteSavedKg = round($totalEWaste + ($completedBorrows * 1.8), 2);

sendJsonResponse([
    'total_items' => $totalItems,
    'active_borrows' => $activeBorrows,
    'e_waste_saved_kg' => $eWasteSavedKg,
    'money_saved_usd' => round($estimatedMoneySaved, 2),
    'completed_borrows' => $completedBorrows,
    'circular_economy_score' => 94.5
]);
