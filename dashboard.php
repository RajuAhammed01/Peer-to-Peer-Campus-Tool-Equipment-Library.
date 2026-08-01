<?php
require_once __DIR__ . '/config/db.php';
$user = requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard | Campus Tool Library</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
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
        <a href="add_item.php" class="nav-link"><i class="fa fa-plus-circle"></i> List Equipment</a>
        <a href="report.php" class="nav-link"><i class="fa fa-file-alt"></i> Lab Report</a>
        <button onclick="handleLogout()" class="btn btn-outline btn-sm"><i class="fa fa-sign-out-alt"></i> Logout</button>
      </nav>
    </div>
  </header>

  <main class="container" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Welcome Header & Profile Card -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px; margin-bottom: 36px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
      <div>
        <h1 style="font-size: 1.8rem; font-weight: 800;">Welcome, <?= htmlspecialchars($user['full_name']) ?>!</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 4px;">
          <?= htmlspecialchars($user['role']) ?> &bull; <?= htmlspecialchars($user['department']) ?> &bull; ID: <code><?= htmlspecialchars($user['student_id']) ?></code>
        </p>
      </div>

      <div style="display: flex; gap: 16px;">
        <div style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); padding: 12px 18px; border-radius: var(--radius-md); text-align: center;">
          <div style="font-size: 0.8rem; color: var(--text-muted);">Trust Rating</div>
          <div style="font-size: 1.3rem; font-weight: 800; color: var(--primary);"><i class="fa fa-star"></i> <?= number_format($user['reputation_score'] ?? 5.0, 1) ?> / 5.0</div>
        </div>

        <a href="add_item.php" class="btn btn-primary" style="height: fit-content; align-self: center;"><i class="fa fa-plus"></i> List New Tool</a>
      </div>
    </div>

    <!-- Incoming Borrow Requests Section (Owner View) -->
    <section class="mb-20" style="margin-bottom: 40px;">
      <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-inbox text-accent"></i> Incoming Borrow Requests (Owner View)
      </h2>
      <div id="incomingRequestsContainer" style="display: flex; flex-direction: column; gap: 16px;">
        <!-- Loaded via JS -->
      </div>
    </section>

    <!-- My Borrowed / Requested Items Section (Borrower View) -->
    <section class="mb-20" style="margin-bottom: 40px;">
      <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-handshake text-primary"></i> My Active & Requested Borrows (Borrower View)
      </h2>
      <div id="myBorrowsContainer" style="display: flex; flex-direction: column; gap: 16px;">
        <!-- Loaded via JS -->
      </div>
    </section>

    <!-- My Listed Equipment Section -->
    <section>
      <h2 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-boxes-stacked text-warning"></i> My Listed Equipment
      </h2>
      <div id="myItemsContainer" class="catalog-grid">
        <!-- Loaded via JS -->
      </div>
    </section>
  </main>

  <script src="assets/js/app.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      loadDashboardData();
    });

    async function loadDashboardData() {
      loadIncomingRequests();
      loadMyBorrows();
      loadMyItems();
    }

    // Load incoming requests for items owned by current user
    async function loadIncomingRequests() {
      const container = document.getElementById('incomingRequestsContainer');
      try {
        const res = await fetch('api/borrow.php?action=incoming_requests');
        const data = await res.json();

        if (data.requests.length === 0) {
          container.innerHTML = `<div style="background:var(--bg-card);padding:20px;border-radius:var(--radius-md);color:var(--text-muted);border:1px solid var(--border-color);">No pending borrow requests for your equipment.</div>`;
          return;
        }

        container.innerHTML = data.requests.map(req => `
          <div style="background:var(--bg-card);border:1px solid var(--border-color);padding:20px;border-radius:var(--radius-md);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
            <div>
              <h4 style="font-size:1.1rem;font-weight:700;">${escapeHtml(req.item_title)}</h4>
              <p style="font-size:0.88rem;color:var(--text-muted);margin-top:4px;">
                Requested by: <strong>${escapeHtml(req.borrower_name)}</strong> (${escapeHtml(req.borrower_department)}) &bull; Rating: ★${parseFloat(req.borrower_rating).toFixed(1)}
              </p>
              <p style="font-size:0.85rem;color:var(--text-dim);margin-top:4px;">
                <strong>Dates:</strong> ${req.start_date} to ${req.end_date} | <strong>Purpose:</strong> "${escapeHtml(req.purpose)}"
              </p>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
              <span class="status-badge ${req.status}">${req.status}</span>
              ${req.status === 'pending' ? `
                <button onclick="updateRequestStatus(${req.id}, 'approved')" class="btn btn-primary btn-sm"><i class="fa fa-check"></i> Approve</button>
                <button onclick="updateRequestStatus(${req.id}, 'rejected')" class="btn btn-outline btn-sm" style="color:var(--danger);"><i class="fa fa-times"></i> Reject</button>
              ` : ''}
            </div>
          </div>
        `).join('');
      } catch (err) {
        container.innerHTML = `<div style="color:var(--danger)">Failed to load incoming requests.</div>`;
      }
    }

    // Load active & past borrows requested by current user
    async function loadMyBorrows() {
      const container = document.getElementById('myBorrowsContainer');
      try {
        const res = await fetch('api/borrow.php?action=my_borrows');
        const data = await res.json();

        if (data.requests.length === 0) {
          container.innerHTML = `<div style="background:var(--bg-card);padding:20px;border-radius:var(--radius-md);color:var(--text-muted);border:1px solid var(--border-color);">You have not requested any equipment yet. Explore the catalog!</div>`;
          return;
        }

        container.innerHTML = data.requests.map(req => `
          <div style="background:var(--bg-card);border:1px solid var(--border-color);padding:20px;border-radius:var(--radius-md);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
            <div>
              <h4 style="font-size:1.1rem;font-weight:700;"><i class="fa ${req.image_icon}"></i> ${escapeHtml(req.item_title)}</h4>
              <p style="font-size:0.88rem;color:var(--text-muted);margin-top:4px;">
                Owner: <strong>${escapeHtml(req.owner_name)}</strong> (${escapeHtml(req.owner_email)}) &bull; Pickup: ${escapeHtml(req.location)}
              </p>
              <p style="font-size:0.85rem;color:var(--text-dim);margin-top:4px;">
                <strong>Due Date:</strong> ${req.end_date} | <strong>E-Waste Prevented:</strong> <span class="text-primary">${req.e_waste_kg} kg</span>
              </p>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
              <span class="status-badge ${req.status}">${req.status}</span>
              ${(req.status === 'approved' || req.status === 'active') ? `
                <button onclick="updateRequestStatus(${req.id}, 'returned')" class="btn btn-primary btn-sm"><i class="fa fa-undo"></i> Return Item</button>
              ` : ''}
            </div>
          </div>
        `).join('');
      } catch (err) {
        container.innerHTML = `<div style="color:var(--danger)">Failed to load your borrow history.</div>`;
      }
    }

    // Load user's own listed tools
    async function loadMyItems() {
      const container = document.getElementById('myItemsContainer');
      try {
        const userId = <?= (int)$user['id'] ?>;
        const res = await fetch(`api/items.php?action=list&owner_id=${userId}`);
        const data = await res.json();

        if (data.items.length === 0) {
          container.innerHTML = `<div style="grid-column:1/-1;background:var(--bg-card);padding:20px;border-radius:var(--radius-md);color:var(--text-muted);border:1px solid var(--border-color);">You haven't listed any equipment yet. <a href="add_item.php" class="text-primary font-bold">List an item now</a></div>`;
          return;
        }

        container.innerHTML = data.items.map(item => createEquipmentCardHtml(item)).join('');
      } catch (err) {
        container.innerHTML = `<div style="color:var(--danger)">Failed to load your listed items.</div>`;
      }
    }

    // Update request status (Approve / Reject / Return)
    async function updateRequestStatus(requestId, status) {
      const formData = new FormData();
      formData.append('action', 'update_status');
      formData.append('request_id', requestId);
      formData.append('status', status);

      try {
        const res = await fetch('api/borrow.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (res.ok) {
          alert('Status updated: ' + data.message);
          loadDashboardData();
        } else {
          alert('Error: ' + data.error);
        }
      } catch (err) {
        alert('Failed to update status.');
      }
    }
  </script>
</body>
</html>
