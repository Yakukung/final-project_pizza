<?php
require_once "dbconfig.php";

$user_name = $_GET['user_name'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["order_id"]) && isset($_POST["payment_status"])) {
    $order_id = $_POST["order_id"];
    $payment_status = $_POST["payment_status"];

    // คำสั่ง SQL สำหรับอัปเดตสถานะการชำระเงินในตาราง Order
    $sql_update_payment_status = "UPDATE `Order` SET payment_status = ? WHERE order_id = ?";
    $stmt_update_payment_status = $conn->prepare($sql_update_payment_status);
    $stmt_update_payment_status->bind_param("si", $payment_status, $order_id);

    // ทำการอัปเดตข้อมูลในตาราง Order สำหรับ payment_status
    if ($stmt_update_payment_status->execute()) {
        // อัปเดตสถานะการชำระเงินสำเร็จ
        header("Location: owner_dashboard.php?user_name=" . $user_name); // รีเดิร์กต์หน้า owner_dashboard.php
        exit();
    } else {
        // ไม่สามารถอัปเดตสถานะการชำระเงินได้
        echo '<div class="alert alert-danger text-center" role="alert">ไม่สามารถอัปเดตสถานะการชำระเงินได้</div>';
    }

    // ปิดการเชื่อมต่อกับฐานข้อมูลสำหรับ payment_status
    $stmt_update_payment_status->close();
}

// สอบถามข้อมูลออร์เดอร์
$sql = "SELECT * FROM `Order`";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าเจ้าของร้าน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="navbar">
     <div class="logo">
     <img src="css/LOGOpizza.png"alt="">
     </div>
     <div class="nav-user">
        <a class="user-image" href="login.php">
            <i class="bi bi-person-circle"></i>
        </a>
        <a class="user-name" href="login.php"style="text-decoration: none;" >
           <h1>สวัสดี, <?php echo $user_name; ?>!</h1>
        </a>
     </div>
 </div>
<div class="container mt-5">
    <h1>รายการออเดอร์ลูกค้า</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>ชื่อลูกค้า</th>
                <th>รายการที่สั่ง</th>
                <th>ราคารวม</th>
                <th>สถานะชำระเงิน</th>
                <th>สถานะจัดส่ง</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["order_id"] . "</td>";
                    echo "<td></td>";
                    echo "<td>" . $row["order_name"] . "</td>";
                    echo "<td>" . $row["total"] . "</td>";
                    echo "<td>";
                    echo "<form method='POST' action='owner_dashboard.php'>";
                    echo "<input type='hidden' name='order_id' value='" . $row["order_id"] . "'>";
                    echo "<select name='payment_status'>";
                    echo "<option value='ยังไม่จ่าย' " . ($row["payment_status"] == "ยังไม่จ่าย" ? "selected" : "") . ">ยังไม่จ่าย</option>";
                    echo "<option value='จ่ายแล้ว' " . ($row["payment_status"] == "จ่ายแล้ว" ? "selected" : "") . ">จ่ายแล้ว</option>";
                    echo "</select>";
                    echo "<button type='submit' class='btn btn-primary'>บันทึก</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>ไม่มีรายการออร์เดอร์</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
