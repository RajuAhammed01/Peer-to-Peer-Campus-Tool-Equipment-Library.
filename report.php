<?php
require_once __DIR__ . '/config/db.php';
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lab Report - CSE 3120 Open-Ended Experiment</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .report-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-lg);
      padding: 32px;
      margin-bottom: 28px;
    }
    .report-card h2 {
      color: var(--primary);
      font-size: 1.4rem;
      font-weight: 700;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .report-table {
      width: 100%;
      border-collapse: collapse;
      margin: 16px 0;
      font-size: 0.9rem;
    }
    .report-table th, .report-table td {
      border: 1px solid var(--border-color);
      padding: 10px 14px;
      text-align: left;
    }
    .report-table th {
      background: rgba(255,255,255,0.05);
      color: var(--text-main);
    }
    code.code-block {
      display: block;
      background: #0f172a;
      padding: 16px;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border-color);
      color: #38bdf8;
      font-family: monospace;
      font-size: 0.85rem;
      white-space: pre-wrap;
    }
  </style>
</head>
<body>

  <header class="navbar">
    <div class="container nav-container">
      <a href="index.php" class="logo">
        <i class="fa-solid fa-recycle"></i>
        <span>Campus Tool Library</span>
      </a>

      <nav class="nav-links">
        <a href="index.php" class="nav-link"><i class="fa fa-search"></i> Catalog</a>
        <a href="dashboard.php" class="nav-link"><i class="fa fa-tachometer-alt"></i> Dashboard</a>
        <a href="report.php" class="nav-link active"><i class="fa fa-file-alt"></i> Lab Report</a>
      </nav>
    </div>
  </header>

  <main class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 960px;">
    
    <!-- Report Header Box -->
    <div class="report-card" style="border-left: 4px solid var(--primary);">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
        <div>
          <h1 style="font-size: 1.8rem; font-weight: 800;">Academic Open-Ended Experiment Report</h1>
          <p style="color: var(--text-muted); margin-top: 4px;">
            <strong>Course:</strong> Web Programming (CSE 3120) &bull; <strong>Semester:</strong> Summer 2026
          </p>
          <p style="color: var(--text-muted);">
            Department of Computer Science & Engineering | University of Liberal Arts Bangladesh (ULAB)
          </p>
        </div>
        <span class="badge-tag" style="font-size:0.9rem;padding:6px 14px;">Total Marks: 40</span>
      </div>

      <table class="report-table" style="margin-top: 20px;">
        <thead>
          <tr>
            <th>Course Outcome (CO)</th>
            <th>Description</th>
            <th>Domain / Level</th>
            <th>Marks</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>CO1</strong></td>
            <td>Explain latest practices and performance implications of web technology</td>
            <td>Cognitive/L2</td>
            <td>10 Marks</td>
          </tr>
          <tr>
            <td><strong>CO2</strong></td>
            <td>Develop web application based on real-world problem</td>
            <td>Cognitive/L3</td>
            <td>20 Marks (Tasks 2 & 3)</td>
          </tr>
          <tr>
            <td><strong>CO3</strong></td>
            <td>Address ethical concerns in the projects using ethical framework</td>
            <td>Cognitive/L4</td>
            <td>10 Marks</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Task 1: System Functionalities and Architecture -->
    <section class="report-card">
      <h2><i class="fa-solid fa-list-check"></i> Task 1: Problem Definition & System Functionalities (CO1 - 10 Marks)</h2>
      <p>
        <strong>Problem Statement:</strong> Modern consumer habits drive massive electronic waste (e-waste) and manufacturing carbon footprints because students and faculty buy expensive single-use tools, electronics development kits (Arduinos, oscilloscopes), and textbooks that lie underutilized after a single semester.
      </p>
      <p style="margin-top: 10px;">
        <strong>Solution:</strong> The Peer-to-Peer Campus Tool & Equipment Library creates a localized circular economy. Key functionalities incorporated:
      </p>
      <ul style="margin-left: 20px; margin-top: 8px; color: var(--text-muted); line-height: 1.8;">
        <li><strong>Role-Based User Management:</strong> Student and Faculty authentication with institutional email validation and reputation scoring.</li>
        <li><strong>Dynamic Equipment Catalog & Search:</strong> Real-time filtering by category (Robotics, Test Equipment, Microcontrollers, Textbooks) and location-aware keyword search.</li>
        <li><strong>Borrow Lifecycle Workflow:</strong> Automated transaction state transitions (Pending &rarr; Approved &rarr; Active &rarr; Returned).</li>
        <li><strong>E-Waste Impact Analytics:</strong> Dynamic real-time calculation of electronic waste saved (in kilograms) and total student financial savings ($).</li>
      </ul>
    </section>

    <!-- Task 2: Database Design -->
    <section class="report-card">
      <h2><i class="fa-solid fa-database"></i> Task 2: Database Schema & ERD Design (CO2 - 10 Marks)</h2>
      <p>
        The application database is designed using <strong>MySQL / Relational PDO Architecture</strong> adhering to Third Normal Form (3NF) to eliminate reduntant data.
      </p>
      
      <h3 style="font-size: 1.05rem; margin-top: 16px; margin-bottom: 8px;">Relational Tables:</h3>
      <ul style="margin-left: 20px; color: var(--text-muted); line-height: 1.8;">
        <li><code>users</code> (id, full_name, email, student_id, password_hash, role, department, reputation_score)</li>
        <li><code>categories</code> (id, name, icon, description)</li>
        <li><code>items</code> (id, owner_id, category_id, title, description, item_condition, daily_fee, security_deposit, status, e_waste_kg, location)</li>
        <li><code>borrow_requests</code> (id, item_id, borrower_id, start_date, end_date, purpose, status, total_cost)</li>
        <li><code>reviews</code> (id, borrow_request_id, reviewer_id, rating, comment)</li>
      </ul>

      <h3 style="font-size: 1.05rem; margin-top: 16px; margin-bottom: 8px;">Database DDL Snippet:</h3>
      <code class="code-block">CREATE TABLE items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_id INT NOT NULL,
  category_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  status ENUM('available', 'borrowed', 'maintenance') DEFAULT 'available',
  e_waste_kg DECIMAL(6,2) DEFAULT 1.50,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);</code>
    </section>

    <!-- Task 3: Codebase Implementation -->
    <section class="report-card">
      <h2><i class="fa-solid fa-code"></i> Task 3: Codebase Architecture & Technology Stack (CO2 - 10 Marks)</h2>
      <p>
        The codebase utilizes modern web technologies following MVC separation of concerns:
      </p>
      <ul style="margin-left: 20px; margin-top: 10px; color: var(--text-muted); line-height: 1.8;">
        <li><strong>Backend Engine:</strong> PHP 8.4 with PDO parameter binding for MySQL database queries, protected against SQL Injection and XSS attacks.</li>
        <li><strong>Frontend Logic:</strong> Asynchronous JavaScript (AJAX Fetch API) for real-time catalog search without full page reloads.</li>
        <li><strong>Styling & Aesthetics:</strong> Vanilla CSS3 custom properties with glassmorphic containers, sleek dark theme, and micro-animations.</li>
      </ul>
    </section>

    <!-- Task 4: Ethical Considerations Framework & Citations -->
    <section class="report-card">
      <h2><i class="fa-solid fa-shield-halved"></i> Task 4: Ethical Framework, E-Waste Reduction & Citations (CO3 - 10 Marks)</h2>
      
      <h3 style="font-size: 1.1rem; color: var(--text-main); margin-bottom: 8px;">1. Applied Ethical Framework (IEEE & ACM Code of Ethics)</h3>
      <p style="color: var(--text-muted); line-height: 1.7;">
        The system implements the <strong>IEEE Code of Ethics Clause 1.2</strong> (avoiding harm to the environment and society) and <strong>ACM Ethics Principle 1.2</strong> (contributing to human well-being).
        In campus peer-to-peer sharing, data privacy is ethically protected by encrypting user passwords with BCRYPT hashing and displaying only necessary contact information once a borrow request is mutually approved.
      </p>

      <h3 style="font-size: 1.1rem; color: var(--text-main); margin-top: 20px; margin-bottom: 8px;">2. Circular Economy & E-Waste Impact Analysis</h3>
      <p style="color: var(--text-muted); line-height: 1.7;">
        According to the Global E-waste Monitor (UNU), global electronic waste reaches over 62 million metric tons annually. By shifting university culture from individual tool ownership to shared access, a single oscilloscope or Arduino kit can serve 15–20 students over its lifecycle, preventing up to 95% of unnecessary manufacturing e-waste and saving students hundreds of dollars.
      </p>

      <h3 style="font-size: 1.1rem; color: var(--text-main); margin-top: 20px; margin-bottom: 8px;">Academic Citations:</h3>
      <ol style="margin-left: 20px; color: var(--text-muted); line-height: 1.8; font-size: 0.9rem;">
        <li>IEEE Computer Society. (2020). <em>IEEE Code of Ethics</em>. IEEE Standard Association.</li>
        <li>United Nations University (UNU). (2024). <em>The Global E-waste Monitor 2024: Quantifying Global E-waste and Circular Economy Potential</em>. International Telecommunication Union (ITU).</li>
        <li>ACM Code of Ethics and Professional Conduct. (2018). Association for Computing Machinery.</li>
      </ol>
    </section>

  </main>

  <footer class="footer">
    <div class="container">
      <p>CSE 3120 Web Programming Open-Ended Experiment Report</p>
    </div>
  </footer>
</body>
</html>
