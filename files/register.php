<?php
// register.php  –  Registration Page
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: buy_lottery.php');
    exit;
}

$error   = '';
$success = '';

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
        // Check duplicate username/email
        $chk = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $chk->execute([$username, $email]);
        if ($chk->fetch()) {
            $error = 'Username or Email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $pdo->prepare('INSERT INTO users (username, password, fullname, email) VALUES (?,?,?,?)');
            $ins->execute([$username, $hash, $fullname, $email]);
            $success = 'Account created! <a href="index.php">Login now →</a>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LottoVerse – Register</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --gold:#f5c842; --gold2:#e8a800; --dark:#0a0a0f;
    --card:#12121a; --border:#2a2a3a; --text:#e8e8f0; --muted:#7070a0;
  }
  *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
  body {
    min-height:100vh; background:var(--dark);
    font-family:'DM Sans',sans-serif; color:var(--text);
    display:flex; align-items:center; justify-content:center;
    background-image:
      radial-gradient(ellipse 80% 60% at 50% -10%, rgba(245,200,66,.18) 0%, transparent 60%),
      repeating-linear-gradient(0deg,transparent,transparent 40px,rgba(255,255,255,.02) 40px,rgba(255,255,255,.02) 41px),
      repeating-linear-gradient(90deg,transparent,transparent 40px,rgba(255,255,255,.02) 40px,rgba(255,255,255,.02) 41px);
    padding: 2rem 0;
  }
  .wrapper { width:100%; max-width:480px; padding:2rem; animation:fadeUp .6s ease both; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:translateY(0)} }
  .logo { text-align:center; margin-bottom:2rem; }
  .logo h1 { font-family:'Bebas Neue',sans-serif; font-size:2.8rem; letter-spacing:4px; color:var(--gold); text-shadow:0 0 30px rgba(245,200,66,.4); }
  .logo p { color:var(--muted); font-size:.85rem; letter-spacing:2px; text-transform:uppercase; margin-top:4px; }
  .card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:2.5rem; box-shadow:0 24px 60px rgba(0,0,0,.5); }
  .card h2 { font-size:1.4rem; font-weight:600; margin-bottom:1.8rem; }
  .row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
  .field { margin-bottom:1.2rem; }
  .field label { display:block; font-size:.8rem; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:.5rem; }
  .field input { width:100%; background:rgba(255,255,255,.05); border:1px solid var(--border); border-radius:8px; color:var(--text); padding:.75rem 1rem; font-size:.95rem; font-family:inherit; transition:border-color .2s,box-shadow .2s; outline:none; }
  .field input:focus { border-color:var(--gold); box-shadow:0 0 0 3px rgba(245,200,66,.15); }
  .btn-primary { width:100%; background:linear-gradient(135deg,var(--gold),var(--gold2)); border:none; border-radius:8px; color:#0a0a0f; font-family:inherit; font-size:1rem; font-weight:700; padding:.85rem; cursor:pointer; letter-spacing:1px; text-transform:uppercase; transition:transform .15s,box-shadow .2s; margin-top:.5rem; }
  .btn-primary:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(245,200,66,.35); }
  .error-box { background:rgba(255,60,60,.12); border:1px solid rgba(255,60,60,.3); border-radius:8px; color:#ff6b6b; padding:.7rem 1rem; font-size:.9rem; margin-bottom:1.2rem; }
  .success-box { background:rgba(60,200,100,.12); border:1px solid rgba(60,200,100,.3); border-radius:8px; color:#5ddb85; padding:.7rem 1rem; font-size:.9rem; margin-bottom:1.2rem; }
  .success-box a { color:var(--gold); text-decoration:none; font-weight:600; }
  .footer-link { text-align:center; margin-top:1.5rem; color:var(--muted); font-size:.9rem; }
  .footer-link a { color:var(--gold); text-decoration:none; font-weight:600; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="logo">
    <h1>🎰 LottoVerse</h1>
    <p>Create Your Account</p>
  </div>

  <div class="card">
    <h2>New Account</h2>

    <?php if ($error): ?>
    <div class="error-box">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="success-box">✅ <?= $success ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
      <div class="field">
        <label>Full Name</label>
        <input type="text" name="fullname" placeholder="Your full name" required>
      </div>
      <div class="row">
        <div class="field">
          <label>Username</label>
          <input type="text" name="username" placeholder="Choose username" required>
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" name="email" placeholder="your@email.com" required>
        </div>
      </div>
      <div class="row">
        <div class="field">
          <label>Password</label>
          <input type="password" name="password" placeholder="Min 6 characters" required>
        </div>
        <div class="field">
          <label>Confirm Password</label>
          <input type="password" name="confirm" placeholder="Repeat password" required>
        </div>
      </div>
      <button type="submit" class="btn-primary">🎟️ Create Account</button>
    </form>
  </div>

  <div class="footer-link">
    Already have an account? <a href="index.php">Login here</a>
  </div>
</div>
</body>
</html>
