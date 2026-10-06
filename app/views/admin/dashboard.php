<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:22px;">
    <div>
        <h1 class="font-display" style="font-size:1.6rem;">Welcome back, <?= e(explode(' ', currentUser()['full_name'])[0]) ?> 👋</h1>
        <p class="text-muted-aq" style="font-size:.9rem;">Here's what's happening across AquaFlow today.</p>
    </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:18px; margin-bottom:22px;">
    <div class="aq-card aq-stat">
        <div class="aq-stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-4 3-6 7-6s7 2 7 6"/></svg></div>
        <div class="label">Total Customers</div>
        <div class="value"><?= number_format($stats['total_customers']) ?></div>
        <div class="fill-track"><div class="fill-bar" style="width:80%"></div></div>
    </div>
    <div class="aq-card aq-stat">
        <div class="aq-stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg></div>
        <div class="label">Total Meters</div>
        <div class="value"><?= number_format($stats['total_meters']) ?></div>
        <div class="fill-track"><div class="fill-bar" style="width:70%"></div></div>
    </div>
    <div class="aq-card aq-stat">
        <div class="aq-stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14Z"/></svg></div>
        <div class="label">Monthly Consumption</div>
        <div class="value"><?= number_format($stats['monthly_consumption'],1) ?> m³</div>
        <div class="fill-track"><div class="fill-bar" style="width:55%"></div></div>
    </div>
    <div class="aq-card aq-stat">
        <div class="aq-stat-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg></div>
        <div class="label">Monthly Revenue</div>
        <div class="value"><?= formatMoney($stats['monthly_revenue']) ?></div>
        <div class="fill-track"><div class="fill-bar" style="width:65%"></div></div>
    </div>
    <div class="aq-card aq-stat">
        <div class="aq-stat-icon" style="background:linear-gradient(135deg,#10B981,#22D3EE)"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg></div>
        <div class="label">Paid Invoices</div>
        <div class="value"><?= number_format($stats['paid_invoices']) ?></div>
        <div class="fill-track"><div class="fill-bar" style="width:60%;background:linear-gradient(90deg,#10B981,#22D3EE)"></div></div>
    </div>
    <div class="aq-card aq-stat">
        <div class="aq-stat-icon" style="background:linear-gradient(135deg,#F59E0B,#EF4444)"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg></div>
        <div class="label">Unpaid / Late</div>
        <div class="value"><?= number_format($stats['unpaid_invoices'] + $stats['late_invoices']) ?></div>
        <div class="fill-track"><div class="fill-bar" style="width:40%;background:linear-gradient(90deg,#F59E0B,#EF4444)"></div></div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1.6fr 1fr; gap:18px; margin-bottom:18px;">
    <div class="aq-card">
        <div class="aq-card-header">
            <h3 style="font-size:1rem;">Revenue trend</h3>
            <span class="text-muted-aq" style="font-size:.78rem;">Last 6 months</span>
        </div>
        <div class="aq-card-body"><canvas id="revenueChart" height="110"></canvas></div>
    </div>
    <div class="aq-card">
        <div class="aq-card-header"><h3 style="font-size:1rem;">Invoice status</h3></div>
        <div class="aq-card-body"><canvas id="statusChart" height="150"></canvas></div>
    </div>
</div>

<div class="aq-card">
    <div class="aq-card-header"><h3 style="font-size:1rem;">Recent activity</h3></div>
    <div class="aq-card-body" style="padding-top:8px;">
        <?php if (empty($recentActivity)): ?>
            <p class="text-muted-aq" style="font-size:.85rem;">No recent activity yet.</p>
        <?php else: foreach ($recentActivity as $log): ?>
            <div style="display:flex; gap:12px; padding:10px 0; border-bottom:1px solid var(--aq-border);">
                <div style="width:34px;height:34px;border-radius:10px;background:rgba(11,107,255,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--aq-primary)" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>
                </div>
                <div>
                    <div style="font-size:.86rem;"><strong><?= e($log['full_name'] ?? 'System') ?></strong> — <?= e(str_replace('_',' ', $log['action'])) ?></div>
                    <div class="text-muted-aq" style="font-size:.76rem;"><?= e($log['description'] ?? '') ?> · <?= formatDate($log['created_at'], 'd M Y, H:i') ?></div>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<script>
const revCtx = document.getElementById('revenueChart');
new Chart(revCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(fn($r) => date('M Y', strtotime($r['ym'].'-01')), $revenueTrend)) ?>,
        datasets: [{
            label: 'Revenue (MAD)',
            data: <?= json_encode(array_map(fn($r) => (float)$r['revenue'], $revenueTrend)) ?>,
            borderColor: '#0B6BFF',
            backgroundColor: 'rgba(11,107,255,0.12)',
            fill: true, tension: 0.4, borderWidth: 3, pointRadius: 4, pointBackgroundColor: '#22D3EE',
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

const statusCtx = document.getElementById('statusChart');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_map(fn($r) => ucfirst($r['status']), $statusBreakdown)) ?>,
        datasets: [{
            data: <?= json_encode(array_map(fn($r) => (int)$r['c'], $statusBreakdown)) ?>,
            backgroundColor: ['#10B981', '#F59E0B', '#EF4444', '#94A3B8'],
            borderWidth: 0,
        }]
    },
    options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } }, cutout: '68%' }
});
</script>
