<?php
session_start();              
require_once "config.php";

$conn = getDB();              
if (!$conn) {
    die("Database connection failed");
}

// เช็ค login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = "";
$error = "";

// เมื่อกดบันทึก
if (isset($_POST['update'])) {

    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("UPDATE users 
                            SET firstname=?, lastname=?, phone=?, email=? 
                            WHERE user_id=?");

    $stmt->bind_param("ssssi", $firstname, $lastname, $phone, $email, $user_id);

    if ($stmt->execute()) {
        $success = "อัปเดตข้อมูลสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาด";
    }
}

// ดึงข้อมูลผู้ใช้ใหม่เสมอ
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>แก้ไขโปรไฟล์ | LottoShop</title>
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
                <a href="dashboard.php">
                    <button class="btn-login">Dashboard</button>
                </a>
            </div>
        </div>
    </header>

    <!-- PROFILE SECTION -->
    <div class="modal-bg open"
        style="position:relative;display:flex;min-height:85vh;background:none;backdrop-filter:none;">
        <div class="modal" style="max-width:520px;">

            <div class="modal-logo">
                <div class="m-ico">✏️</div>
                <h2>แก้ไขโปรไฟล์</h2>
                <p>ปรับปรุงข้อมูลส่วนตัวของคุณ</p>
            </div>

            <?php if (!empty($success)): ?>
                <div
                    style="background:#D4EDE1;color:#1A6642;padding:10px;border-radius:6px;margin-bottom:15px;text-align:center;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div
                    style="background:#FADDDD;color:#A02A2A;padding:10px;border-radius:6px;margin-bottom:15px;text-align:center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label>ชื่อ</label>
                    <input type="text" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>นามสกุล</label>
                    <input type="text" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label>เบอร์โทร</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <button type="submit" name="update" class="btn-submit">
                    บันทึกการแก้ไข
                </button>

            </form>

            <div class="modal-switch">
                <a href="index.php">← กลับหน้าหลัก</a>
            </div>

        </div>
    </div>

</body>

</html>