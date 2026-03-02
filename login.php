<?php
session_start();
require_once "logconfig.php";

$conn = getDB();

if (!$conn) {
    die("Database connection failed");
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {

            $_SESSION['user'] = [
                'id' => $row['user_id'],
                'name' => $row['username'],
                'email' => $row['email'] ?? ''
            ];
            header("Location: index.php");
            exit();

        } else {
            $error = "รหัสผ่านไม่ถูกต้อง";
        }

    } else {
        $error = "ไม่พบผู้ใช้";
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>เข้าสู่ระบบ | LottoShop</title>
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
                <a href="register.php">
                    <button class="btn-register">สมัครสมาชิก</button>
                </a>
            </div>
        </div>
    </header>

    <!-- LOGIN SECTION -->
    <div class="modal-bg open"
        style="position:relative;display:flex;min-height:85vh;background:none;backdrop-filter:none;">
        <div class="modal">

            <div class="modal-logo">
                <div class="m-ico">🎟️</div>
                <h2>เข้าสู่ระบบ</h2>
                <p>เข้าสู่ระบบเพื่อซื้อลอตเตอรี่</p>
            </div>

            <?php if (!empty($error)): ?>
                <div
                    style="background:#FADDDD;color:#A02A2A;padding:10px;border-radius:6px;margin-bottom:15px;text-align:center;font-size:14px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="กรอกชื่อผู้ใช้" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="กรอกรหัสผ่าน" required>
                </div>

                <div class="form-forgot">
                    <a href="forgot_password.php">ลืมรหัสผ่าน?</a>
                </div>

                <button type="submit" name="login" class="btn-submit">
                    เข้าสู่ระบบ
                </button>

            </form>

            <div class="modal-switch">
                ยังไม่มีบัญชี?
                <a href="register.php">สมัครสมาชิก</a>
            </div>

        </div>
    </div>

</body>


</html>
