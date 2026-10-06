<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
<title>Sign in · AquaFlow</title>
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="aq-auth-wrap">
    <div class="aq-auth-side">
        <svg viewBox="0 0 24 24" width="46" height="46" fill="none" style="margin-bottom:22px;"><path d="M12 2C12 2 5 11.5 5 16a7 7 0 0 0 14 0c0-4.5-7-14-7-14Z" fill="url(#g2)"/><defs><linearGradient id="g2" x1="5" y1="2" x2="19" y2="23"><stop stop-color="#22D3EE"/><stop offset="1" stop-color="#fff"/></linearGradient></defs></svg>
        <h1 class="font-display" style="color:#fff; font-size:2.3rem; max-width:460px;">Smart water management, from meter to invoice.</h1>
        <p style="color:rgba(255,255,255,0.75); max-width:420px; margin-top:14px; font-size:.95rem; line-height:1.6;">
            AquaFlow gives administrators, field agents, and customers one connected platform for readings, progressive billing, and payments.
        </p>
        <div style="margin-top:44px; display:flex; gap:28px;">
            <div>
                <div class="font-display" style="color:#fff;font-size:1.5rem;font-weight:700;">3</div>
                <div style="color:rgba(255,255,255,0.6);font-size:.78rem;">Role-based dashboards</div>
            </div>
            <div>
                <div class="font-display" style="color:#fff;font-size:1.5rem;font-weight:700;">100%</div>
                <div style="color:rgba(255,255,255,0.6);font-size:.78rem;">Automated billing</div>
            </div>
            <div>
                <div class="font-display" style="color:#fff;font-size:1.5rem;font-weight:700;">24/7</div>
                <div style="color:rgba(255,255,255,0.6);font-size:.78rem;">Consumption tracking</div>
            </div>
        </div>
    </div>

    <div class="aq-auth-form">
        <div class="aq-auth-card">
            <h2 class="font-display" style="font-size:1.6rem;margin-bottom:4px;">Welcome back</h2>
            <p class="text-muted-aq" style="font-size:.88rem;margin-bottom:26px;">Sign in to your AquaFlow account</p>

            <?php if (!empty($error)): ?>
                <div style="background:rgba(239,68,68,0.1); color:#c73030; padding:12px 16px; border-radius:12px; font-size:.85rem; margin-bottom:18px;">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= url('login') ?>">
                <?= Csrf::field() ?>
                <div style="margin-bottom:16px;">
                    <label class="form-label-aq">Email address</label>
                    <input type="email" name="email" class="form-control-aq" placeholder="you@aquaflow.local" required autofocus>
                </div>
                <div style="margin-bottom:22px;">
                    <label class="form-label-aq">Password</label>
                    <input type="password" name="password" class="form-control-aq" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn-aq-primary" style="width:100%; padding:12px;">Sign in</button>
            </form>

            <div style="margin-top:26px; padding-top:20px; border-top:1px solid var(--aq-border); font-size:.78rem; color:var(--aq-text-muted);">
                <strong>Demo accounts</strong> (password: <code>Passw0rd!</code>)<br>
                Admin: admin@aquaflow.local<br>
                Agent: sara.agent@aquaflow.local<br>
                Customer: fatima@example.com
            </div>
        </div>
    </div>
</div>
</body>
</html>
