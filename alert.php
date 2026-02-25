<?php
    $getLottery = $_GET['lotteryNum'];

    $wait = 1;

    if(strlen($getLottery) > 6 || strlen($getLottery) < 6){
        echo "<center style='margin-top: 9rem; color: red; font-size: 2rem;'>";
        echo "<h1>กรุณาใส่ตัวเลข<br><br>ไม่เกิน และ ไม่น้อยกว่า 6 หลัก</h1>";
        echo "</center>";

        $wait += 1;
        if($wait !== 1){
            header("refresh:3;url=Lottery.php");
        }
    }else{
        echo "<center style='margin-top: 15rem; color: blue; font-size: 4rem;'><h1>การซื้อสำเร็จ</h1></center>";
        $wait += 1;
        if($wait !== 1){
            header("refresh:1.5;url=Lottery.php");
        }
    }


    
?>