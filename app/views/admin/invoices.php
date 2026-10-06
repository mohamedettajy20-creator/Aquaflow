<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <div>
        <h1 class="font-display" style="font-size:1.5rem;">Invoices</h1>
        <p class="text-muted-aq" style="font-size:.88rem;">All generated invoices across every billing period.</p>
    </div>
</div>

<div class="aq-card" style="margin-bottom:18px;">
    <div class="aq-card-body" style="padding:16px 22px; display:flex; gap:10px; flex-wrap:wrap;">
        <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap;">
            <input type="text" name="q" class="form-control-aq" placeholder="Search invoice # or customer…" value="<?= e($filters['search'] ?? '') ?>" style="max-width:280px;">
            <select name="status" class="form-select-aq" style="max-width:180px;" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <?php foreach (['unpaid','paid','late','cancelled'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn-aq-outline" type="submit">Filter</button>
        </form>
    </div>
</div>

<div class="aq-card">
    <div style="overflow-x:auto;">
    <table class="aq-table">
        <thead><tr><th>Invoice #</th><th>Customer</th><th>Meter</th><th>Consumption</th><th>Total</th><th>Due date</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($invoices)): ?>
            <tr><td colspan="8" class="text-muted-aq" style="text-align:center; padding:30px;">No invoices found.</td></tr>
        <?php endif; ?>
        <?php foreach ($invoices as $inv): ?>
            <tr>
                <td style="font-weight:600;"><?= e($inv['invoice_number']) ?></td>
                <td><?= e($inv['customer_name']) ?></td>
                <td><?= e($inv['meter_number']) ?></td>
                <td><?= number_format($inv['consumption'],2) ?> m³</td>
                <td style="font-weight:600;"><?= formatMoney($inv['total_amount']) ?></td>
                <td><?= formatDate($inv['due_date']) ?></td>
                <td><?= statusBadge($inv['status']) ?></td>
                <td>
                    <?php if ($inv['status'] !== 'paid'): ?>
                        <button class="btn-aq-outline" style="padding:6px 12px;" onclick="payInvoice(<?= (int)$inv['id'] ?>, <?= (float)$inv['total_amount'] ?>, '<?= e($inv['invoice_number']) ?>')">Record payment</button>
                    <?php else: ?>
                        <span class="text-muted-aq" style="font-size:.8rem;">Settled</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="aq-modal-backdrop" id="payModal">
    <div class="aq-modal">
        <div class="aq-modal-header">
            <h3 style="font-size:1.05rem;" id="payModalTitle">Record payment</h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="cursor:pointer" onclick="AQ.closeModal('payModal')"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </div>
        <form id="payForm">
            <div class="aq-modal-body">
                <input type="hidden" id="pay_invoice_id">
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Amount (MAD)</label>
                    <input type="number" step="0.01" id="pay_amount" class="form-control-aq" required>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Method</label>
                    <select id="pay_method" class="form-select-aq">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank transfer</option>
                        <option value="mobile_payment">Mobile payment</option>
                    </select>
                </div>
                <div>
                    <label class="form-label-aq">Reference (optional)</label>
                    <input type="text" id="pay_reference" class="form-control-aq" placeholder="Transaction ID">
                </div>
            </div>
            <div class="aq-modal-footer">
                <button type="button" class="btn-aq-outline" onclick="AQ.closeModal('payModal')">Cancel</button>
                <button type="submit" class="btn-aq-primary">Confirm payment</button>
            </div>
        </form>
    </div>
</div>

<script>
function payInvoice(id, amount, number) {
    document.getElementById('pay_invoice_id').value = id;
    document.getElementById('pay_amount').value = amount;
    document.getElementById('payModalTitle').textContent = 'Record payment — ' + number;
    AQ.openModal('payModal');
}
document.getElementById('payForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
        invoice_id: document.getElementById('pay_invoice_id').value,
        amount: document.getElementById('pay_amount').value,
        method: document.getElementById('pay_method').value,
        reference: document.getElementById('pay_reference').value,
    };
    const res = await AQ.request(`<?= url('admin/payments') ?>`, { method: 'POST', body: payload });
    if (res.success) { AQ.toast(res.message); AQ.closeModal('payModal'); setTimeout(() => location.reload(), 600); }
    else AQ.toast(res.message || 'Something went wrong.', 'error');
});
</script>
