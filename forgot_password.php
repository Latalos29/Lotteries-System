<?php
session_start();
require_once "logconfig.php";

$conn = getDB();

if (!$conn) {
    die("Database connection failed");
}

$error = "";
$success = "";
$reset_link = "";

if (isset($_POST['submit'])) {

    $email = trim($_POST['email']);

    // ค้นหา email แบบปลอดภัย
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];

        $token = bin2hex(random_bytes(16));
        $expire = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt2 = $conn->prepare("INSERT INTO password_reset (user_id, reset_token, expire_time)
                                 VALUES (?, ?, ?)");
        $stmt2->bind_param("iss", $user_id, $token, $expire);
        $stmt2->execute();

        $success = "สร้างลิงก์รีเซ็ตแล้ว";
        $reset_link = "reset.php?token=$token";

    } else {
        $error = "ไม่พบอีเมลนี้";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ลืมรหัสผ่าน | LottoShop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="bg-glow"></div>
    <div class="bg-dots"></div>

    <header>
        <div class="hdr">
            <a href="index.php" class="logo">
                <div>
                    <span class="logo-name">LottoShop</span>
                    <span class="logo-tag">Thai Lottery Online</span>
                </div>
            </a>

            <div class="hdr-right">
                <a href="login.php">
                    <button class="btn-login">เข้าสู่ระบบ</button>
                </a>
            </div>
        </div>
    </header>

    <div class="modal-bg open"
        style="position:relative;display:flex;min-height:85vh;background:none;backdrop-filter:none;">
        <div class="modal" style="max-width:520px;">

            <div class="modal-logo">
                <div class="m-ico">🔐</div>
                <h2>ลืมรหัสผ่าน</h2>
                <p>กรอกอีเมลเพื่อสร้างลิงก์รีเซ็ต</p>
            </div>

            <?php if (!empty($error)): ?>
                <div
                    style="background:#FADDDD;color:#A02A2A;padding:10px;border-radius:6px;margin-bottom:15px;text-align:center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div
                    style="background:#D4EDE1;color:#1A6642;padding:10px;border-radius:6px;margin-bottom:15px;text-align:center;">
                    <?php echo $success; ?><br><br>
                    <a href="<?php echo htmlspecialchars($reset_link); ?>">
                        <button type="button" class="btn-submit">
                            คลิกที่นี่เพื่อรีเซ็ต
                        </button>
                    </a>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="example@email.com" required>
                </div>

                <button type="submit" name="submit" class="btn-submit">
                    ส่งคำขอรีเซ็ต
                </button>

            </form>

            <div class="modal-switch">
                <a href="login.php">← กลับหน้า Login</a>
            </div>

        </div>
    </div>

</body>


</html>
