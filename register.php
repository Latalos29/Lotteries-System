<?php
session_start();
require_once "config.php";

$conn = getDB();  

if (!$conn) {
    die("Database connection failed");
}

$error = "";
$success = "";

if (isset($_POST['register'])) {

    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $phone = trim($_POST['phone']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // ตรวจสอบรหัสผ่าน
    if ($password !== $confirm_password) {
        $error = "รหัสผ่านไม่ตรงกัน";
    } else {

        // เช็ค username ซ้ำ
        $check = $conn->prepare("SELECT user_id FROM users WHERE username=? OR email=?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Username หรือ Email นี้ถูกใช้แล้ว";
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users 
                (firstname, lastname, phone, username, email, password) 
                VALUES (?, ?, ?, ?, ?, ?)");

            $stmt->bind_param(
                "ssssss",
                $firstname,
                $lastname,
                $phone,
                $username,
                $email,
                $hash
            );

            if ($stmt->execute()) {
                $success = "สมัครสมาชิกสำเร็จ 🎉";
            } else {
                $error = "เกิดข้อผิดพลาด: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก | LottoShop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="bg-glow"></div>
    <div class="bg-dots"></div>

    <!-- HEADER -->
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

    <!-- REGISTER SECTION -->
    <div class="modal-bg open"
        style="position:relative;display:flex;min-height:85vh;background:none;backdrop-filter:none;">
        <div class="modal" style="max-width:520px;">

            <div class="modal-logo">
                <div class="m-ico">📝</div>
                <h2>สมัครสมาชิก</h2>
                <p>สร้างบัญชีเพื่อเริ่มซื้อลอตเตอรี่</p>
            </div>

            <?php if (!empty($error)): ?>
                <div
                    style="background:#FADDDD;color:#A02A2A;padding:10px;border-radius:6px;margin-bottom:15px;text-align:center;font-size:14px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div
                    style="background:#D4EDE1;color:#1A6642;padding:10px;border-radius:6px;margin-bottom:15px;text-align:center;font-size:14px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>ชื่อ</label>
                    <input type="text" name="firstname" placeholder="กรอกชื่อ" required>
                </div>

                <div class="form-group">
                    <label>นามสกุล</label>
                    <input type="text" name="lastname" placeholder="กรอกนามสกุล" required>
                </div>

                <div class="form-group">
                    <label>เบอร์โทร</label>
                    <input type="text" name="phone" placeholder="กรอกเบอร์โทรศัพท์">
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="ตั้งชื่อผู้ใช้" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="example@email.com" required>
                </div>

                <div class="form-group">
                    <label>รหัสผ่าน</label>
                    <input type="password" name="password" placeholder="ตั้งรหัสผ่าน" required>
                </div>

                <div class="form-group">
                    <label>ยืนยันรหัสผ่าน</label>
                    <input type="password" name="confirm_password" placeholder="กรอกรหัสผ่านอีกครั้ง" required>
                </div>

                <button type="submit" name="register" class="btn-submit">
                    สมัครสมาชิก
                </button>

            </form>

            <div class="modal-switch">
                มีบัญชีแล้ว?
                <a href="login.php">เข้าสู่ระบบ</a>
            </div>

        </div>
    </div>

</body>

</html>