<?php
include "buyDB.php";

if (isset($_POST['lotteryNum'])) {

    $number = $_POST['lotteryNum'];
    $unit = $_POST['unitLottery'];


    // ตรวจสอบว่าต้องเป็นตัวเลข 6 หลัก
    if (!ctype_digit($number) || strlen($number) != 6 || strlen($number) == 0) { //ctype_digit ตรวจสอบว่าเป็นตัวเลขหรือไม่ และ strlen ตรวจสอบความยาวของตัวเลข

        header("refresh:2;url=buyLottery.php"); // header ใส่ url page ที่ต้องการจะให้ไป แสดงข้อความแล้วรอ 2 วินาที ก่อนจะไปหน้า buyLottery.php

        echo "<center style='margin-top:15rem; color:red; font-size:2rem;'>";
        echo "<h1>กรุณากรอกเลข<br>ให้ครบ 6 หลักเท่านั้น</h1>";
        echo "</center>";

        exit();
    } else if (!isset($unit) || $unit === "") { // ตรวจสอบว่าผู้ใช้เลือกจำนวนที่ต้องการซื้อหรือไม่

        header("refresh:2;url=buyLottery.php");

        echo "<center style='margin-top:15rem; color:red; font-size:2rem;'>";
        echo "<h1>กรุณาใส่จำนวนที่ต้องการซื้อ</h1>";
        echo "</center>";

        exit();
    } else {

        $result = mysqli_query(
            $conn,
            "SELECT MAX(CAST(SUBSTRING(lotteryID,2) AS UNSIGNED)) AS max_id FROM buylottery"
        );

        $row = mysqli_fetch_assoc($result);

        if ($row['max_id'] === NULL) {
            // ยังไม่มีข้อมูล
            $newNumber = 0;
        } else {
            // มีข้อมูลแล้ว
            $newNumber = $row['max_id'] + 1;
        }

        // format เป็น 6 หลัก
        $newID = "b" . str_pad($newNumber, 6, "0", STR_PAD_LEFT);

        // INSERT โดยใช้ ID ที่ PHP สร้างเอง
        $sql = "INSERT INTO buylottery (lotteryID, numLottery, unitLottery)
                    VALUES ('$newID', '$number', '$unit')";

        if (mysqli_query($conn, $sql)) {

            header("refresh:2;url=buyLottery.php");

            echo "<center style='margin-top:10rem; color:blue; font-size:2rem;'>";
            echo "<h1>ซื้อเลข $number <br>จำนวน $unit ใบ สำเร็จ</h1>";
            echo "</center>";

            exit();
        } else {

            echo "Error: " . mysqli_error($conn);
        }

        mysqli_close($conn);
    }
}
