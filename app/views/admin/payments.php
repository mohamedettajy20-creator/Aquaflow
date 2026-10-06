<div style="margin-bottom:20px;">
    <h1 class="font-display" style="font-size:1.5rem;">Payments</h1>
    <p class="text-muted-aq" style="font-size:.88rem;">Full payment history recorded across all invoices.</p>
</div>

<div class="aq-card">
    <div style="overflow-x:auto;">
    <table class="aq-table">
        <thead><tr><th>Invoice #</th><th>Customer</th><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th>Paid at</th></tr></thead>
        <tbody>
        <?php if (empty($payments)): ?>
            <tr><td colspan="7" class="text-muted-aq" style="text-align:center; padding:30px;">No payments recorded yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($payments as $p): ?>
            <tr>
                <td style="font-weight:600;"><?= e($p['invoice_number']) ?></td>
                <td><?= e($p['customer_name']) ?></td>
                <td><?= formatMoney($p['amount']) ?></td>
                <td style="text-transform:capitalize;"><?= e(str_replace('_',' ',$p['method'])) ?></td>
                <td class="text-muted-aq"><?= e($p['reference'] ?? '—') ?></td>
                <td><?= statusBadge($p['status']) ?></td>
                <td><?= formatDate($p['paid_at'], 'd M Y, H:i') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
