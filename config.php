<?php
/* ══════════════════════════════════════
   config.php — เชื่อมต่อ DB lottery-system
   ══════════════════════════════════════ */

   function getDB(): ?mysqli {
        $host = 'localhost';
        $user = 'root';
        $password = '';
        $namedb = 'lottery';
        
        $conn = mysqli_connect($host, $user, $password, $namedb);

        if ($conn->connect_error) return null;

        $conn->set_charset('utf8mb4');
        return $conn;
    }

?>