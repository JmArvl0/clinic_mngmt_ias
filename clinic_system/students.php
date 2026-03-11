<?php
require_once 'php/config.php';
requireLogin();
$pageTitle = 'Students';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="search-bar" style="width:300px;position:relative">
    <i class="bi bi-search"></i>
    <input type="text" class="form-control" id="searchInput" placeholder="Search students...">
  </div>
  <button class="btn btn-primary-soft" onclick="openModal()">
    <i class="bi bi-person-plus me-1"></i>Add Student
  </button>
</div>

<div class="card-soft">
  <div class="card-body-soft p-0">
    <div id="tableContainer">
      <div class="d-flex justify-content-center p-5">
        <div class="spinner-border spinner-soft"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="studentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"><i class="bi bi-person-plus me-2"></i>Add Student</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="studentForm">
          <input type="hidden" id="studentId">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Student ID *</label>
              <input type="text" class="form-control" id="fStudentId" placeholder="e.g. 2024-0001" required>
            </div>
            <div class="col-md-8">
              <label class="form-label">Full Name *</label>
              <input type="text" class="form-control" id="fFullName" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Date of Birth</label>
              <input type="date" class="form-control" id="fDOB">
            </div>
            <div class="col-md-4">
              <label class="form-label">Gender</label>
              <select class="form-select" id="fGender">
                <option value="">Select</option>
                <option>Male</option><option>Female</option><option>Other</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Blood Type</label>
              <select class="form-select" id="fBloodType">
                <option value="Unknown">Unknown</option>
                <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt): ?>
                <option><?= $bt ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Course/Program</label>
              <input type="text" class="form-control" id="fCourse">
            </div>
            <div class="col-md-3">
              <label class="form-label">Year Level</label>
              <select class="form-select" id="fYearLevel">
                <option value="">Select</option>
                <?php foreach(['1st Year','2nd Year','3rd Year','4th Year','5th Year','Graduate'] as $y): ?>
                <option><?= $y ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Section</label>
              <input type="text" class="form-control" id="fSection">
            </div>
            <div class="col-md-6">
              <label class="form-label">Contact Number</label>
              <input type="text" class="form-control" id="fContact">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" id="fEmail">
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <input type="text" class="form-control" id="fAddress">
            </div>
            <div class="col-md-6">
              <label class="form-label">Guardian Name</label>
              <input type="text" class="form-control" id="fGuardianName">
            </div>
            <div class="col-md-6">
              <label class="form-label">Guardian Contact</label>
              <input type="text" class="form-control" id="fGuardianContact">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-soft" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary-soft" onclick="saveStudent()"><i class="bi bi-floppy me-1"></i>Save Student</button>
      </div>
    </div>
  </div>
</div>

<script>
const modal = new bootstrap.Modal(document.getElementById('studentModal'));
let searchTimeout;

document.getElementById('searchInput').addEventListener('input', function() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => loadStudents(this.value), 300);
});

async function loadStudents(search = '') {
  document.getElementById('tableContainer').innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div>';
  const data = await apiCall('students', 'list', { search });
  if (!Array.isArray(data)) { document.getElementById('tableContainer').innerHTML = '<div class="empty-state"><i class="bi bi-exclamation-circle"></i><p>Failed to load data</p></div>'; return; }
  if (data.length === 0) { document.getElementById('tableContainer').innerHTML = '<div class="empty-state"><i class="bi bi-person-x"></i><p>No students found</p></div>'; return; }

  const rows = data.map(s => `
    <tr>
      <td><span class="badge-soft badge-sky">${s.student_id}</span></td>
      <td><strong>${s.full_name}</strong></td>
      <td>${s.course || '—'}</td>
      <td>${s.year_level || '—'}</td>
      <td>${s.contact_number || '—'}</td>
      <td><span class="badge-soft badge-mint">${s.blood_type}</span></td>
      <td>
        <button class="btn-action btn-edit me-1" onclick="editStudent(${s.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn-action btn-delete" onclick="confirmDelete(() => deleteStudent(${s.id}))"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join('');

  document.getElementById('tableContainer').innerHTML = `
    <table class="table table-soft mb-0">
      <thead><tr>
        <th>ID</th><th>Full Name</th><th>Course</th><th>Year</th><th>Contact</th><th>Blood Type</th><th>Actions</th>
      </tr></thead>
      <tbody>${rows}</tbody>
    </table>`;
}

function openModal(data = null) {
  ['studentId','fStudentId','fFullName','fDOB','fGender','fBloodType','fCourse','fYearLevel','fSection','fContact','fEmail','fAddress','fGuardianName','fGuardianContact']
    .forEach(id => { const el = document.getElementById(id); if (el) el.value = data ? (data[id.replace('f','').toLowerCase()] ?? '') : ''; });
  document.getElementById('modalTitle').innerHTML = data
    ? '<i class="bi bi-pencil-square me-2"></i>Edit Student'
    : '<i class="bi bi-person-plus me-2"></i>Add Student';
  if (data) {
    document.getElementById('studentId').value      = data.id;
    document.getElementById('fStudentId').value     = data.student_id;
    document.getElementById('fFullName').value      = data.full_name;
    document.getElementById('fDOB').value           = data.date_of_birth || '';
    document.getElementById('fGender').value        = data.gender || '';
    document.getElementById('fBloodType').value     = data.blood_type || 'Unknown';
    document.getElementById('fCourse').value        = data.course || '';
    document.getElementById('fYearLevel').value     = data.year_level || '';
    document.getElementById('fSection').value       = data.section || '';
    document.getElementById('fContact').value       = data.contact_number || '';
    document.getElementById('fEmail').value         = data.email || '';
    document.getElementById('fAddress').value       = data.address || '';
    document.getElementById('fGuardianName').value  = data.guardian_name || '';
    document.getElementById('fGuardianContact').value = data.guardian_contact || '';
  }
  modal.show();
}

async function editStudent(id) {
  const data = await apiCall('students', 'get', { id });
  openModal(data);
}

async function saveStudent() {
  const id = document.getElementById('studentId').value;
  const payload = {
    id, student_id: document.getElementById('fStudentId').value,
    full_name: document.getElementById('fFullName').value,
    date_of_birth: document.getElementById('fDOB').value,
    gender: document.getElementById('fGender').value,
    blood_type: document.getElementById('fBloodType').value,
    course: document.getElementById('fCourse').value,
    year_level: document.getElementById('fYearLevel').value,
    section: document.getElementById('fSection').value,
    contact_number: document.getElementById('fContact').value,
    email: document.getElementById('fEmail').value,
    address: document.getElementById('fAddress').value,
    guardian_name: document.getElementById('fGuardianName').value,
    guardian_contact: document.getElementById('fGuardianContact').value,
  };
  if (!payload.student_id || !payload.full_name) { showToast('Student ID and Name are required', 'error'); return; }
  const res = await apiCall('students', 'save', payload, 'POST');
  if (res.success) { showToast('Student saved!'); modal.hide(); loadStudents(); }
  else showToast(res.error || 'Failed to save', 'error');
}

async function deleteStudent(id) {
  const res = await apiCall('students', 'delete', { id }, 'POST');
  if (res.success) { showToast('Student deleted'); loadStudents(); }
  else showToast(res.error || 'Failed to delete', 'error');
}

loadStudents();
</script>

<?php include 'includes/footer.php'; ?>
