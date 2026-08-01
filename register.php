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
  <title>Register | Campus Tool Library</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;padding:40px 16px;">

  <div style="background:var(--bg-card);border:1px solid var(--border-color);width:100%;max-width:480px;border-radius:var(--radius-lg);padding:36px;box-shadow:var(--shadow-md);">
    <div style="text-align:center;margin-bottom:28px;">
      <a href="index.php" class="logo" style="justify-content:center;margin-bottom:12px;">
        <i class="fa-solid fa-recycle text-primary" style="font-size:2rem;"></i>
      </a>
      <h1 style="font-size:1.6rem;font-weight:800;">Create Campus Account</h1>
      <p style="color:var(--text-muted);font-size:0.9rem;margin-top:4px;">Join the Peer-to-Peer Tool Sharing Community</p>
    </div>

    <form id="registerForm" onsubmit="submitRegister(event)">
      <div class="form-group">
        <label class="form-label" for="fullName">Full Name</label>
        <input type="text" id="fullName" class="form-control" placeholder="e.g. Raju Ahmed" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Campus Email</label>
        <input type="email" id="email" class="form-control" placeholder="student.name@ulab.edu.bd" required>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group">
          <label class="form-label" for="studentId">Student / Faculty ID</label>
          <input type="text" id="studentId" class="form-control" placeholder="213014001" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="role">Role</label>
          <select id="role" class="form-control">
            <option value="Student">Student</option>
            <option value="Faculty">Faculty</option>
            <option value="Lab Admin">Lab Admin</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="department">Department</label>
        <select id="department" class="form-control">
          <option value="Computer Science & Engineering">Computer Science & Engineering</option>
          <option value="Electrical & Electronic Engineering">Electrical & Electronic Engineering</option>
          <option value="Media Studies & Journalism">Media Studies & Journalism</option>
          <option value="General Studies">General Studies</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" class="form-control" placeholder="Create a strong password" required>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:12px;padding:12px;"><i class="fa fa-user-plus"></i> Complete Registration</button>
    </form>

    <div style="margin-top:24px;text-align:center;font-size:0.88rem;color:var(--text-muted);">
      Already have an account? <a href="login.php" class="text-primary font-bold">Log in here</a>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
  <script>
    async function submitRegister(e) {
      e.preventDefault();
      const formData = new FormData();
      formData.append('action', 'register');
      formData.append('full_name', document.getElementById('fullName').value);
      formData.append('email', document.getElementById('email').value);
      formData.append('student_id', document.getElementById('studentId').value);
      formData.append('role', document.getElementById('role').value);
      formData.append('department', document.getElementById('department').value);
      formData.append('password', document.getElementById('password').value);

      try {
        const res = await fetch('api/auth.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (res.ok) {
          alert('Registration successful!');
          window.location.href = 'dashboard.php';
        } else {
          alert('Registration Failed: ' + data.error);
        }
      } catch (err) {
        alert('Network error during registration.');
      }
    }
  </script>
</body>
</html>
