<?php
session_start();
require_once 'config.php';

$db     = getDB();
$isLive = ($db !== null);

/* ── helper: format draw_date เป็นภาษาไทย ── */
function thDate(string $d): string {
    $thM = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    $dt  = new DateTime($d);
    return (int)$dt->format('j').' '.$thM[(int)$dt->format('n')].' '.(((int)$dt->format('Y'))+543);
}

/* ════════════════════════════════════════════════
   CART — จัดการด้วย PHP Session
   ════════════════════════════════════════════════ */
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

/* ── ล้าง cart เก่าที่ key ไม่ตรง (เช่น session ค้างจากเวอร์ชันก่อน) ── */
foreach ($_SESSION['cart'] as $k => $it) {
    if (!isset($it['lottery_id'], $it['lotteryNumber'], $it['price'], $it['draw_date'])) {
        unset($_SESSION['cart'][$k]);
    }
}

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $lid       = $_POST['lottery_id']    ?? '';
    $num       = $_POST['lotteryNumber'] ?? '';
    $price     = (int)($_POST['price']   ?? 0);
    $draw      = $_POST['draw_date']     ?? '';
    $qtyAct    = $_POST['qty_action']    ?? 'add';
    $qtyVal    = max(1, min(10, (int)($_POST['qty'] ?? 1)));

    /* กด − หรือ + → กลับไป popup พร้อมอัปเดต qty ใน URL */
    if ($qtyAct === 'inc' || $qtyAct === 'dec') {
        $newQty = $qtyAct === 'inc' ? min(10, $qtyVal + 1) : max(1, $qtyVal - 1);
        $qp = array_merge($_GET, ['qty_popup' => $lid, 'qty' => $newQty]);
        unset($qp['cart']);
        header('Location: '.$_SERVER['PHP_SELF'].'?'.http_build_query($qp));
        exit;
    }

    /* กด ยืนยัน → เพิ่มลง cart */
    if ($lid) {
        if (!isset($_SESSION['cart'][$lid])) {
            $_SESSION['cart'][$lid] = [
                'lottery_id'    => $lid,
                'lotteryNumber' => $num,
                'price'         => $price,
                'draw_date'     => $draw,
                'qty'           => $qtyVal,
            ];
        } else {
            $_SESSION['cart'][$lid]['qty'] = min(10, $_SESSION['cart'][$lid]['qty'] + $qtyVal);
        }
        $_SESSION['toast'] = "✅ เพิ่ม {$num} จำนวน {$qtyVal} ใบ ลงตะกร้าแล้ว";
    }
    header('Location: '.$_SERVER['PHP_SELF'].'?'.http_build_query(array_merge($_GET,['cart'=>'open'])));
    exit;
}

if ($action === 'remove') {
    $lid = $_POST['lottery_id'] ?? '';
    if (isset($_SESSION['cart'][$lid])) {
        unset($_SESSION['cart'][$lid]);
        $_SESSION['toast'] = '🗑️ นำรายการออกจากตะกร้าแล้ว';
    }
    header('Location: '.$_SERVER['PHP_SELF'].'?'.http_build_query(array_merge($_GET,['cart'=>'open'])));
    exit;
}

if ($action === 'checkout') {
    $nums  = implode(', ', array_column($_SESSION['cart'], 'lotteryNumber'));
    $total = array_sum(array_column($_SESSION['cart'], 'price'));
    $_SESSION['cart']  = [];
    $_SESSION['toast'] = "✅ บันทึกคำสั่งซื้อแล้ว | เลข: {$nums} | รวม: ฿".number_format($total);
    $qp = $_GET; unset($qp['cart']);
    header('Location: '.$_SERVER['PHP_SELF'].'?'.http_build_query($qp));
    exit;
}

if ($action === 'login') {
    $u = trim($_POST['loginUser'] ?? '');
    if ($u) {
        $_SESSION['user']  = $u;
        $_SESSION['toast'] = '✅ เข้าสู่ระบบสำเร็จ (Demo Mode)';
    } else {
        $_SESSION['toast'] = '⚠️ กรุณากรอกเบอร์หรืออีเมล';
    }
    $qp = $_GET; unset($qp['modal']);
    header('Location: '.$_SERVER['PHP_SELF'].'?'.http_build_query($qp));
    exit;
}

if ($action === 'register') {
    $_SESSION['toast'] = '✅ สมัครสมาชิกสำเร็จ (Demo Mode)';
    $qp = $_GET; unset($qp['modal']);
    header('Location: '.$_SERVER['PHP_SELF'].'?'.http_build_query($qp));
    exit;
}

if ($action === 'logout') {
    unset($_SESSION['user']);
    $_SESSION['toast'] = '👋 ออกจากระบบแล้ว';
    $qp = $_GET; unset($qp['modal']);
    header('Location: '.$_SERVER['PHP_SELF'].'?'.http_build_query($qp));
    exit;
}

/* ── ดึง toast แล้วล้าง ── */
$toast             = $_SESSION['toast'] ?? '';
$_SESSION['toast'] = '';

/* ── state ── */
$cart      = $_SESSION['cart'];
$cartCount = array_sum(array_column($cart, 'qty'));
$cartTotal = array_sum(array_map(fn($i) => $i['price'] * ($i['qty'] ?? 1), $cart));
$cartOpen  = ($_GET['cart'] ?? '') === 'open';
$qtyPopup  = $_GET['qty_popup'] ?? '';   /* lottery_id ที่จะเปิด popup เลือกจำนวน */

$loggedIn  = isset($_SESSION['user']);
$userName  = $loggedIn ? $_SESSION['user'] : '';

  //  Filter & Search จาก GET
$filterStatus = $_GET['status'] ?? 'available';
$searchQ      = trim($_GET['q'] ?? '');
if (!in_array($filterStatus, ['available','reserved','all'])) $filterStatus = 'available';

/* helper: สร้าง query string โดยคง q, status, ไว้ แล้ว override */
function qs(array $ov = []): string {
    global $searchQ, $filterStatus;
    $base = ['q' => $searchQ, 'status' => $filterStatus];
    $merged = array_merge($base, $ov);
    // ตัดค่าว่างออก
    $merged = array_filter($merged, fn($v) => $v !== '' && $v !== null);
    return http_build_query($merged);
}

  //  หางวดล่าสุด
$latestDate   = null;
$latestDrawId = null;
$latestDrawTh = '—';

if ($isLive) {
    $rL = $db->query(
        "SELECT d.draw_date, MIN(d.draw_id) AS draw_id
         FROM draws d
         INNER JOIN lotteries l ON l.draw_id = d.draw_id
         WHERE d.status = 'open'
         GROUP BY d.draw_date
         ORDER BY d.draw_date DESC
         LIMIT 1"
    );
    if ($rowL = $rL->fetch_assoc()) {
        $latestDate   = $rowL['draw_date'];
        $latestDrawId = $rowL['draw_id'];
        $latestDrawTh = thDate($latestDate);
    }
}

  //  Stats
$stats = ['available'=>0,'sold'=>0,'reserved'=>0];

if ($isLive && $latestDate) {
    $rS = $db->prepare(
        "SELECT l.status, COUNT(*) c
         FROM lotteries l
         INNER JOIN draws d ON l.draw_id = d.draw_id
         WHERE d.draw_date = ? AND d.status = 'open'
         GROUP BY l.status"
    );
    $rS->bind_param('s', $latestDate);
    $rS->execute();
    $resS = $rS->get_result();
    while ($row = $resS->fetch_assoc()) {
        if (isset($stats[$row['status']])) $stats[$row['status']] = (int)$row['c'];
    }
    $rS->close();
}

  //  ดึงลอตเตอรี่ตาม filter + search
$lotteries = [];

if ($isLive && $latestDate) {
    $sql    = "SELECT l.lottery_id, l.lotteryNumber, l.price, l.status, d.draw_date
               FROM lotteries l
               INNER JOIN draws d ON l.draw_id = d.draw_id
               WHERE d.draw_date = ? AND d.status = 'open'";
    $params = [$latestDate];
    $types  = 's';

    if ($filterStatus !== 'all') {
        $sql    .= " AND l.status = ?";
        $params[] = $filterStatus;
        $types   .= 's';
    }
    if ($searchQ !== '') {
        $sql    .= " AND l.lotteryNumber LIKE ?";
        $params[] = '%'.$searchQ.'%';
        $types   .= 's';
    }
    $sql .= " ORDER BY l.lotteryNumber ASC";

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $lotteries[] = $row;
    $stmt->close();
}

// หา lottery ที่จะแสดงใน popup เลือกจำนวน
$popupLottery = null;
if ($qtyPopup !== '' && $isLive) {
    $stmtP = $db->prepare(
        "SELECT l.lottery_id, l.lotteryNumber, l.price, l.status, d.draw_date
         FROM lotteries l
         INNER JOIN draws d ON l.draw_id = d.draw_id
         WHERE l.lottery_id = ? AND l.status = 'available'
         LIMIT 1"
    );
    $stmtP->bind_param('s', $qtyPopup);
    $stmtP->execute();
    $popupLottery = $stmtP->get_result()->fetch_assoc();
    $stmtP->close();
}
function statusBadge(string $s): string {
    return match($s) {
        'sold'     => '<span class="stag stag-sold">❌ จำหน่ายแล้ว</span>',
        'reserved' => '<span class="stag stag-reserved">🔒 จองแล้ว</span>',
        default    => '<span class="stag stag-ok">✅ ว่างอยู่</span>',
    };
}

if ($db) $db->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LottoShop — ลอตเตอรี่ออนไลน์</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="bg-glow"></div>
<div class="bg-dots"></div>

<!-- ══════════════════ HEADER ══════════════════ -->
<header>
  <div class="hdr">

    <a href="index.php" class="logo">
      <div>
        <span class="logo-name">LottoShop</span>
        <span class="logo-tag">Thai Lottery Online</span>
      </div>
    </a>

    <nav class="nav">
      <a href="index.php" class="active">หน้าแรก</a>
      <a href="###">ผลรางวัล</a>
    </nav>

    <div class="hdr-right">
      <?php if ($loggedIn): ?>
        <span class="btn-login">👤 <?= htmlspecialchars(mb_substr($userName,0,8)) ?></span>
        <form method="POST" action="?<?= qs() ?>" style="display:inline;margin-left:8px;">
          <input type="hidden" name="action" value="logout">
          <button type="submit" class="btn-register danger">ออกจากระบบ</button>
        </form>
      <?php else: ?>
        <a href="login.php" class="btn-login">👤 เข้าสู่ระบบ</a>
        <a href="register.php" class="btn-register">สมัครสมาชิก</a>
      <?php endif; ?>
      <a href="?<?= qs(['cart'=>'open']) ?>" class="btn-cart" title="ตะกร้า">
        🛒<span class="cart-dot <?= $cartCount > 0 ? 'show' : '' ?>"><?= $cartCount ?></span>
      </a>
    </div>

  </div>
</header>

<!-- ══════════════════ HERO ══════════════════ -->
<section class="hero">
  <div class="hero-eyebrow">
    <span class="pulse-dot"></span>
    จำหน่ายลอตเตอรี่งวด <?= htmlspecialchars($latestDrawTh) ?>
  </div>
  <h1>
    <span class="line1">ลอตเตอรี่รัฐบาลไทย</span>
    <span class="line2">ราคาเป็นทางการ สั่งซื้อออนไลน์ได้ทุกวัย</span>
  </h1>
  <p class="hero-sub">เลือกเลขโชค สั่งซื้อง่าย รับรองของแท้ทุกใบ</p>


</section>

<!-- ══════════════════ ตารางรางวัล ══════════════════ -->
<section class="prize-section">
  <div class="prize-wrap">
    <h2 class="prize-title">🏆 รางวัลประจำงวดที่ <?= htmlspecialchars($latestDrawTh) ?></h2>
    <div class="prize-grid">

      <div class="prize-card prize-top">
        <div class="prize-rank">รางวัลที่ 1</div>
        <div class="prize-amount">6,000,000 ฿</div>
        <div class="prize-count">1 รางวัล</div>
      </div>

      <div class="prize-card">
        <div class="prize-rank">รางวัลข้างเคียงที่ 1</div>
        <div class="prize-amount">100,000 ฿</div>
        <div class="prize-count">2 รางวัล</div>
      </div>

      <div class="prize-card">
        <div class="prize-rank">รางวัลที่ 2</div>
        <div class="prize-amount">200,000 ฿</div>
        <div class="prize-count">5 รางวัล</div>
      </div>

      <div class="prize-card">
        <div class="prize-rank">รางวัลที่ 3</div>
        <div class="prize-amount">80,000 ฿</div>
        <div class="prize-count">10 รางวัล</div>
      </div>

      <div class="prize-card">
        <div class="prize-rank">รางวัลที่ 4</div>
        <div class="prize-amount">40,000 ฿</div>
        <div class="prize-count">50 รางวัล</div>
      </div>

      <div class="prize-card">
        <div class="prize-rank">รางวัลที่ 5</div>
        <div class="prize-amount">20,000 ฿</div>
        <div class="prize-count">100 รางวัล</div>
      </div>

      <div class="prize-card">
        <div class="prize-rank">เลขหน้า 3 ตัว</div>
        <div class="prize-amount">4,000 ฿</div>
        <div class="prize-count">2,000 รางวัล</div>
      </div>

      <div class="prize-card">
        <div class="prize-rank">เลขท้าย 3 ตัว</div>
        <div class="prize-amount">4,000 ฿</div>
        <div class="prize-count">2,000 รางวัล</div>
      </div>

      <div class="prize-card">
        <div class="prize-rank">เลขท้าย 2 ตัว</div>
        <div class="prize-amount">2,000 ฿</div>
        <div class="prize-count">10,000 รางวัล</div>
      </div>

    </div>
  </div>
</section>

<!-- ══════════════════ SEARCH ══════════════════ -->
<div class="search-section">
  <form class="search-card" method="GET" action="index.php">
    <div class="search-row">
      <span class="search-ico">🔍</span>
      <input
        type="text" name="q"
        value="<?= htmlspecialchars($searchQ) ?>"
        placeholder="พิมพ์ตัวเลขเพื่อค้นหา เช่น 123, 56, 999..."
        autocomplete="off"
      >
      <input type="hidden" name="status" value="<?= htmlspecialchars($filterStatus) ?>">
      <button type="submit" class="btn-search">ค้นหา</button>
      <?php if ($searchQ !== ''): ?>
        <a href="?<?= qs(['q'=>'']) ?>" class="btn-clear">✕ ล้าง</a>
      <?php endif; ?>
      <span class="search-status">
        พบ <?= count($lotteries) ?> รายการ <?= $isLive ? '(DB)' : '(Demo)' ?>
      </span>
    </div>

    <div class="search-divider"></div>

    <div class="filter-row">
      <span class="filter-lbl" style="margin-left:14px;">สถานะ :</span>
      <a href="?<?= qs(['status'=>'available']) ?>" class="chip <?= $filterStatus==='available'?'on':'' ?>">ว่างอยู่</a>
      <a href="?<?= qs(['status'=>'reserved'])  ?>" class="chip <?= $filterStatus==='reserved' ?'on':'' ?>">จองแล้ว</a>
      <a href="?<?= qs(['status'=>'all'])        ?>" class="chip <?= $filterStatus==='all'      ?'on':'' ?>">ทั้งหมด</a>
    </div>
  </form>
</div>

<!-- ══════════════════ GRID ══════════════════ -->
<main class="main-wrap">
  <div class="sec-bar">
    <div class="sec-title">
      🎟 ลอตเตอรี่งวด <?= htmlspecialchars($latestDrawTh) ?>
      <span class="count-badge"><?= count($lotteries) ?> ใบ</span>
    </div>
    <div class="db-badge <?= $isLive ? 'live' : 'demo' ?>">
      <span class="blink"></span>
      <?= $isLive ? 'Live DB' : 'Demo Mode' ?>
    </div>
  </div>

  <div class="grid">
    <?php if (empty($lotteries)): ?>
      <div class="empty-state">
        <span class="empty-ico">🎟</span>
        <p>ไม่พบลอตเตอรี่ที่ตรงกับการค้นหา</p>
      </div>
    <?php else: ?>
      <?php foreach ($lotteries as $l): ?>
        <?php
          $num      = str_pad((string)$l['lotteryNumber'], 6, '0', STR_PAD_LEFT);
          $price    = (int)$l['price'];
          $drawDate = $l['draw_date'] ? thDate($l['draw_date']) : '—';
          $sold     = $l['status'] === 'sold';
          $rsv      = $l['status'] === 'reserved';
          $inCart   = isset($cart[$l['lottery_id']]);
        ?>
        <div class="card <?= $sold?'card-sold':'' ?> <?= $rsv?'card-reserved':'' ?>">
          <div class="ticket">
            <div class="t-period">งวด <?= htmlspecialchars($drawDate) ?></div>
            <div class="t-number"><?= htmlspecialchars($num) ?></div>
            <div class="t-id">ID: <?= htmlspecialchars($l['lottery_id']) ?></div>
            <?= statusBadge($l['status']) ?>
            <div class="notch l"></div>
            <div class="notch r"></div>
          </div>
          <div class="cbody">
            <div class="crow">
              <div class="price">฿<?= number_format($price) ?><span>/ใบ</span></div>
            </div>

            <?php if ($sold || $rsv): ?>
              <button class="btn-add" disabled>— ไม่ว่าง —</button>

            <?php elseif ($inCart): ?>
              <button class="btn-add in-cart" disabled>✓ อยู่ในตะกร้าแล้ว</button>

            <?php else: ?>
              <!-- ปุ่มเปิด popup เลือกจำนวนใบ -->
              <a href="?<?= qs(['qty_popup' => $l['lottery_id']]) ?>" class="btn-add">
                🛒 หยิบใส่ตะกร้า
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

     <!-- POPUP ตะกร้าสินค้า (CART SIDEBAR) -->

<!-- 1. Overlay: คลิกพื้นหลังเพื่อปิด popup -->
<a href="?<?= qs() ?>" class="overlay <?= $cartOpen?'open':'' ?>"></a>

<!-- 2. Sidebar popup หลัก -->
<aside class="sidebar <?= $cartOpen?'open':'' ?>">

  <!-- หัว popup -->
  <div class="sb-hdr">
    <h3>🛒 ตะกร้าของฉัน</h3>
    <a href="?<?= qs() ?>" class="btn-x">×</a>
  </div>

  <!-- รายการลอตเตอรี่ในตะกร้า -->
  <div class="sb-body">
    <?php if (empty($cart)): ?>
      <!-- กรณีตะกร้าว่าง -->
      <div class="sb-empty">
        <span class="sb-empty-ico">🎟</span>
        <p>ยังไม่มีรายการในตะกร้า</p>
      </div>
    <?php else: ?>
      <!-- วนแสดงแต่ละใบในตะกร้า -->
      <?php foreach ($cart as $it): ?>
        <div class="sb-item">
          <div style="flex:1">
            <div class="sb-num"><?= htmlspecialchars($it['lotteryNumber'] ?? '------') ?></div>
            <div class="sb-meta">งวด <?= htmlspecialchars($it['draw_date'] ?? '—') ?> &nbsp;·&nbsp; <?= (int)($it['qty'] ?? 1) ?> ใบ × ฿<?= number_format($it['price'] ?? 0) ?></div>
          </div>
          <div class="sb-price">฿<?= number_format(($it['price'] ?? 0) * ($it['qty'] ?? 1)) ?></div>
          <!-- ปุ่มลบรายการออกจากตะกร้า -->
          <form method="POST" action="?<?= qs(['cart'=>'open']) ?>">
            <input type="hidden" name="action"     value="remove">
            <input type="hidden" name="lottery_id" value="<?= htmlspecialchars($it['lottery_id']) ?>">
            <button type="submit" class="btn-del" title="ลบ">×</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- ส่วนล่าง: ยอดรวม + ปุ่มชำระเงิน -->
  <div class="sb-foot">
    <div class="total-row">
      <span class="tl">รวม (<?= $cartCount ?> ใบ)</span>
      <span class="tv">฿<?= number_format($cartTotal) ?></span>
    </div>
    <?php if ($cartCount > 0): ?>
      <!-- ปุ่มชำระเงิน: ลิงก์ไปหน้า checkout.php -->
      <a href="checkout.php" class="btn-checkout" style="display:block;text-align:center;text-decoration:none;">
        ชำระเงิน →
      </a>
    <?php else: ?>
      <!-- ปุ่มชำระเงิน: disabled เมื่อตะกร้าว่าง -->
      <button class="btn-checkout" disabled>ชำระเงิน →</button>
    <?php endif; ?>
  </div>
</aside>

     <!-- POPUP เลือกจำนวนใบ (QTY POPUP) -->
<?php if ($qtyPopup !== '' && $popupLottery): ?>
  <?php
    $pNum      = str_pad((string)$popupLottery['lotteryNumber'], 6, '0', STR_PAD_LEFT);
    $pPrice    = (int)$popupLottery['price'];
    $pDrawDate = $popupLottery['draw_date'] ? thDate($popupLottery['draw_date']) : '—';
  ?>

  <a href="?<?= qs() ?>" class="qty-overlay"></a>

  <div class="qty-modal">
    <a href="?<?= qs() ?>" class="qty-modal-close">×</a>

    <div class="qty-modal-header">
      <div class="qty-modal-ico">🎟</div>
      <h3>เลือกจำนวนใบ</h3>
    </div>

    <!-- ข้อมูลลอตเตอรี่ที่เลือก -->
    <div class="qty-ticket-preview">
      <div class="qty-period">งวด <?= htmlspecialchars($pDrawDate) ?></div>
      <div class="qty-number"><?= htmlspecialchars($pNum) ?></div>
      <div class="qty-price-unit">฿<?= number_format($pPrice) ?> / ใบ</div>
    </div>

    <!-- form เลือกจำนวน -->
    <form method="POST" action="?<?= qs(['cart'=>'open']) ?>" class="qty-form">
      <input type="hidden" name="action"        value="add">
      <input type="hidden" name="lottery_id"    value="<?= htmlspecialchars($popupLottery['lottery_id']) ?>">
      <input type="hidden" name="lotteryNumber" value="<?= htmlspecialchars($pNum) ?>">
      <input type="hidden" name="price"         value="<?= $pPrice ?>">
      <input type="hidden" name="draw_date"     value="<?= htmlspecialchars($pDrawDate) ?>">

      <div class="qty-label">จำนวน (สูงสุด 10 ใบ)</div>

      <!-- ปุ่ม − / ตัวเลข / + -->
      <div class="qty-ctrl">
        <button type="submit" name="qty_action" value="dec" class="qty-btn">−</button>
        <input  type="number" name="qty" id="qtyInput"
                value="<?= max(1, min(10, (int)($_GET['qty'] ?? 1))) ?>"
                min="1" max="10" class="qty-input" readonly>
        <button type="submit" name="qty_action" value="inc" class="qty-btn">+</button>
      </div>

      <!-- ยอดรวม -->
      <?php $qtyVal = max(1, min(10, (int)($_GET['qty'] ?? 1))); ?>
      <div class="qty-total">
        รวม <?= $qtyVal ?> ใบ = <strong>฿<?= number_format($pPrice * $qtyVal) ?></strong>
      </div>

      <button type="submit" name="qty_action" value="add" class="qty-confirm">
        ✅ ยืนยันหยิบใส่ตะกร้า
      </button>
    </form>
  </div>
<?php endif; ?>


<?php if ($toast): ?>
  <div class="toast-php"><?= htmlspecialchars($toast) ?></div>
<?php endif; ?>

<!-- ══════════════════ FOOTER ══════════════════ -->
<footer>
  <p class="gold" style="font-family:'Kanit',sans-serif;font-size:20px;margin-bottom:6px;">LottoShop</p>
  <p>ลอตเตอรี่รัฐบาลไทย งวด <?= htmlspecialchars($latestDrawTh) ?></p>
  <p>จำหน่ายในราคาอย่างเป็นทางการเท่านั้น &nbsp;|&nbsp; DB: <span class="gold">lottery-system</span></p>
  <p>ติดต่อ</p>
</footer>

</body>
</html>