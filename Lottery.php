<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUYING LOTTERY</title>
</head>
<body style="align-content: center;">
    <?php
        // $page = false;
        // $action = "";

        // if(strlen($_GET['lotteryNum']) > 6){
        //     if ($page == true) {
        //         $action = "";
        //     }else if($page == false){
        //         $action = "alert.php";
        //     }
        // }
    ?>
    <center>
        <form method="get" action="alert.php">
            <h1>BUYING LOTTERY</h1>
            <table>
                <tr>
                    <td>กรุณาเลือกตัวเลขที่ต้องการซื้อ : </td>
                    <td>
                        <input type="number" name="lotteryNum" required>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" value="BUY">
                        <input type="reset" value="Cancel">
                    </td>
                </tr>
            </table>
        </form>
    </center> 
</body>
</html>