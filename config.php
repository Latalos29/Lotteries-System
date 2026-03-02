<?php
/* ══════════════════════════════════════
   config.php — เชื่อมต่อ DB lottery-system
   ══════════════════════════════════════ */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lottery-system');

function getDB(): ?mysqli {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) return null;
    $conn->set_charset('utf8mb4');
    return $conn;
}
