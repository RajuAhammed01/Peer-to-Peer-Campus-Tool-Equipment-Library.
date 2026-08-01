<?php
// config/setup.php - CLI / Browser setup script for initializing database

require_once __DIR__ . '/db.php';

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Database Setup - Campus Tool Library</title><style>body{font-family:sans-serif;background:#0f172a;color:#f8fafc;padding:40px;max-width:800px;margin:auto;line-height:1.6;} .card{background:#1e293b;padding:24px;border-radius:12px;box-shadow:0 10px 25px rgba(0,0,0,0.5);} h1{color:#10b981;} .badge{background:#10b981;color:#0f172a;padding:4px 10px;border-radius:6px;font-weight:bold;} code{background:#334155;padding:2px 6px;border-radius:4px;color:#38bdf8;}</style></head><body>";
echo "<div class='card'>";
echo "<h1><i class='fa fa-database'></i> Database Setup & Migration</h1>";

try {
    $pdo = getDBConnection();
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "<p>Connected to Database via driver: <strong>" . strtoupper($driver) . "</strong></p>";

    if ($driver === 'mysql') {
        $sql = file_get_contents(__DIR__ . '/../schema.sql');
        $pdo->exec($sql);
        echo "<p><span class='badge'>SUCCESS</span> MySQL Database <code>campus_tool_library</code> initialized and populated with schema and seed data!</p>";
    } else {
        // SQLite already auto-initialized on first connection if missing
        echo "<p><span class='badge'>SUCCESS</span> SQLite Database <code>campus_tool_library.sqlite</code> ready with schema and seed data!</p>";
    }

    echo "<hr style='border-color:#334155;'>";
    echo "<h3>Test Accounts Created:</h3>";
    echo "<ul>";
    echo "<li><strong>Student 1:</strong> raju@ulab.edu.bd | Password: <code>password123</code> (Student ID: 213014001)</li>";
    echo "<li><strong>Student 2:</strong> sadia.cse@ulab.edu.bd | Password: <code>password123</code> (Student ID: 213014050)</li>";
    echo "<li><strong>Faculty:</strong> tanvir.hossain@ulab.edu.bd | Password: <code>password123</code> (ID: FAC-5092)</li>";
    echo "</ul>";

    echo "<p><a href='../index.php' style='display:inline-block;padding:10px 20px;background:#10b981;color:#0f172a;text-decoration:none;font-weight:bold;border-radius:6px;'>Go to Application &rarr;</a></p>";

} catch (Exception $e) {
    echo "<p style='color:#ef4444;'><strong>Setup Failed:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div></body></html>";
