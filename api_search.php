<?php
/* ════════════════════════════════════
   api_search.php — Realtime Search API
   GET params: q, draw_id, status
   Returns JSON
   ════════════════════════════════════ */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache');

require_once 'config.php';

$q      = trim($_GET['q']      ?? '');
$drawId = trim($_GET['draw_id']?? '');
$status = trim($_GET['status'] ?? 'available');

$db = getDB();

if ($db) {
    /* ── หา draw_date ของ draw_id ที่รับมา เพื่อ filter ทุก draw_id ในงวดเดียวกัน ── */
    $filterByDate = '';
    if ($drawId !== '') {
        $stmtD = $db->prepare("SELECT draw_date FROM draws WHERE draw_id = ? LIMIT 1");
        $stmtD->bind_param('s', $drawId);
        $stmtD->execute();
        $resD = $stmtD->get_result();
        if ($rowD = $resD->fetch_assoc()) {
            $filterByDate = $rowD['draw_date'];
        }
        $stmtD->close();
    }

    /* ── Live DB Query ── */
    $sql    = "SELECT l.lottery_id, l.lotteryNumber, l.price, l.draw_id, l.status,
                      d.draw_date
               FROM lotteries l
               LEFT JOIN draws d ON l.draw_id = d.draw_id
               WHERE 1=1";
    $params = [];
    $types  = '';

    if ($q !== '') {
        $sql   .= " AND CAST(l.lotteryNumber AS CHAR) LIKE ?";
        $params[] = "%$q%";
        $types .= 's';
    }
    /* filter by draw_date (ครอบทุก draw_id ของงวดนั้น) */
    if ($filterByDate !== '') {
        $sql   .= " AND d.draw_date = ?";
        $params[] = $filterByDate;
        $types .= 's';
    }
    if ($status !== 'all') {
        $sql   .= " AND l.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    $sql .= " ORDER BY l.lotteryNumber ASC LIMIT 200";

    $stmt = $db->prepare($sql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res  = $stmt->get_result();
    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    $stmt->close();
    $db->close();

    echo json_encode(['source'=>'db','count'=>count($data),'data'=>$data]);

} else {
    /* ── Demo Fallback ── */
    $data = getDemoData();

    /* หา draw_date ของ draw_id ที่รับมา */
    $filterByDate = '';
    if ($drawId !== '') {
        foreach ($data as $l) {
            if ($l['draw_id'] === $drawId) { $filterByDate = $l['draw_date']; break; }
        }
    }

    if ($q !== '') {
        $data = array_values(array_filter($data, fn($l) =>
            str_contains((string)$l['lotteryNumber'], $q)
        ));
    }
    if ($filterByDate !== '') {
        $data = array_values(array_filter($data, fn($l) => $l['draw_date'] === $filterByDate));
    }
    if ($status !== 'all') {
        $data = array_values(array_filter($data, fn($l) => $l['status'] === $status));
    }

    echo json_encode(['source'=>'demo','count'=>count($data),'data'=>$data]);
}