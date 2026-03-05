<?php
// check_lottery.php  –  Check Lottery Result Page
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'db_config.php';
$userId   = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// Latest winning number
$win = $pdo->query('SELECT number, draw_date FROM winning_numbers ORDER BY draw_date DESC, id DESC LIMIT 1')->fetch();
$winNum   = $win ? $win['number'] : null;
$winFront = $winNum ? substr($winNum, 0, 3) : null;  // 3 หน้า
$winBack  = $winNum ? substr($winNum, 3, 3) : null;  // 3 ท้าย

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $check = trim($_POST['check_number'] ?? '');

    if (!preg_match('/^\d{6}$/', $check)) {
        $result = ['type' => 'error', 'msg' => 'Please enter exactly 6 digits.'];
    } elseif (!$winNum) {
        $result = ['type' => 'error', 'msg' => 'No winning number has been set yet.'];
    } else {
        // How many units of this number did user buy?
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(units),0) AS total_units FROM lottery_purchases WHERE user_id = ? AND number = ?');
        $stmt->execute([$userId, $check]);
        $userUnits = (int)$stmt->fetch()['total_units'];

        if ($userUnits === 0) {
            $result = ['type' => 'none_bought', 'check' => $check];
        } else {
            $prizes = [];

            // ── 6 ตัวตรง (exact 6-digit match) ─────────────────────
            if ($check === $winNum) {
                $prizes[] = [
                    'label'   => '🏆 6-Digit Exact Match',
                    'units'   => $userUnits,
                    'rate'    => 6000000,
                    'total'   => $userUnits * 6000000,
                    'color'   => '#f5c842',
                ];
            }

            // ── 3 หน้า (first 3 digits match) ───────────────────────
            if (substr($check, 0, 3) === $winFront && $check !== $winNum) {
                $prizes[] = [
                    'label' => '🥇 3-Front Digit Match',
                    'units' => $userUnits,
                    'rate'  => 4000,
                    'total' => $userUnits * 4000,
                    'color' => '#60c8ff',
                ];
            }

            // ── 3 ท้าย (last 3 digits match) ────────────────────────
            if (substr($check, 3, 3) === $winBack && $check !== $winNum && substr($check, 0, 3) !== $winFront) {
                $prizes[] = [
                    'label' => '🥈 3-Tail Digit Match',
                    'units' => $userUnits,
                    'rate'  => 4000,
                    'total' => $userUnits * 4000,
                    'color' => '#a78bfa',
                ];
            }

            // Edge: if both 3-front and 3-tail match but not 6-digit (impossible unless all 6 match)
            // Handle case where both front and tail match but number != winner
            if (substr($check, 0, 3) === $winFront && substr($check, 3, 3) === $winBack && $check !== $winNum) {
                // clear previous and add both
                $prizes = [];
                $prizes[] = ['label'=>'🥇 3-Front Match','units'=>$userUnits,'rate'=>4000,'total'=>$userUnits*4000,'color'=>'#60c8ff'];
                $prizes[] = ['label'=>'🥈 3-Tail Match', 'units'=>$userUnits,'rate'=>4000,'total'=>$userUnits*4000,'color'=>'#a78bfa'];
            }

            if (empty($prizes)) {
                $result = ['type' => 'no_win', 'check' => $check, 'units' => $userUnits];
            } else {
                $result = ['type' => 'win', 'check' => $check, 'units' => $userUnits, 'prizes' => $prizes];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LottoVerse – Check Results</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root { --gold:#f5c842; --gold2:#e8a800; --dark:#0a0a0f; --card:#12121a; --border:#2a2a3a; --text:#e8e8f0; --muted:#7070a0; }
  *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
  body { min-height:100vh; background:var(--dark); font-family:'DM Sans',sans-serif; color:var(--text); background-image:radial-gradient(ellipse 80% 60% at 50% -10%,rgba(245,200,66,.15) 0%,transparent 60%); }

  nav { display:flex; align-items:center; justify-content:space-between; padding:1rem 2rem; border-bottom:1px solid var(--border); background:rgba(10,10,15,.8); backdrop-filter:blur(12px); position:sticky; top:0; z-index:100; }
  .nav-logo { font-family:'Bebas Neue',sans-serif; font-size:1.8rem; color:var(--gold); letter-spacing:3px; }
  .nav-links { display:flex; gap:1rem; }
  .nav-links a { color:var(--muted); text-decoration:none; font-size:.9rem; font-weight:500; padding:.4rem .8rem; border-radius:6px; transition:color .2s; }
  .nav-links a:hover, .nav-links a.active { color:var(--gold); background:rgba(245,200,66,.08); }
  .btn-logout { background:transparent; border:1px solid var(--border); color:var(--muted); padding:.4rem .9rem; border-radius:6px; cursor:pointer; font-family:inherit; font-size:.85rem; transition:all .2s; }
  .btn-logout:hover { border-color:#ff6b6b; color:#ff6b6b; }

  .container { max-width:800px; margin:0 auto; padding:2.5rem 1.5rem; }
  .page-title { font-family:'Bebas Neue',sans-serif; font-size:2.5rem; letter-spacing:3px; color:var(--gold); margin-bottom:.25rem; }
  .page-sub { color:var(--muted); margin-bottom:2.5rem; }

  /* WINNING NUMBER BANNER */
  .winning-banner {
    background: linear-gradient(135deg, rgba(245,200,66,.12), rgba(245,200,66,.04));
    border: 1px solid rgba(245,200,66,.3);
    border-radius: 16px; padding: 1.5rem 2rem;
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 2.5rem;
    flex-wrap: wrap; gap: 1rem;
  }
  .winning-label { color:var(--muted); font-size:.85rem; text-transform:uppercase; letter-spacing:1.5px; }
  .winning-num { font-family:'Bebas Neue',sans-serif; font-size:3rem; letter-spacing:14px; color:var(--gold); text-shadow:0 0 30px rgba(245,200,66,.4); }
  .winning-date { color:var(--muted); font-size:.9rem; text-align:right; }

  /* CHECK FORM */
  .card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:2rem; margin-bottom:2rem; }
  .card h3 { font-size:1.1rem; font-weight:600; margin-bottom:1.5rem; }
  .check-row { display:flex; gap:1rem; align-items:flex-end; }
  .field { flex:1; }
  .field label { display:block; font-size:.78rem; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:.45rem; }
  .field input { width:100%; background:rgba(255,255,255,.05); border:1px solid var(--border); border-radius:8px; color:var(--text); padding:.75rem 1rem; font-size:1.3rem; font-family:'Bebas Neue',sans-serif; letter-spacing:6px; outline:none; transition:border-color .2s,box-shadow .2s; }
  .field input:focus { border-color:var(--gold); box-shadow:0 0 0 3px rgba(245,200,66,.15); }
  .btn-check { background:linear-gradient(135deg,var(--gold),var(--gold2)); border:none; border-radius:8px; color:#0a0a0f; font-family:inherit; font-size:1rem; font-weight:700; padding:.78rem 1.8rem; cursor:pointer; letter-spacing:1px; white-space:nowrap; transition:transform .15s,box-shadow .2s; }
  .btn-check:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(245,200,66,.35); }

  /* RESULTS */
  .result-box { border-radius:16px; padding:2rem; text-align:center; margin-bottom:1rem; animation:fadeUp .4s ease; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
  .result-icon { font-size:3.5rem; margin-bottom:.75rem; }
  .result-title { font-family:'Bebas Neue',sans-serif; font-size:1.8rem; letter-spacing:3px; margin-bottom:.5rem; }
  .result-sub { color:var(--muted); font-size:.95rem; }

  .no-win { background:rgba(255,100,100,.07); border:1px solid rgba(255,100,100,.2); }
  .no-win .result-title { color:#ff6b6b; }

  .win-section { background:rgba(245,200,66,.06); border:1px solid rgba(245,200,66,.25); }

  .prize-cards { display:flex; flex-direction:column; gap:.8rem; margin-top:1.5rem; }
  .prize-card { display:flex; justify-content:space-between; align-items:center; background:rgba(255,255,255,.04); border:1px solid var(--border); border-radius:10px; padding:.9rem 1.2rem; }
  .prize-card-label { font-size:.9rem; font-weight:600; }
  .prize-card-detail { color:var(--muted); font-size:.8rem; margin-top:.2rem; }
  .prize-amount { font-family:'Bebas Neue',sans-serif; font-size:1.6rem; letter-spacing:2px; }

  .total-row { display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border); padding-top:1rem; margin-top:.5rem; }
  .total-row span { color:var(--muted); }
  .total-row strong { font-family:'Bebas Neue',sans-serif; font-size:2rem; color:var(--gold); letter-spacing:2px; }

  .error-box { background:rgba(255,60,60,.12); border:1px solid rgba(255,60,60,.3); border-radius:10px; color:#ff6b6b; padding:.7rem 1rem; margin-bottom:1rem; }
</style>
</head>
<body>

<nav>
  <div class="nav-logo">🎰 LottoVerse</div>
  <div class="nav-links">
    <a href="buy_lottery.php">🎟️ Buy</a>
    <a href="check_lottery.php" class="active">🔍 Check</a>
  </div>
  <div style="display:flex;align-items:center;gap:1rem;">
    <span style="color:var(--muted);font-size:.9rem;">👤 <?= htmlspecialchars($fullname) ?></span>
    <a href="logout.php"><button class="btn-logout">Logout</button></a>
  </div>
</nav>

<div class="container">
  <h1 class="page-title">🔍 Check Results</h1>
  <p class="page-sub">Enter your 6-digit number to see if you've won</p>

  <!-- WINNING NUMBER DISPLAY -->
  <?php if ($winNum): ?>
  <div class="winning-banner">
    <div>
      <div class="winning-label">🏆 Latest Winning Number</div>
      <div class="winning-num"><?= htmlspecialchars($winNum) ?></div>
    </div>
    <div class="winning-date">
      <div style="color:var(--muted);font-size:.8rem;text-transform:uppercase;letter-spacing:1px;">Draw Date</div>
      <div style="font-size:1rem;font-weight:600;"><?= date('d M Y', strtotime($win['draw_date'])) ?></div>
      <div style="font-size:.8rem;color:var(--muted);margin-top:.3rem;">3-Front: <strong style="color:#60c8ff"><?= $winFront ?></strong> &nbsp;|&nbsp; 3-Tail: <strong style="color:#a78bfa"><?= $winBack ?></strong></div>
    </div>
  </div>
  <?php else: ?>
  <div style="background:rgba(255,200,0,.08);border:1px solid rgba(255,200,0,.2);border-radius:12px;padding:1rem 1.5rem;margin-bottom:2rem;color:#ffd966;">⚠️ No winning number has been set yet.</div>
  <?php endif; ?>

  <!-- CHECK FORM -->
  <div class="card">
    <h3>🎯 Enter Your Number</h3>
    <?php if (isset($result) && $result['type']==='error'): ?>
    <div class="error-box">⚠️ <?= htmlspecialchars($result['msg']) ?></div>
    <?php endif; ?>
    <form method="POST" action="check_lottery.php">
      <div class="check-row">
        <div class="field">
          <label>Your Lottery Number</label>
          <input type="text" name="check_number" maxlength="6" placeholder="0 0 0 0 0 0"
            value="<?= isset($_POST['check_number']) ? htmlspecialchars($_POST['check_number']) : '' ?>"
            oninput="this.value=this.value.replace(/\D/g,'')" required>
        </div>
        <button type="submit" class="btn-check">🔍 Check Now</button>
      </div>
    </form>
  </div>

  <!-- RESULTS -->
  <?php if (isset($result) && $result['type'] !== 'error'): ?>

    <?php if ($result['type'] === 'none_bought'): ?>
    <div class="result-box no-win">
      <div class="result-icon">😔</div>
      <div class="result-title">Number Not Found</div>
      <div class="result-sub">You have not purchased number <strong style="color:var(--text)"><?= htmlspecialchars($result['check']) ?></strong>. Buy it first!</div>
    </div>

    <?php elseif ($result['type'] === 'no_win'): ?>
    <div class="result-box no-win">
      <div class="result-icon">😢</div>
      <div class="result-title">Sorry, but you have No number correct</div>
      <div class="result-sub">
        Number <strong style="color:var(--text)"><?= htmlspecialchars($result['check']) ?></strong> does not match any prize.<br>
        You owned <strong style="color:var(--text)"><?= $result['units'] ?> unit(s)</strong> of this number. Better luck next time!
      </div>
    </div>

    <?php elseif ($result['type'] === 'win'): ?>
    <div class="result-box win-section">
      <div class="result-icon">🎉</div>
      <div class="result-title" style="color:var(--gold)">Congratulations!</div>
      <div class="result-sub">Number <strong style="color:var(--text)"><?= htmlspecialchars($result['check']) ?></strong> wins!</div>

      <div class="prize-cards">
        <?php $grandTotal = 0; foreach ($result['prizes'] as $p): $grandTotal += $p['total']; ?>
        <div class="prize-card">
          <div>
            <div class="prize-card-label" style="color:<?= $p['color'] ?>"><?= $p['label'] ?></div>
            <div class="prize-card-detail"><?= $p['units'] ?> unit(s) × ฿<?= number_format($p['rate'],0) ?></div>
          </div>
          <div class="prize-amount" style="color:<?= $p['color'] ?>">฿<?= number_format($p['total'],0) ?></div>
        </div>
        <?php endforeach; ?>

        <div class="total-row">
          <span>🏦 Total Prize Money</span>
          <strong>฿<?= number_format($grandTotal,0) ?></strong>
        </div>
      </div>
    </div>
    <?php endif; ?>

  <?php endif; ?>
</div>

</body>
</html>
