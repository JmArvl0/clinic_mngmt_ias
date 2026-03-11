<?php
require_once 'php/config.php';
requireLogin();
$pageTitle = 'Medical Clearance Issuance';
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <div class="d-flex gap-2">
    <div class="search-bar" style="width:260px;position:relative">
      <i class="bi bi-search"></i>
      <input type="text" class="form-control" id="searchInput" placeholder="Search student or clearance no...">
    </div>
    <select class="form-select" id="statusFilter" style="width:160px">
      <option value="">All Status</option>
      <option>pending</option><option>approved</option><option>rejected</option><option>expired</option>
    </select>
  </div>
  <button class="btn btn-primary-soft" onclick="openModal()"><i class="bi bi-patch-plus me-1"></i>Issue Clearance</button>
</div>

<div class="card-soft"><div class="card-body-soft p-0"><div id="tableContainer"><div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div></div></div></div>

<!-- Modal -->
<div class="modal fade" id="clearModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle"><i class="bi bi-patch-plus me-2"></i>Issue Medical Clearance</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="clearId">
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
            <label class="form-label">Purpose *</label>
            <select class="form-select" id="fPurpose" onchange="toggleOther(this.value)">
              <option value="enrollment">Enrollment</option>
              <option value="school_activity">School Activity</option>
              <option value="sports">Sports/PE</option>
              <option value="ojt">OJT/Internship</option>
              <option value="graduation">Graduation</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="col-md-8" id="otherPurposeDiv" style="display:none">
            <label class="form-label">Specify Purpose</label>
            <input type="text" class="form-control" id="fOtherPurpose">
          </div>
          <div class="col-md-4">
            <label class="form-label">Issued Date *</label>
            <input type="date" class="form-control" id="fIssuedDate" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Valid Until</label>
            <input type="date" class="form-control" id="fValidUntil">
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" id="fStatus">
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Medical Findings</label>
            <textarea class="form-control" id="fFindings" rows="3" placeholder="Physical examination findings..."></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" id="fRemarks" rows="2" placeholder="Additional remarks..."></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-soft" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary-soft" onclick="saveClearance()"><i class="bi bi-floppy me-1"></i>Save Clearance</button>
      </div>
    </div>
  </div>
</div>

<!-- Print Modal -->
<div class="modal fade" id="printModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-printer me-2"></i>Medical Clearance Certificate</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="printContent"></div>
      <div class="modal-footer">
        <button class="btn btn-outline-soft" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary-soft" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
      </div>
    </div>
  </div>
</div>

<style>
@media print { body * { visibility: hidden; } #printContent, #printContent * { visibility: visible; } #printContent { position: absolute; left: 0; top: 0; width: 100%; } }
.clearance-cert { border: 2px solid var(--mint-deep); border-radius: 12px; padding: 2rem; font-family: 'Nunito', sans-serif; }
.clearance-cert .cert-header { text-align: center; border-bottom: 1px solid var(--mint-mid); padding-bottom: 1rem; margin-bottom: 1.5rem; }
.clearance-cert .cert-field { margin-bottom: 0.75rem; display: flex; gap: 0.5rem; }
.clearance-cert .cert-label { font-weight: 700; min-width: 160px; color: var(--text-mid); }
.clearance-cert .cert-value { color: var(--text-dark); }
.clearance-cert .sig-area { margin-top: 2rem; text-align: right; }
.clearance-cert .sig-line { border-top: 1px solid var(--text-dark); width: 200px; margin-left: auto; padding-top: 0.3rem; text-align: center; font-size: 0.85rem; }
</style>

<script>
const modal = new bootstrap.Modal(document.getElementById('clearModal'));
const printModal = new bootstrap.Modal(document.getElementById('printModal'));
let st;
document.getElementById('searchInput').addEventListener('input', function() { clearTimeout(st); st = setTimeout(loadData, 300); });
document.getElementById('statusFilter').addEventListener('change', loadData);

function toggleOther(val) { document.getElementById('otherPurposeDiv').style.display = val === 'other' ? 'block' : 'none'; }

async function loadData() {
  const search = document.getElementById('searchInput').value;
  const status = document.getElementById('statusFilter').value;
  document.getElementById('tableContainer').innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div>';
  const data = await apiCall('clearances','list',{search,status});
  if (!Array.isArray(data) || !data.length) { document.getElementById('tableContainer').innerHTML = '<div class="empty-state"><i class="bi bi-patch-exclamation"></i><p>No clearances found</p></div>'; return; }
  const sc = { pending:'badge-yellow', approved:'badge-mint', rejected:'badge-blush', expired:'badge-gray' };
  const rows = data.map(c => `
    <tr>
      <td><span class="badge-soft badge-sky">${c.clearance_number}</span></td>
      <td><strong>${c.full_name}</strong><br><small class="text-muted">${c.sid}</small></td>
      <td>${c.course||'—'}</td>
      <td><span class="badge-soft badge-lavender">${c.purpose.replace('_',' ')}</span></td>
      <td>${c.issued_date}</td>
      <td>${c.valid_until||'—'}</td>
      <td><span class="badge-soft ${sc[c.status]||'badge-gray'}">${c.status}</span></td>
      <td>
        <button class="btn-action btn-view me-1" onclick="printClearance(${c.id})" title="Print"><i class="bi bi-printer"></i></button>
        ${c.status==='pending' ? `<button class="btn-action btn-edit me-1" onclick="approveClearance(${c.id})" style="background:var(--mint);color:var(--text-dark)" title="Approve"><i class="bi bi-check-lg"></i></button>` : ''}
        <button class="btn-action btn-edit me-1" onclick="editClearance(${c.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn-action btn-delete" onclick="confirmDelete(()=>deleteClearance(${c.id}))"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`).join('');
  document.getElementById('tableContainer').innerHTML = `<table class="table table-soft mb-0"><thead><tr><th>Clearance No.</th><th>Student</th><th>Course</th><th>Purpose</th><th>Issued</th><th>Valid Until</th><th>Status</th><th>Actions</th></tr></thead><tbody>${rows}</tbody></table>`;
}

function openModal(data = null) {
  document.getElementById('clearId').value = data?.id || '';
  document.getElementById('studentSearch').value = data ? `${data.full_name} (${data.sid})` : '';
  document.getElementById('studentIdHidden').value = data?.student_id || '';
  document.getElementById('fPurpose').value = data?.purpose || 'enrollment';
  document.getElementById('fOtherPurpose').value = data?.other_purpose || '';
  document.getElementById('fIssuedDate').value = data?.issued_date || '<?= date('Y-m-d') ?>';
  document.getElementById('fValidUntil').value = data?.valid_until || '';
  document.getElementById('fStatus').value = data?.status || 'pending';
  document.getElementById('fFindings').value = data?.medical_findings || '';
  document.getElementById('fRemarks').value = data?.remarks || '';
  toggleOther(document.getElementById('fPurpose').value);
  document.getElementById('modalTitle').innerHTML = data ? '<i class="bi bi-pencil-square me-2"></i>Edit Clearance' : '<i class="bi bi-patch-plus me-2"></i>Issue Medical Clearance';
  modal.show();
  searchStudent('studentSearch','studentDropdown','studentIdHidden');
}

async function editClearance(id) { const d = await apiCall('clearances','get',{id}); openModal(d); }

async function saveClearance() {
  const sid = document.getElementById('studentIdHidden').value;
  if (!sid) { showToast('Please select a student','error'); return; }
  const res = await apiCall('clearances','save',{
    id:document.getElementById('clearId').value, student_id:sid,
    purpose:document.getElementById('fPurpose').value,
    other_purpose:document.getElementById('fOtherPurpose').value,
    issued_date:document.getElementById('fIssuedDate').value,
    valid_until:document.getElementById('fValidUntil').value,
    status:document.getElementById('fStatus').value,
    medical_findings:document.getElementById('fFindings').value,
    remarks:document.getElementById('fRemarks').value,
  },'POST');
  if (res.success) { showToast('Clearance saved!'); modal.hide(); loadData(); }
  else showToast(res.error || 'Failed','error');
}

async function approveClearance(id) {
  const res = await apiCall('clearances','approve',{id},'POST');
  if (res.success) { showToast('Clearance approved!'); loadData(); }
  else showToast('Failed','error');
}

async function deleteClearance(id) {
  const res = await apiCall('clearances','delete',{id},'POST');
  if (res.success) { showToast('Deleted'); loadData(); }
}

async function printClearance(id) {
  const d = await apiCall('clearances','get',{id});
  const statusColors = { approved:'#52b788', pending:'#e9a820', rejected:'#e05c5c' };
  document.getElementById('printContent').innerHTML = `
    <div class="clearance-cert">
      <div class="cert-header">
        <div style="font-size:1.5rem;font-weight:800;color:var(--mint-deep)">UNIVERSITY CLINIC</div>
        <div style="font-size:0.9rem;color:var(--text-mid)">Medical Services Office</div>
        <div style="margin-top:0.75rem;font-size:1.1rem;font-weight:700;color:var(--text-dark)">MEDICAL CLEARANCE CERTIFICATE</div>
        <div style="font-size:0.85rem;margin-top:0.3rem">Clearance No: <strong>${d.clearance_number}</strong></div>
      </div>
      <p>This is to certify that:</p>
      <div class="cert-field"><span class="cert-label">Student Name:</span><span class="cert-value"><strong>${d.full_name}</strong></span></div>
      <div class="cert-field"><span class="cert-label">Student ID:</span><span class="cert-value">${d.sid}</span></div>
      <div class="cert-field"><span class="cert-label">Course:</span><span class="cert-value">${d.course||'—'}</span></div>
      <div class="cert-field"><span class="cert-label">Year Level:</span><span class="cert-value">${d.year_level||'—'}</span></div>
      <div class="cert-field"><span class="cert-label">Date of Birth:</span><span class="cert-value">${d.date_of_birth||'—'}</span></div>
      <div class="cert-field"><span class="cert-label">Blood Type:</span><span class="cert-value">${d.blood_type||'—'}</span></div>
      <div class="cert-field"><span class="cert-label">Purpose:</span><span class="cert-value">${d.purpose.replace('_',' ')} ${d.other_purpose ? '('+d.other_purpose+')' : ''}</span></div>
      <div class="cert-field"><span class="cert-label">Medical Findings:</span><span class="cert-value">${d.medical_findings||'No significant findings'}</span></div>
      ${d.remarks ? `<div class="cert-field"><span class="cert-label">Remarks:</span><span class="cert-value">${d.remarks}</span></div>` : ''}
      <div class="cert-field"><span class="cert-label">Status:</span><span class="cert-value" style="font-weight:700;color:${statusColors[d.status]||'#666'}">${d.status.toUpperCase()}</span></div>
      <div class="cert-field"><span class="cert-label">Issued Date:</span><span class="cert-value">${d.issued_date}</span></div>
      <div class="cert-field"><span class="cert-label">Valid Until:</span><span class="cert-value">${d.valid_until||'One semester'}</span></div>
      <div class="sig-area">
        <div class="sig-line">
          ${d.approved_by_name||d.issued_by_name||'Authorized Signatory'}<br>
          <span style="font-size:0.75rem;color:var(--text-light)">University Physician / Nurse</span>
        </div>
      </div>
    </div>`;
  printModal.show();
}

loadData();
</script>

<?php include 'includes/footer.php'; ?>
