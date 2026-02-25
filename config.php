<?php
/* ══════════════════════════════════════
   config.php — เชื่อมต่อ DB lottery-system
   ══════════════════════════════════════ */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lottery-system');

function getDB(): ?mysqli {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) return null;
    $conn->set_charset('utf8mb4');
    return $conn;
}

/* ── Demo Data (fallback เมื่อไม่มี DB) ── */
// function getDemoData(): array {
//     return [
//         ['lottery_id'=>'3000000001','lotteryNumber'=>100001,'price'=>80, 'draw_id'=>'30000001','draw_date'=>'2026-03-16','status'=>'available'],
//         ['lottery_id'=>'3000000002','lotteryNumber'=>200034,'price'=>80, 'draw_id'=>'30000001','draw_date'=>'2026-03-16','status'=>'available'],
//         ['lottery_id'=>'3000000003','lotteryNumber'=>345678,'price'=>100,'draw_id'=>'30000002','draw_date'=>'2026-03-16','status'=>'available'],
//         ['lottery_id'=>'3000000004','lotteryNumber'=>456789,'price'=>100,'draw_id'=>'30000002','draw_date'=>'2026-03-16','status'=>'reserved'],
//         ['lottery_id'=>'3000000005','lotteryNumber'=>567890,'price'=>120,'draw_id'=>'30000003','draw_date'=>'2026-03-16','status'=>'available'],
//         ['lottery_id'=>'3000000006','lotteryNumber'=>678901,'price'=>120,'draw_id'=>'30000003','draw_date'=>'2026-03-16','status'=>'sold'],
//         ['lottery_id'=>'3000000007','lotteryNumber'=>789012,'price'=>80, 'draw_id'=>'30000004','draw_date'=>'2026-03-16','status'=>'available'],
//         ['lottery_id'=>'3000000008','lotteryNumber'=>890123,'price'=>80, 'draw_id'=>'30000004','draw_date'=>'2026-03-16','status'=>'reserved'],
//         ['lottery_id'=>'3000000009','lotteryNumber'=>901234,'price'=>100,'draw_id'=>'30000005','draw_date'=>'2026-03-16','status'=>'available'],
//         ['lottery_id'=>'3000000010','lotteryNumber'=>123456,'price'=>100,'draw_id'=>'30000005','draw_date'=>'2026-03-16','status'=>'sold'],
//         ['lottery_id'=>'3000000011','lotteryNumber'=>234567,'price'=>120,'draw_id'=>'30000006','draw_date'=>'2026-04-01','status'=>'available'],
//         ['lottery_id'=>'3000000012','lotteryNumber'=>135792,'price'=>80, 'draw_id'=>'30000006','draw_date'=>'2026-04-01','status'=>'available'],
//         ['lottery_id'=>'3000000013','lotteryNumber'=>246801,'price'=>100,'draw_id'=>'30000007','draw_date'=>'2026-04-01','status'=>'reserved'],
//         ['lottery_id'=>'3000000014','lotteryNumber'=>357912,'price'=>100,'draw_id'=>'30000007','draw_date'=>'2026-04-01','status'=>'available'],
//         ['lottery_id'=>'3000000015','lotteryNumber'=>468023,'price'=>120,'draw_id'=>'30000008','draw_date'=>'2026-04-01','status'=>'sold'],
//         ['lottery_id'=>'3000000016','lotteryNumber'=>579134,'price'=>80, 'draw_id'=>'30000008','draw_date'=>'2026-04-01','status'=>'available'],
//         ['lottery_id'=>'3000000017','lotteryNumber'=>680245,'price'=>100,'draw_id'=>'30000009','draw_date'=>'2026-04-01','status'=>'available'],
//         ['lottery_id'=>'3000000018','lotteryNumber'=>791356,'price'=>80, 'draw_id'=>'30000009','draw_date'=>'2026-04-01','status'=>'reserved'],
//         ['lottery_id'=>'3000000019','lotteryNumber'=>802467,'price'=>100,'draw_id'=>'30000010','draw_date'=>'2026-04-01','status'=>'available'],
//         ['lottery_id'=>'3000000020','lotteryNumber'=>913578,'price'=>120,'draw_id'=>'30000010','draw_date'=>'2026-04-01','status'=>'sold'],
//     ];
// }