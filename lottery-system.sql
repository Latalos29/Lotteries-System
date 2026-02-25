<?php
/* ══════════════════════════════════
   index.php — โชว์ & ค้นหาลอตเตอรี่
   ══════════════════════════════════ */

$allLotteries = [
  ['lottery_id'=>'3000000001','lotteryNumber'=>'100001','price'=>80, 'draw_id'=>'30000001','status'=>'available','draw_date'=>'2026-03-16'],
  ['lottery_id'=>'3000000002','lotteryNumber'=>'200034','price'=>80, 'draw_id'=>'30000001','status'=>'available','draw_date'=>'2026-03-16'],
  ['lottery_id'=>'3000000003','lotteryNumber'=>'345678','price'=>100,'draw_id'=>'30000002','status'=>'available','draw_date'=>'2026-03-16'],
  ['lottery_id'=>'3000000004','lotteryNumber'=>'456789','price'=>100,'draw_id'=>'30000002','status'=>'reserved', 'draw_date'=>'2026-03-16'],
  ['lottery_id'=>'3000000005','lotteryNumber'=>'567890','price'=>120,'draw_id'=>'30000003','status'=>'available','draw_date'=>'2026-03-16'],
  ['lottery_id'=>'3000000006','lotteryNumber'=>'678901','price'=>120,'draw_id'=>'30000003','status'=>'sold',     'draw_date'=>'2026-03-16'],
  ['lottery_id'=>'3000000007','lotteryNumber'=>'789012','price'=>80, 'draw_id'=>'30000004','status'=>'available','draw_date'=>'2026-03-16'],
  ['lottery_id'=>'3000000008','lotteryNumber'=>'890123','price'=>80, 'draw_id'=>'30000004','status'=>'reserved', 'draw_date'=>'2026-03-16'],
  ['lottery_id'=>'3000000009','lotteryNumber'=>'901234','price'=>100,'draw_id'=>'30000005','status'=>'available','draw_date'=>'2026-03-16'],
  ['lottery_id'=>'3000000010','lotteryNumber'=>'123456','price'=>100,'draw_id'=>'30000005','status'=>'sold',     'draw_date'=>'2026-03-16'],
  ['lottery_id'=>'3000000011','lotteryNumber'=>'234567','price'=>120,'draw_id'=>'30000006','status'=>'available','draw_date'=>'2026-04-01'],
  ['lottery_id'=>'3000000012','lotteryNumber'=>'135792','price'=>80, 'draw_id'=>'30000006','status'=>'available','draw_date'=>'2026-04-01'],
  ['lottery_id'=>'3000000013','lotteryNumber'=>'246801','price'=>100,'draw_id'=>'30000007','status'=>'reserved', 'draw_date'=>'2026-04-01'],
  ['lottery_id'=>'3000000014','lotteryNumber'=>'357912','price'=>100,'draw_id'=>'30000007','status'=>'available','draw_date'=>'2026-04-01'],
  ['lottery_id'=>'3000000015','lotteryNumber'=>'468023','price'=>120,'draw_id'=>'30000008','status'=>'sold',     'draw_date'=>'2026-04-01'],
  ['lottery_id'=>'3000000016','lotteryNumber'=>'579134','price'=>80, 'draw_id'=>'30000008','status'=>'available','draw_date'=>'2026-04-01'],
  ['lottery_id'=>'3000000017','lotteryNumber'=>'680245','price'=>100,'draw_id'=>'30000009','status'=>'available','draw_date'=>'2026-04-01'],
  ['lottery_id'=>'3000000018','lotteryNumber'=>'791356','price'=>80, 'draw_id'=>'30000009','status'=>'reserved', 'draw_date'=>'2026-04-01'],
  ['lottery_id'=>'3000000019','lotteryNumber'=>'802467','price'=>100,'draw_id'=>'30000010','status'=>'available','draw_date'=>'2026-04-01'],
  ['lottery_id'=>'3000000020','lotteryNumber'=>'913578','price'=>120,'draw_id'=>'30000010','status'=>'sold',     'draw_date'=>'2026-04-01'],
  ['lottery_id'=>'12345678',  'lotteryNumber'=>'111111','price'=>100,'draw_id'=>'11111111','status'=>'available','draw_date'=>'2026-02-16'],
  ['lottery_id'=>'87654321',  'lotteryNumber'=>'222222','price'=>120,'draw_id'=>'22222222','status'=>'reserved', 'draw_date'=>'2026-01-16'],
  ['lottery_id'=>'43216578',  'lotteryNumber'=>'321654','price'=>150,'draw_id'=>'33333333','status'=>'sold',     'draw_date'=>'2026-02-16'],
];

$countAvail = count(array_filter($allLotteries, fn($l) => $l['status'] === 'available'));
$countSold  = count(array_filter($allLotteries, fn($l) => $l['status'] === 'sold'));

function fmtDate(string $d): string {
  $mo = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
  [$y,$m,$day] = explode('-', $d);
  return "$day {$mo[(int)$m]} ".((int)$y+543);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>LottoShop — ลอตเตอรี่ออนไลน์</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="bg-mesh"></div>
<div class="bg-dots"></div>

<!-- HEADER -->
<header>
  <div class="hdr">
    <a href="index.php" class="logo">
      <div class="logo-ic">🎟</div>
      <div>
        <span class="logo-name">LottoShop</span>
        <span class="logo-sub">Thai Government Lottery</span>
      </div>
    </a>
    <nav class="nav">
      <a href="index.php" class="here">หน้าแรก</a>
      <a href="#">ผลรางวัล</a>
      <a href="#">วิธีสั่งซื้อ</a>
      <div class="nav-sep"></div>
      <a href="login.php" class="btn-login">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
          <circle cx="12" cy="8" r="4"/>
          <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
        </svg>
        เข้าสู่ระบบ
      </a>
    </nav>
  </div>
</header>

<!-- HERO -->
<section class="hero">
  <div class="hero-pill">🏆 งวดประจำวันที่ 16 มีนาคม 2569</div>
  <h1>ลอตเตอรี่รัฐบาลไทย<br>ราคาตามหน้าบัตร</h1>
  <p>เลือกดูลอตเตอรี่ที่มีขาย ค้นหาเลขที่ชอบได้เลย</p>
  <div class="stats">
    <div class="stat">
      <span class="stat-lbl">รางวัลที่ 1</span>
      <span class="stat-val">6,000,000 ฿</span>
    </div>
    <div class="stat">
      <span class="stat-lbl">ว่างอยู่</span>
      <span class="stat-val green"><?= $countAvail ?> ใบ</span>
    </div>
    <div class="stat">
      <span class="stat-lbl">จำหน่ายแล้ว</span>
      <span class="stat-val red"><?= $countSold ?> ใบ</span>
    </div>
    <div class="stat">
      <span class="stat-lbl">ออกรางวัล</span>
      <span class="stat-val">16 มี.ค.</span>
    </div>
  </div>
</section>

<!-- SEARCH & FILTER -->
<div class="search-wrap">
  <div class="search-row">
    <span class="search-ic">🔍</span>
    <input
      type="text" id="searchInput"
      placeholder="พิมพ์เลขที่ต้องการ เช่น 100001, 456, 78..."
      oninput="onSearch(this.value)"
      autocomplete="off"
    >
    <span class="search-hint" id="searchHint"></span>
    <button class="search-clear" id="searchClear" onclick="clearSearch()">✕</button>
  </div>
  <div class="filter-row">
    <span class="fl">สถานะ :</span>
    <a class="chip on" href="#" onclick="filterStatus('all',this)">ทั้งหมด</a>
    <a class="chip"    href="#" onclick="filterStatus('available',this)">ว่างอยู่</a>
    <a class="chip"    href="#" onclick="filterStatus('reserved',this)">จองแล้ว</a>
    <a class="chip"    href="#" onclick="filterStatus('sold',this)">จำหน่ายแล้ว</a>
    <div class="sep"></div>
    <span class="fl">งวด :</span>
    <a class="chip on" href="#" onclick="filterDraw('',this)">ทั้งหมด</a>
    <a class="chip"    href="#" onclick="filterDraw('2026-03-16',this)">16 มี.ค. 2569</a>
    <a class="chip"    href="#" onclick="filterDraw('2026-04-01',this)">1 เม.ย. 2569</a>
  </div>
</div>

<!-- GRID -->
<main class="main">
  <div class="sec-hd">
    <h2>🎟 ลอตเตอรี่ทั้งหมด</h2>
    <span class="sec-cnt" id="gridCount"><?= count($allLotteries) ?> ใบ</span>
  </div>

  <div class="no-result" id="noResult">
    <span class="no-result-ic">🔍</span>
    <p>ไม่พบเลขที่ค้นหา</p>
    <p style="margin-top:6px;font-size:13px;">ลองพิมพ์เลขอื่นดูนะครับ</p>
  </div>

  <div class="grid" id="grid">
    <?php foreach ($allLotteries as $l):
      $numFmt   = str_pad($l['lotteryNumber'], 6, '0', STR_PAD_LEFT);
      $drawDate = fmtDate($l['draw_date']);
      $status   = $l['status'];
    ?>
    <div class="card <?= $status === 'sold' ? 'sold' : ($status === 'reserved' ? 'reserved' : '') ?>"
         data-number="<?= $l['lotteryNumber'] ?>"
         data-status="<?= $status ?>"
         data-draw="<?= $l['draw_date'] ?>">

      <div class="tf">
        <div class="tf-period">งวด <?= $drawDate ?></div>
        <div class="tf-num" data-raw="<?= $numFmt ?>"><?= $numFmt ?></div>
        <div class="notch nl"></div>
        <div class="notch nr"></div>
      </div>

      <div class="draw-ribbon">Draw <?= htmlspecialchars($l['draw_id']) ?> · <?= $drawDate ?></div>

      <div class="cb">
        <div class="cb-row">
          <div class="price">฿<?= number_format($l['price']) ?><span class="price-u">/ใบ</span></div>
          <?php if ($status === 'sold'): ?>
            <span class="stag tag-sold">จำหน่ายแล้ว</span>
          <?php elseif ($status === 'reserved'): ?>
            <span class="stag tag-reserved">จองแล้ว</span>
          <?php else: ?>
            <span class="stag tag-ok">ว่างอยู่</span>
          <?php endif; ?>
        </div>
        <a href="detail.php?id=<?= htmlspecialchars($l['lottery_id']) ?>" class="btn-detail">
          ดูรายละเอียด →
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</main>

<!-- FOOTER -->
<footer>
  <p class="gold">LottoShop</p>
  <p>ลอตเตอรี่รัฐบาลไทย · จำหน่ายราคาตามหน้าบัตร 80–150 บาท</p>
</footer>

<script>
let activeStatus = 'all';
let activeDraw   = '';
let searchTerm   = '';
let searchTimer;

/* Real-time search */
function onSearch(val) {
  searchTerm = val.trim();
  document.getElementById('searchClear').classList.toggle('show', searchTerm !== '');
  clearTimeout(searchTimer);
  searchTimer = setTimeout(applyFilters, 120);
}

function clearSearch() {
  document.getElementById('searchInput').value = '';
  searchTerm = '';
  document.getElementById('searchClear').classList.remove('show');
  document.getElementById('searchHint').textContent = '';
  applyFilters();
}

/* Filter chips */
function filterStatus(s, el) {
  event.preventDefault();
  activeStatus = s;
  el.closest('.filter-row').querySelectorAll('.chip').forEach(c => {
    if (c.getAttribute('onclick') && c.getAttribute('onclick').startsWith('filterStatus'))
      c.classList.remove('on');
  });
  el.classList.add('on');
  applyFilters();
}

function filterDraw(d, el) {
  event.preventDefault();
  activeDraw = d;
  el.closest('.filter-row').querySelectorAll('.chip').forEach(c => {
    if (c.getAttribute('onclick') && c.getAttribute('onclick').startsWith('filterDraw'))
      c.classList.remove('on');
  });
  el.classList.add('on');
  applyFilters();
}

/* Apply */
function applyFilters() {
  const cards = document.querySelectorAll('#grid .card');
  let visible = 0;

  cards.forEach(card => {
    const num    = card.dataset.number;
    const numPad = num.padStart(6, '0');
    const status = card.dataset.status;
    const draw   = card.dataset.draw;

    const matchSearch = !searchTerm || numPad.includes(searchTerm) || num.includes(searchTerm);
    const matchStatus = activeStatus === 'all' || status === activeStatus;
    const matchDraw   = activeDraw   === ''    || draw   === activeDraw;

    const show = matchSearch && matchStatus && matchDraw;
    card.classList.toggle('hidden', !show);

    if (show) {
      visible++;
      const el = card.querySelector('.tf-num');
      el.innerHTML = searchTerm && matchSearch
        ? highlight(numPad, searchTerm)
        : numPad;
    }
  });

  document.getElementById('gridCount').textContent = visible + ' ใบ';
  document.getElementById('noResult').style.display = visible === 0 ? 'block' : 'none';

  const hint = document.getElementById('searchHint');
  if (searchTerm) {
    hint.textContent = visible > 0 ? `พบ ${visible} ใบ` : 'ไม่พบ';
    hint.style.color = visible > 0 ? 'var(--green)' : 'var(--red)';
  } else {
    hint.textContent = '';
  }
}

function highlight(str, term) {
  const i = str.indexOf(term);
  if (i === -1) return str;
  return str.slice(0, i)
    + `<mark style="background:rgba(212,168,67,.35);color:var(--gold2);border-radius:3px;padding:0 1px">${str.slice(i, i + term.length)}</mark>`
    + str.slice(i + term.length);
}
</script>
</body>
</html>