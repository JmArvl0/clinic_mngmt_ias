<?php
require_once 'php/config.php';
requireLogin();
$pageTitle = 'Student Medical Records';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="search-bar" style="width:300px;position:relative">
    <i class="bi bi-search"></i>
    <input type="text" class="form-control" id="searchInput" placeholder="Search student...">
  </div>
  <button class="btn btn-primary-soft" onclick="openModal()"><i class="bi bi-file-plus me-1"></i>Add Medical Record</button>
</div>

<div class="card-soft"><div class="card-body-soft p-0"><div id="tableContainer"><div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div></div></div></div>

<!-- Modal -->
<div class="modal fade" id="recordModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"><i class="bi bi-file-medical me-2"></i>Add Medical Record</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="recordId">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Student *</label>
            <div style="position:relative">
              <input type="text" class="form-control" id="studentSearch" placeholder="Search student..." autocomplete="off">
              <input type="hidden" id="studentIdHidden">
              <div class="student-search-dropdown" id="studentDropdown"></div>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Record Date *</label>
            <input type="date" class="form-control" id="fRecordDate" value="<?= date('Y-m-d') ?>">
          </div>

          <div class="col-12"><hr style="border-color:var(--border)"><small class="fw-bold text-muted"><i class="bi bi-heart-pulse me-1"></i>VITAL SIGNS & PHYSICAL</small></div>
          <div class="col-md-2"><label class="form-label">Height (cm)</label><input type="number" class="form-control" id="fHeight" step="0.1"></div>
          <div class="col-md-2"><label class="form-label">Weight (kg)</label><input type="number" class="form-control" id="fWeight" step="0.1"></div>
          <div class="col-md-2"><label class="form-label">Blood Pressure</label><input type="text" class="form-control" id="fBP" placeholder="120/80"></div>
          <div class="col-md-2"><label class="form-label">Pulse Rate</label><input type="number" class="form-control" id="fPulse"></div>
          <div class="col-md-2"><label class="form-label">Temperature (°C)</label><input type="number" class="form-control" id="fTemp" step="0.1"></div>
          <div class="col-md-2"></div>
          <div class="col-md-3"><label class="form-label">Vision Left</label><input type="text" class="form-control" id="fVisionL" placeholder="20/20"></div>
          <div class="col-md-3"><label class="form-label">Vision Right</label><input type="text" class="form-control" id="fVisionR" placeholder="20/20"></div>

          <div class="col-12"><hr style="border-color:var(--border)"><small class="fw-bold text-muted"><i class="bi bi-clipboard2-heart me-1"></i>HEALTH HISTORY</small></div>
          <div class="col-md-6"><label class="form-label">Known Allergies</label><textarea class="form-control" id="fAllergies" rows="2" placeholder="List all allergies..."></textarea></div>
          <div class="col-md-6"><label class="form-label">Chronic Conditions</label><textarea class="form-control" id="fChronic" rows="2" placeholder="Diabetes, hypertension, etc..."></textarea></div>
          <div class="col-md-6"><label class="form-label">Past Illnesses</label><textarea class="form-control" id="fPastIll" rows="2" placeholder="Previous major illnesses..."></textarea></div>
          <div class="col-md-6"><label class="form-label">Surgical History</label><textarea class="form-control" id="fSurgical" rows="2" placeholder="Previous surgeries..."></textarea></div>
          <div class="col-md-6"><label class="form-label">Family Medical History</label><textarea class="form-control" id="fFamilyHx" rows="2" placeholder="Hereditary conditions in family..."></textarea></div>
          <div class="col-md-6"><label class="form-label">Vaccination Records</label><textarea class="form-control" id="fVaccines" rows="2" placeholder="Vaccines received..."></textarea></div>
          <div class="col-12"><label class="form-label">Medical Notes / Remarks</label><textarea class="form-control" id="fNotes" rows="3" placeholder="General health assessment notes..."></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-soft" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary-soft" onclick="saveRecord()"><i class="bi bi-floppy me-1"></i>Save Record</button>
      </div>
    </div>
  </div>
</div>

<script>
const modal = new bootstrap.Modal(document.getElementById('recordModal'));
let st;
document.getElementById('searchInput').addEventListener('input', function() { clearTimeout(st); st = setTimeout(loadData,300); });

async function loadData() {
  const search = document.getElementById('searchInput').value;
  document.getElementById('tableContainer').innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div>';
  const data = await apiCall('medical_records','list',{search});
  if (!Array.isArray(data) || !data.length) { document.getElementById('tableContainer').innerHTML = '<div class="empty-state"><i class="bi bi-file-medical"></i><p>No medical records found</p></div>'; return; }
  const rows = data.map(r => `
    <tr>
      <td><strong>${r.full_name}</strong><br><small class="text-muted">${r.sid}</small></td>
      <td>${r.record_date}</td>
      <td>${r.height_cm ? r.height_cm+'cm' : '—'} / ${r.weight_kg ? r.weight_kg+'kg' : '—'}</td>
      <td>${r.blood_pressure||'—'}</td>
      <td>${r.allergies ? '<span class="badge-soft badge-blush">'+r.allergies.substring(0,30)+(r.allergies.length>30?'...':'')+'</span>' : '—'}</td>
      <td>${r.chronic_conditions ? r.chronic_conditions.substring(0,30)+(r.chronic_conditions.length>30?'...':'') : '—'}</td>
      <td>${r.recorded_by_name||'—'}</td>
      <td>
        <button class="btn-action btn-edit me-1" onclick="editRecord(${r.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn-action btn-delete" onclick="confirmDelete(()=>deleteRecord(${r.id}))"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join('');
  document.getElementById('tableContainer').innerHTML = `<table class="table table-soft mb-0"><thead><tr><th>Student</th><th>Record Date</th><th>Height/Weight</th><th>Blood Pressure</th><th>Allergies</th><th>Chronic Conditions</th><th>Recorded By</th><th>Actions</th></tr></thead><tbody>${rows}</tbody></table>`;
}

function openModal(data = null) {
  document.getElementById('recordId').value = data?.id || '';
  document.getElementById('studentSearch').value = data ? `${data.full_name} (${data.sid})` : '';
  document.getElementById('studentIdHidden').value = data?.student_id || '';
  document.getElementById('fRecordDate').value = data?.record_date || '<?= date('Y-m-d') ?>';
  ['fHeight','fWeight','fBP','fPulse','fTemp','fVisionL','fVisionR','fAllergies','fChronic','fPastIll','fSurgical','fFamilyHx','fVaccines','fNotes'].forEach(id => {
    const map = {fHeight:'height_cm',fWeight:'weight_kg',fBP:'blood_pressure',fPulse:'pulse_rate',fTemp:'temperature',fVisionL:'vision_left',fVisionR:'vision_right',fAllergies:'allergies',fChronic:'chronic_conditions',fPastIll:'past_illnesses',fSurgical:'surgical_history',fFamilyHx:'family_medical_history',fVaccines:'vaccination_records',fNotes:'medical_notes'};
    document.getElementById(id).value = data?.[map[id]] || '';
  });
  document.getElementById('modalTitle').innerHTML = data ? '<i class="bi bi-pencil-square me-2"></i>Edit Medical Record' : '<i class="bi bi-file-medical me-2"></i>Add Medical Record';
  modal.show();
  searchStudent('studentSearch','studentDropdown','studentIdHidden');
}

async function editRecord(id) { const d = await apiCall('medical_records','get',{id}); openModal(d); }

async function saveRecord() {
  const sid = document.getElementById('studentIdHidden').value;
  if (!sid) { showToast('Please select a student','error'); return; }
  const res = await apiCall('medical_records','save',{
    id:document.getElementById('recordId').value, student_id:sid,
    record_date:document.getElementById('fRecordDate').value,
    height_cm:document.getElementById('fHeight').value, weight_kg:document.getElementById('fWeight').value,
    blood_pressure:document.getElementById('fBP').value, pulse_rate:document.getElementById('fPulse').value,
    temperature:document.getElementById('fTemp').value, vision_left:document.getElementById('fVisionL').value,
    vision_right:document.getElementById('fVisionR').value, allergies:document.getElementById('fAllergies').value,
    chronic_conditions:document.getElementById('fChronic').value, past_illnesses:document.getElementById('fPastIll').value,
    surgical_history:document.getElementById('fSurgical').value, family_medical_history:document.getElementById('fFamilyHx').value,
    vaccination_records:document.getElementById('fVaccines').value, medical_notes:document.getElementById('fNotes').value,
  },'POST');
  if (res.success) { showToast('Medical record saved!'); modal.hide(); loadData(); }
  else showToast(res.error || 'Failed','error');
}

async function deleteRecord(id) {
  const res = await apiCall('medical_records','delete',{id},'POST');
  if (res.success) { showToast('Deleted'); loadData(); }
}

loadData();
</script>

<?php include 'includes/footer.php'; ?>
