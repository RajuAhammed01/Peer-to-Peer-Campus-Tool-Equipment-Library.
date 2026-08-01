<?php
require_once __DIR__ . '/config/db.php';
if (getCurrentUser()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Campus Tool Library</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;padding:40px 16px;">

  <div style="background:var(--bg-card);border:1px solid var(--border-color);width:100%;max-width:440px;border-radius:var(--radius-lg);padding:36px;box-shadow:var(--shadow-md);">
    <div style="text-align:center;margin-bottom:28px;">
      <a href="index.php" class="logo" style="justify-content:center;margin-bottom:12px;">
        <i class="fa-solid fa-recycle text-primary" style="font-size:2rem;"></i>
      </a>
      <h1 style="font-size:1.6rem;font-weight:800;">Welcome Back</h1>
      <p style="color:var(--text-muted);font-size:0.9rem;margin-top:4px;">Log in to access campus tool sharing</p>
    </div>

    <form id="loginForm" onsubmit="submitLogin(event)">
      <div class="form-group">
        <label class="form-label" for="email">Campus Email</label>
        <input type="email" id="email" class="form-control" placeholder="raju@ulab.edu.bd" value="raju@ulab.edu.bd" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" class="form-control" placeholder="••••••••" value="password123" required>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:12px;padding:12px;"><i class="fa fa-sign-in-alt"></i> Login</button>
    </form>

    <div style="margin-top:24px;text-align:center;font-size:0.88rem;color:var(--text-muted);">
      Demo Credentials: <code>raju@ulab.edu.bd</code> / <code>password123</code><br>
      Don't have an account? <a href="register.php" class="text-primary font-bold">Register here</a>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
  <script>
    async function submitLogin(e) {
      e.preventDefault();
      const formData = new FormData();
      formData.append('action', 'login');
      formData.append('email', document.getElementById('email').value);
      formData.append('password', document.getElementById('password').value);

      try {
        const res = await fetch('api/auth.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (res.ok) {
          window.location.href = 'dashboard.php';
        } else {
          alert('Login Failed: ' + data.error);
        }
      } catch (err) {
        alert('Network error during login.');
      }
    }
  </script>
</body>
</html>
