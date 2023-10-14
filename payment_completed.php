<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "dbconfig.php";
session_start();

$change_amount = isset($_SESSION['change_amount']) ? $_SESSION['change_amount'] : 0;

if (isset($_GET['user_name']) && isset($_GET['order_id']) && isset($_GET['total'])) {
    $user_name = $_GET['user_name'];
    $order_id = $_GET['order_id'];
    $total = $_GET['total'];

    $update_order_sql = "UPDATE `Order` SET payment_status = 'ชำระเงินแล้ว', status = '1', total = ? WHERE order_id = ?";
    $stmt_update_order = $conn->prepare($update_order_sql);

    if (!$stmt_update_order) {
        echo '<p>เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: ' . $conn->error . '</p>';
    } else {
        $stmt_update_order->bind_param("ii", $total, $order_id);
    }
}

if (isset($_GET['user_name']) && isset($_GET['order_id']) && isset($_GET['total'])) {
    $user_name = $_GET['user_name'];
    $order_id = $_GET['order_id'];
    $total = $_GET['total'];

    $message = "ขอบคุณ $user_name ที่ใช้บริการเรา และคุณชำระเงินทั้งหมด: " . number_format($total, 0) . " บาท";

    // ทำการล้าง session เพื่อเคลียร์ข้อมูลที่ไม่จำเป็น
    session_unset();
    session_destroy();
} else {
    // หากไม่มีข้อมูลผู้ใช้หรือหมายเลขออร์เดอร์ที่ส่งมา
    $message = "เกิดข้อผิดพลาดในการประมวลผล";
}
$stmt_update_order->execute();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ชำระเงินเสร็จสิ้น</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="navbar">
     <div class="logo">
     <a href="home.php?user_name=<?php echo $user_name; ?>">
         <img src="css/LOGOpizza.png"alt="">
     </a>
     </div>
     <div class="basket">
          <a class="btn btn-box" href="order.php?user_name=<?php echo $user_name; ?>">
             <i class="bi bi-box2-fill"></i>
          </a>
         <a class="btn btn-basket" href="basket.php?user_name=<?php echo $user_name; ?>">
             <i class="bi bi-basket3-fill"></i>
          </a>
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
    <div class="text-center">
        <i class="bi bi-check-circle text-success" style="font-size: 48px;"></i>
        <h1 class="mt-3">สั่งออร์เดอร์เสร็จสิ้น</h1>
        <p><?php echo $message; ?></p>
    </div>
</div>
</body>
</html>
