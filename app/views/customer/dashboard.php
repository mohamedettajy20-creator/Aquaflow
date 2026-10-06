<div style="margin-bottom:20px;">
    <h1 class="font-display" style="font-size:1.5rem;">Welcome, <?= e(explode(' ', currentUser()['full_name'])[0]) ?> 👋</h1>
    <p class="text-muted-aq" style="font-size:.88rem;">Track your consumption, invoices, and payments.</p>
</div>

<div class="aq-card" style="margin-bottom:18px;">
    <div class="aq-card-header"><h3 style="font-size:1rem;">Your invoices</h3></div>
    <div style="overflow-x:auto;">
    <table class="aq-table">
        <thead><tr><th>Invoice #</th><th>Consumption</th><th>Total</th><th>Due date</th><th>Status</th></tr></thead>
        <tbody>
        <?php if (empty($invoices)): ?>
            <tr><td colspan="5" class="text-muted-aq" style="text-align:center; padding:30px;">No invoices yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($invoices as $i): ?>
            <tr>
                <td style="font-weight:600;"><?= e($i['invoice_number']) ?></td>
                <td><?= number_format($i['consumption'],2) ?> m³</td>
                <td><?= formatMoney($i['total_amount']) ?></td>
                <td><?= formatDate($i['due_date']) ?></td>
                <td><?= statusBadge($i['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="aq-card aq-card-body" style="text-align:center; padding:40px;">
    <p class="text-muted-aq">📊 Consumption charts, PDF invoice downloads, and payment history land in <strong>Phase 4</strong> of the build.</p>
</div>
