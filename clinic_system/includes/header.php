<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?? 'UniClinic' ?></title>
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
      --peach:      #ffe8d6;
      --lavender:   #e8d5f5;
      --sky:        #cce5ff;
      --yellow-soft:#fff3cd;
      --cream:      #fefaf4;
      --sidebar-bg: #f0faf4;
      --text-dark:  #2d4a3e;
      --text-mid:   #4a6b5a;
      --text-light: #7a9b8a;
      --white:      #ffffff;
      --border:     rgba(82,183,136,0.18);
      --shadow-soft: 0 4px 24px rgba(82,183,136,0.12);
    }

    * { box-sizing: border-box; }
    body { font-family: 'Nunito', sans-serif; background: #f5faf7; color: var(--text-dark); margin: 0; }

    /* ---- SIDEBAR ---- */
    .sidebar {
      position: fixed; top: 0; left: 0;
      height: 100vh; width: 265px;
      background: var(--sidebar-bg);
      border-right: 1px solid var(--border);
      display: flex; flex-direction: column;
      z-index: 100;
      transition: transform 0.3s;
      box-shadow: 2px 0 16px rgba(82,183,136,0.08);
    }
    .sidebar-brand {
      padding: 1.4rem 1.5rem;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; gap: 0.75rem;
    }
    .sidebar-brand .brand-icon {
      width: 42px; height: 42px;
      background: linear-gradient(135deg, var(--mint-deep), var(--sage));
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 3px 10px rgba(82,183,136,0.3);
      flex-shrink: 0;
    }
    .sidebar-brand .brand-icon i { color: white; font-size: 1.2rem; }
    .sidebar-brand .brand-name {
      font-family: 'Playfair Display', serif;
      font-size: 1.25rem; font-weight: 600;
      color: var(--text-dark); line-height: 1.1;
    }
    .sidebar-brand .brand-sub { font-size: 0.72rem; color: var(--text-light); font-weight: 500; }

    .sidebar-nav { flex: 1; padding: 1rem 0.75rem; overflow-y: auto; }
    .nav-section-title {
      font-size: 0.7rem; font-weight: 700; letter-spacing: 0.08em;
      color: var(--text-light); text-transform: uppercase;
      padding: 0.5rem 0.75rem 0.3rem;
    }
    .nav-link-item {
      display: flex; align-items: center; gap: 0.7rem;
      padding: 0.65rem 0.9rem;
      border-radius: 12px;
      text-decoration: none;
      color: var(--text-mid);
      font-weight: 600; font-size: 0.9rem;
      transition: all 0.18s;
      margin-bottom: 0.15rem;
    }
    .nav-link-item i { font-size: 1.05rem; width: 22px; text-align: center; flex-shrink: 0; }
    .nav-link-item:hover { background: var(--mint); color: var(--text-dark); }
    .nav-link-item.active {
      background: linear-gradient(135deg, var(--mint-deep), var(--sage));
      color: white;
      box-shadow: 0 3px 12px rgba(82,183,136,0.35);
    }
    .nav-link-item.active i { color: white; }

    .sidebar-user {
      padding: 1rem 1.2rem;
      border-top: 1px solid var(--border);
      display: flex; align-items: center; gap: 0.75rem;
    }
    .user-avatar {
      width: 38px; height: 38px;
      background: linear-gradient(135deg, var(--lavender), var(--sky));
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; color: var(--text-dark); font-size: 0.9rem;
      flex-shrink: 0;
    }
    .user-info .user-name { font-weight: 700; font-size: 0.88rem; color: var(--text-dark); }
    .user-info .user-role {
      font-size: 0.72rem; font-weight: 600; text-transform: capitalize;
      padding: 0.1rem 0.5rem; border-radius: 50px;
    }
    .user-role.admin   { background: var(--lavender); color: #6a3fa0; }
    .user-role.doctor  { background: var(--sky);      color: #1a5c8a; }
    .user-role.nurse   { background: var(--mint);     color: var(--text-dark); }
    .logout-btn { margin-left: auto; color: var(--text-light); font-size: 1.1rem; text-decoration: none; }
    .logout-btn:hover { color: #e05c5c; }

    /* ---- MAIN CONTENT ---- */
    .main-content { margin-left: 265px; min-height: 100vh; }

    .top-bar {
      background: rgba(255,255,255,0.9);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
      padding: 0.85rem 1.8rem;
      display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; z-index: 50;
    }
    .top-bar h1 { font-size: 1.3rem; font-weight: 800; color: var(--text-dark); margin: 0; }
    .top-bar .breadcrumb { margin: 0; font-size: 0.82rem; }
    .top-bar .breadcrumb-item a { color: var(--mint-deep); text-decoration: none; }

    .page-body { padding: 1.8rem; }

    /* ---- CARDS ---- */
    .card-soft {
      background: white;
      border-radius: 18px;
      border: 1px solid var(--border);
      box-shadow: var(--shadow-soft);
      overflow: hidden;
    }
    .card-soft .card-header-soft {
      padding: 1.1rem 1.4rem;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
      background: rgba(212,241,228,0.25);
    }
    .card-soft .card-header-soft h5 {
      font-weight: 800; font-size: 0.98rem; color: var(--text-dark); margin: 0;
    }
    .card-body-soft { padding: 1.4rem; }

    /* ---- STAT CARDS ---- */
    .stat-card {
      background: white;
      border-radius: 18px;
      border: 1px solid var(--border);
      padding: 1.3rem 1.4rem;
      display: flex; align-items: center; gap: 1rem;
      box-shadow: var(--shadow-soft);
      transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-3px); }
    .stat-icon {
      width: 52px; height: 52px; border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem; flex-shrink: 0;
    }
    .stat-card .stat-value { font-size: 1.7rem; font-weight: 800; color: var(--text-dark); line-height: 1; }
    .stat-card .stat-label { font-size: 0.8rem; color: var(--text-light); font-weight: 600; margin-top: 0.2rem; }

    /* ---- TABLES ---- */
    .table-soft { font-size: 0.88rem; }
    .table-soft thead th {
      background: rgba(212,241,228,0.4);
      color: var(--text-mid); font-weight: 700;
      border-color: var(--border); padding: 0.75rem 1rem;
    }
    .table-soft tbody td { padding: 0.75rem 1rem; border-color: var(--border); vertical-align: middle; }
    .table-soft tbody tr:hover { background: rgba(212,241,228,0.15); }

    /* ---- BADGES ---- */
    .badge-soft {
      padding: 0.32rem 0.75rem; border-radius: 50px;
      font-size: 0.75rem; font-weight: 700; letter-spacing: 0.02em;
    }
    .badge-mint     { background: var(--mint);      color: var(--text-dark); }
    .badge-blush    { background: var(--blush);     color: #8b2020; }
    .badge-peach    { background: var(--peach);     color: #7a3a00; }
    .badge-lavender { background: var(--lavender);  color: #6a3fa0; }
    .badge-sky      { background: var(--sky);       color: #1a5c8a; }
    .badge-yellow   { background: var(--yellow-soft); color: #856404; }
    .badge-gray     { background: #f0f0f0;          color: #666; }

    /* ---- BUTTONS ---- */
    .btn-primary-soft {
      background: linear-gradient(135deg, var(--mint-deep), var(--sage));
      border: none; color: white; border-radius: 10px;
      font-weight: 700; font-size: 0.88rem; padding: 0.5rem 1.1rem;
      transition: all 0.2s; cursor: pointer;
    }
    .btn-primary-soft:hover { opacity: 0.9; transform: translateY(-1px); color: white; }
    .btn-outline-soft {
      background: transparent;
      border: 1.5px solid var(--mint-deep);
      color: var(--mint-deep); border-radius: 10px;
      font-weight: 700; font-size: 0.88rem; padding: 0.5rem 1.1rem;
      transition: all 0.2s; cursor: pointer;
    }
    .btn-outline-soft:hover { background: var(--mint); color: var(--text-dark); }
    .btn-danger-soft {
      background: var(--blush); border: none;
      color: #8b2020; border-radius: 10px;
      font-weight: 700; font-size: 0.82rem; padding: 0.4rem 0.85rem;
      transition: all 0.2s; cursor: pointer;
    }
    .btn-danger-soft:hover { background: #ffb3b3; }
    .btn-action {
      border: none; border-radius: 8px; padding: 0.3rem 0.65rem;
      font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all 0.18s;
    }
    .btn-edit   { background: var(--sky);     color: #1a5c8a; }
    .btn-delete { background: var(--blush);   color: #8b2020; }
    .btn-view   { background: var(--mint);    color: var(--text-dark); }
    .btn-edit:hover, .btn-delete:hover, .btn-view:hover { opacity: 0.8; }

    /* ---- FORMS ---- */
    .form-control, .form-select {
      border: 1.5px solid #ddeee5; border-radius: 10px;
      font-family: 'Nunito', sans-serif; font-size: 0.9rem;
      padding: 0.5rem 0.9rem; color: var(--text-dark);
      background: rgba(212,241,228,0.15);
      transition: all 0.2s;
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--mint-deep);
      box-shadow: 0 0 0 3px rgba(82,183,136,0.15);
      background: white; outline: none;
    }
    .form-label { font-weight: 600; font-size: 0.83rem; color: var(--text-mid); margin-bottom: 0.35rem; }

    /* ---- MODAL ---- */
    .modal-content {
      border-radius: 20px; border: none;
      box-shadow: 0 20px 60px rgba(45,74,62,0.2);
    }
    .modal-header {
      background: linear-gradient(135deg, rgba(212,241,228,0.6), rgba(168,230,207,0.4));
      border-bottom: 1px solid var(--border);
      border-radius: 20px 20px 0 0; padding: 1.2rem 1.5rem;
    }
    .modal-title { font-weight: 800; color: var(--text-dark); font-size: 1rem; }
    .modal-footer { border-top: 1px solid var(--border); border-radius: 0 0 20px 20px; }
    .btn-close { opacity: 0.5; }

    /* ---- ALERTS ---- */
    .alert-soft {
      border-radius: 12px; font-size: 0.88rem;
      font-weight: 600; padding: 0.75rem 1rem; border: none;
    }
    .alert-success-soft { background: var(--mint);    color: var(--text-dark); }
    .alert-danger-soft  { background: var(--blush);   color: #8b2020; }
    .alert-warning-soft { background: var(--yellow-soft); color: #856404; }
    .alert-info-soft    { background: var(--sky);     color: #1a5c8a; }

    /* ---- EMPTY STATE ---- */
    .empty-state { text-align: center; padding: 3rem 1rem; color: var(--text-light); }
    .empty-state i { font-size: 3rem; opacity: 0.3; margin-bottom: 0.75rem; }
    .empty-state p { font-weight: 600; font-size: 0.9rem; }

    /* ---- RESPONSIVE ---- */
    .hamburger { display: none; background: none; border: none; font-size: 1.4rem; color: var(--text-dark); cursor: pointer; }
    @media (max-width: 991px) {
      .sidebar { transform: translateX(-100%); }
      .sidebar.open { transform: translateX(0); }
      .main-content { margin-left: 0; }
      .hamburger { display: block; }
      .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 99; }
      .sidebar-overlay.open { display: block; }
    }

    /* Loading spinner */
    .spinner-soft { color: var(--mint-deep); }

    /* Search bar */
    .search-bar {
      position: relative;
    }
    .search-bar input { padding-left: 2.2rem; }
    .search-bar i {
      position: absolute; left: 0.75rem; top: 50%;
      transform: translateY(-50%); color: var(--text-light); pointer-events: none;
    }
  </style>
</head>
<body>
<?php
$user = currentUser();
$initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') !== false ? substr($user['name'], strpos($user['name'], ' ') + 1, 1) : ''));
?>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="bi bi-heart-pulse-fill"></i></div>
    <div>
      <div class="brand-name">UniClinic</div>
      <div class="brand-sub">Medical Services</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-title">Overview</div>
    <a href="dashboard.php" class="nav-link-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
      <i class="bi bi-grid-1x2"></i> Dashboard
    </a>

    <div class="nav-section-title mt-2">Modules</div>
    <a href="students.php" class="nav-link-item <?= basename($_SERVER['PHP_SELF']) === 'students.php' ? 'active' : '' ?>">
      <i class="bi bi-people"></i> Students
    </a>
    <a href="medical_records.php" class="nav-link-item <?= basename($_SERVER['PHP_SELF']) === 'medical_records.php' ? 'active' : '' ?>">
      <i class="bi bi-file-medical"></i> Medical Records
    </a>
    <a href="consultations.php" class="nav-link-item <?= basename($_SERVER['PHP_SELF']) === 'consultations.php' ? 'active' : '' ?>">
      <i class="bi bi-clipboard2-pulse"></i> Consultations
    </a>
    <a href="medicines.php" class="nav-link-item <?= basename($_SERVER['PHP_SELF']) === 'medicines.php' ? 'active' : '' ?>">
      <i class="bi bi-capsule"></i> Medicine Inventory
    </a>
    <a href="clearances.php" class="nav-link-item <?= basename($_SERVER['PHP_SELF']) === 'clearances.php' ? 'active' : '' ?>">
      <i class="bi bi-patch-check"></i> Medical Clearance
    </a>
    <a href="incidents.php" class="nav-link-item <?= basename($_SERVER['PHP_SELF']) === 'incidents.php' ? 'active' : '' ?>">
      <i class="bi bi-exclamation-triangle"></i> Health Incidents
    </a>

    <?php if ($user['role'] === 'admin'): ?>
    <div class="nav-section-title mt-2">Administration</div>
    <a href="users.php" class="nav-link-item <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>">
      <i class="bi bi-person-gear"></i> User Management
    </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-user">
    <div class="user-avatar"><?= $initials ?></div>
    <div class="user-info">
      <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
      <span class="user-role <?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
    </div>
    <a href="php/auth.php?action=logout" class="logout-btn" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
  </div>
</aside>

<div class="main-content">
  <div class="top-bar">
    <div class="d-flex align-items-center gap-3">
      <button class="hamburger" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
      <div>
        <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <small class="text-muted d-none d-md-block"><?= date('l, F j, Y') ?></small>
    </div>
  </div>
  <div class="page-body">
