<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: buy_lottery.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db_config.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id,username,password,fullname FROM users WHERE username=?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        header('Location: buy_lottery.php'); exit;
    } else {
        $error = 'Username or Password is not correct';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>LuckyStar – Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --cream:  #fdf8f0;
  --white:  #ffffff;
  --red:    #c0392b;
  --red2:   #e74c3c;
  --gold:   #d4a017;
  --gold2:  #f0c040;
  --ink:    #1a1a2e;
  --gray:   #6b7280;
  --border: #e5ddd0;
  --shadow: rgba(192,57,43,.12);
}
*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
body {
  min-height:100vh;
  font-family:'Nunito',sans-serif;
  background: var(--cream);
  display:flex;
  overflow:hidden;
  position:relative;
}

/* Decorative circles */
body::before {
  content:'';
  position:fixed; top:-120px; right:-120px;
  width:420px; height:420px;
  border-radius:50%;
  background: radial-gradient(circle, rgba(212,160,23,.18), transparent 70%);
  pointer-events:none;
}
body::after {
  content:'';
  position:fixed; bottom:-100px; left:-100px;
  width:360px; height:360px;
  border-radius:50%;
  background: radial-gradient(circle, rgba(192,57,43,.10), transparent 70%);
  pointer-events:none;
}

/* LEFT PANEL */
.left-panel {
  width: 52%;
  background: linear-gradient(145deg, var(--red) 0%, #8b1a1a 100%);
  display:flex; flex-direction:column; justify-content:center; align-items:center;
  padding: 3rem;
  position: relative;
  overflow: hidden;
}
.left-panel::before {
  content:'';
  position:absolute; inset:0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.brand-area { text-align:center; position:relative; z-index:1; }
.brand-star { font-size:5rem; filter:drop-shadow(0 4px 20px rgba(0,0,0,.3)); animation:float 3s ease-in-out infinite; }
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
.brand-name {
  font-family:'Playfair Display',serif;
  font-size:3.5rem; font-weight:900;
  color:#fff;
  letter-spacing:2px;
  line-height:1;
  margin-top:.5rem;
  text-shadow: 0 2px 20px rgba(0,0,0,.25);
}
.brand-tagline {
  color:rgba(255,255,255,.7);
  font-size:.9rem;
  letter-spacing:4px;
  text-transform:uppercase;
  margin-top:.6rem;
}
.brand-divider {
  width:60px; height:2px;
  background:linear-gradient(90deg, transparent, var(--gold2), transparent);
  margin:1.5rem auto;
}
.feature-list { list-style:none; text-align:left; }
.feature-list li {
  color:rgba(255,255,255,.85);
  font-size:.9rem;
  padding:.4rem 0;
  display:flex; align-items:center; gap:.7rem;
}
.feature-list li span { font-size:1.1rem; }

/* RIGHT PANEL */
.right-panel {
  width:48%;
  display:flex; align-items:center; justify-content:center;
  padding:3rem 2.5rem;
  background:var(--white);
}
.form-box { width:100%; max-width:380px; animation:slideIn .5s ease; }
@keyframes slideIn { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }

.form-header { margin-bottom:2.5rem; }
.form-header h2 {
  font-family:'Playfair Display',serif;
  font-size:2rem; font-weight:700;
  color:var(--ink); line-height:1.2;
}
.form-header p { color:var(--gray); margin-top:.4rem; font-size:.95rem; }

.field { margin-bottom:1.4rem; }
.field label {
  display:block; font-size:.78rem;
  text-transform:uppercase; letter-spacing:1.5px;
  color:var(--gray); margin-bottom:.5rem; font-weight:600;
}
.input-wrap { position:relative; }
.input-wrap .icon {
  position:absolute; left:.9rem; top:50%; transform:translateY(-50%);
  font-size:1rem; opacity:.5; pointer-events:none;
}
.field input {
  width:100%; padding:.8rem 1rem .8rem 2.6rem;
  border:1.5px solid var(--border);
  border-radius:10px;
  font-family:'Nunito',sans-serif; font-size:.95rem;
  color:var(--ink); background:var(--cream);
  outline:none; transition:all .2s;
}
.field input:focus {
  border-color:var(--red);
  background:#fff;
  box-shadow:0 0 0 3px rgba(192,57,43,.1);
}

.error-box {
  background:#fff5f5; border:1.5px solid #fca5a5;
  border-radius:10px; padding:.7rem 1rem;
  color:#dc2626; font-size:.88rem; margin-bottom:1.2rem;
  display:flex; align-items:center; gap:.5rem;
}

.btn-login {
  width:100%; padding:.9rem;
  background:linear-gradient(135deg, var(--red), var(--red2));
  border:none; border-radius:10px;
  color:#fff; font-family:'Nunito',sans-serif;
  font-size:1rem; font-weight:700; letter-spacing:.5px;
  cursor:pointer; transition:all .2s;
  box-shadow:0 4px 16px rgba(192,57,43,.3);
}
.btn-login:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(192,57,43,.4); }
.btn-login:active { transform:translateY(0); }

.form-footer { text-align:center; margin-top:1.8rem; color:var(--gray); font-size:.9rem; }
.form-footer a { color:var(--red); text-decoration:none; font-weight:700; }
.form-footer a:hover { text-decoration:underline; }

.gold-line {
  height:3px;
  background:linear-gradient(90deg, var(--gold), var(--gold2), var(--gold));
  border-radius:2px; margin:2rem 0;
}

@media(max-width:700px){
  body { flex-direction:column; }
  .left-panel,.right-panel { width:100%; }
  .left-panel { padding:2rem; min-height:260px; }
  .brand-name { font-size:2.5rem; }
}
</style>
</head>
<body>

<!-- LEFT BRAND PANEL -->
<div class="left-panel">
  <div class="brand-area">
    <div class="brand-star">⭐</div>
    <div class="brand-name">LuckyStar</div>
    <div class="brand-tagline">Premium Lottery</div>
    <div class="brand-divider"></div>
    <ul class="feature-list">
      <li><span>🎯</span> 6-digit jackpot up to ฿6,000,000</li>
      <li><span>🏆</span> 3-front & 3-tail prizes ฿4,000/unit</li>
      <li><span>🔒</span> Secure & instant results</li>
      <li><span>💳</span> Only ฿80 per unit</li>
    </ul>
  </div>
</div>

<!-- RIGHT LOGIN PANEL -->
<div class="right-panel">
  <div class="form-box">
    <div class="form-header">
      <h2>Welcome back 👋</h2>
      <p>Sign in to your LuckyStar account</p>
    </div>

    <?php if ($error): ?>
    <div class="error-box">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="field">
        <label>Username</label>
        <div class="input-wrap">
          <span class="icon">👤</span>
          <input type="text" name="username" placeholder="Your username" required autocomplete="username">
        </div>
      </div>
      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <span class="icon">🔑</span>
          <input type="password" name="password" placeholder="Your password" required autocomplete="current-password">
        </div>
      </div>
      <button type="submit" class="btn-login">Sign In →</button>
    </form>

    <div class="gold-line"></div>
    <div class="form-footer">
      New to LuckyStar? <a href="register.php">Create an account</a>
    </div>
  </div>
</div>

</body>
</html>
