<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <div>
        <h1 class="font-display" style="font-size:1.5rem;">Customers</h1>
        <p class="text-muted-aq" style="font-size:.88rem;">Manage subscriber accounts and meter assignments.</p>
    </div>
    <button class="btn-aq-primary" onclick="AQ.openModal('customerModal')">+ Add customer</button>
</div>

<div class="aq-card" style="margin-bottom:18px;">
    <div class="aq-card-body" style="padding:16px 22px;">
        <form method="GET" style="display:flex; gap:10px;">
            <input type="text" name="q" class="form-control-aq" placeholder="Search by name, email, code, address…" value="<?= e($search) ?>" style="max-width:340px;">
            <button class="btn-aq-outline" type="submit">Search</button>
        </form>
    </div>
</div>

<div class="aq-card">
    <div style="overflow-x:auto;">
    <table class="aq-table">
        <thead><tr><th>Customer</th><th>Code</th><th>Address</th><th>Type</th><th>Agent</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($customers)): ?>
            <tr><td colspan="7" class="text-muted-aq" style="text-align:center; padding:30px;">No customers found.</td></tr>
        <?php endif; ?>
        <?php foreach ($customers as $c): ?>
            <tr>
                <td>
                    <div style="font-weight:600;"><?= e($c['full_name']) ?></div>
                    <div class="text-muted-aq" style="font-size:.78rem;"><?= e($c['email']) ?></div>
                </td>
                <td><?= e($c['customer_code']) ?></td>
                <td><?= e($c['address']) ?><?= $c['city'] ? ', '.e($c['city']) : '' ?></td>
                <td style="text-transform:capitalize;"><?= e($c['customer_type']) ?></td>
                <td><?= e($c['agent_name'] ?? '—') ?></td>
                <td><?= statusBadge($c['status']) ?></td>
                <td>
                    <div style="display:flex; gap:6px;">
                        <button class="btn-aq-outline" style="padding:6px 12px;" onclick='editCustomer(<?= json_encode($c) ?>)'>Edit</button>
                        <button class="btn-aq-outline" style="padding:6px 12px;" onclick="toggleStatus(<?= (int)$c['id'] ?>)">Toggle</button>
                        <button class="btn-aq-outline" style="padding:6px 12px; color:var(--aq-danger); border-color:var(--aq-danger);" data-confirm="Delete this customer permanently?" onclick="deleteCustomer(<?= (int)$c['id'] ?>)">Delete</button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<div style="display:flex; gap:6px; margin-top:16px; justify-content:center;">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="?q=<?= urlencode($search) ?>&page=<?= $p ?>" class="btn-aq-outline" style="padding:7px 13px; <?= $p === $page ? 'background:var(--aq-primary);color:#fff;border-color:var(--aq-primary);' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Customer Modal (Create / Edit) -->
<div class="aq-modal-backdrop" id="customerModal">
    <div class="aq-modal">
        <div class="aq-modal-header">
            <h3 style="font-size:1.05rem;" id="customerModalTitle">Add customer</h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="cursor:pointer" onclick="AQ.closeModal('customerModal')"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </div>
        <form id="customerForm">
            <div class="aq-modal-body">
                <input type="hidden" id="customer_id">
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Full name</label>
                    <input type="text" id="full_name" class="form-control-aq" required>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Email</label>
                    <input type="email" id="email" class="form-control-aq" required>
                </div>
                <div style="margin-bottom:14px;" id="passwordField">
                    <label class="form-label-aq">Password</label>
                    <input type="password" id="password" class="form-control-aq" placeholder="Min. 6 characters">
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Phone</label>
                    <input type="text" id="phone" class="form-control-aq">
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Address</label>
                    <input type="text" id="address" class="form-control-aq" required>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                    <div>
                        <label class="form-label-aq">City</label>
                        <input type="text" id="city" class="form-control-aq">
                    </div>
                    <div>
                        <label class="form-label-aq">Postal code</label>
                        <input type="text" id="postal_code" class="form-control-aq">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div>
                        <label class="form-label-aq">Type</label>
                        <select id="customer_type" class="form-select-aq">
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="industrial">Industrial</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label-aq">Assigned agent</label>
                        <select id="agent_id" class="form-select-aq">
                            <option value="">— None —</option>
                            <?php foreach ($agentList as $a): ?>
                                <option value="<?= (int)$a['id'] ?>"><?= e($a['full_name']) ?> (<?= e($a['employee_code']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="aq-modal-footer">
                <button type="button" class="btn-aq-outline" onclick="AQ.closeModal('customerModal')">Cancel</button>
                <button type="submit" class="btn-aq-primary">Save customer</button>
            </div>
        </form>
    </div>
</div>

<script>
function resetCustomerForm() {
    document.getElementById('customerForm').reset();
    document.getElementById('customer_id').value = '';
    document.getElementById('customerModalTitle').textContent = 'Add customer';
    document.getElementById('passwordField').style.display = 'block';
}

function editCustomer(c) {
    resetCustomerForm();
    document.getElementById('customerModalTitle').textContent = 'Edit customer';
    document.getElementById('customer_id').value = c.id;
    document.getElementById('full_name').value = c.full_name;
    document.getElementById('email').value = c.email;
    document.getElementById('phone').value = c.phone || '';
    document.getElementById('address').value = c.address;
    document.getElementById('city').value = c.city || '';
    document.getElementById('postal_code').value = c.postal_code || '';
    document.getElementById('customer_type').value = c.customer_type;
    document.getElementById('agent_id').value = c.agent_id || '';
    document.getElementById('passwordField').style.display = 'none';
    AQ.openModal('customerModal');
}

document.getElementById('customerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('customer_id').value;
    const payload = {
        full_name: document.getElementById('full_name').value,
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        phone: document.getElementById('phone').value,
        address: document.getElementById('address').value,
        city: document.getElementById('city').value,
        postal_code: document.getElementById('postal_code').value,
        customer_type: document.getElementById('customer_type').value,
        agent_id: document.getElementById('agent_id').value,
    };
    const url = id ? `<?= url('admin/customers') ?>/${id}/update` : `<?= url('admin/customers') ?>`;
    const res = await AQ.request(url, { method: 'POST', body: payload });
    if (res.success) {
        AQ.toast(res.message);
        AQ.closeModal('customerModal');
        setTimeout(() => location.reload(), 600);
    } else {
        AQ.toast(res.message || 'Something went wrong.', 'error');
    }
});

async function deleteCustomer(id) {
    const res = await AQ.request(`<?= url('admin/customers') ?>/${id}/delete`, { method: 'POST', body: {} });
    if (res.success) { AQ.toast(res.message); setTimeout(() => location.reload(), 600); }
    else AQ.toast(res.message || 'Delete failed.', 'error');
}

async function toggleStatus(id) {
    const res = await AQ.request(`<?= url('admin/customers') ?>/${id}/toggle`, { method: 'POST', body: {} });
    if (res.success) { AQ.toast('Status updated to ' + res.status); setTimeout(() => location.reload(), 600); }
    else AQ.toast('Failed to update status.', 'error');
}

document.querySelector('[onclick="AQ.openModal(\'customerModal\')"]').addEventListener('click', resetCustomerForm);
</script>
