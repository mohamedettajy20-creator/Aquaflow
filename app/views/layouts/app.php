<?php
$user = currentUser();
$role = Auth::role();
$notifModel = new NotificationModel();
$unread = Auth::check() ? $notifModel->unreadCount(Auth::id()) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
<title><?= e($pageTitle ?? 'Dashboard') ?> · AquaFlow</title>
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="aq-shell">

    <aside class="aq-sidebar">
        <div class="aq-sidebar-brand">
            <svg class="drop" viewBox="0 0 24 24" fill="none"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14Z" fill="url(#g1)"/><defs><linearGradient id="g1" x1="5" y1="2" x2="19" y2="23"><stop stop-color="#22D3EE"/><stop offset="1" stop-color="#0B6BFF"/></linearGradient></defs></svg>
            AquaFlow
        </div>

        <nav class="aq-nav">
            <?php if ($role === 'admin'): ?>
                <div class="aq-nav-section">Overview</div>
                <a href="<?= url('admin/dashboard') ?>" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/></svg>
                    Dashboard
                </a>
                <div class="aq-nav-section">Management</div>
                <a href="<?= url('admin/customers') ?>" class="<?= ($active ?? '') === 'customers' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-4 3-6 7-6s7 2 7 6"/><circle cx="18" cy="8" r="2.5"/><path d="M22 20c0-3-2-5-4.5-5.5"/></svg>
                    Customers
                </a>
                <a href="<?= url('admin/agents') ?>" class="<?= ($active ?? '') === 'agents' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M17 3.5a4 4 0 0 1 0 7.8"/></svg>
                    Agents
                </a>
                <a href="<?= url('admin/meters') ?>" class="<?= ($active ?? '') === 'meters' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>
                    Meters
                </a>
                <a href="<?= url('admin/invoices') ?>" class="<?= ($active ?? '') === 'invoices' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2h9l5 5v15H6z"/><path d="M15 2v5h5"/><path d="M9 13h6M9 17h6"/></svg>
                    Invoices
                </a>
                <a href="<?= url('admin/payments') ?>" class="<?= ($active ?? '') === 'payments' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                    Payments
                </a>
                <div class="aq-nav-section">Insights</div>
                <a href="<?= url('admin/reports') ?>" class="<?= ($active ?? '') === 'reports' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg>
                    Reports
                </a>
                <div class="aq-nav-section">System</div>
                <a href="<?= url('admin/users') ?>" class="<?= ($active ?? '') === 'users' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>
                    Users
                </a>
                <a href="<?= url('admin/settings') ?>" class="<?= ($active ?? '') === 'settings' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.6V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.6-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3H9a1.7 1.7 0 0 0 1-1.6V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.6 1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9V9a1.7 1.7 0 0 0 1.6 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.6 1Z"/></svg>
                    Settings
                </a>
            <?php elseif ($role === 'agent'): ?>
                <div class="aq-nav-section">Overview</div>
                <a href="<?= url('agent/dashboard') ?>" class="active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/></svg>
                    Dashboard
                </a>
            <?php elseif ($role === 'customer'): ?>
                <div class="aq-nav-section">Overview</div>
                <a href="<?= url('customer/dashboard') ?>" class="active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/></svg>
                    Dashboard
                </a>
            <?php endif; ?>
        </nav>

        <div class="aq-sidebar-foot">
            &copy; <?= date('Y') ?> AquaFlow · v1.0<br>PFE Graduation Project
        </div>
    </aside>

    <div class="aq-main">
        <header class="aq-topbar">
            <div style="display:flex; align-items:center; gap:14px;">
                <svg class="d-none" onclick="AQ.toggleSidebar()" style="cursor:pointer;width:22px;height:22px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                <div class="aq-search">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" placeholder="Search customers, invoices, meters…" id="globalSearch">
                </div>
            </div>
            <div class="aq-topbar-actions">
                <div class="aq-icon-btn" onclick="AQ.toggleTheme()" title="Toggle dark mode">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                </div>
                <div class="aq-icon-btn" title="Notifications">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10 21a2 2 0 0 0 4 0"/></svg>
                    <?php if ($unread > 0): ?><span class="dot"></span><?php endif; ?>
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,var(--aq-primary),var(--aq-aqua));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-family:var(--font-display);">
                        <?= e(mb_substr($user['full_name'] ?? '?', 0, 1)) ?>
                    </div>
                    <div style="line-height:1.2;">
                        <div style="font-weight:600; font-size:.85rem;"><?= e($user['full_name'] ?? '') ?></div>
                        <div style="font-size:.72rem; color:var(--aq-text-muted); text-transform:capitalize;"><?= e($role ?? '') ?></div>
                    </div>
                    <a href="<?= url('logout') ?>" class="aq-icon-btn" title="Log out">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                    </a>
                </div>
            </div>
        </header>

        <main class="aq-content aq-fade-in">
            <?= $content ?>
        </main>
    </div>
</div>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
