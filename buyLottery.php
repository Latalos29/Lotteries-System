<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUYING LOTTERY</title>
    <link rel="stylesheet" href="buy.css">
</head>
<body style="align-content: center;">
    <center>
        <form method="post" action="buyAlert.php">
            <h1 class="title">BUYING LOTTERY</h1>
            <table>
                <tr>
                    <td>กรุณาเลือกตัวเลขที่ต้องการซื้อ : </td>
                    <td>
                        <input type="number" name="lotteryNum" required>
                    </td>
                </tr>
                <tr style="height: 3rem;">
                    <td>เลือกจำนวนที่ต้องการซื้อ : </td>
                    <td>
                        <select name="unitLottery">
                            <?php
                                for($i = 1; $i <= 20; $i++){
                                    echo "<option value='$i'>$i</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr style="height: 1rem;"></tr>
                    <td colspan="3" align="center">
                        <input type="submit" value="BUY">
                        <input type="reset" value="Cancel" style="margin-left: 1rem;">
                    </td>
                </tr>
            </table>
        </form>
    </center> 
</body>
</html>