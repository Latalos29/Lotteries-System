<?php
// index.php  –  Login Page
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: buy_lottery.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db_config.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, username, password, fullname FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['fullname'] = $user['fullname'];
        header('Location: buy_lottery.php');
        exit;
    } else {
        $error = 'Username or Password is not correct';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LottoVerse – Login</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root {
    --gold:   #f5c842;
    --gold2:  #e8a800;
    --dark:   #0a0a0f;
    --card:   #12121a;
    --border: #2a2a3a;
    --text:   #e8e8f0;
    --muted:  #7070a0;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    min-height: 100vh;
    background: var(--dark);
    font-family: 'DM Sans', sans-serif;
    color: var(--text);
    display: flex;
    align-items: center;
    justify-content: center;
    background-image:
      radial-gradient(ellipse 80% 60% at 50% -10%, rgba(245,200,66,.18) 0%, transparent 60%),
      repeating-linear-gradient(0deg, transparent, transparent 40px, rgba(255,255,255,.02) 40px, rgba(255,255,255,.02) 41px),
      repeating-linear-gradient(90deg, transparent, transparent 40px, rgba(255,255,255,.02) 40px, rgba(255,255,255,.02) 41px);
  }
  .wrapper {
    width: 100%;
    max-width: 440px;
    padding: 2rem;
    animation: fadeUp .6s ease both;
  }
  @keyframes fadeUp {
    from { opacity:0; transform:translateY(24px); }
    to   { opacity:1; transform:translateY(0); }
  }
  .logo {
    text-align: center;
    margin-bottom: 2.5rem;
  }
  .logo-icon {
    font-size: 3.5rem;
    line-height:1;
    filter: drop-shadow(0 0 20px rgba(245,200,66,.5));
  }
  .logo h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 3rem;
    letter-spacing: 4px;
    color: var(--gold);
    text-shadow: 0 0 30px rgba(245,200,66,.4);
    line-height: 1;
  }
  .logo p { color: var(--muted); font-size: .85rem; letter-spacing: 2px; text-transform: uppercase; margin-top: 4px; }

  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 2.5rem;
    box-shadow: 0 24px 60px rgba(0,0,0,.5), 0 0 0 1px rgba(245,200,66,.05);
  }
  .card h2 { font-size: 1.4rem; font-weight: 600; margin-bottom: 1.8rem; }

  .field { margin-bottom: 1.2rem; }
  .field label { display: block; font-size: .8rem; text-transform: uppercase; letter-spacing: 1.5px; color: var(--muted); margin-bottom: .5rem; }
  .field input {
    width: 100%;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    padding: .75rem 1rem;
    font-size: .95rem;
    font-family: inherit;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
  }
  .field input:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(245,200,66,.15);
  }

  .btn-primary {
    width: 100%;
    background: linear-gradient(135deg, var(--gold), var(--gold2));
    border: none;
    border-radius: 8px;
    color: #0a0a0f;
    font-family: inherit;
    font-size: 1rem;
    font-weight: 700;
    padding: .85rem;
    cursor: pointer;
    letter-spacing: 1px;
    text-transform: uppercase;
    transition: transform .15s, box-shadow .2s;
    margin-top: .5rem;
  }
  .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(245,200,66,.35); }
  .btn-primary:active { transform: translateY(0); }

  .error-box {
    background: rgba(255,60,60,.12);
    border: 1px solid rgba(255,60,60,.3);
    border-radius: 8px;
    color: #ff6b6b;
    padding: .7rem 1rem;
    font-size: .9rem;
    margin-bottom: 1.2rem;
    display: flex; align-items: center; gap: .5rem;
  }

  .footer-link {
    text-align: center;
    margin-top: 1.5rem;
    color: var(--muted);
    font-size: .9rem;
  }
  .footer-link a {
    color: var(--gold);
    text-decoration: none;
    font-weight: 600;
    transition: opacity .2s;
  }
  .footer-link a:hover { opacity: .8; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="logo">
    <div class="logo-icon">🎰</div>
    <h1>LottoVerse</h1>
    <p>Your Lucky Numbers Await</p>
  </div>

  <div class="card">
    <h2>Welcome Back</h2>

    <?php if ($error): ?>
    <div class="error-box">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
      <div class="field">
        <label>Username</label>
        <input type="text" name="username" placeholder="Enter your username" required autocomplete="username">
      </div>
      <div class="field">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-primary">🔓 Sign In</button>
    </form>
  </div>

  <div class="footer-link">
    Don't have an account? <a href="register.php">Register here</a>
  </div>
</div>
</body>
</html>
