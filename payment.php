<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "dbconfig.php";
session_start();

if (isset($_GET['user_name']) && isset($_GET['total']) && isset($_GET['order_id'])) {
    $user_name = $_GET['user_name'];
    $total = $_GET['total'];
    $new_order_id = $_GET['order_id'];

    if (isset($_SESSION['basket_data'])) {
        $basket_data = $_SESSION['basket_data'];
    } else {
        $sql_basket = "SELECT Basket.amount, Item.*, Pizza.pizza_image, Pizza.pizza_name, Size.size_name, Crust.crust_name
                        FROM Basket
                        INNER JOIN Item ON Basket.item_id = Item.item_id
                        INNER JOIN Pizza ON Item.pizza_id = Pizza.pizza_id
                        INNER JOIN Size ON Item.size_id = Size.size_id
                        INNER JOIN Crust ON Item.crust_id = Crust.crust_id
                        INNER JOIN `Order` ON Basket.order_id = `Order`.order_id
                        INNER JOIN User ON `Order`.user_id = User.user_id
                        WHERE User.user_name = ?
                        AND `Order`.order_id = ?"; // เพิ่มเงื่อนไขใน WHERE เพื่อเลือกเฉพาะ order_id ที่ต้องการ

        $stmt_basket = $conn->prepare($sql_basket);
        $stmt_basket->bind_param("si", $user_name, $new_order_id); // เพิ่มการ bind order_id
        $stmt_basket->execute();
        $result_basket = $stmt_basket->get_result();
        $basket_data = $result_basket->fetch_all(MYSQLI_ASSOC);

        $sql_order_info = "SELECT *
                          FROM `Order`
                          INNER JOIN User ON `Order`.user_id = User.user_id
                          WHERE User.user_name = ?
                          AND `Order`.order_id = ?"; // เพิ่มเงื่อนไขใน WHERE เพื่อเลือกเฉพาะ order_id ที่ต้องการ

        $stmt_order_info = $conn->prepare($sql_order_info);
        $stmt_order_info->bind_param("si", $user_name, $new_order_id); // เพิ่มการ bind order_id
        $stmt_order_info->execute();
        $result_order_info = $stmt_order_info->get_result();
        $order_info = $result_order_info->fetch_assoc();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["order_address"]) && isset($_POST["order_phone"]) && isset($_POST["order_address"]) && isset($_GET['order_id']) ) {
            $new_name = $_POST["order_name"];
            $new_phone = $_POST["order_phone"];
            $new_address = $_POST["order_address"];
            $new_order_id = $_GET['order_id']; // เปลี่ยนตรงนี้เป็นการใช้ค่าที่ได้จาก GET

            $sql_update = "UPDATE `Order` SET order_name = ?, order_phone = ?, order_address = ? WHERE order_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sssi", $new_name, $new_phone, $new_address, $new_order_id); // เปลี่ยน order_info['order_id'] เป็น $new_order_id

            if ($stmt_update->execute()) {
                echo "<script>alert('ยืนยันการสั่งซื้อ');</script>";
                $order_info['order_name'] = $new_name;
                $order_info['order_phone'] = $new_phone;
                $order_info['order_address'] = $new_address;
                $order_info['order_id'] = $new_order_id;

                // ส่งข้อมูลไปที่หน้า payment_completed.php พร้อมกับ $new_order_id
                $new_order_id = $order_info['order_id']; // ใช้ค่า order_id ใหม่ที่ได้จากการอัพเดต
                header("Location: payment_completed.php?user_name=" . $user_name . "&order_id=" . $new_order_id . "&total=" . $total);
                exit; // ให้สคริปต์หยุดการทำงานที่นี่

            } else {
                echo "<script>alert('ไม่สามารถยืนยันการสั่งซื้อได้');</script>";
            }
        }
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <title>Pizza Custom Page</title>
    <link rel="stylesheet" href="css/style.css">
    
</head>
<body>
<?php
    include "navbar.php";
?>
<div class="container mt-5">
    <form method="post">
        <div class="row justify-content-center" style="background-color: #FAFAFA; padding: 2rem; border-radius: 65px; margin: 2rem;">
            <h2>ข้อมูลการจัดส่ง</h2>
            <div class="col-4">
                <label for="order_name" class="form-label">ชื่อลูกค้า:</label>
                <input type="text" name="order_name" id="order_name" class="form-control" value="<?php echo $order_info['order_name']; ?>" required>
            </div>
            <div class="col-4">
                <label for="order_phone" class="form-label">โทรศัพท์:</label>
                <input type="text" name="order_phone" id="order_phone" class="form-control" value="<?php echo $order_info['order_phone']; ?>" required>
            </div>
            <div class="col-4">
                <label for="order_address" class="form-label">ที่อยู่:</label>
                <input type="text" name="order_address" id="order_address" class="form-control" value="<?php echo $order_info['order_address']; ?>" required>
            </div>
        </div>
        <div class="row justify-content-center" style="background-color: #FAFAFA; padding: 2rem; border-radius: 65px; margin: 2rem;">
            <h2>รายการสินค้าที่ต้องชำระเงิน</h2>
            <p><strong>รหัสคำสั่งซื้อ: <?php echo $order_info['order_id']; ?></strong></p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>รหัสสินค้า</th>
                        <th>รูปสินค้า</th>
                        <th>ชื่อพิซซ่า</th>
                        <th>ขนาด</th>
                        <th>ขอบ</th>
                        <th>จำนวน</th>
                        <th>ราคารวม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($basket_data)) {
                        foreach ($basket_data as $item) {
                            echo '<tr>
                                <td>' . $item['item_id'] . '</td>
                                <td><img src="' . $item['pizza_image'] . '" alt="' . $item['pizza_name'] . '" style="max-width: 100px;"></td>
                                <td>' . $item['pizza_name'] . '</td>
                                <td>' . $item['size_name'] . '</td>
                                <td>' . $item['crust_name'] . '</td>
                                <td>' . $item['amount'] . '</td>
                                <td>' . ($item['Price'] * $item['amount']) . '</td>
                            </tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>
            <div class="total" style="display: flex; justify-content: end; align-items: center;">
                <p><strong>ราคารวมทั้งหมด:
                    <div class="bath" style="color: #4aa774; font-size: 2rem; margin: 0rem 2rem 1rem 0.5rem;"><?php echo $total; ?> บาท</div>
                </strong></p>
            </div>
            <div class="row" style="width: 13%; margin-left: 80%; margin-top: -1rem;">
                <button type="submit" class="btn btn-primary">ยืนยันการสั่งซื้อ</button>
            </div>
        </div>
    </form>
</div>
</body>
</html>
