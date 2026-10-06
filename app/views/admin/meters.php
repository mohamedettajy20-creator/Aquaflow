<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <div>
        <h1 class="font-display" style="font-size:1.5rem;">Water Meters</h1>
        <p class="text-muted-aq" style="font-size:.88rem;">Track meter status and installation across all customers.</p>
    </div>
    <button class="btn-aq-primary" onclick="resetMeterForm(); AQ.openModal('meterModal')">+ Add meter</button>
</div>

<div class="aq-card" style="margin-bottom:18px;">
    <div class="aq-card-body" style="padding:16px 22px;">
        <form method="GET" style="display:flex; gap:10px;">
            <input type="text" name="q" class="form-control-aq" placeholder="Search by meter number, customer, code…" value="<?= e($search) ?>" style="max-width:340px;">
            <button class="btn-aq-outline" type="submit">Search</button>
        </form>
    </div>
</div>

<div class="aq-card">
    <div style="overflow-x:auto;">
    <table class="aq-table">
        <thead><tr><th>Meter #</th><th>Customer</th><th>Installed</th><th>Status</th><th>Previous</th><th>Current</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($meters)): ?>
            <tr><td colspan="7" class="text-muted-aq" style="text-align:center; padding:30px;">No meters found.</td></tr>
        <?php endif; ?>
        <?php
        $meterModel = new MeterModel();
        foreach ($meters as $m):
            $prev = $meterModel->lastReadingValue((int)$m['id']);
        ?>
            <tr>
                <td style="font-weight:600;"><?= e($m['meter_number']) ?></td>
                <td><?= e($m['customer_name']) ?> <span class="text-muted-aq">(<?= e($m['customer_code']) ?>)</span></td>
                <td><?= formatDate($m['installation_date']) ?></td>
                <td><?= statusBadge($m['status']) ?></td>
                <td><?= number_format($prev,2) ?></td>
                <td class="text-muted-aq">—</td>
                <td>
                    <div style="display:flex; gap:6px;">
                        <button class="btn-aq-outline" style="padding:6px 12px;" onclick='editMeter(<?= json_encode($m) ?>)'>Edit</button>
                        <button class="btn-aq-outline" style="padding:6px 12px; color:var(--aq-danger); border-color:var(--aq-danger);" data-confirm="Delete this meter?" onclick="deleteMeter(<?= (int)$m['id'] ?>)">Delete</button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="aq-modal-backdrop" id="meterModal">
    <div class="aq-modal">
        <div class="aq-modal-header">
            <h3 style="font-size:1.05rem;" id="meterModalTitle">Add meter</h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="cursor:pointer" onclick="AQ.closeModal('meterModal')"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </div>
        <form id="meterForm">
            <div class="aq-modal-body">
                <input type="hidden" id="meter_id">
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Customer</label>
                    <select id="customer_id" class="form-select-aq" required>
                        <option value="">Select a customer…</option>
                        <?php foreach ($customerList as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"><?= e($c['full_name']) ?> (<?= e($c['customer_code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Installation date</label>
                    <input type="date" id="installation_date" class="form-control-aq" required>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Initial reading (m³)</label>
                    <input type="number" step="0.01" id="initial_reading" class="form-control-aq" value="0">
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Status</label>
                    <select id="status" class="form-select-aq">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="replaced">Replaced</option>
                    </select>
                </div>
                <div>
                    <label class="form-label-aq">Location note</label>
                    <input type="text" id="location_note" class="form-control-aq" placeholder="e.g. Front yard, near gate">
                </div>
            </div>
            <div class="aq-modal-footer">
                <button type="button" class="btn-aq-outline" onclick="AQ.closeModal('meterModal')">Cancel</button>
                <button type="submit" class="btn-aq-primary">Save meter</button>
            </div>
        </form>
    </div>
</div>

<script>
function resetMeterForm() {
    document.getElementById('meterForm').reset();
    document.getElementById('meter_id').value = '';
    document.getElementById('meterModalTitle').textContent = 'Add meter';
    document.getElementById('customer_id').disabled = false;
}

function editMeter(m) {
    resetMeterForm();
    document.getElementById('meterModalTitle').textContent = 'Edit meter — ' + m.meter_number;
    document.getElementById('meter_id').value = m.id;
    document.getElementById('customer_id').value = m.customer_id;
    document.getElementById('customer_id').disabled = true;
    document.getElementById('installation_date').value = m.installation_date;
    document.getElementById('initial_reading').value = m.initial_reading;
    document.getElementById('status').value = m.status;
    document.getElementById('location_note').value = m.location_note || '';
    AQ.openModal('meterModal');
}

document.getElementById('meterForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('meter_id').value;
    const payload = {
        customer_id: document.getElementById('customer_id').value,
        installation_date: document.getElementById('installation_date').value,
        initial_reading: document.getElementById('initial_reading').value,
        status: document.getElementById('status').value,
        location_note: document.getElementById('location_note').value,
    };
    const url = id ? `<?= url('admin/meters') ?>/${id}/update` : `<?= url('admin/meters') ?>`;
    const res = await AQ.request(url, { method: 'POST', body: payload });
    if (res.success) { AQ.toast(res.message); AQ.closeModal('meterModal'); setTimeout(() => location.reload(), 600); }
    else AQ.toast(res.message || 'Something went wrong.', 'error');
});

async function deleteMeter(id) {
    const res = await AQ.request(`<?= url('admin/meters') ?>/${id}/delete`, { method: 'POST', body: {} });
    if (res.success) { AQ.toast(res.message); setTimeout(() => location.reload(), 600); }
    else AQ.toast(res.message || 'Delete failed.', 'error');
}
</script>
