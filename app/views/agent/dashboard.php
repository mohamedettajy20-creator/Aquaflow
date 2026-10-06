<div style="margin-bottom:20px;">
    <h1 class="font-display" style="font-size:1.5rem;">Welcome, <?= e(explode(' ', currentUser()['full_name'])[0]) ?> 👋</h1>
    <p class="text-muted-aq" style="font-size:.88rem;">Your field agent dashboard — capture readings and manage your assigned customers.</p>
</div>

<div class="aq-card" style="margin-bottom:18px;">
    <div class="aq-card-header"><h3 style="font-size:1rem;">Your assigned customers</h3></div>
    <div style="overflow-x:auto;">
    <table class="aq-table">
        <thead><tr><th>Customer</th><th>Phone</th><th>Meter</th></tr></thead>
        <tbody>
        <?php if (empty($assigned)): ?>
            <tr><td colspan="3" class="text-muted-aq" style="text-align:center; padding:30px;">No customers assigned yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($assigned as $c): ?>
            <tr>
                <td style="font-weight:600;"><?= e($c['full_name']) ?></td>
                <td><?= e($c['phone'] ?? '—') ?></td>
                <td><?= e($c['meter_number'] ?? '—') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="aq-card aq-card-body" style="text-align:center; padding:40px;">
    <p class="text-muted-aq">📋 Reading capture with photo upload, previous/current values, and validation workflow lands in <strong>Phase 2</strong> of the build.</p>
</div>
