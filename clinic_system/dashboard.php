<?php
require_once 'php/config.php';
requireLogin();
$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div id="dashContent">
  <!-- Stat cards row -->
  <div class="row g-3 mb-4" id="statsRow">
    <div class="col-6 col-md-4 col-xl-2">
      <div class="stat-card">
        <div class="stat-icon" style="background:var(--mint)"><i class="bi bi-people-fill" style="color:var(--mint-deep)"></i></div>
        <div><div class="stat-value" id="statStudents">—</div><div class="stat-label">Students</div></div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="stat-card">
        <div class="stat-icon" style="background:var(--sky)"><i class="bi bi-clipboard2-pulse-fill" style="color:#1a5c8a"></i></div>
        <div><div class="stat-value" id="statToday">—</div><div class="stat-label">Today's Visits</div></div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="stat-card">
        <div class="stat-icon" style="background:var(--peach)"><i class="bi bi-capsule-pill" style="color:#c45c00"></i></div>
        <div><div class="stat-value" id="statLowStock">—</div><div class="stat-label">Low Stock</div></div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="stat-card">
        <div class="stat-icon" style="background:var(--lavender)"><i class="bi bi-patch-check-fill" style="color:#6a3fa0"></i></div>
        <div><div class="stat-value" id="statClearances">—</div><div class="stat-label">Pending Clearances</div></div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="stat-card">
        <div class="stat-icon" style="background:var(--blush)"><i class="bi bi-exclamation-triangle-fill" style="color:#8b2020"></i></div>
        <div><div class="stat-value" id="statIncidents">—</div><div class="stat-label">Open Incidents</div></div>
      </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
      <div class="stat-card">
        <div class="stat-icon" style="background:var(--mint-mid)"><i class="bi bi-graph-up" style="color:var(--mint-deep)"></i></div>
        <div><div class="stat-value" id="statTotal">—</div><div class="stat-label">Total Visits</div></div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- Chart -->
    <div class="col-lg-7">
      <div class="card-soft">
        <div class="card-header-soft">
          <h5><i class="bi bi-bar-chart-line me-2"></i>Monthly Consultations (<?= date('Y') ?>)</h5>
        </div>
        <div class="card-body-soft">
          <canvas id="consultChart" height="220"></canvas>
        </div>
      </div>
    </div>

    <!-- Recent consultations -->
    <div class="col-lg-5">
      <div class="card-soft h-100">
        <div class="card-header-soft">
          <h5><i class="bi bi-clock-history me-2"></i>Recent Visits</h5>
          <a href="consultations.php" class="btn btn-sm btn-primary-soft">View All</a>
        </div>
        <div class="card-body-soft p-0">
          <div id="recentList" style="max-height:280px;overflow-y:auto;">
            <div class="d-flex justify-content-center p-4">
              <div class="spinner-border spinner-soft spinner-border-sm"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-12">
      <div class="card-soft">
        <div class="card-header-soft"><h5><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5></div>
        <div class="card-body-soft">
          <div class="d-flex gap-2 flex-wrap">
            <a href="consultations.php?new=1" class="btn btn-primary-soft"><i class="bi bi-plus-circle me-1"></i>New Consultation</a>
            <a href="medical_records.php?new=1" class="btn btn-outline-soft"><i class="bi bi-file-medical me-1"></i>Add Medical Record</a>
            <a href="clearances.php?new=1" class="btn btn-outline-soft"><i class="bi bi-patch-check me-1"></i>Issue Clearance</a>
            <a href="incidents.php?new=1" class="btn btn-outline-soft"><i class="bi bi-exclamation-triangle me-1"></i>Report Incident</a>
            <a href="medicines.php" class="btn btn-outline-soft"><i class="bi bi-capsule me-1"></i>Check Inventory</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

async function loadDashboard() {
  const data = await apiCall('dashboard', '', {});
  if (data.error) { showToast('Failed to load dashboard', 'error'); return; }

  document.getElementById('statStudents').textContent   = data.total_students;
  document.getElementById('statToday').textContent      = data.consultations_today;
  document.getElementById('statLowStock').textContent   = data.low_stock_medicines;
  document.getElementById('statClearances').textContent = data.pending_clearances;
  document.getElementById('statIncidents').textContent  = data.open_incidents;
  document.getElementById('statTotal').textContent      = data.total_consultations;

  // Chart
  const monthly = Array(12).fill(0);
  data.monthly_data.forEach(d => monthly[d.month - 1] = parseInt(d.count));
  new Chart(document.getElementById('consultChart'), {
    type: 'bar',
    data: {
      labels: MONTHS,
      datasets: [{
        label: 'Consultations',
        data: monthly,
        backgroundColor: 'rgba(82,183,136,0.35)',
        borderColor: '#52b788',
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0, font: { family: 'Nunito' } }, grid: { color: 'rgba(82,183,136,0.1)' } },
        x: { ticks: { font: { family: 'Nunito', weight: '600' } }, grid: { display: false } }
      }
    }
  });

  // Recent consultations
  const statusColors = { completed: 'badge-mint', ongoing: 'badge-sky', referred: 'badge-lavender', 'follow-up': 'badge-yellow' };
  const recentHTML = data.recent_consultations.length === 0
    ? '<div class="empty-state"><i class="bi bi-clipboard2-x"></i><p>No consultations yet</p></div>'
    : data.recent_consultations.map(c => `
        <div class="d-flex align-items-center gap-3 px-3 py-2" style="border-bottom:1px solid var(--border)">
          <div style="width:36px;height:36px;background:var(--mint);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--mint-deep);font-size:0.85rem;flex-shrink:0">
            ${c.full_name.charAt(0)}
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-weight:700;font-size:0.88rem;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${c.full_name}</div>
            <div style="font-size:0.78rem;color:var(--text-light)">${c.chief_complaint ?? ''}</div>
          </div>
          <div style="text-align:right;flex-shrink:0">
            <span class="badge-soft ${statusColors[c.status] || 'badge-gray'}">${c.status}</span>
            <div style="font-size:0.72rem;color:var(--text-light);margin-top:2px">${new Date(c.visit_date).toLocaleDateString()}</div>
          </div>
        </div>`).join('');
  document.getElementById('recentList').innerHTML = recentHTML;
}

loadDashboard();
</script>

<?php include 'includes/footer.php'; ?>
