<?php
require_once 'php/config.php';
requireLogin();
$pageTitle = 'Health Incident Reporting';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div class="d-flex gap-2">
    <div class="search-bar" style="width:260px;position:relative">
      <i class="bi bi-search"></i>
      <input type="text" class="form-control" id="searchInput" placeholder="Search student or incident no...">
    </div>
    <select class="form-select" id="statusFilter" style="width:150px">
      <option value="">All Status</option>
      <option>open</option><option>resolved</option><option>referred</option><option>follow-up</option>
    </select>
  </div>
  <button class="btn btn-primary-soft" onclick="openModal()"><i class="bi bi-plus-circle me-1"></i>Report Incident</button>
</div>

<div class="card-soft"><div class="card-body-soft p-0"><div id="tableContainer"><div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div></div></div></div>

<!-- Modal -->
<div class="modal fade" id="incidentModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"><i class="bi bi-exclamation-triangle me-2"></i>Report Health Incident</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="incidentId">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Student *</label>
            <div style="position:relative">
              <input type="text" class="form-control" id="studentSearch" placeholder="Search student..." autocomplete="off">
              <input type="hidden" id="studentIdHidden">
              <div class="student-search-dropdown" id="studentDropdown"></div>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Incident Date & Time *</label>
            <input type="datetime-local" class="form-control" id="fIncidentDate">
          </div>
          <div class="col-md-4">
            <label class="form-label">Incident Type *</label>
            <select class="form-select" id="fType">
              <option value="accident">Accident</option>
              <option value="injury">Injury</option>
              <option value="illness">Illness</option>
              <option value="emergency">Emergency</option>
              <option value="fainting">Fainting</option>
              <option value="allergic_reaction">Allergic Reaction</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Severity</label>
            <select class="form-select" id="fSeverity">
              <option value="minor">Minor</option>
              <option value="moderate">Moderate</option>
              <option value="severe">Severe</option>
              <option value="critical">Critical</option>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label">Location of Incident</label>
            <input type="text" class="form-control" id="fLocation" placeholder="e.g. Gymnasium, Room 202, Canteen...">
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" id="fStatus">
              <option value="open">Open</option>
              <option value="resolved">Resolved</option>
              <option value="referred">Referred</option>
              <option value="follow-up">Follow-up</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Description *</label>
            <textarea class="form-control" id="fDescription" rows="3" placeholder="Detailed description of what happened..."></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Immediate Action Taken</label>
            <textarea class="form-control" id="fImmediateAction" rows="3" placeholder="First aid or immediate response..."></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Treatment Given</label>
            <textarea class="form-control" id="fTreatment" rows="3" placeholder="Medical treatment provided..."></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Referred To</label>
            <input type="text" class="form-control" id="fReferredTo" placeholder="Doctor, department, specialist...">
          </div>
          <div class="col-md-6">
            <label class="form-label">Hospital (if applicable)</label>
            <input type="text" class="form-control" id="fHospital">
          </div>
          <div class="col-md-6">
            <label class="form-label">Witnesses</label>
            <input type="text" class="form-control" id="fWitnesses" placeholder="Names of witnesses...">
          </div>
          <div class="col-md-6">
            <label class="form-label">Follow-up Date</label>
            <input type="date" class="form-control" id="fFollowUp">
          </div>
          <div class="col-12">
            <label class="form-label">Resolution Notes</label>
            <textarea class="form-control" id="fResolution" rows="2" placeholder="How was the incident resolved?"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-soft" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary-soft" onclick="saveIncident()"><i class="bi bi-floppy me-1"></i>Save Report</button>
      </div>
    </div>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-file-text me-2"></i>Incident Report Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewContent"></div>
      <div class="modal-footer">
        <button class="btn btn-outline-soft" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary-soft" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
      </div>
    </div>
  </div>
</div>

<script>
const modal = new bootstrap.Modal(document.getElementById('incidentModal'));
const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
let st;
document.getElementById('searchInput').addEventListener('input', function() { clearTimeout(st); st = setTimeout(loadData,300); });
document.getElementById('statusFilter').addEventListener('change', loadData);

async function loadData() {
  const search = document.getElementById('searchInput').value;
  const status = document.getElementById('statusFilter').value;
  document.getElementById('tableContainer').innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div>';
  const data = await apiCall('incidents','list',{search,status});
  if (!Array.isArray(data) || !data.length) { document.getElementById('tableContainer').innerHTML = '<div class="empty-state"><i class="bi bi-shield-check"></i><p>No incidents found</p></div>'; return; }
  const sc = { open:'badge-blush', resolved:'badge-mint', referred:'badge-lavender', 'follow-up':'badge-yellow' };
  const sv = { minor:'badge-mint', moderate:'badge-yellow', severe:'badge-peach', critical:'badge-blush' };
  const rows = data.map(i => `
    <tr>
      <td><span class="badge-soft badge-sky" style="font-size:0.72rem">${i.incident_number}</span></td>
      <td>${new Date(i.incident_date).toLocaleDateString()}</td>
      <td><strong>${i.full_name}</strong><br><small class="text-muted">${i.sid}</small></td>
      <td><span class="badge-soft badge-lavender">${i.incident_type.replace('_',' ')}</span></td>
      <td>${i.location||'—'}</td>
      <td><span class="badge-soft ${sv[i.injury_severity]||'badge-gray'}">${i.injury_severity}</span></td>
      <td><span class="badge-soft ${sc[i.status]||'badge-gray'}">${i.status}</span></td>
      <td>
        <button class="btn-action btn-view me-1" onclick="viewIncident(${i.id})"><i class="bi bi-eye"></i></button>
        ${i.status==='open' ? `<button class="btn-action me-1" style="background:var(--mint);color:var(--text-dark)" onclick="resolveIncident(${i.id})" title="Mark Resolved"><i class="bi bi-check-lg"></i></button>` : ''}
        <button class="btn-action btn-edit me-1" onclick="editIncident(${i.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn-action btn-delete" onclick="confirmDelete(()=>deleteIncident(${i.id}))"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join('');
  document.getElementById('tableContainer').innerHTML = `<table class="table table-soft mb-0"><thead><tr><th>Incident No.</th><th>Date</th><th>Student</th><th>Type</th><th>Location</th><th>Severity</th><th>Status</th><th>Actions</th></tr></thead><tbody>${rows}</tbody></table>`;
}

function openModal(data = null) {
  document.getElementById('incidentId').value = data?.id || '';
  document.getElementById('studentSearch').value = data ? `${data.full_name} (${data.sid})` : '';
  document.getElementById('studentIdHidden').value = data?.student_id || '';
  document.getElementById('fIncidentDate').value = data?.incident_date?.replace(' ','T').slice(0,16) || new Date().toISOString().slice(0,16);
  document.getElementById('fType').value = data?.incident_type || 'accident';
  document.getElementById('fSeverity').value = data?.injury_severity || 'minor';
  document.getElementById('fLocation').value = data?.location || '';
  document.getElementById('fStatus').value = data?.status || 'open';
  document.getElementById('fDescription').value = data?.description || '';
  document.getElementById('fImmediateAction').value = data?.immediate_action || '';
  document.getElementById('fTreatment').value = data?.treatment_given || '';
  document.getElementById('fReferredTo').value = data?.referred_to || '';
  document.getElementById('fHospital').value = data?.hospital_name || '';
  document.getElementById('fWitnesses').value = data?.witnesses || '';
  document.getElementById('fFollowUp').value = data?.follow_up_date || '';
  document.getElementById('fResolution').value = data?.resolution_notes || '';
  document.getElementById('modalTitle').innerHTML = data ? '<i class="bi bi-pencil-square me-2"></i>Edit Incident Report' : '<i class="bi bi-exclamation-triangle me-2"></i>Report Health Incident';
  modal.show();
  searchStudent('studentSearch','studentDropdown','studentIdHidden');
}

async function editIncident(id) { const d = await apiCall('incidents','get',{id}); openModal(d); }

async function viewIncident(id) {
  const d = await apiCall('incidents','get',{id});
  const sv = { minor:'#52b788', moderate:'#e9a820', severe:'#e07820', critical:'#e05c5c' };
  document.getElementById('viewContent').innerHTML = `
    <div style="border:1px solid var(--border);border-radius:12px;padding:1.5rem">
      <div class="row g-3">
        <div class="col-12"><strong style="color:var(--mint-deep)">Incident No: ${d.incident_number}</strong></div>
        <div class="col-md-6"><label class="form-label">Student</label><p><strong>${d.full_name}</strong> (${d.sid})</p></div>
        <div class="col-md-6"><label class="form-label">Course</label><p>${d.course||'—'}</p></div>
        <div class="col-md-4"><label class="form-label">Date & Time</label><p>${new Date(d.incident_date).toLocaleString()}</p></div>
        <div class="col-md-4"><label class="form-label">Type</label><p>${d.incident_type.replace('_',' ')}</p></div>
        <div class="col-md-4"><label class="form-label">Severity</label><p style="color:${sv[d.injury_severity]||'#666'};font-weight:700">${d.injury_severity.toUpperCase()}</p></div>
        <div class="col-12"><label class="form-label">Location</label><p>${d.location||'—'}</p></div>
        <div class="col-12"><label class="form-label">Description</label><p>${d.description}</p></div>
        <div class="col-md-6"><label class="form-label">Immediate Action</label><p>${d.immediate_action||'—'}</p></div>
        <div class="col-md-6"><label class="form-label">Treatment Given</label><p>${d.treatment_given||'—'}</p></div>
        <div class="col-md-6"><label class="form-label">Referred To</label><p>${d.referred_to||'—'}</p></div>
        <div class="col-md-6"><label class="form-label">Hospital</label><p>${d.hospital_name||'—'}</p></div>
        <div class="col-12"><label class="form-label">Witnesses</label><p>${d.witnesses||'—'}</p></div>
        ${d.resolution_notes ? `<div class="col-12"><label class="form-label">Resolution Notes</label><p>${d.resolution_notes}</p></div>` : ''}
        <div class="col-md-6"><label class="form-label">Reported By</label><p>${d.reported_by_name||'—'}</p></div>
        <div class="col-md-6"><label class="form-label">Resolved By</label><p>${d.resolved_by_name||'—'}</p></div>
      </div>
    </div>`;
  viewModal.show();
}

async function saveIncident() {
  const sid = document.getElementById('studentIdHidden').value;
  const desc = document.getElementById('fDescription').value;
  if (!sid) { showToast('Please select a student','error'); return; }
  if (!desc) { showToast('Description is required','error'); return; }
  const res = await apiCall('incidents','save',{
    id:document.getElementById('incidentId').value, student_id:sid,
    incident_date:document.getElementById('fIncidentDate').value,
    incident_type:document.getElementById('fType').value,
    injury_severity:document.getElementById('fSeverity').value,
    location:document.getElementById('fLocation').value,
    status:document.getElementById('fStatus').value,
    description:desc,
    immediate_action:document.getElementById('fImmediateAction').value,
    treatment_given:document.getElementById('fTreatment').value,
    referred_to:document.getElementById('fReferredTo').value,
    hospital_name:document.getElementById('fHospital').value,
    witnesses:document.getElementById('fWitnesses').value,
    follow_up_date:document.getElementById('fFollowUp').value,
    resolution_notes:document.getElementById('fResolution').value,
  },'POST');
  if (res.success) { showToast('Incident report saved!'); modal.hide(); loadData(); }
  else showToast(res.error || 'Failed','error');
}

async function resolveIncident(id) {
  const notes = prompt('Resolution notes (optional):') || '';
  const res = await apiCall('incidents','resolve',{id,resolution_notes:notes},'POST');
  if (res.success) { showToast('Incident marked as resolved!'); loadData(); }
}

async function deleteIncident(id) {
  const res = await apiCall('incidents','delete',{id},'POST');
  if (res.success) { showToast('Deleted'); loadData(); }
}

loadData();
</script>

<?php include 'includes/footer.php'; ?>
