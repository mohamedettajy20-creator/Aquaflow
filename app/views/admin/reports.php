<div style="margin-bottom:20px;">
    <h1 class="font-display" style="font-size:1.5rem;">Reports & Statistics</h1>
    <p class="text-muted-aq" style="font-size:.88rem;">Revenue trends, top consumers, and outstanding balances.</p>
</div>

<div class="aq-card" style="margin-bottom:18px;">
    <div class="aq-card-header"><h3 style="font-size:1rem;">Revenue — last 12 months</h3></div>
    <div class="aq-card-body"><canvas id="revChart" height="90"></canvas></div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
    <div class="aq-card">
        <div class="aq-card-header"><h3 style="font-size:1rem;">Top consumers</h3></div>
        <div class="aq-card-body" style="padding-top:8px;">
            <?php foreach ($topConsumers as $i => $t): ?>
                <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--aq-border);">
                    <div><span class="text-muted-aq" style="margin-right:8px;">#<?= $i+1 ?></span><strong><?= e($t['full_name']) ?></strong> <span class="text-muted-aq" style="font-size:.78rem;">(<?= e($t['customer_code']) ?>)</span></div>
                    <div style="font-weight:600;"><?= number_format($t['total_consumption'],1) ?> m³</div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($topConsumers)): ?><p class="text-muted-aq">No data yet.</p><?php endif; ?>
        </div>
    </div>
    <div class="aq-card">
        <div class="aq-card-header"><h3 style="font-size:1rem;">Unpaid & late invoices</h3></div>
        <div class="aq-card-body" style="padding-top:8px; max-height:340px; overflow-y:auto;">
            <?php foreach (array_merge($unpaid, $late) as $inv): ?>
                <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid var(--aq-border); align-items:center;">
                    <div>
                        <div style="font-weight:600; font-size:.86rem;"><?= e($inv['invoice_number']) ?></div>
                        <div class="text-muted-aq" style="font-size:.76rem;"><?= e($inv['customer_name']) ?> · due <?= formatDate($inv['due_date']) ?></div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:600;"><?= formatMoney($inv['total_amount']) ?></div>
                        <?= statusBadge($inv['status']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($unpaid) && empty($late)): ?><p class="text-muted-aq">All invoices are settled 🎉</p><?php endif; ?>
        </div>
    </div>
</div>

<div style="margin-top:18px; display:flex; gap:10px;">
    <button class="btn-aq-outline" onclick="window.print()">Export view to PDF (print)</button>
</div>

<script>
new Chart(document.getElementById('revChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($r) => date('M Y', strtotime($r['ym'].'-01')), $revenueTrend)) ?>,
        datasets: [{
            label: 'Revenue (MAD)',
            data: <?= json_encode(array_map(fn($r) => (float)$r['revenue'], $revenueTrend)) ?>,
            backgroundColor: '#0B6BFF', borderRadius: 6, maxBarThickness: 34,
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
