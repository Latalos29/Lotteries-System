<?php
session_start();        // ⭐ เพิ่มบรรทัดนี้
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Dashboard | LottoShop</title>
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
                <a href="logout.php">
                    <button class="btn-login">ออกจากระบบ</button>
                </a>
            </div>
        </div>
    </header>

    <!-- DASHBOARD SECTION -->
    <div class="modal-bg open"
        style="position:relative;display:flex;min-height:85vh;background:none;backdrop-filter:none;">
        <div class="modal" style="max-width:520px; text-align:center;">

            <div class="modal-logo">
                <div class="m-ico">👤</div>
                <h2>ยินดีต้อนรับ</h2>
                <p>เข้าสู่ระบบสำเร็จ</p>
            </div>

            <div style="margin-bottom:25px;">
                <div style="font-family:'Kanit',sans-serif;font-size:24px;font-weight:700;color:var(--navy);">
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </div>
            </div>

            <a href="profile.php">
                <button class="btn-submit" style="margin-bottom:12px;">
                    ดูโปรไฟล์
                </button>
            </a>

            <a href="index.php">
                <button class="btn-social">
                    กลับหน้าแรก
                </button>
            </a>

        </div>
    </div>

</body>

</html>