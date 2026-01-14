<!---- Modern centered login card ---->
<style>
    .login-page{display:flex;min-height:80vh;align-items:center;justify-content:center;background:linear-gradient(135deg,#eef2ff,#fef3c7);}
    .login-card{width:360px;background:white;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.08);padding:28px}
    .login-card h2{margin:0 0 10px;font-size:20px}
    .login-field{margin-bottom:12px}
    .login-field label{display:block;font-size:13px;color:#374151;margin-bottom:6px}
    .login-field input{width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px}
    .login-actions{margin-top:14px}
    .btn-primary{background:#2563eb;color:#fff;border:none;padding:10px 14px;border-radius:6px;width:100%;cursor:pointer}
    .login-footer{margin-top:12px;font-size:13px;color:#6b7280;text-align:center}
</style>
<div class="login-page">
    <div class="login-card">
        <div style="text-align:center;margin-bottom:14px;">
            <img src="<?= BASE_URL ?>/public/images/logo.png" alt="Logo" style="height:48px;object-fit:contain;" onerror="this.style.display='none'">
        </div>
        <h2>Sign in to Dev B.O.A.R.D</h2>
        <form method="post" action="<?= BASE_URL ?>/index.php?action=login">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= $csrfToken ?>">
            <input type="hidden" name="action" value="login">
            <div class="login-field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="admin" required autofocus>
            </div>
            <div class="login-field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <div class="login-actions">
                <button class="btn-primary" type="submit">Sign In</button>
            </div>
        </form>
        <div class="login-footer">© <?= date('Y') ?> Dev B.O.A.R.D.</div>
    </div>
</div>