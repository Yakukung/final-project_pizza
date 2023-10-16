<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "dbconfig.php";
session_start();

$change_amount = isset($_SESSION['change_amount']) ? $_SESSION['change_amount'] : 0;

if (isset($_GET['user_name']) && isset($_GET['order_id']) && isset($_GET['total'])) {
    $user_name = $_GET['user_name'];
    $new_order_id = $_GET['order_id'];
    $total = $_GET['total'];

    $update_order_sql = "UPDATE `Order` SET payment_status = 'ชำระเงินแล้ว', status = '1', total = ? WHERE order_id = ?";
    $stmt_update_order = $conn->prepare($update_order_sql);
    $stmt_update_order->bind_param("ii", $total, $new_order_id);
    $stmt_update_order->execute();

    // สร้างข้อความที่แสดงหมายเลขออร์เดอร์ใหม่
    $message = "ขอบคุณ $user_name ที่ใช้บริการเรา และคุณชำระเงินทั้งหมด: " . number_format($total, 0) . " บาท หมายเลขออร์เดอร์ใหม่คือ: $new_order_id";

    // ทำการล้าง session เพื่อเคลียร์ข้อมูลที่ไม่จำเป็น
    session_unset();
    session_destroy();
}

if (isset($_GET['user_name']) && isset($_GET['order_id']) && isset($_GET['total']) && isset($_POST['action'])) {
    $action = $_POST['action'];
    $user_name = $_GET['user_name'];
    $new_order_id = $_GET['order_id'];
    $total = $_GET['total'];

    if ($action === 'go_home') {

        // สร้าง `order_id` ใหม่ เพื่อรับออเดอร์ต่อไป
        $order_id = createNewOrder($user_name);

        if (isset($new_order_id)) {
            header("Location: home.php?user_name=" . $user_name . "&order_id=" . $order_id);
            exit();
        } else {
            // หากไม่สามารถสร้าง `order_id` ใหม่ได้
            $message = "เกิดข้อผิดพลาดในการประมวลผล";
        }
    }
}

// ฟังก์ชันสร้าง `order_id` ใหม่
function createNewOrder($user_name) {
    global $conn;

    // ค้นหา `user_id`, `user_phone`, และ `user_address` โดยใช้ชื่อผู้ใช้
    $user_info_sql = "SELECT user_id, phone, address FROM User WHERE user_name = ?";
    $stmt_user_info = $conn->prepare($user_info_sql);
    $stmt_user_info->bind_param("s", $user_name);
    $stmt_user_info->execute();
    $result_user_info = $stmt_user_info->get_result();

    if ($result_user_info->num_rows > 0) {
        $row = $result_user_info->fetch_assoc();
        $user_id = $row['user_id'];
        $user_phone = $row['phone'];
        $user_address = $row['address'];

        // เพิ่ม `order_id` ใหม่
        $add_order_sql = "INSERT INTO `Order` (`user_id`, `order_date`, `order_name`, `order_phone`, `order_address`)
                          VALUES (?, NOW(), ?, ?, ?)";
        $stmt_add_order = $conn->prepare($add_order_sql);
        $stmt_add_order->bind_param("isss", $user_id, $user_name, $user_phone, $user_address);
        $stmt_add_order->execute();

        // คืนค่า `order_id` ใหม่
        return $stmt_add_order->insert_id;
    }

    return null;
}
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
<?php
include "navbar.php";
?>
<div class="container mt-5">
    <div class="text-center">
        <i class="bi bi-check-circle text-success" style="font-size: 48px;"></i>
        <h1 class="mt-3">สั่งออร์เดอร์เสร็จสิ้น</h1>
        <p><?php echo $message; ?></p>
        <!-- เพิ่มลิงก์ไปหน้าหลัก -->
        <form method="post" action="">
            <button class="btn btn-primary" name="action" value="go_home">กลับหน้าหลัก</button>
        </form>
    </div>
</div>
</body>
</html>
