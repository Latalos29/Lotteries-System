<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
require 'db_config.php';

$userId   = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];

$win = $pdo->query('SELECT number,draw_date FROM winning_numbers ORDER BY draw_date DESC,id DESC LIMIT 1')->fetch();
$winNum   = $win ? $win['number'] : null;
$winFront = $winNum ? substr($winNum,0,3) : null;
$winBack  = $winNum ? substr($winNum,3,3) : null;

$result = null;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $check = trim($_POST['check_number'] ?? '');
    if (!preg_match('/^\d{6}$/', $check)) {
        $result=['type'=>'error','msg'=>'Please enter exactly 6 digits.'];
    } elseif (!$winNum) {
        $result=['type'=>'error','msg'=>'No winning number has been set yet.'];
    } else {
        $stmt=$pdo->prepare('SELECT COALESCE(SUM(units),0) AS tu FROM lottery_purchases WHERE user_id=? AND number=?');
        $stmt->execute([$userId,$check]);
        $userUnits=(int)$stmt->fetch()['tu'];

        if ($userUnits===0) {
            $result=['type'=>'none_bought','check'=>$check];
        } else {
            $prizes=[];
            if ($check===$winNum) {
                $prizes[]=['label'=>'6-Digit Jackpot','icon'=>'👑','units'=>$userUnits,'rate'=>6000000,'total'=>$userUnits*6000000,'color'=>'var(--gold)'];
            }
            if (substr($check,0,3)===$winFront && $check!==$winNum) {
                $prizes[]=['label'=>'3-Front Match','icon'=>'🥇','units'=>$userUnits,'rate'=>4000,'total'=>$userUnits*4000,'color'=>'#2563eb'];
            }
            if (substr($check,3,3)===$winBack && $check!==$winNum && substr($check,0,3)!==$winFront) {
                $prizes[]=['label'=>'3-Tail Match','icon'=>'🥈','units'=>$userUnits,'rate'=>4000,'total'=>$userUnits*4000,'color'=>'#7c3aed'];
            }
            if (substr($check,0,3)===$winFront && substr($check,3,3)===$winBack && $check!==$winNum) {
                $prizes=[];
                $prizes[]=['label'=>'3-Front Match','icon'=>'🥇','units'=>$userUnits,'rate'=>4000,'total'=>$userUnits*4000,'color'=>'#2563eb'];
                $prizes[]=['label'=>'3-Tail Match','icon'=>'🥈','units'=>$userUnits,'rate'=>4000,'total'=>$userUnits*4000,'color'=>'#7c3aed'];
            }
            if (empty($prizes)) {
                $result=['type'=>'no_win','check'=>$check,'units'=>$userUnits];
            } else {
                $result=['type'=>'win','check'=>$check,'units'=>$userUnits,'prizes'=>$prizes];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>LuckyStar – Check Results</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --cream:#fdf8f0;--white:#fff;--red:#c0392b;--red2:#e74c3c;
  --gold:#d4a017;--gold2:#f0c040;--ink:#1a1a2e;--gray:#6b7280;
  --border:#e5ddd0;--light:#f9f4ec;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{min-height:100vh;font-family:'Nunito',sans-serif;color:var(--ink);background:var(--cream);}

nav{
  background:var(--white);border-bottom:1px solid var(--border);
  padding:.9rem 2rem;display:flex;align-items:center;justify-content:space-between;
  position:sticky;top:0;z-index:100;box-shadow:0 2px 12px rgba(26,26,46,.06);
}
.nav-logo{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:900;color:var(--red);display:flex;align-items:center;gap:.4rem;}
.nav-links{display:flex;gap:.5rem;}
.nav-links a{color:var(--gray);text-decoration:none;font-size:.9rem;font-weight:600;padding:.45rem .9rem;border-radius:8px;transition:all .2s;}
.nav-links a:hover,.nav-links a.active{color:var(--red);background:rgba(192,57,43,.08);}
.nav-right{display:flex;align-items:center;gap:.8rem;}
.nav-user{font-size:.85rem;color:var(--gray);background:var(--light);padding:.35rem .8rem;border-radius:20px;border:1px solid var(--border);}
.btn-logout{background:transparent;border:1.5px solid var(--border);color:var(--gray);padding:.35rem .9rem;border-radius:8px;cursor:pointer;font-family:inherit;font-size:.85rem;transition:all .2s;}
.btn-logout:hover{border-color:var(--red);color:var(--red);}

.container{max-width:820px;margin:0 auto;padding:2.5rem 1.5rem;}
.page-header{margin-bottom:2.5rem;}
.page-header h1{font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:900;}
.page-header p{color:var(--gray);margin-top:.3rem;}

/* WINNING BANNER */
.win-banner{
  background:var(--white);border:1px solid var(--border);
  border-radius:20px;padding:1.8rem 2rem;margin-bottom:2.5rem;
  box-shadow:0 4px 20px rgba(26,26,46,.06);
  display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.2rem;
  position:relative;overflow:hidden;
}
.win-banner::before{
  content:'⭐';
  position:absolute;right:1.5rem;top:50%;transform:translateY(-50%);
  font-size:6rem;opacity:.06;
}
.wb-label{font-size:.75rem;text-transform:uppercase;letter-spacing:2px;color:var(--gray);font-weight:700;margin-bottom:.3rem;}
.wb-number{
  font-family:'Playfair Display',serif;
  font-size:3rem;font-weight:900;color:var(--red);
  letter-spacing:12px;
  text-shadow:0 2px 8px rgba(192,57,43,.15);
}
.wb-chips{display:flex;gap:.7rem;flex-wrap:wrap;}
.chip{
  padding:.3rem .9rem;border-radius:20px;font-size:.8rem;font-weight:700;letter-spacing:1px;
}
.chip-blue{background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;}
.chip-purple{background:#f5f3ff;color:#7c3aed;border:1px solid #ddd6fe;}
.chip-date{background:var(--light);color:var(--gray);border:1px solid var(--border);}

/* CHECK FORM */
.card{background:var(--white);border:1px solid var(--border);border-radius:20px;padding:2rem;box-shadow:0 4px 20px rgba(26,26,46,.06);margin-bottom:1.5rem;}
.card-title{font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:700;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--border);}

.check-row{display:flex;gap:1rem;align-items:flex-end;}
.field{flex:1;}
.field label{display:block;font-size:.75rem;text-transform:uppercase;letter-spacing:1.5px;color:var(--gray);margin-bottom:.4rem;font-weight:700;}
.number-input{
  width:100%;padding:.85rem 1rem;
  border:2px solid var(--border);border-radius:12px;
  font-family:'Playfair Display',serif;font-size:1.8rem;letter-spacing:10px;
  text-align:center;color:var(--ink);background:var(--light);outline:none;transition:all .2s;
}
.number-input:focus{border-color:var(--red);background:#fff;box-shadow:0 0 0 4px rgba(192,57,43,.08);}
.number-input::placeholder{color:#ccc;letter-spacing:6px;font-size:1.4rem;}

.btn-check{
  background:linear-gradient(135deg,var(--red),var(--red2));
  border:none;border-radius:12px;color:#fff;
  font-family:'Nunito',sans-serif;font-size:1rem;font-weight:700;
  padding:.86rem 1.8rem;cursor:pointer;transition:all .2s;white-space:nowrap;
  box-shadow:0 4px 16px rgba(192,57,43,.2);
}
.btn-check:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(192,57,43,.3);}

.error-alert{background:#fff5f5;border:1.5px solid #fca5a5;border-radius:10px;color:#dc2626;padding:.7rem 1rem;margin-bottom:1rem;font-size:.88rem;}

/* RESULTS */
.result-card{border-radius:20px;padding:2rem;text-align:center;animation:fadeUp .4s ease;}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}

.result-no-win{background:#fff5f5;border:2px solid #fca5a5;}
.result-sorry-title{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:#dc2626;margin:.8rem 0 .4rem;}
.result-sorry-sub{color:var(--gray);font-size:.95rem;}

.result-win{background:linear-gradient(135deg,#fffbeb,#fef9c3);border:2px solid var(--gold2);}
.win-title{font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:900;color:var(--red);margin:.5rem 0 .3rem;}

.prize-row{
  display:flex;align-items:center;justify-content:space-between;
  background:var(--white);border:1px solid var(--border);border-radius:12px;
  padding:.9rem 1.2rem;margin:.5rem 0;text-align:left;
}
.prize-row-left{display:flex;align-items:center;gap:.7rem;}
.prize-icon{font-size:1.5rem;}
.prize-name{font-weight:700;font-size:.95rem;}
.prize-detail{font-size:.78rem;color:var(--gray);margin-top:.1rem;}
.prize-amount{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;}

.total-banner{
  background:linear-gradient(135deg,var(--red),var(--red2));
  border-radius:12px;padding:1rem 1.5rem;
  display:flex;justify-content:space-between;align-items:center;
  margin-top:.8rem;color:#fff;
}
.total-banner .tl{font-size:.85rem;opacity:.85;}
.total-banner .ta{font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;}
</style>
</head>
<body>

<nav>
  <div class="nav-logo"><span>⭐</span> LuckyStar</div>
  <div class="nav-links">
    <a href="buy_lottery.php">🎟️ Buy</a>
    <a href="check_lottery.php" class="active">🔍 Check</a>
  </div>
  <div class="nav-right">
    <div class="nav-user">👤 <?=htmlspecialchars($fullname)?></div>
    <a href="logout.php"><button class="btn-logout">Sign Out</button></a>
  </div>
</nav>

<div class="container">
  <div class="page-header">
    <h1>🔍 Check Your Results</h1>
    <p>Enter your number to see if fortune favors you today</p>
  </div>

  <!-- WINNING BANNER -->
  <?php if($winNum): ?>
  <div class="win-banner">
    <div>
      <div class="wb-label">🏆 Latest Winning Number</div>
      <div class="wb-number"><?=htmlspecialchars($winNum)?></div>
    </div>
    <div>
      <div class="wb-label" style="margin-bottom:.6rem;">Prize Combinations</div>
      <div class="wb-chips">
        <span class="chip chip-blue">3-Front: <?=$winFront?></span>
        <span class="chip chip-purple">3-Tail: <?=$winBack?></span>
        <span class="chip chip-date">📅 <?=date('d M Y',strtotime($win['draw_date']))?></span>
      </div>
    </div>
  </div>
  <?php else: ?>
  <div style="background:#fffbeb;border:1.5px solid #fcd34d;border-radius:12px;padding:1rem 1.5rem;color:#92400e;margin-bottom:2rem;">⚠️ No winning number has been set yet.</div>
  <?php endif; ?>

  <!-- CHECK FORM -->
  <div class="card">
    <div class="card-title">🎯 Enter Your Number to Check</div>
    <?php if(isset($result)&&$result['type']==='error'): ?>
    <div class="error-alert">⚠️ <?=htmlspecialchars($result['msg'])?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="check-row">
        <div class="field">
          <label>Your 6-Digit Lottery Number</label>
          <input type="text" class="number-input" name="check_number" maxlength="6"
            placeholder="• • • • • •"
            value="<?=isset($_POST['check_number'])?htmlspecialchars($_POST['check_number']):''?>"
            oninput="this.value=this.value.replace(/\D/g,'')" required>
        </div>
        <button type="submit" class="btn-check">🔍 Check Now</button>
      </div>
    </form>
  </div>

  <!-- RESULTS -->
  <?php if(isset($result) && $result['type']!=='error'): ?>

    <?php if($result['type']==='none_bought'): ?>
    <div class="result-card result-no-win">
      <div style="font-size:3rem;">🔍</div>
      <div class="result-sorry-title">Ticket Not Found</div>
      <div class="result-sorry-sub">You haven't purchased <strong><?=htmlspecialchars($result['check'])?></strong> yet. Head to Buy to get this number!</div>
    </div>

    <?php elseif($result['type']==='no_win'): ?>
    <div class="result-card result-no-win">
      <div style="font-size:3rem;">😢</div>
      <div class="result-sorry-title">Sorry, but you have No number correct</div>
      <div class="result-sorry-sub">
        Number <strong><?=htmlspecialchars($result['check'])?></strong> didn't match any prizes.<br>
        You owned <strong><?=$result['units']?> unit(s)</strong>. Better luck next draw!
      </div>
    </div>

    <?php elseif($result['type']==='win'): ?>
    <?php $grand=array_sum(array_column($result['prizes'],'total')); ?>
    <div class="result-card result-win">
      <div style="font-size:3.5rem;">🎊</div>
      <div class="win-title">You're a Winner!</div>
      <div style="color:var(--gray);font-size:.9rem;margin-bottom:1.5rem;">Number <strong style="color:var(--ink)"><?=htmlspecialchars($result['check'])?></strong> wins the following prizes</div>

      <?php foreach($result['prizes'] as $p): ?>
      <div class="prize-row">
        <div class="prize-row-left">
          <span class="prize-icon"><?=$p['icon']?></span>
          <div>
            <div class="prize-name" style="color:<?=$p['color']?>"><?=$p['label']?></div>
            <div class="prize-detail"><?=$p['units']?> unit(s) × ฿<?=number_format($p['rate'],0)?></div>
          </div>
        </div>
        <div class="prize-amount" style="color:<?=$p['color']?>">฿<?=number_format($p['total'],0)?></div>
      </div>
      <?php endforeach; ?>

      <div class="total-banner">
        <div class="tl">💰 Total Prize Money</div>
        <div class="ta">฿<?=number_format($grand,0)?></div>
      </div>
    </div>
    <?php endif; ?>

  <?php endif; ?>
</div>
</body>
</html>
