<?php
session_start();
require_once "logconfig.php";

$conn = getDB();   // ⭐ เพิ่มบรรทัดนี้

if (!$conn) {
    die("Database connection failed");
}

$token = "";
$error = "";
$success = "";

// รับ token จาก URL
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
}

// เมื่อกดบันทึกรหัสใหม่
if (isset($_POST['reset'])) {

    $token = trim($_POST['token']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($token)) {
        $error = "Token ไม่ถูกต้อง";
    } elseif ($password !== $confirm_password) {
        $error = "รหัสผ่านไม่ตรงกัน";
    } else {

        // ตรวจสอบ token แบบปลอดภัย
        $stmt = $conn->prepare("SELECT * FROM password_reset 
                                WHERE reset_token=? 
                                AND expire_time > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error = "Token ไม่ถูกต้องหรือหมดอายุ";
        } else {

            $row = $result->fetch_assoc();
            $user_id = $row['user_id'];

            $newpass = password_hash($password, PASSWORD_DEFAULT);

            // อัปเดตรหัสผ่าน
            $stmt2 = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
            $stmt2->bind_param("si", $newpass, $user_id);
            $stmt2->execute();

            // ลบ token
            $stmt3 = $conn->prepare("DELETE FROM password_reset WHERE reset_token=?");
            $stmt3->bind_param("s", $token);
            $stmt3->execute();

            $success = "เปลี่ยนรหัสผ่านสำเร็จ";
            $token = ""; // ปิดฟอร์ม
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ตั้งรหัสผ่านใหม่ | LottoShop</title>
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
        </div>
    </header>

    <div class="modal-bg open"
        style="position:relative;display:flex;min-height:85vh;background:none;backdrop-filter:none;">
        <div class="modal" style="max-width:520px;">

            <div class="modal-logo">
                <div class="m-ico">🔑</div>
                <h2>ตั้งรหัสผ่านใหม่</h2>
                <p>กรอกรหัสผ่านใหม่ของคุณ</p>
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
                    <a href="login.php">
                        <button type="button" class="btn-submit">
                            เข้าสู่ระบบ
                        </button>
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($token)): ?>
                <form method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <label>รหัสผ่านใหม่</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label>ยืนยันรหัสผ่าน</label>
                        <input type="password" name="confirm_password" required>
                    </div>

                    <button type="submit" name="reset" class="btn-submit">
                        บันทึกรหัสผ่าน
                    </button>
                </form>
            <?php endif; ?>

        </div>
    </div>

</body>


</html>
