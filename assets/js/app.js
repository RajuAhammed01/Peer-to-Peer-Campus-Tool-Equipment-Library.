/**
 * Campus Tool & Equipment Library - Main JavaScript Application
 */

document.addEventListener('DOMContentLoaded', () => {
  initApp();
});

let currentUser = null;
let activeCategoryId = 0;
let currentSearchTerm = '';

async function initApp() {
  await checkUserSession();
  loadSustainabilityStats();
  loadCategories();
  loadEquipmentCatalog();

  setupEventListeners();
}

// Check logged in user session
async function checkUserSession() {
  try {
    const res = await fetch('api/auth.php?action=check');
    const data = await res.json();
    if (data.logged_in) {
      currentUser = data.user;
      renderUserNav(true, data.user);
    } else {
      currentUser = null;
      renderUserNav(false);
    }
  } catch (err) {
    console.error('Auth check error:', err);
  }
}

// Render Header Navigation based on auth state
function renderUserNav(isLoggedIn, user = null) {
  const navContainer = document.getElementById('userNavContainer');
  if (!navContainer) return;

  if (isLoggedIn && user) {
    navContainer.innerHTML = `
      <a href="dashboard.php" class="nav-link"><i class="fa fa-tachometer-alt"></i> <span>Dashboard</span></a>
      <a href="add_item.php" class="nav-link"><i class="fa fa-plus-circle"></i> <span>List Equipment</span></a>
      <a href="report.php" class="nav-link"><i class="fa fa-file-alt"></i> <span>Lab Report</span></a>
      <div style="display:flex;align-items:center;gap:10px;margin-left:10px;padding-left:10px;border-left:1px solid rgba(255,255,255,0.1);">
        <span style="font-size:0.85rem;color:#10b981;font-weight:600;"><i class="fa fa-user-circle"></i> ${escapeHtml(user.full_name.split(' ')[0])}</span>
        <button onclick="handleLogout()" class="btn btn-outline btn-sm"><i class="fa fa-sign-out-alt"></i> Logout</button>
      </div>
    `;
  } else {
    navContainer.innerHTML = `
      <a href="report.php" class="nav-link"><i class="fa fa-file-alt"></i> <span>Lab Report</span></a>
      <a href="login.php" class="btn btn-outline btn-sm"><i class="fa fa-sign-in-alt"></i> Login</a>
      <a href="register.php" class="btn btn-primary btn-sm"><i class="fa fa-user-plus"></i> Register</a>
    `;
  }
}

// Handle Logout
async function handleLogout() {
  await fetch('api/auth.php?action=logout');
  window.location.href = 'index.php';
}

// Load real-time sustainability stats
async function loadSustainabilityStats() {
  try {
    const res = await fetch('api/stats.php');
    const stats = await res.json();

    const totalItemsEl = document.getElementById('statTotalItems');
    const eWasteEl = document.getElementById('statEWasteSaved');
    const moneyEl = document.getElementById('statMoneySaved');
    const activeEl = document.getElementById('statActiveBorrows');

    if (totalItemsEl) totalItemsEl.innerText = stats.total_items;
    if (eWasteEl) eWasteEl.innerText = `${stats.e_waste_saved_kg} kg`;
    if (moneyEl) moneyEl.innerText = `$${stats.money_saved_usd}`;
    if (activeEl) activeEl.innerText = stats.active_borrows;
  } catch (err) {
    console.error('Stats loading error:', err);
  }
}

// Load categories dynamically into filter tabs
async function loadCategories() {
  try {
    const res = await fetch('api/items.php?action=categories');
    const data = await res.json();
    const scrollContainer = document.getElementById('categoriesScroll');

    if (!scrollContainer) return;

    let html = `<button class="cat-tab ${activeCategoryId === 0 ? 'active' : ''}" onclick="filterByCategory(0)">
      <i class="fa fa-th-large"></i> All Equipment
    </button>`;

    data.categories.forEach(cat => {
      html += `
        <button class="cat-tab ${activeCategoryId === cat.id ? 'active' : ''}" onclick="filterByCategory(${cat.id})">
          <i class="fa ${cat.icon}"></i> ${escapeHtml(cat.name)}
        </button>
      `;
    });

    scrollContainer.innerHTML = html;
  } catch (err) {
    console.error('Categories error:', err);
  }
}

// Filter catalog by category
function filterByCategory(catId) {
  activeCategoryId = catId;
  loadCategories(); // update active tab UI
  loadEquipmentCatalog();
}

// Load equipment items grid
async function loadEquipmentCatalog() {
  const grid = document.getElementById('equipmentGrid');
  if (!grid) return;

  grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted);"><i class="fa fa-spinner fa-spin fa-2x"></i><p style="margin-top:10px;">Loading campus tools...</p></div>`;

  try {
    let url = `api/items.php?action=list&search=${encodeURIComponent(currentSearchTerm)}`;
    if (activeCategoryId > 0) {
      url += `&category_id=${activeCategoryId}`;
    }

    const res = await fetch(url);
    const data = await res.json();

    if (data.items.length === 0) {
      grid.innerHTML = `
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px; background: var(--bg-card); border-radius: var(--radius-lg); border: 1px dashed var(--border-color);">
          <i class="fa fa-box-open fa-3x" style="color: var(--text-dim); margin-bottom: 16px;"></i>
          <h3 style="color: var(--text-main); font-weight: 700;">No Equipment Found</h3>
          <p style="color: var(--text-muted); max-width: 400px; margin: 8px auto 20px auto;">No tools currently match your search filters. Be the first student to list your underutilized tools!</p>
          <a href="add_item.php" class="btn btn-primary"><i class="fa fa-plus"></i> List Your Tool Now</a>
        </div>
      `;
      return;
    }

    grid.innerHTML = data.items.map(item => createEquipmentCardHtml(item)).join('');
  } catch (err) {
    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;color:var(--danger);padding:40px;">Failed to load catalog. Please check database connection.</div>`;
  }
}

// Render individual item card HTML
function createEquipmentCardHtml(item) {
  const isAvailable = item.status === 'available';
  const feeDisplay = parseFloat(item.daily_fee) === 0 ? '<span class="text-primary font-bold">FREE Share</span>' : `$${parseFloat(item.daily_fee).toFixed(2)}<small>/day</small>`;

  return `
    <div class="equipment-card">
      <div>
        <div class="card-header">
          <div class="card-icon-box">
            <i class="fa ${item.image_icon || item.category_icon || 'fa-microchip'}"></i>
          </div>
          <span class="status-badge ${item.status}">${item.status}</span>
        </div>
        <h3 class="card-title">${escapeHtml(item.title)}</h3>
        <p class="card-desc">${escapeHtml(item.description)}</p>
        
        <div class="card-meta-list">
          <div class="meta-item">
            <span><i class="fa fa-tag"></i> Category:</span>
            <strong>${escapeHtml(item.category_name)}</strong>
          </div>
          <div class="meta-item">
            <span><i class="fa fa-user"></i> Owner:</span>
            <span>${escapeHtml(item.owner_name)} (${escapeHtml(item.owner_department)})</span>
          </div>
          <div class="meta-item">
            <span><i class="fa fa-leaf text-primary"></i> E-Waste Saved:</span>
            <strong class="text-primary">${parseFloat(item.e_waste_kg).toFixed(1)} kg</strong>
          </div>
          <div class="meta-item">
            <span><i class="fa fa-map-marker-alt"></i> Location:</span>
            <span>${escapeHtml(item.location)}</span>
          </div>
        </div>
      </div>

      <div class="card-footer">
        <div class="price-tag">${feeDisplay}</div>
        <button onclick="openBorrowModal(${item.id}, '${escapeHtml(item.title.replace(/'/g, "\\'"))}', ${item.daily_fee})" 
                class="btn ${isAvailable ? 'btn-primary' : 'btn-outline'}" 
                ${!isAvailable ? 'disabled' : ''}>
          <i class="fa ${isAvailable ? 'fa-handshake' : 'fa-lock'}"></i> ${isAvailable ? 'Borrow Tool' : 'Borrowed'}
        </button>
      </div>
    </div>
  `;
}

// Setup Event Listeners for Search
function setupEventListeners() {
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    let debounceTimeout;
    searchInput.addEventListener('keyup', (e) => {
      clearTimeout(debounceTimeout);
      debounceTimeout = setTimeout(() => {
        currentSearchTerm = e.target.value;
        loadEquipmentCatalog();
      }, 300);
    });
  }
}

// Open Borrow Modal
function openBorrowModal(itemId, title, dailyFee) {
  if (!currentUser) {
    alert('Please log in with your Student / Faculty account to borrow campus tools.');
    window.location.href = 'login.php';
    return;
  }

  const modal = document.getElementById('borrowModal');
  const modalItemTitle = document.getElementById('modalItemTitle');
  const modalItemId = document.getElementById('modalItemId');
  
  if (modalItemTitle) modalItemTitle.innerText = title;
  if (modalItemId) modalItemId.value = itemId;

  // Set default dates (Today to +3 days)
  const today = new Date().toISOString().split('T')[0];
  const next3Days = new Date(Date.now() + 3 * 86400000).toISOString().split('T')[0];

  const startDateInput = document.getElementById('borrowStartDate');
  const endDateInput = document.getElementById('borrowEndDate');
  if (startDateInput) startDateInput.value = today;
  if (endDateInput) endDateInput.value = next3Days;

  if (modal) modal.classList.add('active');
}

// Close Modal
function closeModal() {
  const modal = document.getElementById('borrowModal');
  if (modal) modal.classList.remove('active');
}

// Submit Borrow Request via AJAX
async function submitBorrowRequest(event) {
  event.preventDefault();
  const itemId = document.getElementById('modalItemId').value;
  const startDate = document.getElementById('borrowStartDate').value;
  const endDate = document.getElementById('borrowEndDate').value;
  const purpose = document.getElementById('borrowPurpose').value;

  const formData = new FormData();
  formData.append('action', 'create');
  formData.append('item_id', itemId);
  formData.append('start_date', startDate);
  formData.append('end_date', endDate);
  formData.append('purpose', purpose);

  try {
    const res = await fetch('api/borrow.php', {
      method: 'POST',
      body: formData
    });
    const data = await res.json();

    if (res.ok) {
      alert('Success: ' + data.message);
      closeModal();
      loadEquipmentCatalog();
      loadSustainabilityStats();
    } else {
      alert('Error: ' + (data.error || 'Failed to submit borrow request'));
    }
  } catch (err) {
    alert('Network error submitting request.');
  }
}

// Sanitize HTML string helper
function escapeHtml(str) {
  if (!str) return '';
  return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}
