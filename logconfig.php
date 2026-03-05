<?php
function getDB() {
    $host = 'localhost';
        $user = 'root';
        $password = '';
        $namedb = 'lottery_system';
        
        $conn = mysqli_connect($host, $user, $password, $namedb);

    if ($conn->connect_error) {
        return null;
    }

    // ตั้ง timezone
    date_default_timezone_set("Asia/Bangkok");
    $conn->query("SET time_zone = '+07:00'");

    return $conn;
}
?>
