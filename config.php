<?php
function getDB() {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "lottery-system"; // สำคัญ ต้องตรงกับฐานข้อมูลจริง

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        return null;
    }

    // ตั้ง timezone
    date_default_timezone_set("Asia/Bangkok");
    $conn->query("SET time_zone = '+07:00'");

    return $conn;
}
?>