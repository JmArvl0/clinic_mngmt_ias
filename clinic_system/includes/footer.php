  </div><!-- end page-body -->
</div><!-- end main-content -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Sidebar toggle
function toggleSidebar() {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('open');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
}

// Toast notification
function showToast(message, type = 'success') {
  const colors = { success: 'var(--mint)', error: 'var(--blush)', warning: 'var(--yellow-soft)', info: 'var(--sky)' };
  const icons  = { success: 'check-circle-fill', error: 'x-circle-fill', warning: 'exclamation-triangle-fill', info: 'info-circle-fill' };
  const toast  = document.createElement('div');
  toast.style.cssText = `
    position:fixed; bottom:1.5rem; right:1.5rem; z-index:9999;
    background:${colors[type]}; border-radius:14px; padding:0.85rem 1.3rem;
    font-family:'Nunito',sans-serif; font-weight:700; font-size:0.9rem;
    box-shadow:0 8px 24px rgba(0,0,0,0.12); display:flex; align-items:center; gap:0.6rem;
    animation:slideIn 0.3s ease; max-width:320px;
  `;
  toast.innerHTML = `<i class="bi bi-${icons[type]}"></i> ${message}`;
  document.body.appendChild(toast);
  setTimeout(() => { toast.style.animation = 'fadeOut 0.3s ease forwards'; setTimeout(() => toast.remove(), 300); }, 3000);
}

// Confirm delete helper
function confirmDelete(callback) {
  if (confirm('Are you sure you want to delete this record? This cannot be undone.')) callback();
}

// API call helper
async function apiCall(module, action, data = {}, method = 'GET') {
  try {
    let url = `php/api.php?module=${module}&action=${action}`;
    let options = { method };
    if (method === 'POST') {
      const fd = new FormData();
      fd.append('module', module); fd.append('action', action);
      Object.entries(data).forEach(([k, v]) => fd.append(k, v));
      options.body = fd;
    } else {
      const params = new URLSearchParams(data);
      url += '&' + params.toString();
    }
    const res = await fetch(url, options);
    return await res.json();
  } catch (e) {
    console.error('API Error:', e);
    return { error: e.message };
  }
}

// Student search for modals
async function searchStudent(inputId, listId, hiddenId) {
  const input = document.getElementById(inputId);
  const list  = document.getElementById(listId);
  const hidden = document.getElementById(hiddenId);
  input.addEventListener('input', async function() {
    const q = this.value.trim();
    if (q.length < 2) { list.innerHTML = ''; list.style.display = 'none'; return; }
    const data = await apiCall('students', 'list', { search: q });
    if (!Array.isArray(data) || data.length === 0) { list.innerHTML = '<div class="p-2 text-muted small">No students found</div>'; list.style.display = 'block'; return; }
    list.innerHTML = data.slice(0, 6).map(s =>
      `<div class="p-2 px-3 student-option" style="cursor:pointer;font-family:'Nunito',sans-serif;font-size:0.88rem;" data-id="${s.id}" data-name="${s.full_name} (${s.student_id})">
        <strong>${s.full_name}</strong> <span class="text-muted">${s.student_id} — ${s.course}</span>
      </div>`
    ).join('');
    list.style.display = 'block';
    list.querySelectorAll('.student-option').forEach(el => {
      el.addEventListener('mouseenter', () => el.style.background = 'var(--mint)');
      el.addEventListener('mouseleave', () => el.style.background = '');
      el.addEventListener('click', () => {
        input.value = el.dataset.name;
        hidden.value = el.dataset.id;
        list.style.display = 'none';
      });
    });
  });
  document.addEventListener('click', e => { if (!input.contains(e.target)) list.style.display = 'none'; });
}
</script>
<style>
@keyframes slideIn  { from { transform: translateX(100px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@keyframes fadeOut  { to { opacity: 0; transform: translateX(10px); } }
.student-search-dropdown {
  position: absolute; z-index: 1000; background: white;
  border: 1.5px solid var(--border); border-radius: 12px;
  box-shadow: var(--shadow-soft); width: 100%;
  max-height: 200px; overflow-y: auto; display: none;
}
</style>
</body>
</html>
