<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UniClinic — Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --mint:       #d4f1e4;
      --mint-mid:   #a8e6cf;
      --mint-deep:  #52b788;
      --sage:       #74c69d;
      --blush:      #ffd6d6;
      --peach:      #ffc9a0;
      --lavender:   #e8d5f5;
      --sky:        #cce5ff;
      --cream:      #fefaf4;
      --text-dark:  #2d4a3e;
      --text-mid:   #4a6b5a;
      --text-light: #7a9b8a;
      --white:      #ffffff;
      --shadow-soft: 0 8px 32px rgba(82,183,136,0.15);
      --shadow-card: 0 4px 24px rgba(45,74,62,0.10);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Nunito', sans-serif;
      background: linear-gradient(135deg, #e8f5ee 0%, #f0e8f5 40%, #e8f0f5 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    /* Floating blobs */
    body::before, body::after {
      content: '';
      position: fixed;
      border-radius: 50%;
      filter: blur(60px);
      opacity: 0.35;
      pointer-events: none;
      animation: float 8s ease-in-out infinite;
    }
    body::before {
      width: 500px; height: 500px;
      background: radial-gradient(circle, var(--mint-mid), transparent);
      top: -100px; left: -100px;
    }
    body::after {
      width: 400px; height: 400px;
      background: radial-gradient(circle, var(--lavender), transparent);
      bottom: -80px; right: -80px;
      animation-delay: -4s;
    }
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50%       { transform: translateY(-20px); }
    }

    .login-wrapper {
      width: 100%;
      max-width: 460px;
      padding: 1rem;
      z-index: 10;
      animation: fadeUp 0.7s ease both;
    }
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(30px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .login-card {
      background: rgba(255,255,255,0.92);
      backdrop-filter: blur(20px);
      border-radius: 28px;
      padding: 2.8rem 2.5rem;
      box-shadow: var(--shadow-soft), 0 1px 0 rgba(255,255,255,0.8) inset;
      border: 1px solid rgba(255,255,255,0.6);
    }

    .brand-icon {
      width: 72px; height: 72px;
      background: linear-gradient(135deg, var(--mint-deep), var(--sage));
      border-radius: 20px;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 1.2rem;
      box-shadow: 0 6px 20px rgba(82,183,136,0.35);
    }
    .brand-icon i { font-size: 2rem; color: white; }

    .brand-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.9rem;
      font-weight: 600;
      color: var(--text-dark);
      text-align: center;
      margin-bottom: 0.3rem;
    }
    .brand-subtitle {
      text-align: center;
      color: var(--text-light);
      font-size: 0.88rem;
      margin-bottom: 2rem;
      font-weight: 500;
    }

    .form-label {
      font-weight: 600;
      font-size: 0.85rem;
      color: var(--text-mid);
      margin-bottom: 0.4rem;
      letter-spacing: 0.02em;
    }
    .form-control {
      border: 1.5px solid #e2ede8;
      border-radius: 12px;
      padding: 0.7rem 1rem;
      font-family: 'Nunito', sans-serif;
      font-size: 0.95rem;
      background: rgba(212,241,228,0.2);
      color: var(--text-dark);
      transition: all 0.2s;
    }
    .form-control:focus {
      border-color: var(--mint-deep);
      box-shadow: 0 0 0 3px rgba(82,183,136,0.15);
      background: white;
      outline: none;
    }

    .input-group .form-control { border-right: none; border-radius: 12px 0 0 12px; }
    .input-group .btn-outline-secondary {
      border: 1.5px solid #e2ede8;
      border-left: none;
      border-radius: 0 12px 12px 0;
      background: rgba(212,241,228,0.2);
      color: var(--text-light);
    }
    .input-group .btn-outline-secondary:hover { background: var(--mint); }

    .btn-login {
      width: 100%;
      padding: 0.85rem;
      background: linear-gradient(135deg, var(--mint-deep), var(--sage));
      border: none;
      border-radius: 14px;
      color: white;
      font-family: 'Nunito', sans-serif;
      font-weight: 700;
      font-size: 1rem;
      letter-spacing: 0.03em;
      cursor: pointer;
      transition: all 0.25s;
      box-shadow: 0 4px 16px rgba(82,183,136,0.4);
      margin-top: 0.5rem;
    }
    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(82,183,136,0.45);
    }
    .btn-login:active { transform: translateY(0); }

    .role-badges {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 1.8rem;
      padding-top: 1.5rem;
      border-top: 1px solid rgba(82,183,136,0.15);
    }
    .role-badge {
      display: flex; align-items: center; gap: 0.4rem;
      padding: 0.35rem 0.85rem;
      border-radius: 50px;
      font-size: 0.78rem;
      font-weight: 600;
    }
    .role-badge.admin    { background: var(--lavender); color: #6a3fa0; }
    .role-badge.doctor   { background: var(--sky);      color: #1a5c8a; }
    .role-badge.nurse    { background: var(--mint);     color: var(--text-dark); }

    .alert-custom {
      border-radius: 12px;
      font-size: 0.88rem;
      padding: 0.75rem 1rem;
      margin-bottom: 1.2rem;
      font-weight: 600;
    }
    .alert-danger-custom  { background: var(--blush);   color: #8b2020; border: 1px solid #f5c2c2; }
    .alert-success-custom { background: var(--mint);    color: var(--text-dark); border: 1px solid var(--mint-mid); }

    .demo-hint {
      text-align: center;
      font-size: 0.78rem;
      color: var(--text-light);
      margin-top: 1rem;
    }
    .demo-hint strong { color: var(--mint-deep); }
  </style>
</head>
<body>
<?php
require_once 'php/config.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit(); }
$flash = getFlash();
?>

<div class="login-wrapper">
  <div class="login-card">
    <div class="brand-icon"><i class="bi bi-heart-pulse-fill"></i></div>
    <div class="brand-title">UniClinic</div>
    <div class="brand-subtitle">University Clinic & Medical Services</div>

    <?php if ($flash): ?>
      <div class="alert-custom <?= $flash['type'] === 'error' ? 'alert-danger-custom' : 'alert-success-custom' ?>">
        <i class="bi bi-<?= $flash['type'] === 'error' ? 'exclamation-circle' : 'check-circle' ?>"></i>
        <?= htmlspecialchars($flash['message']) ?>
      </div>
    <?php endif; ?>

    <form action="php/auth.php" method="POST">
      <input type="hidden" name="action" value="login">

      <div class="mb-3">
        <label class="form-label"><i class="bi bi-envelope me-1"></i>Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="your@university.edu" required autofocus>
      </div>

      <div class="mb-3">
        <label class="form-label"><i class="bi bi-lock me-1"></i>Password</label>
        <div class="input-group">
          <input type="password" name="password" id="pwdInput" class="form-control" placeholder="••••••••" required>
          <button type="button" class="btn btn-outline-secondary" onclick="togglePwd()">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-login">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
      </button>
    </form>

    <div class="role-badges">
      <span class="role-badge admin"><i class="bi bi-shield-check"></i> Admin</span>
      <span class="role-badge doctor"><i class="bi bi-person-badge"></i> Doctor</span>
      <span class="role-badge nurse"><i class="bi bi-bandaid"></i> Nurse</span>
    </div>

    <div class="demo-hint">
      Demo password: <strong>password</strong> for all accounts
    </div>
  </div>
</div>

<script>
function togglePwd() {
  const i = document.getElementById('pwdInput');
  const e = document.getElementById('eyeIcon');
  if (i.type === 'password') { i.type = 'text'; e.className = 'bi bi-eye-slash'; }
  else { i.type = 'password'; e.className = 'bi bi-eye'; }
}
</script>
</body>
</html>
