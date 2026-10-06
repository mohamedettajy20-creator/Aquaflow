<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
    <div>
        <h1 class="font-display" style="font-size:1.5rem;">Field Agents</h1>
        <p class="text-muted-aq" style="font-size:.88rem;">Manage agents responsible for meter readings.</p>
    </div>
    <button class="btn-aq-primary" onclick="AQ.openModal('agentModal')">+ Add agent</button>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px;">
    <?php foreach ($agents as $a): ?>
        <div class="aq-card aq-card-body">
            <div style="display:flex; gap:14px; align-items:center; margin-bottom:14px;">
                <div style="width:46px;height:46px;border-radius:13px;background:linear-gradient(135deg,var(--aq-primary),var(--aq-aqua));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-family:var(--font-display);">
                    <?= e(mb_substr($a['full_name'],0,1)) ?>
                </div>
                <div>
                    <div style="font-weight:600;"><?= e($a['full_name']) ?></div>
                    <div class="text-muted-aq" style="font-size:.78rem;"><?= e($a['employee_code']) ?></div>
                </div>
            </div>
            <div style="font-size:.84rem; color:var(--aq-text-muted); line-height:1.8;">
                <div>📍 Zone: <?= e($a['zone'] ?? '—') ?></div>
                <div>✉️ <?= e($a['email']) ?></div>
                <div>👥 <?= (int)$a['customer_count'] ?> assigned customers</div>
                <div><?= statusBadge($a['status']) ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="aq-modal-backdrop" id="agentModal">
    <div class="aq-modal">
        <div class="aq-modal-header">
            <h3 style="font-size:1.05rem;">Add agent</h3>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="cursor:pointer" onclick="AQ.closeModal('agentModal')"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </div>
        <form id="agentForm">
            <div class="aq-modal-body">
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Full name</label>
                    <input type="text" id="a_full_name" class="form-control-aq" required>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Email</label>
                    <input type="email" id="a_email" class="form-control-aq" required>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Password</label>
                    <input type="password" id="a_password" class="form-control-aq" required>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="form-label-aq">Phone</label>
                    <input type="text" id="a_phone" class="form-control-aq">
                </div>
                <div>
                    <label class="form-label-aq">Zone / sector</label>
                    <input type="text" id="a_zone" class="form-control-aq" placeholder="e.g. Gueliz">
                </div>
            </div>
            <div class="aq-modal-footer">
                <button type="button" class="btn-aq-outline" onclick="AQ.closeModal('agentModal')">Cancel</button>
                <button type="submit" class="btn-aq-primary">Save agent</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('agentForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
        full_name: document.getElementById('a_full_name').value,
        email: document.getElementById('a_email').value,
        password: document.getElementById('a_password').value,
        phone: document.getElementById('a_phone').value,
        zone: document.getElementById('a_zone').value,
    };
    const res = await AQ.request(`<?= url('admin/agents') ?>`, { method: 'POST', body: payload });
    if (res.success) { AQ.toast(res.message); AQ.closeModal('agentModal'); setTimeout(() => location.reload(), 600); }
    else AQ.toast(res.message || 'Something went wrong.', 'error');
});
</script>
