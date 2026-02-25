<?php
/* ════════════════════════════════════════════════
   index.php — LottoShop หน้าหลัก
   ════════════════════════════════════════════════ */
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
   หางวดล่าสุด (draw_date สูงสุด) ที่มีลอตเตอรี่ขายอยู่จริง
   ════════════════════════════════════════════════ */
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
} else {
    foreach (getDemoData() as $l) {
        if ($latestDate === null || $l['draw_date'] > $latestDate) {
            $latestDate   = $l['draw_date'];
            $latestDrawId = $l['draw_id'];
        }
    }
    if ($latestDate) $latestDrawTh = thDate($latestDate);
}

/* ════════════════════════════════════════════════
   Stats — นับเฉพาะงวดล่าสุด
   ════════════════════════════════════════════════ */
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
        $key = $row['status'];
        if (isset($stats[$key])) $stats[$key] = (int)$row['c'];
    }
    $rS->close();
} elseif (!$isLive) {
    foreach (getDemoData() as $l) {
        if ($l['draw_date'] === $latestDate && isset($stats[$l['status']])) {
            $stats[$l['status']]++;
        }
    }
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
      <a href="#">ผลรางวัล</a>
    </nav>

    <div class="hdr-right">
      <button class="btn-login" onclick="openModal('login')">👤 เข้าสู่ระบบ</button>
      <button class="btn-register" onclick="openModal('register')">สมัครสมาชิก</button>
      <button class="btn-cart" onclick="openCart()" title="ตะกร้า">
        🛒<span class="cart-dot" id="cartDot">0</span>
      </button>
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

  <div class="stats-bar">
    <div class="stat">
      <span class="stat-l">รางวัลที่ 1</span>
      <span class="stat-v">6,000,000 ฿</span>
    </div>
    <div class="stat">
      <span class="stat-l">รางวัลที่ 2</span>
      <span class="stat-v">200,000 ฿</span>
    </div>
    <div class="stat">
      <span class="stat-l">รางวัลที่ 3</span>
      <span class="stat-v">80,000 ฿</span>
    </div>
    <div class="stat">
      <span class="stat-l">งวดออกรางวัล</span>
      <span class="stat-v"><?= htmlspecialchars($latestDrawTh) ?></span>
    </div>
    <div class="stat">
      <span class="stat-l">ราคา/ใบ</span>
      <span class="stat-v">120 ฿</span>
    </div>
  </div>
</section>

<!-- ══════════════════ SEARCH ══════════════════ -->
<div class="search-section">
  <div class="search-card">
    <div class="search-row">
      <span class="search-ico">🔍</span>
      <input
        type="text" id="searchInput"
        placeholder="พิมพ์ตัวเลขเพื่อค้นหาทันที เช่น 123, 56, 999..."
        autocomplete="off"
      >
      <span class="search-status" id="searchStatus">กำลังโหลด...</span>
    </div>

    <div class="search-divider"></div>

    <div class="filter-row">
      <span class="filter-lbl" style="margin-left:14px;">สถานะ :</span>
      <span class="chip on" data-status="available">ว่างอยู่</span>
      <span class="chip"    data-status="reserved">จองแล้ว</span>
      <span class="chip"    data-status="all">ทั้งหมด</span>
    </div>
  </div>
</div>

<!-- ══════════════════ GRID ══════════════════ -->
<main class="main-wrap">
  <div class="sec-bar">
    <div class="sec-title">
      🎟 ลอตเตอรี่งวด <?= htmlspecialchars($latestDrawTh) ?>
      <span class="count-badge" id="countBadge">—</span>
    </div>
    <div class="db-badge <?= $isLive ? 'live' : 'demo' ?>">
      <span class="blink"></span>
      <?= $isLive ? 'Live DB' : 'Demo Mode' ?>
    </div>
  </div>

  <div class="grid" id="lotteryGrid">
    <?php for($i=0;$i<8;$i++): ?><div class="skel-card"></div><?php endfor; ?>
  </div>
</main>

<!-- ══════════════════ CART SIDEBAR ══════════════════ -->
<div class="overlay" id="overlay" onclick="closeCart()"></div>
<aside class="sidebar" id="sidebar">
  <div class="sb-hdr">
    <h3>🛒 ตะกร้าของฉัน</h3>
    <button class="btn-x" onclick="closeCart()">×</button>
  </div>
  <div class="sb-body" id="sbBody">
    <div class="sb-empty">
      <span class="sb-empty-ico">🎟</span>
      <p>ยังไม่มีรายการในตะกร้า</p>
    </div>
  </div>
  <div class="sb-foot">
    <div class="total-row">
      <span class="tl">รวม (<span id="sbCount">0</span> ใบ)</span>
      <span class="tv" id="sbTotal">฿0</span>
    </div>
    <button class="btn-checkout" id="btnCheckout" disabled onclick="checkout()">
      ชำระเงิน →
    </button>
  </div>
</aside>

<!-- ══════════════════ LOGIN MODAL ══════════════════ -->
<div class="modal-bg" id="authModal" onclick="closeModalOutside(event)">
  <div class="modal" id="authBox">
    <button class="modal-close" onclick="closeModal()">×</button>

    <div class="modal-logo">
      <div class="m-ico">🎟</div>
      <h2>LottoShop</h2>
      <p id="modalDesc">เข้าสู่ระบบเพื่อซื้อลอตเตอรี่</p>
    </div>

    <div class="tab-row">
      <button class="tab-btn on" id="tabLogin"    onclick="switchTab('login')">เข้าสู่ระบบ</button>
      <button class="tab-btn"    id="tabRegister" onclick="switchTab('register')">สมัครสมาชิก</button>
    </div>

    <!-- ── Login Form ── -->
    <div id="frmLogin">
      <div class="form-group">
        <label>เบอร์โทรศัพท์ / อีเมล</label>
        <input type="text" id="loginUser" placeholder="กรอกเบอร์หรืออีเมล">
      </div>
      <div class="form-group">
        <label>รหัสผ่าน</label>
        <input type="password" id="loginPass" placeholder="••••••••">
      </div>
      <div class="form-forgot"><a href="#">ลืมรหัสผ่าน?</a></div>
      <button class="btn-submit" onclick="doLogin()">เข้าสู่ระบบ</button>
      <div class="modal-sep">หรือ</div>
      <button class="btn-social" onclick="doSocialLogin('Line')">
        <span>💚</span> เข้าสู่ระบบด้วย LINE
      </button>
      <button class="btn-social" onclick="doSocialLogin('Google')">
        <span>🔵</span> เข้าสู่ระบบด้วย Google
      </button>
      <div class="modal-switch">
        ยังไม่มีบัญชี? <a onclick="switchTab('register')">สมัครสมาชิกฟรี</a>
      </div>
    </div>

    <!-- ── Register Form ── -->
    <div id="frmRegister" style="display:none">
      <div class="form-group">
        <label>ชื่อ-นามสกุล</label>
        <input type="text" placeholder="กรอกชื่อ-นามสกุล">
      </div>
      <div class="form-group">
        <label>เบอร์โทรศัพท์</label>
        <input type="tel" placeholder="0xx-xxx-xxxx">
      </div>
      <div class="form-group">
        <label>อีเมล</label>
        <input type="email" placeholder="example@email.com">
      </div>
      <div class="form-group">
        <label>รหัสผ่าน</label>
        <input type="password" placeholder="อย่างน้อย 8 ตัวอักษร">
      </div>
      <button class="btn-submit" onclick="doRegister()">สมัครสมาชิก</button>
      <div class="modal-switch">
        มีบัญชีแล้ว? <a onclick="switchTab('login')">เข้าสู่ระบบ</a>
      </div>
    </div>

  </div>
</div>

<!-- ══════════════════ TOAST ══════════════════ -->
<div class="toast" id="toast"></div>

<!-- ══════════════════ FOOTER ══════════════════ -->
<footer>
  <p class="gold" style="font-family:'Kanit',sans-serif;font-size:20px;margin-bottom:6px;">LottoShop</p>
  <p>ลอตเตอรี่รัฐบาลไทย งวด <?= htmlspecialchars($latestDrawTh) ?></p>
  <p>จำหน่ายในราคาอย่างเป็นทางการเท่านั้น &nbsp;|&nbsp; DB: <span class="gold">lottery-system</span></p>
  <p>ติดต่อ</p>
</footer>

<!-- ══════════════════ JAVASCRIPT ══════════════════ -->
<script>
/* ── State ── */
let cart     = [];
let debounce = null;

const LATEST_DRAW_ID = '<?= htmlspecialchars($latestDrawId ?? '', ENT_QUOTES) ?>';

let currentFilter = {
  draw:   LATEST_DRAW_ID,
  status: 'available'
};

/* ════════════════════════════════════
   SEARCH & FETCH (Realtime)
   ════════════════════════════════════ */
function fetchLotteries(q = '') {
  const { draw, status } = currentFilter;
  const url = `api_search.php?q=${encodeURIComponent(q)}&draw_id=${encodeURIComponent(draw)}&status=${encodeURIComponent(status)}`;

  document.getElementById('searchStatus').textContent = 'กำลังค้นหา...';

  fetch(url)
    .then(r => r.json())
    .then(json => {
      renderGrid(json.data ?? []);
      const src = json.source === 'db' ? 'DB' : 'Demo';
      document.getElementById('countBadge').textContent   = (json.count ?? 0) + ' ใบ';
      document.getElementById('searchStatus').textContent = `พบ ${json.count ?? 0} รายการ (${src})`;
    })
    .catch(() => {
      document.getElementById('searchStatus').textContent = 'เกิดข้อผิดพลาด';
      document.getElementById('lotteryGrid').innerHTML =
        '<div class="empty-state"><span class="empty-ico">⚠️</span><p>ไม่สามารถโหลดข้อมูลได้</p></div>';
    });
}

document.getElementById('searchInput').addEventListener('input', function () {
  clearTimeout(debounce);
  debounce = setTimeout(() => fetchLotteries(this.value.trim()), 250);
});

/* ════════════════════════════════════
   RENDER CARDS
   ════════════════════════════════════ */
function renderGrid(items) {
  const grid = document.getElementById('lotteryGrid');
  if (!items.length) {
    grid.innerHTML = '<div class="empty-state"><span class="empty-ico">🎟</span><p>ไม่พบลอตเตอรี่ที่ตรงกับการค้นหา</p></div>';
    return;
  }

  grid.innerHTML = items.map(l => {
    const num    = String(l.lotteryNumber).padStart(6, '0');
    const price  = parseInt(l.price);
    const sold   = l.status === 'sold';
    const rsv    = l.status === 'reserved';
    const inCart = cart.some(c => c.lottery_id === l.lottery_id);

    let drawLabel = '—';
    if (l.draw_date) {
      try {
        const d  = new Date(l.draw_date + 'T00:00:00');
        const th = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
        drawLabel = d.getDate() + ' ' + th[d.getMonth()+1] + ' ' + (d.getFullYear()+543);
      } catch(e) { drawLabel = l.draw_date; }
    }

    let stag = sold
      ? '<span class="stag stag-sold">❌ จำหน่ายแล้ว</span>'
      : rsv
        ? '<span class="stag stag-reserved">🔒 จองแล้ว</span>'
        : '<span class="stag stag-ok">✅ ว่างอยู่</span>';

    let btn = '';
    if (sold || rsv) {
      btn = `<button class="btn-add" disabled>— ไม่ว่าง —</button>`;
    } else if (inCart) {
      btn = `<button class="btn-add in-cart" disabled>✓ อยู่ในตะกร้าแล้ว</button>`;
    } else {
      btn = `<button class="btn-add"
               onclick="addToCart('${l.lottery_id}','${num}',${price},'${drawLabel}')">
               🛒 หยิบใส่ตะกร้า</button>`;
    }

    return `
      <div class="card ${sold?'card-sold':''} ${rsv?'card-reserved':''}" id="card-${l.lottery_id}">
        <div class="ticket">
          <div class="t-period">งวด ${drawLabel}</div>
          <div class="t-number">${num}</div>
          <div class="t-id">ID: ${l.lottery_id}</div>
          ${stag}
          <div class="notch l"></div>
          <div class="notch r"></div>
        </div>
        <div class="cbody">
          <div class="crow">
            <div class="price">฿${price.toLocaleString()}<span>/ใบ</span></div>
          </div>
          ${btn}
        </div>
      </div>`;
  }).join('');
}

/* ════════════════════════════════════
   FILTER CHIPS
   ════════════════════════════════════ */
document.querySelectorAll('[data-draw]').forEach(c => {
  c.addEventListener('click', () => {
    document.querySelectorAll('[data-draw]').forEach(x => x.classList.remove('on'));
    c.classList.add('on');
    currentFilter.draw = c.dataset.draw;
    fetchLotteries(document.getElementById('searchInput').value.trim());
  });
});

document.querySelectorAll('[data-status]').forEach(c => {
  c.addEventListener('click', () => {
    document.querySelectorAll('[data-status]').forEach(x => x.classList.remove('on'));
    c.classList.add('on');
    currentFilter.status = c.dataset.status;
    fetchLotteries(document.getElementById('searchInput').value.trim());
  });
});

/* ════════════════════════════════════
   CART
   ════════════════════════════════════ */
function addToCart(id, num, price, drawDate) {
  if (cart.find(c => c.lottery_id === id)) return;
  cart.push({ lottery_id: id, lotteryNumber: num, price, draw_date: drawDate });
  updateCartUI();
  const card = document.getElementById('card-' + id);
  if (card) {
    const btn = card.querySelector('.btn-add');
    if (btn) { btn.disabled = true; btn.textContent = '✓ อยู่ในตะกร้าแล้ว'; btn.classList.add('in-cart'); }
  }
  toast(`✅ เพิ่ม ${num} ลงตะกร้าแล้ว`);
  openCart();
}

function removeFromCart(id) {
  cart = cart.filter(c => c.lottery_id !== id);
  updateCartUI();
  const card = document.getElementById('card-' + id);
  if (card) {
    const btn = card.querySelector('.btn-add');
    if (btn) {
      btn.disabled = false;
      btn.innerHTML = '🛒 หยิบใส่ตะกร้า';
      btn.classList.remove('in-cart');
      const num   = card.querySelector('.t-number').textContent.trim();
      const price = parseInt(card.querySelector('.price').textContent.replace(/[฿,\/ใบ\s]/g,''));
      const draw  = card.querySelector('.t-period').textContent.replace('งวด ','').trim();
      btn.onclick = () => addToCart(id, num, price, draw);
    }
  }
  toast('🗑️ นำรายการออกจากตะกร้าแล้ว');
}

function updateCartUI() {
  const count = cart.length;
  const total = cart.reduce((s, i) => s + i.price, 0);
  const dot = document.getElementById('cartDot');
  dot.textContent = count;
  dot.classList.toggle('show', count > 0);
  document.getElementById('sbCount').textContent = count;
  document.getElementById('sbTotal').textContent = '฿' + total.toLocaleString();
  document.getElementById('btnCheckout').disabled = count === 0;
  const body = document.getElementById('sbBody');
  if (!count) {
    body.innerHTML = '<div class="sb-empty"><span class="sb-empty-ico">🎟</span><p>ยังไม่มีรายการในตะกร้า</p></div>';
    return;
  }
  body.innerHTML = cart.map(it => `
    <div class="sb-item">
      <div style="flex:1">
        <div class="sb-num">${it.lotteryNumber}</div>
        <div class="sb-meta">งวด ${it.draw_date}</div>
      </div>
      <div class="sb-price">฿${it.price.toLocaleString()}</div>
      <button class="btn-del" onclick="removeFromCart('${it.lottery_id}')" title="ลบ">×</button>
    </div>`).join('');
}

function openCart()  { document.getElementById('sidebar').classList.add('open'); document.getElementById('overlay').classList.add('open'); }
function closeCart() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('overlay').classList.remove('open'); }

function checkout() {
  const total = cart.reduce((s,i)=>s+i.price,0);
  const nums  = cart.map(i=>i.lotteryNumber).join(', ');
  alert(`✅ สรุปคำสั่งซื้อ\n\nเลขที่เลือก: ${nums}\nรวม: ฿${total.toLocaleString()}\n\n(Demo — กรุณาเชื่อมต่อ Payment Gateway ในระบบจริง)`);
  cart = []; updateCartUI();
  document.querySelectorAll('.btn-add.in-cart').forEach(b => { b.disabled=false; b.innerHTML='🛒 หยิบใส่ตะกร้า'; b.classList.remove('in-cart'); });
  closeCart(); toast('✅ บันทึกคำสั่งซื้อแล้ว');
}

function openModal(tab = 'login') { document.getElementById('authModal').classList.add('open'); switchTab(tab); }
function closeModal() { document.getElementById('authModal').classList.remove('open'); }
function closeModalOutside(e) { if (e.target === document.getElementById('authModal')) closeModal(); }

function switchTab(tab) {
  const isLogin = tab === 'login';
  document.getElementById('frmLogin').style.display    = isLogin ? 'block' : 'none';
  document.getElementById('frmRegister').style.display = isLogin ? 'none'  : 'block';
  document.getElementById('tabLogin').classList.toggle('on',  isLogin);
  document.getElementById('tabRegister').classList.toggle('on', !isLogin);
  document.getElementById('modalDesc').textContent = isLogin ? 'เข้าสู่ระบบเพื่อซื้อลอตเตอรี่' : 'สร้างบัญชีใหม่ฟรี — ง่ายและรวดเร็ว';
}

function doLogin() {
  const u = document.getElementById('loginUser').value.trim();
  if (!u) { toast('⚠️ กรุณากรอกเบอร์หรืออีเมล'); return; }
  closeModal(); toast('✅ เข้าสู่ระบบสำเร็จ (Demo Mode)');
  document.querySelector('.btn-login').textContent = '👤 ' + u.substring(0,8);
}
function doSocialLogin(provider) { closeModal(); toast(`✅ เข้าสู่ระบบด้วย ${provider} (Demo Mode)`); }
function doRegister() { closeModal(); toast('✅ สมัครสมาชิกสำเร็จ (Demo Mode)'); }

let toastTimer;
function toast(msg) {
  const el = document.getElementById('toast');
  el.textContent = msg; el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 3000);
}

fetchLotteries();
</script>

</body>
</html>