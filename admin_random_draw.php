<?php
require_once 'config.php';

function getDB() {
    $host ='localhost';
    $user = 'root';
    $password = '';
    $nameDB = '';

    $conn = mysqli_connect($host, $user, $password, $nameDB);
    if ($conn->connect_error) {
        return null;
    }

    return $conn;

}

$conn = getDB();

if ($conn === null) { die("Database connection failed!"); }

$status = ""; 
$draw_msg = "";
$user_ticket_info = null; // ตัวแปรเก็บข้อมูลตั๋วที่ User ตรวจ

// ---------------------------------------------------------
// 1. [BACK-END] Admin: กดสุ่มและอัปเดตสถานะ
// ---------------------------------------------------------
if (isset($_POST['admin_draw_action'])) {
    $win_no = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $today = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO draw_results (draw_date, winning_number) VALUES (?, ?)");
    $stmt->bind_param("ss", $today, $win_no);
    
    if ($stmt->execute()) {
        // อัปเดต Won/Lost
        $conn->query("UPDATE tickets SET status = 'Won' WHERE ticket_number = '$win_no'");
        $conn->query("UPDATE tickets SET status = 'Lost' WHERE ticket_number != '$win_no' AND status = 'Pending'");
        $draw_msg = "ออกรางวัลแล้ว: $win_no";
    }
}

// ---------------------------------------------------------
// 2. [FRONT-END] User: ตรวจรางวัลและดึงข้อมูล ID / จำนวน
// ---------------------------------------------------------
if (isset($_POST['user_check_action'])) {
    $search_no = $_POST['ticket_to_check'];
    // ดึงทั้ง ID, เลขที่ซื้อ, จำนวน (amount), และสถานะ
    $stmt = $conn->prepare("SELECT id, ticket_number, amount, status FROM tickets WHERE ticket_number = ? LIMIT 1");
    $stmt->bind_param("s", $search_no);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $status = $row['status'];
        $user_ticket_info = $row; // เก็บก้อนข้อมูลไว้ไปโชว์ใน Pop-up
    } else {
        $status = "not_found";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ระบบลอตเตอรี่ - ข้อมูลครบวงจร</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Kanit', sans-serif; background: #eceff1; padding: 20px; display: flex; flex-direction: column; align-items: center; }
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 450px; text-align: center; }
        .admin-section { margin-top: 40px; padding: 15px; background: #fff; border-left: 5px solid #e74c3c; width: 100%; max-width: 450px; }
        input { width: 80%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; font-size: 18px; text-align: center; }
        button { cursor: pointer; border-radius: 5px; border: none; font-weight: bold; }
        .btn-check { background: #2980b9; color: white; padding: 10px 20px; width: 85%; }
        .btn-draw { background: #c0392b; color: white; padding: 10px 15px; }
    </style>
</head>
<body>

    <div class="card">
        <h2>🔍 ตรวจสอบใบเสร็จลอตเตอรี่</h2>
        <form method="POST">
            <input type="text" name="ticket_to_check" maxlength="6" placeholder="ระบุเลขที่ซื้อ" required>
            <button type="submit" name="user_check_action" class="btn-check">เช็กสถานะการซื้อ</button>
        </form>
    </div>

    <div class="admin-section">
        <h4>🛠️ ระบบสุ่มรางวัลหลังบ้าน</h4>
        <form method="POST">
            <button type="submit" name="admin_draw_action" class="btn-draw" onclick="return confirm('สุ่มใหม่ตอนนี้เลยไหม?')">สุ่มตัวเลขงวดล่าสุด</button>
        </form>
        <?php if($draw_msg) echo "<p style='color:red;'>$draw_msg</p>"; ?>
    </div>

    <script>
    <?php if ($status != ""): ?>
        let s = "<?php echo $status; ?>";
        let info = <?php echo json_encode($user_ticket_info); ?>;

        if (s === "not_found") {
            Swal.fire('ไม่พบข้อมูล', 'มึงยังไม่ได้ซื้อเลขนี้', 'warning');
        } else {
            let title = (s === "Won") ? "ดีใจด้วย มึงถูกรางวัล! 🎉" : "เสียใจด้วย มึงโดนกิน 💸";
            let icon = (s === "Won") ? "success" : "error";
            
            // โชว์ข้อมูลที่มึงอยากได้: ID, เลขที่ซื้อ, จำนวน
            Swal.fire({
                title: title,
                html: `
                    <div style="text-align: left; padding: 10px; background: #f9f9f9; border-radius: 10px;">
                        <p><b>🆔 ID รายการ:</b> #${info.id}</p>
                        <p><b>🔢 เลขที่ซื้อ:</b> ${info.ticket_number}</p>
                        <p><b>📦 จำนวนที่ซื้อ:</b> ${info.amount} ใบ</p>
                        <p><b>📌 สถานะ:</b> ${s}</p>
                    </div>
                `,
                icon: icon,
                confirmButtonText: 'รับทราบ'
            });
        }
    <?php endif; ?>
    </script>

</body>
</html>