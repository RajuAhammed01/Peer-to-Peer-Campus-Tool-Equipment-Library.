<?php
require_once __DIR__ . '/config/db.php';
$user = requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>List New Equipment | Campus Tool Library</title>
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
        <a href="dashboard.php" class="nav-link"><i class="fa fa-tachometer-alt"></i> Dashboard</a>
        <button onclick="handleLogout()" class="btn btn-outline btn-sm"><i class="fa fa-sign-out-alt"></i> Logout</button>
      </nav>
    </div>
  </header>

  <main class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 760px;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 36px; box-shadow: var(--shadow-md);">
      
      <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
        <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: rgba(16,185,129,0.15); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
          <i class="fa-solid fa-plus-circle"></i>
        </div>
        <div>
          <h1 style="font-size: 1.5rem; font-weight: 800;">List New Campus Equipment</h1>
          <p style="color: var(--text-muted); font-size: 0.9rem;">Share underutilized tools to cut e-waste and support fellow students.</p>
        </div>
      </div>

      <form id="addItemForm" onsubmit="submitNewItem(event)">
        <div class="form-group">
          <label class="form-label" for="title">Equipment / Tool Title</label>
          <input type="text" id="title" class="form-control" placeholder="e.g. Raspberry Pi 4 Model B (4GB) Starter Kit" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div class="form-group">
            <label class="form-label" for="categoryId">Category</label>
            <select id="categoryId" class="form-control" required>
              <option value="1">Electronics & Microcontrollers</option>
              <option value="2">Test & Measurement</option>
              <option value="3">Robotics & Mechatronics</option>
              <option value="4">Photography & Media</option>
              <option value="5">Lab Equipment & Tools</option>
              <option value="6">Academic Textbooks & Notes</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="itemCondition">Item Condition</label>
            <select id="itemCondition" class="form-control">
              <option value="Brand New">Brand New</option>
              <option value="Like New" selected>Like New</option>
              <option value="Good">Good</option>
              <option value="Fair">Fair</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="description">Detailed Description & Components Included</label>
          <textarea id="description" class="form-control" placeholder="List key specs, accessories included (probes, cables, power adapter), and usage rules..." required></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
          <div class="form-group">
            <label class="form-label" for="dailyFee">Daily Fee ($)</label>
            <input type="number" step="0.5" id="dailyFee" class="form-control" value="0.00" placeholder="0.00 for FREE share">
            <small style="color:var(--text-dim);font-size:0.75rem;">Set to 0 for FREE peer sharing</small>
          </div>

          <div class="form-group">
            <label class="form-label" for="securityDeposit">Refundable Deposit ($)</label>
            <input type="number" step="10" id="securityDeposit" class="form-control" value="200.00" placeholder="Security deposit">
          </div>

          <div class="form-group">
            <label class="form-label" for="eWasteKg">E-Waste Weight (kg)</label>
            <input type="number" step="0.1" id="eWasteKg" class="form-control" value="1.5" placeholder="Weight in kg">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="location">Pickup / Handover Location on Campus</label>
          <input type="text" id="location" class="form-control" placeholder="e.g. CSE Lab 402, Building A / Library Quiet Zone" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="imageIcon">FontAwesome Equipment Icon</label>
          <select id="imageIcon" class="form-control">
            <option value="fa-microchip">Microchip / Processor (Arduino / Pi)</option>
            <option value="fa-wave-square">Wave Oscilloscope / Multimeter</option>
            <option value="fa-robot">Robot / Motor Chassis</option>
            <option value="fa-camera">Camera / Lens</option>
            <option value="fa-fire">Soldering Iron / Heat Gun</option>
            <option value="fa-book">Book / Textbook</option>
            <option value="fa-tools">General Toolset</option>
          </select>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 16px; margin-top: 28px;">
          <a href="index.php" class="btn btn-outline">Cancel</a>
          <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Publish Listing</button>
        </div>
      </form>
    </div>
  </main>

  <script src="assets/js/app.js"></script>
  <script>
    async function submitNewItem(event) {
      event.preventDefault();
      
      const formData = new FormData();
      formData.append('action', 'create');
      formData.append('title', document.getElementById('title').value);
      formData.append('category_id', document.getElementById('categoryId').value);
      formData.append('item_condition', document.getElementById('itemCondition').value);
      formData.append('description', document.getElementById('description').value);
      formData.append('daily_fee', document.getElementById('dailyFee').value);
      formData.append('security_deposit', document.getElementById('securityDeposit').value);
      formData.append('e_waste_kg', document.getElementById('eWasteKg').value);
      formData.append('location', document.getElementById('location').value);
      formData.append('image_icon', document.getElementById('imageIcon').value);

      try {
        const res = await fetch('api/items.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (res.ok) {
          alert('Success: ' + data.message);
          window.location.href = 'dashboard.php';
        } else {
          alert('Error: ' + data.error);
        }
      } catch (err) {
        alert('Failed to list equipment item.');
      }
    }
  </script>
</body>
</html>
