<?php
require_once 'php/config.php';
requireLogin();
$pageTitle = 'Medicine Inventory & Dispensing';
include 'includes/header.php';
?>

<ul class="nav nav-pills mb-3" id="medTabs">
  <li class="nav-item"><button class="nav-link active" onclick="switchTab('inventory',this)"><i class="bi bi-capsule me-1"></i>Inventory</button></li>
  <li class="nav-item"><button class="nav-link" onclick="switchTab('dispense',this)"><i class="bi bi-bag-plus me-1"></i>Dispense Medicine</button></li>
  <li class="nav-item"><button class="nav-link" onclick="switchTab('log',this)"><i class="bi bi-journal-text me-1"></i>Dispensing Log</button></li>
</ul>

<!-- INVENTORY TAB -->
<div id="tab-inventory">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="search-bar" style="width:280px;position:relative">
      <i class="bi bi-search"></i>
      <input type="text" class="form-control" id="medSearch" placeholder="Search medicines...">
    </div>
    <button class="btn btn-primary-soft" onclick="openMedModal()"><i class="bi bi-plus-circle me-1"></i>Add Medicine</button>
  </div>
  <div class="card-soft"><div class="card-body-soft p-0"><div id="medTable"><div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div></div></div></div>
</div>

<!-- DISPENSE TAB -->
<div id="tab-dispense" style="display:none">
  <div class="card-soft" style="max-width:600px;margin:0 auto">
    <div class="card-header-soft"><h5><i class="bi bi-bag-plus me-2"></i>Dispense Medicine to Student</h5></div>
    <div class="card-body-soft">
      <div class="mb-3">
        <label class="form-label">Student *</label>
        <div style="position:relative">
          <input type="text" class="form-control" id="dStudentSearch" placeholder="Search student..." autocomplete="off">
          <input type="hidden" id="dStudentId">
          <div class="student-search-dropdown" id="dStudentDropdown"></div>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Medicine *</label>
        <select class="form-select" id="dMedicineId"><option value="">-- Select Medicine --</option></select>
        <small id="dStockInfo" class="text-muted"></small>
      </div>
      <div class="mb-3">
        <label class="form-label">Quantity *</label>
        <input type="number" class="form-control" id="dQty" min="1" value="1">
      </div>
      <div class="mb-3">
        <label class="form-label">Purpose</label>
        <input type="text" class="form-control" id="dPurpose" placeholder="Reason for dispensing...">
      </div>
      <button class="btn btn-primary-soft w-100" onclick="dispenseMed()"><i class="bi bi-check-circle me-1"></i>Confirm Dispensing</button>
    </div>
  </div>
</div>

<!-- LOG TAB -->
<div id="tab-log" style="display:none">
  <div class="card-soft"><div class="card-body-soft p-0"><div id="logTable"><div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div></div></div></div>
</div>

<!-- Medicine Modal -->
<div class="modal fade" id="medModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="medModalTitle"><i class="bi bi-plus-circle me-2"></i>Add Medicine</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="medId">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Medicine Name *</label><input type="text" class="form-control" id="mName"></div>
          <div class="col-md-6"><label class="form-label">Generic Name</label><input type="text" class="form-control" id="mGeneric"></div>
          <div class="col-md-4"><label class="form-label">Category</label>
            <select class="form-select" id="mCategory">
              <option value="">Select</option>
              <?php foreach(['Analgesic/Antipyretic','Antibiotic','Antihistamine','Antacid','Cough Remedy','NSAID','Vitamin/Supplement','Antiseptic','Electrolyte Replenisher','Other'] as $c): ?>
              <option><?= $c ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4"><label class="form-label">Unit</label>
            <select class="form-select" id="mUnit">
              <?php foreach(['tablet','capsule','bottle','sachet','vial','ampule','syrup','tube','patch'] as $u): ?>
              <option><?= $u ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4"><label class="form-label">Supplier</label><input type="text" class="form-control" id="mSupplier"></div>
          <div class="col-md-3"><label class="form-label">Quantity in Stock</label><input type="number" class="form-control" id="mQty" min="0" value="0"></div>
          <div class="col-md-3"><label class="form-label">Minimum Stock</label><input type="number" class="form-control" id="mMinStock" min="0" value="10"></div>
          <div class="col-md-3"><label class="form-label">Unit Cost (₱)</label><input type="number" class="form-control" id="mCost" step="0.01" min="0" value="0"></div>
          <div class="col-md-3"><label class="form-label">Expiry Date</label><input type="date" class="form-control" id="mExpiry"></div>
          <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" id="mDesc" rows="2"></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-soft" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary-soft" onclick="saveMed()"><i class="bi bi-floppy me-1"></i>Save Medicine</button>
      </div>
    </div>
  </div>
</div>

<!-- Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-plus-square me-2"></i>Restock</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p id="restockMedName" class="fw-bold"></p>
        <label class="form-label">Add Quantity</label>
        <input type="number" class="form-control" id="restockQty" min="1" value="50">
        <input type="hidden" id="restockMedId">
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline-soft" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary-soft" onclick="doRestock()">Add Stock</button>
      </div>
    </div>
  </div>
</div>

<style>
.nav-pills .nav-link { font-family:'Nunito',sans-serif; font-weight:700; color:var(--text-mid); border-radius:10px; }
.nav-pills .nav-link.active { background:linear-gradient(135deg,var(--mint-deep),var(--sage)); color:white; }
</style>

<script>
const medModal = new bootstrap.Modal(document.getElementById('medModal'));
const restockModal = new bootstrap.Modal(document.getElementById('restockModal'));
let searchT;

function switchTab(tab, btn) {
  ['inventory','dispense','log'].forEach(t => { document.getElementById('tab-'+t).style.display = 'none'; });
  document.getElementById('tab-'+tab).style.display = 'block';
  document.querySelectorAll('#medTabs .nav-link').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  if (tab === 'inventory') loadMeds();
  if (tab === 'log') loadLog();
  if (tab === 'dispense') { loadMedOptions(); searchStudent('dStudentSearch','dStudentDropdown','dStudentId'); }
}

document.getElementById('medSearch').addEventListener('input', function() { clearTimeout(searchT); searchT = setTimeout(loadMeds, 300); });

async function loadMeds() {
  const search = document.getElementById('medSearch').value;
  document.getElementById('medTable').innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div>';
  const data = await apiCall('medicines','list',{search});
  if (!Array.isArray(data) || !data.length) { document.getElementById('medTable').innerHTML = '<div class="empty-state"><i class="bi bi-capsule"></i><p>No medicines found</p></div>'; return; }
  const scMap = { available:'badge-mint', low_stock:'badge-yellow', out_of_stock:'badge-blush', expired:'badge-blush' };
  const rows = data.map(m => {
    const st = m.computed_status;
    return `<tr>
      <td><strong>${m.medicine_name}</strong><br><small class="text-muted">${m.generic_name||''}</small></td>
      <td>${m.category||'—'}</td>
      <td>${m.unit}</td>
      <td><strong>${m.quantity_in_stock}</strong> <span class="badge-soft ${scMap[st]||'badge-gray'} ms-1">${st.replace('_',' ')}</span></td>
      <td>${m.minimum_stock}</td>
      <td>${m.expiry_date || '—'}</td>
      <td>₱${parseFloat(m.unit_cost).toFixed(2)}</td>
      <td>
        <button class="btn-action btn-view me-1" onclick="openRestock(${m.id},'${m.medicine_name}')"><i class="bi bi-plus-square"></i></button>
        <button class="btn-action btn-edit me-1" onclick="editMed(${m.id})"><i class="bi bi-pencil"></i></button>
        <button class="btn-action btn-delete" onclick="confirmDelete(()=>deleteMed(${m.id}))"><i class="bi bi-trash"></i></button>
      </td>
    </tr>`;
  }).join('');
  document.getElementById('medTable').innerHTML = `<table class="table table-soft mb-0"><thead><tr><th>Medicine</th><th>Category</th><th>Unit</th><th>Stock</th><th>Min Stock</th><th>Expiry</th><th>Cost</th><th>Actions</th></tr></thead><tbody>${rows}</tbody></table>`;
}

async function loadMedOptions() {
  const data = await apiCall('medicines','list',{search:''});
  const sel = document.getElementById('dMedicineId');
  sel.innerHTML = '<option value="">-- Select Medicine --</option>';
  if (Array.isArray(data)) data.filter(m => m.computed_status !== 'expired').forEach(m => {
    sel.innerHTML += `<option value="${m.id}" data-stock="${m.quantity_in_stock}">${m.medicine_name} (${m.quantity_in_stock} ${m.unit}s)</option>`;
  });
  sel.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    document.getElementById('dStockInfo').textContent = opt.value ? `Available stock: ${opt.dataset.stock}` : '';
  });
}

function openMedModal(data = null) {
  document.getElementById('medId').value = data?.id || '';
  document.getElementById('mName').value = data?.medicine_name || '';
  document.getElementById('mGeneric').value = data?.generic_name || '';
  document.getElementById('mCategory').value = data?.category || '';
  document.getElementById('mUnit').value = data?.unit || 'tablet';
  document.getElementById('mSupplier').value = data?.supplier || '';
  document.getElementById('mQty').value = data?.quantity_in_stock || 0;
  document.getElementById('mMinStock').value = data?.minimum_stock || 10;
  document.getElementById('mCost').value = data?.unit_cost || 0;
  document.getElementById('mExpiry').value = data?.expiry_date || '';
  document.getElementById('mDesc').value = data?.description || '';
  document.getElementById('medModalTitle').innerHTML = data ? '<i class="bi bi-pencil-square me-2"></i>Edit Medicine' : '<i class="bi bi-plus-circle me-2"></i>Add Medicine';
  medModal.show();
}

async function editMed(id) { const d = await apiCall('medicines','get',{id}); openMedModal(d); }

async function saveMed() {
  if (!document.getElementById('mName').value) { showToast('Medicine name is required','error'); return; }
  const res = await apiCall('medicines','save',{
    id:document.getElementById('medId').value, medicine_name:document.getElementById('mName').value,
    generic_name:document.getElementById('mGeneric').value, category:document.getElementById('mCategory').value,
    unit:document.getElementById('mUnit').value, supplier:document.getElementById('mSupplier').value,
    quantity_in_stock:document.getElementById('mQty').value, minimum_stock:document.getElementById('mMinStock').value,
    unit_cost:document.getElementById('mCost').value, expiry_date:document.getElementById('mExpiry').value,
    description:document.getElementById('mDesc').value,
  },'POST');
  if (res.success) { showToast('Medicine saved!'); medModal.hide(); loadMeds(); }
  else showToast(res.error || 'Failed','error');
}

async function deleteMed(id) {
  const res = await apiCall('medicines','delete',{id},'POST');
  if (res.success) { showToast('Deleted'); loadMeds(); }
}

function openRestock(id, name) {
  document.getElementById('restockMedId').value = id;
  document.getElementById('restockMedName').textContent = name;
  restockModal.show();
}

async function doRestock() {
  const id = document.getElementById('restockMedId').value;
  const qty = document.getElementById('restockQty').value;
  const res = await apiCall('medicines','restock',{id,quantity:qty},'POST');
  if (res.success) { showToast(`Stock updated! New qty: ${res.new_quantity}`); restockModal.hide(); loadMeds(); }
  else showToast(res.error || 'Failed','error');
}

async function dispenseMed() {
  const sid = document.getElementById('dStudentId').value;
  const mid = document.getElementById('dMedicineId').value;
  const qty = document.getElementById('dQty').value;
  if (!sid) { showToast('Select a student','error'); return; }
  if (!mid) { showToast('Select a medicine','error'); return; }
  const res = await apiCall('dispensing','dispense',{ student_id:sid, medicine_id:mid, quantity:qty, purpose:document.getElementById('dPurpose').value },'POST');
  if (res.success) { showToast('Medicine dispensed successfully!'); document.getElementById('dStudentSearch').value=''; document.getElementById('dStudentId').value=''; document.getElementById('dPurpose').value=''; document.getElementById('dQty').value=1; loadMedOptions(); }
  else showToast(res.error || 'Failed to dispense','error');
}

async function loadLog() {
  document.getElementById('logTable').innerHTML = '<div class="d-flex justify-content-center p-5"><div class="spinner-border spinner-soft"></div></div>';
  const data = await apiCall('dispensing','list',{});
  if (!Array.isArray(data) || !data.length) { document.getElementById('logTable').innerHTML = '<div class="empty-state"><i class="bi bi-journal-x"></i><p>No dispensing records</p></div>'; return; }
  const rows = data.map(d => `
    <tr>
      <td>${new Date(d.dispense_date).toLocaleDateString()}</td>
      <td><strong>${d.full_name}</strong><br><small class="text-muted">${d.sid}</small></td>
      <td>${d.medicine_name}</td>
      <td><span class="badge-soft badge-sky">${d.quantity_dispensed}</span></td>
      <td>${d.purpose||'—'}</td>
      <td>${d.dispensed_by_name||'—'}</td>
    </tr>`).join('');
  document.getElementById('logTable').innerHTML = `<table class="table table-soft mb-0"><thead><tr><th>Date</th><th>Student</th><th>Medicine</th><th>Qty</th><th>Purpose</th><th>Dispensed By</th></tr></thead><tbody>${rows}</tbody></table>`;
}

loadMeds();
</script>

<?php include 'includes/footer.php'; ?>
