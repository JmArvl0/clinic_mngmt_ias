<?php
require_once 'php/config.php';
requireLogin();
$pageTitle = 'Consultation & Treatment Logs';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div class="d-flex gap-2">
    <div class="search-bar" style="width:260px;position:relative">
      <i class="bi bi-search"></i>
      <input type="text" class="form-control" id="searchInput" placeholder="Search student...">
    </div>
    <select class="form-select" id="statusFilter" style="width:160px">
      <option value="">All Status</option>
      <option>completed</option><option>ongoing</option><option>referred</option><option>follow-up</option>
    </select>
  </div>
  <button class="btn btn-primary-soft" onclick="openModal()">
    <i class="bi bi-plus-circle me-1"></i>New Consultation
  </button>
</div>

<div class="card-soft">
  <div class="card-body-soft p-0">
    <div id="tableContainer">
      <div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="consultModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"><i class="bi bi-clipboard2-plus me-2"></i>New Consultation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="consultId">
        <div class="row g-3">
          <div class="col-md-12">
            <label class="form-label">Student *</label>
            <div style="position:relative">
              <input type="text" class="form-control" id="studentSearch" placeholder="Search student name or ID..." autocomplete="off">
              <input type="hidden" id="studentIdHidden">
              <div class="student-search-dropdown" id="studentDropdown"></div>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Visit Date & Time *</label>
            <input type="datetime-local" class="form-control" id="fVisitDate">
          </div>
          <div class="col-md-4">
            <label class="form-label">Vital Signs</label>
            <input type="text" class="form-control" id="fVitals" placeholder="e.g. BP: 120/80, T: 36.5°C">
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" id="fStatus">
              <option value="completed">Completed</option>
              <option value="ongoing">Ongoing</option>
              <option value="referred">Referred</option>
              <option value="follow-up">Follow-up</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Chief Complaint *</label>
            <input type="text" class="form-control" id="fComplaint" placeholder="Main reason for visit">
          </div>
          <div class="col-md-6">
            <label class="form-label">Symptoms</label>
            <textarea class="form-control" id="fSymptoms" rows="3" placeholder="Describe symptoms..."></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Diagnosis</label>
            <textarea class="form-control" id="fDiagnosis" rows="3" placeholder="Clinical diagnosis..."></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Treatment Given</label>
            <textarea class="form-control" id="fTreatment" rows="3" placeholder="Treatment administered..."></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Prescription</label>
            <textarea class="form-control" id="fPrescription" rows="3" placeholder="Medications prescribed..."></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label">Follow-up Date</label>
            <input type="date" class="form-control" id="fFollowUp">
          </div>
          <div class="col-md-8">
            <label class="form-label">Referral</label>
            <input type="text" class="form-control" id="fReferral" placeholder="Referred to (doctor/hospital)...">
          </div>
          <div class="col-12">
            <label class="form-label">Additional Notes</label>
            <textarea class="form-control" id="fNotes" rows="2"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-soft" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary-soft" onclick="saveConsultation()"><i class="bi bi-floppy me-1"></i>Save Record</button>
      </div>
    </div>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Consultation Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewContent"></div>
    </div>
  </div>
</div>

<script>
const modal = new bootstrap.Modal(document.getElementById('consultModal'));
const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
let st;

document.getElementById('searchInput').addEventListener('input', function() { clearTimeout(st); st = setTimeout(() => loadData(), 300); });
document.getElementById('statusFilter').addEventListener('change', loadData);

async function loadData() {
  const search = document.getElementById('searchInput').value;
  const status = document.getElementById('statusFilter').value;
  document.getElementById('tableContainer').innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div>';
  const data = await apiCall('consultations', 'list', { search, status });
  if (!Array.isArray(data) || data.length === 0) {
    document.getElementById('tableContainer').innerHTML = '<div class="empty-state"><i class="bi bi-clipboard2-x"></i><p>No consultations found</p></div>';
    return;
  }
  const sc = { completed:'badge-mint', ongoing:'badge-sky', referred:'badge-lavender', 'follow-up':'badge-yellow' };
  const rows = data.map(c => `
    <tr>
      <td>${new Date(c.visit_date).toLocaleDateString()}<br><small class="text-muted">${new Date(c.visit_date).toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})}</small></td>
      <td><strong>${c.full_name}</strong><br><small class="text-muted">${c.sid}</small></td>
      <td>${c.chief_complaint}</td>
      <td>${c.diagnosis || '—'}</td>
      <td>${c.staff_name || '—'}</td>
      <td><span class="badge-soft ${sc[c.status]||'badge-gray'}">${c.status}</span></td>
      <td>
        <button class="btn-action btn-view me-1" onclick="viewConsult(${c.id})"><i class="bi bi-eye"></i></button>
        <button class="btn-action btn-edit me-1" onclick="editConsult(${c.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn-action btn-delete" onclick="confirmDelete(()=>deleteConsult(${c.id}))"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join('');

  document.getElementById('tableContainer').innerHTML = `
    <table class="table table-soft mb-0">
      <thead><tr><th>Date</th><th>Student</th><th>Complaint</th><th>Diagnosis</th><th>Staff</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>${rows}</tbody>
    </table>`;
}

function openModal(data = null) {
  document.getElementById('consultId').value = data?.id || '';
  document.getElementById('studentSearch').value = data ? `${data.full_name} (${data.sid})` : '';
  document.getElementById('studentIdHidden').value = data?.student_id || '';
  document.getElementById('fVisitDate').value   = data?.visit_date?.replace(' ','T').slice(0,16) || new Date().toISOString().slice(0,16);
  document.getElementById('fVitals').value      = data?.vital_signs || '';
  document.getElementById('fStatus').value      = data?.status || 'completed';
  document.getElementById('fComplaint').value   = data?.chief_complaint || '';
  document.getElementById('fSymptoms').value    = data?.symptoms || '';
  document.getElementById('fDiagnosis').value   = data?.diagnosis || '';
  document.getElementById('fTreatment').value   = data?.treatment_given || '';
  document.getElementById('fPrescription').value= data?.prescription || '';
  document.getElementById('fFollowUp').value    = data?.follow_up_date || '';
  document.getElementById('fReferral').value    = data?.referral || '';
  document.getElementById('fNotes').value       = data?.notes || '';
  document.getElementById('modalTitle').innerHTML = data ? '<i class="bi bi-pencil-square me-2"></i>Edit Consultation' : '<i class="bi bi-clipboard2-plus me-2"></i>New Consultation';
  modal.show();
  searchStudent('studentSearch','studentDropdown','studentIdHidden');
}

async function editConsult(id) { const d = await apiCall('consultations','get',{id}); openModal(d); }

async function viewConsult(id) {
  const d = await apiCall('consultations','get',{id});
  const sc = { completed:'badge-mint', ongoing:'badge-sky', referred:'badge-lavender', 'follow-up':'badge-yellow' };
  document.getElementById('viewContent').innerHTML = `
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Student</label><p class="fw-bold">${d.full_name} (${d.sid})</p></div>
      <div class="col-md-3"><label class="form-label">Visit Date</label><p>${new Date(d.visit_date).toLocaleString()}</p></div>
      <div class="col-md-3"><label class="form-label">Status</label><p><span class="badge-soft ${sc[d.status]||'badge-gray'}">${d.status}</span></p></div>
      <div class="col-12"><label class="form-label">Chief Complaint</label><p>${d.chief_complaint}</p></div>
      <div class="col-md-6"><label class="form-label">Symptoms</label><p>${d.symptoms||'—'}</p></div>
      <div class="col-md-6"><label class="form-label">Diagnosis</label><p>${d.diagnosis||'—'}</p></div>
      <div class="col-md-6"><label class="form-label">Treatment Given</label><p>${d.treatment_given||'—'}</p></div>
      <div class="col-md-6"><label class="form-label">Prescription</label><p>${d.prescription||'—'}</p></div>
      <div class="col-md-4"><label class="form-label">Vital Signs</label><p>${d.vital_signs||'—'}</p></div>
      <div class="col-md-4"><label class="form-label">Follow-up Date</label><p>${d.follow_up_date||'—'}</p></div>
      <div class="col-md-4"><label class="form-label">Referral</label><p>${d.referral||'—'}</p></div>
      ${d.notes ? `<div class="col-12"><label class="form-label">Notes</label><p>${d.notes}</p></div>` : ''}
    </div>`;
  viewModal.show();
}

async function saveConsultation() {
  const sid = document.getElementById('studentIdHidden').value;
  const complaint = document.getElementById('fComplaint').value;
  if (!sid) { showToast('Please select a student', 'error'); return; }
  if (!complaint) { showToast('Chief complaint is required', 'error'); return; }
  const res = await apiCall('consultations', 'save', {
    id: document.getElementById('consultId').value,
    student_id: sid,
    visit_date: document.getElementById('fVisitDate').value,
    vital_signs: document.getElementById('fVitals').value,
    status: document.getElementById('fStatus').value,
    chief_complaint: complaint,
    symptoms: document.getElementById('fSymptoms').value,
    diagnosis: document.getElementById('fDiagnosis').value,
    treatment_given: document.getElementById('fTreatment').value,
    prescription: document.getElementById('fPrescription').value,
    follow_up_date: document.getElementById('fFollowUp').value,
    referral: document.getElementById('fReferral').value,
    notes: document.getElementById('fNotes').value,
  }, 'POST');
  if (res.success) { showToast('Consultation saved!'); modal.hide(); loadData(); }
  else showToast(res.error || 'Failed to save', 'error');
}

async function deleteConsult(id) {
  const res = await apiCall('consultations','delete',{id},'POST');
  if (res.success) { showToast('Deleted successfully'); loadData(); }
  else showToast(res.error || 'Failed', 'error');
}

loadData();
</script>

<?php include 'includes/footer.php'; ?>
