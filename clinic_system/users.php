<?php
require_once 'php/config.php';
requireRole('admin');
$pageTitle = 'User Management';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <p class="text-muted mb-0" style="font-size:0.88rem">Manage clinic staff accounts and access roles.</p>
  </div>
  <button class="btn btn-primary-soft" onclick="openModal()"><i class="bi bi-person-plus me-1"></i>Add User</button>
</div>

<div class="card-soft"><div class="card-body-soft p-0"><div id="tableContainer"><div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div></div></div></div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"><i class="bi bi-person-plus me-2"></i>Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="userId">
        <div class="row g-3">
          <div class="col-12"><label class="form-label">Full Name *</label><input type="text" class="form-control" id="uName"></div>
          <div class="col-12"><label class="form-label">Email *</label><input type="email" class="form-control" id="uEmail"></div>
          <div id="passwordField" class="col-12">
            <label class="form-label">Password *</label>
            <input type="password" class="form-control" id="uPassword" placeholder="Min. 8 characters">
          </div>
          <div class="col-md-6">
            <label class="form-label">Role</label>
            <select class="form-select" id="uRole">
              <option value="nurse">Nurse</option>
              <option value="doctor">Doctor</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select class="form-select" id="uStatus">
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-soft" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary-soft" onclick="saveUser()"><i class="bi bi-floppy me-1"></i>Save User</button>
      </div>
    </div>
  </div>
</div>

<script>
const modal = new bootstrap.Modal(document.getElementById('userModal'));

async function loadData() {
  document.getElementById('tableContainer').innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div>';
  const data = await apiCall('users','list',{});
  if (!Array.isArray(data) || !data.length) { document.getElementById('tableContainer').innerHTML = '<div class="empty-state"><i class="bi bi-people"></i><p>No users found</p></div>'; return; }
  const rc = { admin:'badge-lavender', doctor:'badge-sky', nurse:'badge-mint' };
  const sc = { active:'badge-mint', inactive:'badge-gray' };
  const rows = data.map(u => `
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:0.75rem">
          <div style="width:38px;height:38px;background:linear-gradient(135deg,var(--lavender),var(--sky));border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.9rem">${u.full_name.charAt(0)}</div>
          <strong>${u.full_name}</strong>
        </div>
      </td>
      <td>${u.email}</td>
      <td><span class="badge-soft ${rc[u.role]||'badge-gray'}">${u.role}</span></td>
      <td><span class="badge-soft ${sc[u.status]||'badge-gray'}">${u.status}</span></td>
      <td>${new Date(u.created_at).toLocaleDateString()}</td>
      <td>
        <button class="btn-action btn-edit me-1" onclick="editUser(${u.id},'${u.full_name}','${u.email}','${u.role}','${u.status}')"><i class="bi bi-pencil"></i></button>
        <button class="btn-action btn-delete" onclick="confirmDelete(()=>deleteUser(${u.id}))"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join('');
  document.getElementById('tableContainer').innerHTML = `<table class="table table-soft mb-0"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody>${rows}</tbody></table>`;
}

function openModal(data = null) {
  document.getElementById('userId').value = data?.id || '';
  document.getElementById('uName').value = data?.name || '';
  document.getElementById('uEmail').value = data?.email || '';
  document.getElementById('uRole').value = data?.role || 'nurse';
  document.getElementById('uStatus').value = data?.status || 'active';
  document.getElementById('uPassword').value = '';
  document.getElementById('passwordField').style.display = data ? 'none' : 'block';
  document.getElementById('modalTitle').innerHTML = data ? '<i class="bi bi-pencil-square me-2"></i>Edit User' : '<i class="bi bi-person-plus me-2"></i>Add User';
  modal.show();
}

function editUser(id, name, email, role, status) { openModal({id,name,email,role,status}); }

async function saveUser() {
  const id = document.getElementById('userId').value;
  const payload = { id, full_name:document.getElementById('uName').value, email:document.getElementById('uEmail').value, role:document.getElementById('uRole').value, status:document.getElementById('uStatus').value };
  if (!id) payload.password = document.getElementById('uPassword').value;
  if (!payload.full_name || !payload.email) { showToast('Name and email are required','error'); return; }
  const res = await apiCall('users','save',payload,'POST');
  if (res.success) { showToast('User saved!'); modal.hide(); loadData(); }
  else showToast(res.error || 'Failed','error');
}

async function deleteUser(id) {
  const res = await apiCall('users','delete',{id},'POST');
  if (res.success) { showToast('User deleted'); loadData(); }
  else showToast(res.error || 'Failed','error');
}

loadData();
</script>

<?php include 'includes/footer.php'; ?>
