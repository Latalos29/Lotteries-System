<?php
/* ════════════════════════════════════════════════
   index.php — LottoShop (PHP Only, No JavaScript)
   ════════════════════════════════════════════════ */
session_start();
require_once 'config.php';

$db     = getDB();
$isLive = ($db !== null);

/* ── helper: format draw_date ── */
function thDate(string $d): string {
    $thM = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    $dt  = new DateTime($d);
    return (int)$dt->format('j').' '.$thM[(int)$dt->format('n')].' '.(((int)$dt->format('Y'))+543);
}

/* ════════════════════
   LOGIN / LOGOUT / REGISTER (PHP Session)
   ════════════════════ */
/* logout จาก index (profile dropdown) */
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['do_logout'])) {
    $_SESSION=[]; session_destroy();
    header('Location: index.php'); exit;
}

$user     = $_SESSION['user'] ?? null;
$loggedIn = ($user !== null);

/* ════════════════════
   CART (PHP Session)
   ════════════════════ */
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $lid   = htmlspecialchars($_POST['lottery_id']     ?? '');
        $lnum  = htmlspecialchars($_POST['lottery_number'] ?? '');
        $lprice= (int)($_POST['lottery_price']  ?? 0);
        $ldate = htmlspecialchars($_POST['lottery_date']   ?? '');
        if ($lid && !isset($_SESSION['cart'][$lid])) {
            $_SESSION['cart'][$lid] = ['id'=>$lid,'number'=>$lnum,'price'=>$lprice,'date'=>$ldate];
        }
        header('Location: index.php?' . http_build_query(array_filter([
            'q'=>$_POST['q']??'','status'=>$_POST['status']??'','draw_id'=>$_POST['draw_id']??'','cart'=>'open'
        ])));
        exit;
    }
    if (isset($_POST['remove_from_cart'])) {
        $lid = $_POST['remove_id'] ?? '';
        unset($_SESSION['cart'][$lid]);
        header('Location: index.php?cart=open');
        exit;
    }
    if (isset($_POST['do_checkout'])) {
        $_SESSION['cart'] = [];
        header('Location: index.php?msg=checkout');
        exit;
    }
}

$cartOpen  = ($_GET['cart'] ?? '') === 'open';
$cartItems = $_SESSION['cart'] ?? [];
$cartTotal = array_sum(array_column($cartItems, 'price'));
$cartCount = count($cartItems);

/* ════════════════════
   งวดล่าสุด
   ════════════════════ */
$latestDate   = null;
$latestDrawId = null;
$latestDrawTh = '—';

if ($isLive) {
    $rL = $db->query(
        "SELECT d.draw_date, MIN(d.draw_id) AS draw_id
         FROM draws d INNER JOIN lotteries l ON l.draw_id = d.draw_id
         WHERE d.status='open'
         GROUP BY d.draw_date ORDER BY d.draw_date DESC LIMIT 1"
    );
    if ($rowL = $rL->fetch_assoc()) {
        $latestDate   = $rowL['draw_date'];
        $latestDrawId = $rowL['draw_id'];
        $latestDrawTh = thDate($latestDate);
    }
} else {
    foreach (getDemoData() as $l) {
        if ($latestDate === null || $l['draw_date'] > $latestDate) {
            $latestDate   = $l['draw_date'];
            $latestDrawId = $l['draw_id'];
        }
    }
    if ($latestDate) $latestDrawTh = thDate($latestDate);
}

/* ════════════════════
   FILTER จาก GET
   ════════════════════ */
$q       = trim($_GET['q']       ?? '');
$status  = trim($_GET['status']  ?? 'available');
$drawId  = trim($_GET['draw_id'] ?? $latestDrawId ?? '');

/* ════════════════════
   ดึงลอตเตอรี่
   ════════════════════ */
$lotteries = [];

if ($isLive) {
    /* หา draw_date จาก draw_id ที่เลือก เพื่อดึงทุก draw_id ในงวดเดียวกัน */
    $filterDate = $latestDate;
    if ($drawId) {
        $stmtD = $db->prepare("SELECT draw_date FROM draws WHERE draw_id=? LIMIT 1");
        $stmtD->bind_param('s',$drawId);
        $stmtD->execute();
        $rd = $stmtD->get_result()->fetch_assoc();
        if ($rd) $filterDate = $rd['draw_date'];
        $stmtD->close();
    }

    $sql    = "SELECT l.lottery_id,l.lotteryNumber,l.price,l.status,d.draw_date
               FROM lotteries l LEFT JOIN draws d ON l.draw_id=d.draw_id WHERE 1=1";
    $params = []; $types = '';

    if ($q !== '') {
        $sql .= " AND CAST(l.lotteryNumber AS CHAR) LIKE ?";
        $params[] = "%$q%"; $types .= 's';
    }
    if ($filterDate) {
        $sql .= " AND d.draw_date=?";
        $params[] = $filterDate; $types .= 's';
    }
    if ($status !== 'all') {
        $sql .= " AND l.status=?";
        $params[] = $status; $types .= 's';
    }
    $sql .= " ORDER BY l.lotteryNumber ASC LIMIT 200";

    $stmt = $db->prepare($sql);
    if ($types) $stmt->bind_param($types,...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $lotteries[] = $row;
    $stmt->close();

} else {
    $lotteries = getDemoData();
    if ($q !== '')        $lotteries = array_values(array_filter($lotteries, fn($l)=>str_contains((string)$l['lotteryNumber'],$q)));
    if ($status !== 'all') $lotteries = array_values(array_filter($lotteries, fn($l)=>$l['status']===$status));
}

$lotteryCount = count($lotteries);

if ($db) $db->close();

/* ════════════════════
   ข้อความแจ้งเตือน
   ════════════════════ */
$flashMsg = '';
if (($_GET['msg'] ?? '') === 'checkout') $flashMsg = '✅ บันทึกคำสั่งซื้อเรียบร้อยแล้ว';

/* ════════════════════
   Avatar initial
   ════════════════════ */
function avatarInitial(string $name): string {
    // UTF-8 safe first char
    preg_match('/./u', $name, $m);
    return strtoupper($m[0] ?? '?');
}
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

<?php if ($flashMsg): ?>
<div class="flash-msg"><?= htmlspecialchars($flashMsg) ?></div>
<?php endif; ?>

<!-- ══════════ HEADER ══════════ -->
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
        <!-- Profile -->
        <div class="profile-wrap">
          <div class="profile-display">
            <div class="avatar"><?= htmlspecialchars(avatarInitial($user['name'])) ?></div>
            <span class="profile-name"><?= htmlspecialchars(mb_substr($user['name'],0,10)) ?></span>
          </div>
          <div class="profile-menu-inline">
            <div class="profile-menu-header">
              <div class="avatar-lg"><?= htmlspecialchars(avatarInitial($user['name'])) ?></div>
              <div>
                <div class="pm-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="pm-email"><?= htmlspecialchars($user['email'] ?: '—') ?></div>
              </div>
            </div>
            <div class="profile-menu-divider"></div>
            <a class="pm-item" href="###">👤 โปรไฟล์ของฉัน</a>
            <a class="pm-item" href="###">🛒 ตะกร้าสินค้า</a>
            <a class="pm-item" href="###">🎟 ผลรางวัล</a>
            <div class="profile-menu-divider"></div>
            <form method="POST">
              <button name="do_logout" class="pm-item pm-logout" type="submit">🚪 ออกจากระบบ</button>
            </form>
          </div>
        </div>
      <?php else: ?>
        <a href="###" class="btn-login">เข้าสู่ระบบ</a>
        <a href="###" class="btn-register">สมัครสมาชิก</a>
      <?php endif; ?>

      <a href="###" class="btn-cart" title="ตะกร้า">
        🛒<?php if ($cartCount > 0): ?>
          <span class="cart-dot show"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>
    </div>

  </div>
</header>

<!-- ══════════ HERO ══════════ -->
<section class="hero">
  <div class="hero-eyebrow">
    <span class="pulse-dot"></span>
    จำหน่ายลอตเตอรี่งวด <?= htmlspecialchars($latestDrawTh) ?>
  </div>
  <h1>
    <span class="line1">ลอตเตอรี่รัฐบาลไทย</span>
    <span class="line2">ราคาเป็นทางการ ส่งตรงถึงมือ</span>
  </h1>
  <p class="hero-sub">เลือกเลขโชค สั่งซื้อง่าย รับรองของแท้ทุกใบ</p>

  <div class="stats-bar">
    <div class="stat"><span class="stat-l">รางวัลที่ 1</span><span class="stat-v">6,000,000 ฿</span></div>
    <div class="stat"><span class="stat-l">รางวัลที่ 2</span><span class="stat-v">200,000 ฿</span></div>
    <div class="stat"><span class="stat-l">รางวัลที่ 3</span><span class="stat-v">80,000 ฿</span></div>
    <div class="stat"><span class="stat-l">งวดออกรางวัล</span><span class="stat-v"><?= htmlspecialchars($latestDrawTh) ?></span></div>
    <div class="stat"><span class="stat-l">ราคา/ใบ</span><span class="stat-v">120 ฿</span></div>
  </div>
</section>

<!-- ══════════ SEARCH ══════════ -->
<div class="search-section">
  <form method="GET" action="index.php" class="search-card">
    <div class="search-row">
      <span class="search-ico">🔍</span>
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
             placeholder="พิมพ์ตัวเลขเพื่อค้นหา เช่น 123, 56, 999...">
      <button type="submit" class="btn-search">ค้นหา</button>
    </div>

    <div class="search-divider"></div>

    <div class="filter-row">
      <span class="filter-lbl">สถานะ :</span>
      <?php foreach (['available'=>'ว่างอยู่','reserved'=>'จองแล้ว','all'=>'ทั้งหมด'] as $val=>$lbl): ?>
        <a href="?<?= http_build_query(['q'=>$q,'status'=>$val,'draw_id'=>$drawId]) ?>"
           class="chip <?= $status===$val?'on':'' ?>"><?= $lbl ?></a>
      <?php endforeach; ?>

      <?php if ($q): ?>
        <a href="?status=<?= urlencode($status) ?>" class="chip chip-clear">✕ ล้างการค้นหา</a>
      <?php endif; ?>

      <span class="search-status" style="margin-left:auto">
        พบ <?= $lotteryCount ?> รายการ (<?= $isLive?'DB':'Demo' ?>)
      </span>
    </div>
  </form>
</div>

<!-- ══════════ GRID ══════════ -->
<main class="main-wrap">
  <div class="sec-bar">
    <div class="sec-title">
      🎟 ลอตเตอรี่งวด <?= htmlspecialchars($latestDrawTh) ?>
      <span class="count-badge"><?= $lotteryCount ?> ใบ</span>
    </div>
    <div class="db-badge <?= $isLive?'live':'demo' ?>">
      <span class="blink"></span>
      <?= $isLive ? 'Live DB' : 'Demo Mode' ?>
    </div>
  </div>

  <?php if (empty($lotteries)): ?>
    <div class="empty-state">
      <span class="empty-ico">🎟</span>
      <p>ไม่พบลอตเตอรี่ที่ตรงกับการค้นหา</p>
    </div>
  <?php else: ?>
  <div class="grid">
    <?php foreach ($lotteries as $l):
      $num      = str_pad($l['lotteryNumber'], 6, '0', STR_PAD_LEFT);
      $price    = (int)$l['price'];
      $sold     = $l['status'] === 'sold';
      $rsv      = $l['status'] === 'reserved';
      $inCart   = isset($cartItems[$l['lottery_id']]);
      $drawLabel= $l['draw_date'] ? thDate($l['draw_date']) : '—';
    ?>
    <div class="card <?= $sold?'card-sold':'' ?> <?= $rsv?'card-reserved':'' ?>">
      <div class="ticket">
        <div class="t-period">งวด <?= htmlspecialchars($drawLabel) ?></div>
        <div class="t-number"><?= htmlspecialchars($num) ?></div>
        <div class="t-id">ID: <?= htmlspecialchars($l['lottery_id']) ?></div>
        <?php if ($sold): ?>
          <span class="stag stag-sold">❌ จำหน่ายแล้ว</span>
        <?php elseif ($rsv): ?>
          <span class="stag stag-reserved">🔒 จองแล้ว</span>
        <?php else: ?>
          <span class="stag stag-ok">✅ ว่างอยู่</span>
        <?php endif; ?>
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
          <form method="POST">
            <input type="hidden" name="lottery_id"     value="<?= htmlspecialchars($l['lottery_id']) ?>">
            <input type="hidden" name="lottery_number" value="<?= htmlspecialchars($num) ?>">
            <input type="hidden" name="lottery_price"  value="<?= $price ?>">
            <input type="hidden" name="lottery_date"   value="<?= htmlspecialchars($drawLabel) ?>">
            <input type="hidden" name="q"              value="<?= htmlspecialchars($q) ?>">
            <input type="hidden" name="status"         value="<?= htmlspecialchars($status) ?>">
            <input type="hidden" name="draw_id"        value="<?= htmlspecialchars($drawId) ?>">
            <button type="submit" name="add_to_cart" class="btn-add">🛒 หยิบใส่ตะกร้า</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>

<!-- ══════════ FOOTER ══════════ -->
<footer>
  <p class="gold" style="font-family:'Kanit',sans-serif;font-size:20px;margin-bottom:6px;">LottoShop</p>
  <p>ลอตเตอรี่รัฐบาลไทย งวด <?= htmlspecialchars($latestDrawTh) ?></p>
  <p>จำหน่ายในราคาอย่างเป็นทางการเท่านั้น &nbsp;|&nbsp; DB: <span class="gold">lottery-system</span></p>
  <p>ติดต่อ</p>
</footer>

</body>
</html>