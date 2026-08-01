<?php
// config/db.php - Database connection & global helper utilities

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration settings
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'peerShare');
define('DB_USER', 'root');
define('DB_PASS', '');

// SQLite Fallback database file in data/ directory if MySQL is unreachable
define('SQLITE_FILE', __DIR__ . '/../data/peerShare.sqlite');

/**
 * Get PDO Database Connection
 * Attempts MySQL connection first; falls back gracefully to SQLite if MySQL is not active.
 */
function getDBConnection(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // Try MySQL DSN
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // MySQL connection failed; fallback to SQLite for zero-config local demo compatibility
        try {
            $dataDir = __DIR__ . '/../data';
            if (!file_exists($dataDir)) {
                mkdir($dataDir, 0777, true);
            }
            $dsn = "sqlite:" . SQLITE_FILE;
            $pdo = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // Enable foreign keys for SQLite
            $pdo->exec("PRAGMA foreign_keys = ON;");
            
            // Check if tables exist in SQLite, if not initialize
            $tablesCheck = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch();
            if (!$tablesCheck) {
                initializeSqliteSchema($pdo);
            }
            return $pdo;
        } catch (PDOException $ex) {
            die("Database Connection Error: " . $ex->getMessage());
        }
    }
}

/**
 * Helper to initialize SQLite database tables if using SQLite fallback
 */
function initializeSqliteSchema(PDO $pdo): void {
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        student_id TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT DEFAULT 'Student',
        department TEXT NOT NULL,
        phone TEXT DEFAULT NULL,
        reputation_score REAL DEFAULT 5.0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        icon TEXT NOT NULL,
        description TEXT DEFAULT NULL
    );

    CREATE TABLE IF NOT EXISTS items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        owner_id INTEGER NOT NULL,
        category_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        description TEXT NOT NULL,
        item_condition TEXT DEFAULT 'Good',
        daily_fee REAL DEFAULT 0.00,
        security_deposit REAL DEFAULT 0.00,
        status TEXT DEFAULT 'available',
        e_waste_kg REAL DEFAULT 1.50,
        location TEXT NOT NULL,
        image_icon TEXT DEFAULT 'fa-microchip',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS borrow_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        item_id INTEGER NOT NULL,
        borrower_id INTEGER NOT NULL,
        start_date TEXT NOT NULL,
        end_date TEXT NOT NULL,
        purpose TEXT NOT NULL,
        status TEXT DEFAULT 'pending',
        total_cost REAL DEFAULT 0.00,
        requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
        FOREIGN KEY (borrower_id) REFERENCES users(id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS reviews (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        borrow_request_id INTEGER NOT NULL,
        reviewer_id INTEGER NOT NULL,
        rating INTEGER CHECK (rating BETWEEN 1 AND 5),
        comment TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (borrow_request_id) REFERENCES borrow_requests(id) ON DELETE CASCADE,
        FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
    );

    INSERT INTO categories (id, name, icon, description) VALUES
    (1, 'Electronics & Microcontrollers', 'fa-microchip', 'Arduino, Raspberry Pi, ESP32, sensors, development boards'),
    (2, 'Test & Measurement', 'fa-wave-square', 'Oscilloscopes, digital multimeters, signal generators, power supplies'),
    (3, 'Robotics & Mechatronics', 'fa-robot', 'Servo motors, motor drivers, chassis, robotic arms, IMUs'),
    (4, 'Photography & Media', 'fa-camera', 'DSLR cameras, lenses, tripods, lighting, audio mics'),
    (5, 'Lab Equipment & Tools', 'fa-tools', 'Soldering stations, 3D pen, wire strippers, heat guns, toolkits'),
    (6, 'Academic Textbooks & Notes', 'fa-book', 'Engineering reference books, lab manuals, course textbooks');

    INSERT INTO users (id, full_name, email, student_id, password_hash, role, department, reputation_score) VALUES
    (1, 'Raju Ahmed', 'raju@ulab.edu.bd', '213014001', '" . password_hash('password123', PASSWORD_BCRYPT) . "', 'Student', 'Computer Science & Engineering', 4.90),
    (2, 'Sadia Rahman', 'sadia.cse@ulab.edu.bd', '213014050', '" . password_hash('password123', PASSWORD_BCRYPT) . "', 'Student', 'Computer Science & Engineering', 4.95),
    (3, 'Dr. Tanvir Hossain', 'tanvir.hossain@ulab.edu.bd', 'FAC-5092', '" . password_hash('password123', PASSWORD_BCRYPT) . "', 'Faculty', 'Computer Science & Engineering', 5.00),
    (4, 'Nabila Islam', 'nabila.eee@ulab.edu.bd', '221015012', '" . password_hash('password123', PASSWORD_BCRYPT) . "', 'Student', 'Electrical & Electronic Engineering', 4.85);

    INSERT INTO items (id, owner_id, category_id, title, description, item_condition, daily_fee, security_deposit, status, e_waste_kg, location, image_icon) VALUES
    (1, 1, 1, 'Arduino Mega 2560 Sensor Kit', 'Complete sensor kit with 37 sensors, jumper wires, OLED display, and breadboards. Perfect for CSE3120 IoT projects.', 'Like New', 0.00, 500.00, 'available', 1.20, 'Campus Building A, Lab 402', 'fa-microchip'),
    (2, 2, 2, 'Rigol DS1054Z 50MHz Digital Oscilloscope', '4-channel digital storage oscilloscope with probes. Ideal for signal analysis in EEE / embedded labs.', 'Good', 50.00, 2000.00, 'available', 3.50, 'Library Quiet Zone / Study Room B', 'fa-wave-square'),
    (3, 3, 4, 'Canon EOS 80D DSLR with 18-135mm Lens', 'Great for documentary projects, campus events, and computer vision lab datasets.', 'Like New', 100.00, 3000.00, 'available', 1.80, 'CSE Department Faculty Office', 'fa-camera'),
    (4, 1, 5, 'Hakko Digital Soldering Station & Heat Gun', 'Temperature-controlled soldering iron station with SMD rework heat gun and solder wire set.', 'Good', 0.00, 800.00, 'available', 2.10, 'MakerSpace Workshop 101', 'fa-fire'),
    (5, 4, 3, '4WD Robotic Chassis + L298N Driver + Servo Set', 'Pre-assembled 4-wheel drive robot chassis with motor driver module and HC-SR04 ultrasonic sensors.', 'Good', 0.00, 400.00, 'borrowed', 0.90, 'EEE Lab 3', 'fa-robot'),
    (6, 2, 6, 'Operating System Concepts (Silberschatz 10th Ed)', 'Hardcover textbook for Operating Systems course in pristine condition with quick reference bookmarks.', 'Brand New', 0.00, 200.00, 'available', 1.40, 'Student Lounge Building B', 'fa-book');

    INSERT INTO borrow_requests (id, item_id, borrower_id, start_date, end_date, purpose, status, total_cost) VALUES
    (1, 5, 1, '2026-08-01', '2026-08-05', 'Testing autonomous obstacle avoidance robot for embedded systems final lab demo.', 'approved', 0.00),
    (2, 2, 4, '2026-08-02', '2026-08-04', 'Measuring PWM pulse signal waveforms for Power Electronics assignment.', 'pending', 100.00);
    ";

    $pdo->exec($sql);
}

/**
 * Return JSON Response
 */
function sendJsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Check if user is logged in
 */
function getCurrentUser(): ?array {
    if (isset($_SESSION['user'])) {
        return $_SESSION['user'];
    }
    return null;
}

/**
 * Require login for protected API routes or pages
 */
function requireLogin(): array {
    $user = getCurrentUser();
    if (!$user) {
        if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            sendJsonResponse(['error' => 'Unauthorized access. Please login first.'], 401);
        } else {
            header('Location: login.php');
            exit;
        }
    }
    return $user;
}

/**
 * Helper to sanitize user input
 */
function sanitizeInput(?string $input): string {
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}
