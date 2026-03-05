<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
require 'db_config.php';

$userId   = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

// ── AJAX buy ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    $number = trim($_POST['number'] ?? '');
    $units  = intval($_POST['units']  ?? 1);
    if (!preg_match('/^\d{6}$/', $number)) { echo json_encode(['ok'=>false,'msg'=>'Number must be exactly 6 digits.']); exit; }
    if ($units<1 || $units>100)            { echo json_encode(['ok'=>false,'msg'=>'Units must be 1–100.']);              exit; }
    $price = $units * 80;
    $pdo->prepare('INSERT INTO lottery_purchases (user_id,number,units,price) VALUES (?,?,?,?)')->execute([$userId,$number,$units,$price]);
    echo json_encode(['ok'=>true,'number'=>$number,'units'=>$units,'price'=>$price]);
    exit;
}

// ── Load purchases ────────────────────────────────────────────────────────
$rows = $pdo->prepare('SELECT number,units,price,bought_at FROM lottery_purchases WHERE user_id=? ORDER BY bought_at DESC');
$rows->execute([$userId]);
$purchases = $rows->fetchAll();
$totalSpent = array_sum(array_column($purchases,'price'));
$totalTickets = count($purchases);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>LuckyStar – Buy Lottery</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --cream:#fdf8f0;--white:#fff;--red:#c0392b;--red2:#e74c3c;
  --gold:#d4a017;--gold2:#f0c040;--ink:#1a1a2e;--gray:#6b7280;
  --border:#e5ddd0;--light:#f9f4ec;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{min-height:100vh;font-family:'Nunito',sans-serif;color:var(--ink);background:var(--cream);}

/* ── NAV ── */
nav{
  background:var(--white);
  border-bottom:1px solid var(--border);
  padding:.9rem 2rem;
  display:flex;align-items:center;justify-content:space-between;
  position:sticky;top:0;z-index:100;
  box-shadow:0 2px 12px rgba(26,26,46,.06);
}
.nav-logo{
  font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:900;
  color:var(--red);display:flex;align-items:center;gap:.4rem;
}
.nav-logo span{font-size:1.3rem;}
.nav-links{display:flex;gap:.5rem;}
.nav-links a{
  color:var(--gray);text-decoration:none;font-size:.9rem;font-weight:600;
  padding:.45rem .9rem;border-radius:8px;transition:all .2s;
}
.nav-links a:hover,.nav-links a.active{color:var(--red);background:rgba(192,57,43,.08);}
.nav-right{display:flex;align-items:center;gap:.8rem;}
.nav-user{
  font-size:.85rem;color:var(--gray);
  background:var(--light);padding:.35rem .8rem;border-radius:20px;
  border:1px solid var(--border);
}
.btn-logout{
  background:transparent;border:1.5px solid var(--border);color:var(--gray);
  padding:.35rem .9rem;border-radius:8px;cursor:pointer;font-family:inherit;
  font-size:.85rem;transition:all .2s;
}
.btn-logout:hover{border-color:var(--red);color:var(--red);}

/* ── LAYOUT ── */
.container{max-width:1080px;margin:0 auto;padding:2.5rem 1.5rem;}
.page-header{margin-bottom:2.5rem;}
.page-header h1{
  font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:900;color:var(--ink);
}
.page-header p{color:var(--gray);margin-top:.3rem;}

/* ── STATS STRIP ── */
.stats-strip{display:flex;gap:1rem;margin-bottom:2.5rem;flex-wrap:wrap;}
.stat-pill{
  background:var(--white);border:1px solid var(--border);border-radius:12px;
  padding:.8rem 1.4rem;display:flex;align-items:center;gap:.7rem;
  box-shadow:0 2px 8px rgba(26,26,46,.04);
}
.stat-pill .s-icon{font-size:1.4rem;}
.stat-pill .s-val{font-size:1.2rem;font-weight:700;color:var(--ink);}
.stat-pill .s-lbl{font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:1px;}

/* ── GRID ── */
.main-grid{display:grid;grid-template-columns:420px 1fr;gap:2rem;}
@media(max-width:860px){.main-grid{grid-template-columns:1fr;}}

/* ── BUY CARD ── */
.card{
  background:var(--white);border:1px solid var(--border);
  border-radius:20px;padding:2rem;
  box-shadow:0 4px 20px rgba(26,26,46,.06);
}
.card-title{
  font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:700;
  margin-bottom:1.5rem;display:flex;align-items:center;gap:.5rem;
  padding-bottom:1rem;border-bottom:1px solid var(--border);
}

.field{margin-bottom:1.2rem;}
.field label{display:block;font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--gray);margin-bottom:.45rem;font-weight:700;}
.number-input{
  width:100%;padding:.85rem 1rem;
  border:2px solid var(--border);border-radius:12px;
  font-family:'Playfair Display',serif;font-size:1.8rem;
  letter-spacing:10px;text-align:center;color:var(--ink);
  background:var(--light);outline:none;transition:all .2s;
}
.number-input:focus{border-color:var(--red);background:#fff;box-shadow:0 0 0 4px rgba(192,57,43,.08);}
.number-input::placeholder{color:#ccc;letter-spacing:6px;}

.units-row{display:flex;align-items:center;gap:.7rem;margin-bottom:1.4rem;}
.units-row label{font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--gray);font-weight:700;white-space:nowrap;}
.units-row input{
  width:80px;padding:.6rem .8rem;
  border:2px solid var(--border);border-radius:8px;
  font-family:'Nunito',sans-serif;font-size:1rem;color:var(--ink);
  background:var(--light);outline:none;transition:all .2s;text-align:center;
}
.units-row input:focus{border-color:var(--red);background:#fff;}

.price-card{
  background:linear-gradient(135deg,rgba(212,160,23,.1),rgba(240,192,64,.08));
  border:1.5px solid rgba(212,160,23,.3);border-radius:12px;
  padding:1rem 1.2rem;display:flex;justify-content:space-between;align-items:center;
  margin-bottom:1.4rem;
}
.price-card .label{font-size:.82rem;color:var(--gray);}
.price-card .amount{font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:var(--gold);}

.error-inline{color:#dc2626;font-size:.84rem;margin-bottom:.8rem;display:none;background:#fff5f5;border:1px solid #fca5a5;border-radius:8px;padding:.5rem .8rem;}

.btn-buy{
  width:100%;padding:.9rem;
  background:linear-gradient(135deg,var(--red),var(--red2));
  border:none;border-radius:12px;color:#fff;
  font-family:'Nunito',sans-serif;font-size:1.05rem;font-weight:700;
  cursor:pointer;transition:all .2s;
  box-shadow:0 4px 16px rgba(192,57,43,.25);
  display:flex;align-items:center;justify-content:center;gap:.5rem;
}
.btn-buy:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(192,57,43,.35);}

/* ── TICKET LIST ── */
.ticket-scroll{
  display:flex;flex-direction:column;gap:.6rem;
  max-height:460px;overflow-y:auto;padding-right:4px;
}
.ticket-scroll::-webkit-scrollbar{width:4px;}
.ticket-scroll::-webkit-scrollbar-thumb{background:var(--border);border-radius:2px;}

.ticket{
  display:flex;align-items:center;justify-content:space-between;
  background:var(--light);border:1.5px solid var(--border);border-radius:12px;
  padding:.8rem 1.1rem;transition:all .2s;cursor:default;
}
.ticket:hover{border-color:rgba(192,57,43,.3);background:#fff;transform:translateX(3px);}
.ticket-left{display:flex;align-items:center;gap:.8rem;}
.ticket-badge{
  background:linear-gradient(135deg,var(--red),var(--red2));
  color:#fff;font-size:.65rem;padding:.2rem .5rem;
  border-radius:4px;font-weight:700;letter-spacing:1px;
}
.ticket-num{
  font-family:'Playfair Display',serif;font-size:1.4rem;
  font-weight:700;color:var(--ink);letter-spacing:5px;
}
.ticket-right{text-align:right;}
.ticket-units{font-size:.85rem;font-weight:700;color:var(--ink);}
.ticket-price{font-size:.75rem;color:var(--gray);}
.empty-msg{text-align:center;color:var(--gray);padding:2.5rem;font-size:.95rem;}
.empty-msg .e-icon{font-size:2.5rem;margin-bottom:.5rem;display:block;}

/* ── MODAL ── */
.overlay{
  position:fixed;inset:0;
  background:rgba(26,26,46,.5);backdrop-filter:blur(8px);
  display:none;align-items:center;justify-content:center;z-index:999;
}
.overlay.show{display:flex;}
.modal{
  background:var(--white);border-radius:24px;padding:3rem 2.5rem;
  text-align:center;max-width:380px;width:90%;
  box-shadow:0 30px 80px rgba(26,26,46,.2);
  animation:popUp .4s cubic-bezier(.34,1.56,.64,1);
}
@keyframes popUp{from{opacity:0;transform:scale(.75)}to{opacity:1;transform:scale(1)}}

.modal-confetti{font-size:3.5rem;margin-bottom:.5rem;animation:spin 1s ease;}
@keyframes spin{from{transform:rotate(-15deg) scale(.8)}to{transform:rotate(0) scale(1)}}
.modal-title{
  font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:900;
  color:var(--red);margin-bottom:.3rem;
}
.modal-sub{color:var(--gray);font-size:.9rem;margin-bottom:1.5rem;}
.modal-ticket{
  background:linear-gradient(135deg,var(--cream),#fff8e8);
  border:2px dashed var(--gold);border-radius:16px;padding:1.2rem;
  margin:1rem 0 1.5rem;
}
.modal-number{
  font-family:'Playfair Display',serif;font-size:2.5rem;font-weight:900;
  color:var(--ink);letter-spacing:10px;
}
.modal-info{font-size:.85rem;color:var(--gray);margin-top:.5rem;line-height:1.7;}
.modal-info strong{color:var(--ink);}
.btn-close-modal{
  background:linear-gradient(135deg,var(--red),var(--red2));
  border:none;border-radius:10px;color:#fff;padding:.8rem 2.5rem;
  font-family:'Nunito',sans-serif;font-size:1rem;font-weight:700;
  cursor:pointer;transition:all .2s;
}
.btn-close-modal:hover{transform:translateY(-1px);}
</style>
</head>
<body>

<nav>
  <div class="nav-logo"><span>⭐</span> LuckyStar</div>
  <div class="nav-links">
    <a href="buy_lottery.php" class="active">🎟️ Buy</a>
    <a href="check_lottery.php">🔍 Check</a>
  </div>
  <div class="nav-right">
    <div class="nav-user">👤 <?=htmlspecialchars($fullname)?></div>
    <a href="logout.php"><button class="btn-logout">Sign Out</button></a>
  </div>
</nav>

<div class="container">
  <div class="page-header">
    <h1>🎟️ Buy Your Lottery</h1>
    <p>Pick a 6-digit number and test your luck · ฿80 per unit</p>
  </div>

  <!-- STATS -->
  <div class="stats-strip">
    <div class="stat-pill">
      <span class="s-icon">🎫</span>
      <div>
        <div class="s-val" id="stat-tickets"><?=$totalTickets?></div>
        <div class="s-lbl">Tickets Owned</div>
      </div>
    </div>
    <div class="stat-pill">
      <span class="s-icon">💰</span>
      <div>
        <div class="s-val" id="stat-spent">฿<?=number_format($totalSpent,0)?></div>
        <div class="s-lbl">Total Spent</div>
      </div>
    </div>
    <div class="stat-pill">
      <span class="s-icon">🏆</span>
      <div>
        <div class="s-val">฿6M</div>
        <div class="s-lbl">Top Prize</div>
      </div>
    </div>
  </div>

  <!-- MAIN GRID -->
  <div class="main-grid">

    <!-- BUY FORM -->
    <div class="card">
      <div class="card-title">🎯 Choose Your Number</div>

      <div class="field">
        <label>Lottery Number (6 digits)</label>
        <input type="text" class="number-input" id="inp-number"
          maxlength="6" placeholder="• • • • • •"
          oninput="this.value=this.value.replace(/\D/g,'');updatePreview()">
      </div>

      <div class="units-row">
        <label>Units</label>
        <input type="number" id="inp-units" min="1" max="100" value="1" oninput="updatePreview()">
        <span style="color:var(--gray);font-size:.85rem;">× ฿80 each</span>
      </div>

      <div class="price-card">
        <div><div class="label">Total to pay</div></div>
        <div class="amount" id="price-display">฿ 80</div>
      </div>

      <div class="error-inline" id="buy-error"></div>

      <button class="btn-buy" onclick="buyLottery()">
        🛒 Confirm Purchase
      </button>
    </div>

    <!-- MY TICKETS -->
    <div class="card">
      <div class="card-title">📋 My Tickets</div>
      <div class="ticket-scroll" id="ticket-list">
        <?php if(empty($purchases)): ?>
        <div class="empty-msg"><span class="e-icon">🎫</span>No tickets yet — buy your first one!</div>
        <?php else: foreach($purchases as $p): ?>
        <div class="ticket">
          <div class="ticket-left">
            <span class="ticket-badge">LOTTO</span>
            <span class="ticket-num"><?=htmlspecialchars($p['number'])?></span>
          </div>
          <div class="ticket-right">
            <div class="ticket-units"><?=$p['units']?> unit<?=$p['units']>1?'s':''?></div>
            <div class="ticket-price">฿<?=number_format($p['price'],0)?> · <?=date('d M Y',strtotime($p['bought_at']))?></div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

  </div>
</div>

<!-- MODAL -->
<div class="overlay" id="modal-overlay">
  <div class="modal">
    <div class="modal-confetti">🎉</div>
    <div class="modal-title">Purchase Complete!</div>
    <div class="modal-sub">Your ticket has been registered</div>
    <div class="modal-ticket">
      <div class="modal-number" id="m-number">------</div>
      <div class="modal-info">
        Units: <strong id="m-units">–</strong><br>
        Amount paid: <strong id="m-price">–</strong>
      </div>
    </div>
    <button class="btn-close-modal" onclick="closeModal()">✓ Done</button>
  </div>
</div>

<script>
let totalTickets = <?=$totalTickets?>;
let totalSpent   = <?=$totalSpent?>;

function updatePreview(){
  const u = parseInt(document.getElementById('inp-units').value)||1;
  document.getElementById('price-display').textContent='฿ '+(u*80).toLocaleString();
}

async function buyLottery(){
  const number = document.getElementById('inp-number').value.trim();
  const units  = parseInt(document.getElementById('inp-units').value)||1;
  const errBox = document.getElementById('buy-error');
  errBox.style.display='none';

  const fd=new FormData();
  fd.append('ajax','1'); fd.append('number',number); fd.append('units',units);

  const res  = await fetch('buy_lottery.php',{method:'POST',body:fd});
  const data = await res.json();

  if(!data.ok){ errBox.textContent='⚠️ '+data.msg; errBox.style.display='block'; return; }

  document.getElementById('m-number').textContent = data.number;
  document.getElementById('m-units').textContent  = data.units+' unit'+(data.units>1?'s':'');
  document.getElementById('m-price').textContent  = '฿'+parseInt(data.price).toLocaleString();
  document.getElementById('modal-overlay').classList.add('show');

  // Add to list
  const list = document.getElementById('ticket-list');
  const emp  = list.querySelector('.empty-msg');
  if(emp) emp.remove();
  const div=document.createElement('div');
  div.className='ticket';
  div.innerHTML=`<div class="ticket-left"><span class="ticket-badge">LOTTO</span><span class="ticket-num">${data.number}</span></div>
    <div class="ticket-right"><div class="ticket-units">${data.units} unit${data.units>1?'s':''}</div><div class="ticket-price">฿${parseInt(data.price).toLocaleString()} · Today</div></div>`;
  list.prepend(div);

  // Update stats
  totalTickets++;
  totalSpent += parseInt(data.price);
  document.getElementById('stat-tickets').textContent = totalTickets;
  document.getElementById('stat-spent').textContent   = '฿'+totalSpent.toLocaleString();

  document.getElementById('inp-number').value='';
  document.getElementById('inp-units').value='1';
  updatePreview();
}

function closeModal(){ document.getElementById('modal-overlay').classList.remove('show'); }
</script>
</body>
</html>
