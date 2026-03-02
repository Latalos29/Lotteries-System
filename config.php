<?php
function getDB() {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "lottery-system"; // สำคัญ ต้องตรงกับฐานข้อมูลจริง

    $conn = new mysqli($host, $user, $pass, $db);

}
    function getDB(): ?mysqli {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) return null;
    $conn->set_charset('utf8mb4');
    return $conn;

    if ($conn->connect_error) {
        return null;
    }

    // ตั้ง timezone
    date_default_timezone_set("Asia/Bangkok");
    $conn->query("SET time_zone = '+07:00'");

    return $conn;
}

?>

