<?php
$settingsMap = [];
foreach ($settings as $s) { $settingsMap[$s['setting_key']] = $s['setting_value']; }
$tiers = json_decode($settingsMap['tier_pricing'] ?? '[]', true) ?: [];
?>
<div style="margin-bottom:20px;">
    <h1 class="font-display" style="font-size:1.5rem;">System Settings</h1>
    <p class="text-muted-aq" style="font-size:.88rem;">Company info, billing rules, and tiered water pricing.</p>
</div>

<form id="settingsForm">
<div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
    <div class="aq-card">
        <div class="aq-card-header"><h3 style="font-size:1rem;">Company</h3></div>
        <div class="aq-card-body">
            <div style="margin-bottom:14px;">
                <label class="form-label-aq">Company name</label>
                <input type="text" name="company_name" class="form-control-aq" value="<?= e($settingsMap['company_name'] ?? '') ?>">
            </div>
            <div>
                <label class="form-label-aq">Company address</label>
                <input type="text" name="company_address" class="form-control-aq" value="<?= e($settingsMap['company_address'] ?? '') ?>">
            </div>
        </div>
    </div>
    <div class="aq-card">
        <div class="aq-card-header"><h3 style="font-size:1rem;">Billing rules</h3></div>
        <div class="aq-card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:14px;">
                <div>
                    <label class="form-label-aq">Currency</label>
                    <input type="text" name="currency" class="form-control-aq" value="<?= e($settingsMap['currency'] ?? 'MAD') ?>">
                </div>
                <div>
                    <label class="form-label-aq">Tax rate (%)</label>
                    <input type="number" step="0.01" name="tax_rate" class="form-control-aq" value="<?= e($settingsMap['tax_rate'] ?? '7.00') ?>">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label class="form-label-aq">Invoice due (days)</label>
                    <input type="number" name="invoice_due_days" class="form-control-aq" value="<?= e($settingsMap['invoice_due_days'] ?? '15') ?>">
                </div>
                <div>
                    <label class="form-label-aq">Late fee (%)</label>
                    <input type="number" step="0.01" name="late_fee_percent" class="form-control-aq" value="<?= e($settingsMap['late_fee_percent'] ?? '5.00') ?>">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="aq-card" style="margin-bottom:18px;">
    <div class="aq-card-header">
        <h3 style="font-size:1rem;">Tiered water pricing</h3>
        <button type="button" class="btn-aq-outline" onclick="addTierRow()">+ Add tier</button>
    </div>
    <div class="aq-card-body">
        <p class="text-muted-aq" style="font-size:.82rem; margin-bottom:14px;">Consumption is billed progressively: the first block of m³ costs less per unit, and price increases as usage climbs into higher tiers. Leave "to" empty for the final, open-ended tier.</p>
        <div id="tierRows">
            <?php foreach ($tiers as $t): ?>
                <div class="tier-row" style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:10px; margin-bottom:10px;">
                    <input type="number" step="0.01" class="form-control-aq tier-from" placeholder="From (m³)" value="<?= e($t['from']) ?>">
                    <input type="number" step="0.01" class="form-control-aq tier-to" placeholder="To (m³) — blank = ∞" value="<?= $t['to'] !== null ? e($t['to']) : '' ?>">
                    <input type="number" step="0.0001" class="form-control-aq tier-price" placeholder="Price / m³" value="<?= e($t['price']) ?>">
                    <button type="button" class="btn-aq-outline" style="color:var(--aq-danger); border-color:var(--aq-danger);" onclick="this.parentElement.remove()">✕</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<button type="submit" class="btn-aq-primary">Save all settings</button>
</form>

<script>
function addTierRow() {
    const div = document.createElement('div');
    div.className = 'tier-row';
    div.style.cssText = 'display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:10px; margin-bottom:10px;';
    div.innerHTML = `
        <input type="number" step="0.01" class="form-control-aq tier-from" placeholder="From (m³)">
        <input type="number" step="0.01" class="form-control-aq tier-to" placeholder="To (m³) — blank = ∞">
        <input type="number" step="0.0001" class="form-control-aq tier-price" placeholder="Price / m³">
        <button type="button" class="btn-aq-outline" style="color:var(--aq-danger); border-color:var(--aq-danger);" onclick="this.parentElement.remove()">✕</button>
    `;
    document.getElementById('tierRows').appendChild(div);
}

document.getElementById('settingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const tiers = [...document.querySelectorAll('.tier-row')].map(row => ({
        from: parseFloat(row.querySelector('.tier-from').value || 0),
        to: row.querySelector('.tier-to').value === '' ? null : parseFloat(row.querySelector('.tier-to').value),
        price: parseFloat(row.querySelector('.tier-price').value || 0),
    }));

    const payload = {
        company_name: document.querySelector('[name=company_name]').value,
        company_address: document.querySelector('[name=company_address]').value,
        currency: document.querySelector('[name=currency]').value,
        tax_rate: document.querySelector('[name=tax_rate]').value,
        invoice_due_days: document.querySelector('[name=invoice_due_days]').value,
        late_fee_percent: document.querySelector('[name=late_fee_percent]').value,
        tier_pricing: JSON.stringify(tiers),
    };
    const res = await AQ.request(`<?= url('admin/settings') ?>`, { method: 'POST', body: payload });
    if (res.success) AQ.toast(res.message);
    else AQ.toast(res.message || 'Something went wrong.', 'error');
});
</script>
