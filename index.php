<?php
require_once __DIR__ . '/config/db.php';
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Peer-to-Peer Campus Tool & Equipment Library for CSE 3120 - Reducing Electronic Waste & Promoting Campus Circular Economy">
  <title>Campus Tool & Equipment Library | Circular Economy Portal</title>
  
  <!-- Fonts & Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Header / Navigation Bar -->
  <header class="navbar">
    <div class="container nav-container">
      <a href="index.php" class="logo">
        <i class="fa-solid fa-recycle"></i>
        <span>Campus Tool Library <span class="badge-tag">CSE 3120 Lab</span></span>
      </a>

      <nav id="userNavContainer" class="nav-links">
        <?php if ($currentUser): ?>
          <a href="dashboard.php" class="nav-link"><i class="fa fa-tachometer-alt"></i> <span>Dashboard</span></a>
          <a href="add_item.php" class="nav-link"><i class="fa fa-plus-circle"></i> <span>List Equipment</span></a>
          <button onclick="handleLogout()" class="btn btn-outline btn-sm"><i class="fa fa-sign-out-alt"></i> Logout</button>
        <?php else: ?>
          <a href="login.php" class="btn btn-outline btn-sm"><i class="fa fa-sign-in-alt"></i> Login</a>
          <a href="register.php" class="btn btn-primary btn-sm"><i class="fa fa-user-plus"></i> Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main class="container">
    <!-- Hero Section & Circular Economy Impact Banner -->
    <section class="hero">
      <h1 class="hero-title">Peer-to-Peer Campus Equipment Library</h1>
      <p class="hero-subtitle">
        Share underutilized lab tools, electronics kits, oscilloscopes, and cameras directly with fellow students. 
        Lower consumer consumption, cut hardware costs, and prevent electronic waste on campus.
      </p>

      <!-- Real-Time Metrics Ticker -->
      <div class="metrics-grid">
        <article class="metric-card">
          <div class="metric-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
          <div>
            <div id="statTotalItems" class="metric-val">0</div>
            <div class="metric-label">Items Listed</div>
          </div>
        </article>

        <article class="metric-card">
          <div class="metric-icon"><i class="fa-solid fa-leaf text-primary"></i></div>
          <div>
            <div id="statEWasteSaved" class="metric-val">0 kg</div>
            <div class="metric-label">E-Waste Prevented</div>
          </div>
        </article>

        <article class="metric-card accent">
          <div class="metric-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
          <div>
            <div id="statMoneySaved" class="metric-val">$0</div>
            <div class="metric-label">Student Money Saved</div>
          </div>
        </article>

        <article class="metric-card">
          <div class="metric-icon"><i class="fa-solid fa-arrow-rotate-right text-info"></i></div>
          <div>
            <div id="statActiveBorrows" class="metric-val">0</div>
            <div class="metric-label">Active Campus Borrows</div>
          </div>
        </article>
      </div>
    </section>

    <!-- Search & Category Filters -->
    <section class="search-filter-section">
      <div class="search-box-row">
        <div class="search-input-wrap">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="searchInput" class="search-input" placeholder="Search equipment by title, keywords, or pickup location (e.g. Arduino, Oscilloscope, Lab 402)..." id="search-bar-input">
        </div>
        <a href="add_item.php" class="btn btn-primary" style="white-space: nowrap;"><i class="fa fa-plus-circle"></i> List New Tool</a>
      </div>

      <div id="categoriesScroll" class="categories-scroll">
        <!-- Categories tabs populated dynamically by app.js -->
      </div>
    </section>

    <!-- Equipment Catalog Grid -->
    <section>
      <h2 style="font-size:1.5rem;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:10px;">
        <i class="fa-solid fa-microchip text-primary"></i> Available Campus Equipment
      </h2>
      
      <div id="equipmentGrid" class="catalog-grid">
        <!-- Cards dynamically loaded via app.js -->
      </div>
    </section>
  </main>

  <!-- Borrow Request Modal -->
  <div id="borrowModal" class="modal-backdrop">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title"><i class="fa-solid fa-handshake text-primary"></i> Request to Borrow</h3>
        <button class="close-btn" onclick="closeModal()">&times;</button>
      </div>
      <form id="borrowForm" onsubmit="submitBorrowRequest(event)">
        <input type="hidden" id="modalItemId">
        <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:16px;">
          Item: <strong id="modalItemTitle" style="color:var(--text-main);">Equipment Title</strong>
        </p>

        <div class="form-group">
          <label class="form-label" for="borrowStartDate">Borrow Start Date</label>
          <input type="date" id="borrowStartDate" class="form-control" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="borrowEndDate">Return Due Date</label>
          <input type="date" id="borrowEndDate" class="form-control" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="borrowPurpose">Lab / Academic Purpose Note</label>
          <textarea id="borrowPurpose" class="form-control" placeholder="Describe how you will use this equipment for your course/project..." required></textarea>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:24px;">
          <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Submit Request</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <p>University of Liberal Arts Bangladesh | Department of Computer Science & Engineering</p>
      <p style="margin-top:6px;font-size:0.8rem;">CSE 3120 Web Programming - Open-Ended Experiment | Peer-to-Peer E-Waste Reduction Initiative</p>
    </div>
  </footer>

  <script src="assets/js/app.js"></script>
</body>
</html>
