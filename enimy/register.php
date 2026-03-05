<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: buy_lottery.php'); exit; }

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db_config.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if (!$username || !$password || !$fullname || !$email) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $chk = $pdo->prepare('SELECT id FROM users WHERE username=? OR email=?');
        $chk->execute([$username, $email]);
        if ($chk->fetch()) {
            $error = 'Username or Email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (username,password,fullname,email) VALUES (?,?,?,?)')->execute([$username,$hash,$fullname,$email]);
            $success = 'Account created! <a href="index.php">Sign in now →</a>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>LuckyStar – Register</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --cream:#fdf8f0; --white:#ffffff; --red:#c0392b; --red2:#e74c3c;
  --gold:#d4a017; --gold2:#f0c040; --ink:#1a1a2e; --gray:#6b7280;
  --border:#e5ddd0; --green:#16a34a;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body {
  min-height:100vh; font-family:'Nunito',sans-serif;
  background:var(--cream);
  background-image:
    radial-gradient(ellipse 60% 50% at 80% 20%, rgba(212,160,23,.12) 0%,transparent 60%),
    radial-gradient(ellipse 50% 40% at 10% 80%, rgba(192,57,43,.08) 0%,transparent 60%);
  display:flex; align-items:center; justify-content:center;
  padding:2rem;
}
.page-card {
  background:var(--white); border-radius:24px;
  box-shadow:0 20px 60px rgba(26,26,46,.08), 0 4px 20px rgba(192,57,43,.06);
  width:100%; max-width:580px; overflow:hidden;
  animation:slideUp .5s ease;
}
@keyframes slideUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}

.card-header {
  background:linear-gradient(135deg, var(--red), #8b1a1a);
  padding:2rem 2.5rem;
  display:flex; align-items:center; gap:1rem;
  position:relative; overflow:hidden;
}
.card-header::after {
  content:'⭐';
  position:absolute; right:1.5rem; top:50%;transform:translateY(-50%);
  font-size:5rem; opacity:.08;
}
.card-header h1 {
  font-family:'Playfair Display',serif;
  font-size:1.6rem; color:#fff; font-weight:700;
}
.card-header p { color:rgba(255,255,255,.7); font-size:.85rem; margin-top:.2rem; }
.back-link {
  display:inline-flex; align-items:center; gap:.4rem;
  color:rgba(255,255,255,.8); text-decoration:none;
  font-size:.85rem; margin-bottom:.4rem;
  transition:color .2s;
}
.back-link:hover{color:#fff;}

.card-body { padding:2.5rem; }

.gold-badge {
  background:linear-gradient(90deg,rgba(212,160,23,.12),rgba(240,192,64,.12));
  border:1px solid rgba(212,160,23,.3);
  border-radius:8px; padding:.5rem 1rem;
  font-size:.82rem; color:var(--gold); font-weight:600;
  margin-bottom:1.5rem; display:inline-block;
}

.grid2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
@media(max-width:500px){.grid2{grid-template-columns:1fr;}}

.field{margin-bottom:1.2rem;}
.field label{display:block;font-size:.78rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--gray);margin-bottom:.4rem;font-weight:600;}
.input-wrap{position:relative;}
.input-wrap .icon{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);font-size:.95rem;opacity:.45;pointer-events:none;}
.field input{
  width:100%;padding:.75rem 1rem .75rem 2.5rem;
  border:1.5px solid var(--border);border-radius:10px;
  font-family:'Nunito',sans-serif;font-size:.95rem;color:var(--ink);
  background:var(--cream);outline:none;transition:all .2s;
}
.field input:focus{border-color:var(--red);background:#fff;box-shadow:0 0 0 3px rgba(192,57,43,.1);}

.error-box{background:#fff5f5;border:1.5px solid #fca5a5;border-radius:10px;padding:.7rem 1rem;color:#dc2626;font-size:.88rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.5rem;}
.success-box{background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:.7rem 1rem;color:var(--green);font-size:.88rem;margin-bottom:1.2rem;}
.success-box a{color:var(--red);font-weight:700;text-decoration:none;}

.btn-register{
  width:100%;padding:.9rem;
  background:linear-gradient(135deg,var(--red),var(--red2));
  border:none;border-radius:10px;color:#fff;
  font-family:'Nunito',sans-serif;font-size:1rem;font-weight:700;
  cursor:pointer;transition:all .2s;
  box-shadow:0 4px 16px rgba(192,57,43,.25);
  margin-top:.3rem;
}
.btn-register:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(192,57,43,.35);}

.divider{height:1px;background:var(--border);margin:1.5rem 0;}
.form-footer{text-align:center;color:var(--gray);font-size:.9rem;}
.form-footer a{color:var(--red);font-weight:700;text-decoration:none;}

.strength-bar{height:4px;background:var(--border);border-radius:2px;margin-top:.4rem;overflow:hidden;}
.strength-fill{height:100%;border-radius:2px;width:0;transition:width .3s,background .3s;}
</style>
</head>
<body>
<div class="page-card">
  <div class="card-header">
    <div>
      <a href="index.php" class="back-link">← Back to Login</a>
      <h1>Create Account</h1>
      <p>Join thousands of winners on LuckyStar</p>
    </div>
  </div>

  <div class="card-body">
    <div class="gold-badge">⭐ Free registration · Instant access</div>

    <?php if($error): ?>
    <div class="error-box">⚠️ <?=htmlspecialchars($error)?></div>
    <?php endif; ?>
    <?php if($success): ?>
    <div class="success-box">✅ <?=$success?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="field">
        <label>Full Name</label>
        <div class="input-wrap">
          <span class="icon">👤</span>
          <input type="text" name="fullname" placeholder="Your full name" required>
        </div>
      </div>
      <div class="grid2">
        <div class="field">
          <label>Username</label>
          <div class="input-wrap">
            <span class="icon">🏷️</span>
            <input type="text" name="username" placeholder="Choose username" required>
          </div>
        </div>
        <div class="field">
          <label>Email</label>
          <div class="input-wrap">
            <span class="icon">📧</span>
            <input type="email" name="email" placeholder="your@email.com" required>
          </div>
        </div>
      </div>
      <div class="grid2">
        <div class="field">
          <label>Password</label>
          <div class="input-wrap">
            <span class="icon">🔑</span>
            <input type="password" name="password" id="pw" placeholder="Min 6 chars" required oninput="checkStrength(this.value)">
          </div>
          <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
        </div>
        <div class="field">
          <label>Confirm Password</label>
          <div class="input-wrap">
            <span class="icon">🔒</span>
            <input type="password" name="confirm" placeholder="Repeat password" required>
          </div>
        </div>
      </div>
      <button type="submit" class="btn-register">🌟 Create My Account</button>
    </form>

    <div class="divider"></div>
    <div class="form-footer">Already have an account? <a href="index.php">Sign in here</a></div>
  </div>
</div>

<script>
function checkStrength(v) {
  const fill = document.getElementById('strength-fill');
  let score = 0;
  if (v.length >= 6) score++;
  if (v.length >= 10) score++;
  if (/[A-Z]/.test(v) && /[0-9]/.test(v)) score++;
  const colors = ['#ef4444','#f97316','#22c55e'];
  const widths  = ['33%','66%','100%'];
  if(v.length===0){fill.style.width='0';return;}
  fill.style.width   = widths[score-1]||'20%';
  fill.style.background = colors[score-1]||'#ef4444';
}
</script>
</body>
</html>
