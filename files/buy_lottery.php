<?php
// buy_lottery.php  –  Buy Lottery Page
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require 'db_config.php';
$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];
$fullname = $_SESSION['fullname'];

// ── Handle AJAX purchase ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $number = trim($_POST['number'] ?? '');
    $units  = intval($_POST['units']  ?? 1);

    if (!preg_match('/^\d{6}$/', $number)) {
        echo json_encode(['ok' => false, 'msg' => 'Number must be exactly 6 digits.']);
        exit;
    }
    if ($units < 1 || $units > 100) {
        echo json_encode(['ok' => false, 'msg' => 'Units must be between 1 and 100.']);
        exit;
    }

    $price = $units * 80; // 80 THB per unit
    $stmt  = $pdo->prepare('INSERT INTO lottery_purchases (user_id, number, units, price) VALUES (?,?,?,?)');
    $stmt->execute([$userId, $number, $units, $price]);
    echo json_encode(['ok' => true, 'number' => $number, 'units' => $units, 'price' => $price]);
    exit;
}

// ── Fetch user's purchased numbers ───────────────────────────────────────
$list = $pdo->prepare('SELECT number, units, price, bought_at FROM lottery_purchases WHERE user_id = ? ORDER BY bought_at DESC');
$list->execute([$userId]);
$purchases = $list->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LottoVerse – Buy Lottery</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root { --gold:#f5c842; --gold2:#e8a800; --dark:#0a0a0f; --card:#12121a; --border:#2a2a3a; --text:#e8e8f0; --muted:#7070a0; --green:#3dd68c; }
  *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
  body { min-height:100vh; background:var(--dark); font-family:'DM Sans',sans-serif; color:var(--text); background-image: radial-gradient(ellipse 80% 60% at 50% -10%,rgba(245,200,66,.15) 0%,transparent 60%); }

  /* NAV */
  nav { display:flex; align-items:center; justify-content:space-between; padding:1rem 2rem; border-bottom:1px solid var(--border); background:rgba(10,10,15,.8); backdrop-filter:blur(12px); position:sticky; top:0; z-index:100; }
  .nav-logo { font-family:'Bebas Neue',sans-serif; font-size:1.8rem; color:var(--gold); letter-spacing:3px; }
  .nav-links { display:flex; gap:1rem; align-items:center; }
  .nav-links a { color:var(--muted); text-decoration:none; font-size:.9rem; font-weight:500; transition:color .2s; padding:.4rem .8rem; border-radius:6px; }
  .nav-links a:hover, .nav-links a.active { color:var(--gold); background:rgba(245,200,66,.08); }
  .nav-user { color:var(--text); font-size:.9rem; }
  .btn-logout { background:transparent; border:1px solid var(--border); color:var(--muted); padding:.4rem .9rem; border-radius:6px; cursor:pointer; font-family:inherit; font-size:.85rem; transition:all .2s; }
  .btn-logout:hover { border-color:#ff6b6b; color:#ff6b6b; }

  /* MAIN */
  .container { max-width:960px; margin:0 auto; padding:2.5rem 1.5rem; }
  .page-title { font-family:'Bebas Neue',sans-serif; font-size:2.5rem; letter-spacing:3px; color:var(--gold); margin-bottom:.25rem; }
  .page-sub { color:var(--muted); margin-bottom:2.5rem; }

  .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:2rem; }
  @media(max-width:700px){ .grid-2 { grid-template-columns:1fr; } }

  .card { background:var(--card); border:1px solid var(--border); border-radius:16px; padding:2rem; }
  .card h3 { font-size:1.1rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:.5rem; }

  .field { margin-bottom:1.2rem; }
  .field label { display:block; font-size:.78rem; text-transform:uppercase; letter-spacing:1.5px; color:var(--muted); margin-bottom:.45rem; }
  .field input { width:100%; background:rgba(255,255,255,.05); border:1px solid var(--border); border-radius:8px; color:var(--text); padding:.75rem 1rem; font-size:1rem; font-family:'Bebas Neue',sans-serif; letter-spacing:4px; outline:none; transition:border-color .2s,box-shadow .2s; }
  .field input:focus { border-color:var(--gold); box-shadow:0 0 0 3px rgba(245,200,66,.15); }
  .field input[type=number] { font-family:'DM Sans',sans-serif; letter-spacing:0; }

  .price-preview { background:rgba(245,200,66,.06); border:1px solid rgba(245,200,66,.2); border-radius:8px; padding:.8rem 1rem; display:flex; justify-content:space-between; margin-bottom:1.2rem; font-size:.9rem; }
  .price-preview span { color:var(--muted); }
  .price-preview strong { color:var(--gold); font-size:1.1rem; }

  .btn-primary { width:100%; background:linear-gradient(135deg,var(--gold),var(--gold2)); border:none; border-radius:8px; color:#0a0a0f; font-family:inherit; font-size:1rem; font-weight:700; padding:.85rem; cursor:pointer; letter-spacing:1px; text-transform:uppercase; transition:transform .15s,box-shadow .2s; }
  .btn-primary:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(245,200,66,.35); }

  .error-inline { color:#ff6b6b; font-size:.85rem; margin-top:.5rem; display:none; }

  /* MY NUMBERS LIST */
  .tickets-list { display:flex; flex-direction:column; gap:.7rem; max-height:420px; overflow-y:auto; padding-right:4px; }
  .tickets-list::-webkit-scrollbar { width:4px; }
  .tickets-list::-webkit-scrollbar-track { background:transparent; }
  .tickets-list::-webkit-scrollbar-thumb { background:var(--border); border-radius:2px; }
  .ticket-item { display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,.04); border:1px solid var(--border); border-radius:10px; padding:.75rem 1rem; transition:border-color .2s; }
  .ticket-item:hover { border-color:rgba(245,200,66,.3); }
  .ticket-num { font-family:'Bebas Neue',sans-serif; font-size:1.5rem; letter-spacing:6px; color:var(--gold); }
  .ticket-meta { text-align:right; font-size:.8rem; color:var(--muted); }
  .ticket-meta strong { display:block; color:var(--text); font-size:.9rem; }
  .empty-state { text-align:center; color:var(--muted); padding:2rem; }

  /* MODAL */
  .overlay { position:fixed; inset:0; background:rgba(0,0,0,.75); backdrop-filter:blur(6px); display:none; align-items:center; justify-content:center; z-index:999; }
  .overlay.show { display:flex; }
  .modal { background:var(--card); border:1px solid var(--border); border-radius:20px; padding:2.5rem; text-align:center; max-width:360px; width:90%; animation:popIn .35s cubic-bezier(.34,1.56,.64,1); }
  @keyframes popIn { from{opacity:0;transform:scale(.8)} to{opacity:1;transform:scale(1)} }
  .modal-icon { font-size:3.5rem; margin-bottom:1rem; filter:drop-shadow(0 0 16px rgba(245,200,66,.5)); }
  .modal h2 { font-family:'Bebas Neue',sans-serif; font-size:1.8rem; letter-spacing:3px; color:var(--gold); margin-bottom:.5rem; }
  .modal-number { font-family:'Bebas Neue',sans-serif; font-size:3rem; letter-spacing:10px; color:var(--text); margin:1rem 0; background:rgba(245,200,66,.08); border-radius:10px; padding:.5rem; }
  .modal-detail { color:var(--muted); font-size:.9rem; line-height:1.7; }
  .modal-detail strong { color:var(--text); }
  .btn-modal-close { background:linear-gradient(135deg,var(--gold),var(--gold2)); border:none; border-radius:8px; color:#0a0a0f; font-family:inherit; font-size:.95rem; font-weight:700; padding:.7rem 2rem; cursor:pointer; margin-top:1.5rem; transition:transform .15s; }
  .btn-modal-close:hover { transform:translateY(-1px); }
</style>
</head>
<body>

<nav>
  <div class="nav-logo">🎰 LottoVerse</div>
  <div class="nav-links">
    <a href="buy_lottery.php" class="active">🎟️ Buy</a>
    <a href="check_lottery.php">🔍 Check</a>
  </div>
  <div style="display:flex;align-items:center;gap:1rem;">
    <span class="nav-user">👤 <?= htmlspecialchars($fullname) ?></span>
    <a href="logout.php"><button class="btn-logout">Logout</button></a>
  </div>
</nav>

<div class="container">
  <h1 class="page-title">🎟️ Buy Lottery</h1>
  <p class="page-sub">Choose your lucky 6-digit number • 80 THB per unit</p>

  <div class="grid-2">
    <!-- BUY FORM -->
    <div class="card">
      <h3>🎯 Pick Your Number</h3>
      <div class="field">
        <label>Lottery Number (6 digits)</label>
        <input type="text" id="inp-number" maxlength="6" placeholder="0 0 0 0 0 0" oninput="this.value=this.value.replace(/\D/g,'');updatePreview()">
      </div>
      <div class="field">
        <label>Number of Units</label>
        <input type="number" id="inp-units" min="1" max="100" value="1" oninput="updatePreview()">
      </div>
      <div class="price-preview">
        <span>Total Price</span>
        <strong id="price-display">฿ 80</strong>
      </div>
      <div class="error-inline" id="buy-error">⚠️ <span id="buy-error-msg"></span></div>
      <button class="btn-primary" onclick="buyLottery()">🛒 Buy Now</button>
    </div>

    <!-- MY NUMBERS -->
    <div class="card">
      <h3>📋 My Lottery Numbers</h3>
      <div class="tickets-list" id="ticket-list">
        <?php if (empty($purchases)): ?>
        <div class="empty-state">🎫 No tickets yet.<br>Buy your first number!</div>
        <?php else: foreach ($purchases as $p): ?>
        <div class="ticket-item">
          <div class="ticket-num"><?= htmlspecialchars($p['number']) ?></div>
          <div class="ticket-meta">
            <strong><?= $p['units'] ?> unit<?= $p['units']>1?'s':'' ?> • ฿<?= number_format($p['price'],0) ?></strong>
            <?= date('d M Y', strtotime($p['bought_at'])) ?>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- SUCCESS MODAL -->
<div class="overlay" id="modal-overlay">
  <div class="modal">
    <div class="modal-icon">🎉</div>
    <h2>Purchase Successful!</h2>
    <div class="modal-number" id="modal-number">------</div>
    <div class="modal-detail">
      Units bought: <strong id="modal-units">-</strong><br>
      Total paid: <strong id="modal-price">-</strong>
    </div>
    <button class="btn-modal-close" onclick="closeModal()">✓ Great!</button>
  </div>
</div>

<script>
function updatePreview(){
  const u = parseInt(document.getElementById('inp-units').value)||1;
  document.getElementById('price-display').textContent = '฿ ' + (u*80).toLocaleString();
}

async function buyLottery(){
  const number = document.getElementById('inp-number').value.trim();
  const units  = parseInt(document.getElementById('inp-units').value)||1;
  const errBox = document.getElementById('buy-error');
  const errMsg = document.getElementById('buy-error-msg');
  errBox.style.display='none';

  const fd = new FormData();
  fd.append('ajax','1');
  fd.append('number', number);
  fd.append('units', units);

  const res  = await fetch('buy_lottery.php', {method:'POST', body:fd});
  const data = await res.json();

  if(!data.ok){
    errMsg.textContent = data.msg;
    errBox.style.display='block';
    return;
  }

  // Show modal
  document.getElementById('modal-number').textContent = data.number;
  document.getElementById('modal-units').textContent  = data.units + ' unit' + (data.units>1?'s':'');
  document.getElementById('modal-price').textContent  = '฿' + parseInt(data.price).toLocaleString();
  document.getElementById('modal-overlay').classList.add('show');

  // Add ticket to list
  const list = document.getElementById('ticket-list');
  const emp  = list.querySelector('.empty-state');
  if(emp) emp.remove();
  const div = document.createElement('div');
  div.className = 'ticket-item';
  div.innerHTML = `<div class="ticket-num">${data.number}</div>
    <div class="ticket-meta"><strong>${data.units} unit${data.units>1?'s':''} • ฿${parseInt(data.price).toLocaleString()}</strong>Today</div>`;
  list.prepend(div);

  document.getElementById('inp-number').value = '';
  document.getElementById('inp-units').value  = '1';
  updatePreview();
}

function closeModal(){
  document.getElementById('modal-overlay').classList.remove('show');
}
</script>
</body>
</html>
