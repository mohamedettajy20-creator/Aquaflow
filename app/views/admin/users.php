<div style="margin-bottom:20px;">
    <h1 class="font-display" style="font-size:1.5rem;">System Users</h1>
    <p class="text-muted-aq" style="font-size:.88rem;">All accounts across every role: admins, agents, and customers.</p>
</div>

<div class="aq-card">
    <div style="overflow-x:auto;">
    <table class="aq-table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Status</th><th>Last login</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td style="font-weight:600;"><?= e($u['full_name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><span class="badge-soft-info" style="text-transform:capitalize;"><?= e($u['role']) ?></span></td>
                <td><?= e($u['phone'] ?? '—') ?></td>
                <td><?= statusBadge($u['status']) ?></td>
                <td class="text-muted-aq"><?= $u['last_login_at'] ? formatDate($u['last_login_at'],'d M Y, H:i') : 'Never' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
