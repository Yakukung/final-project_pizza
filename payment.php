<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "dbconfig.php";
session_start();

if (isset($_GET['user_name']) && isset($_GET['total'])) {
    $user_name = $_GET['user_name'];
    $total = $_GET['total'];

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
                        ORDER BY Basket.item_id ASC";

        $stmt_basket = $conn->prepare($sql_basket);
        $stmt_basket->bind_param("s", $user_name);
        $stmt_basket->execute();
        $result_basket = $stmt_basket->get_result();
        $basket_data = $result_basket->fetch_all(MYSQLI_ASSOC);

        $sql_order_info = "SELECT *
                          FROM `Order`
                          INNER JOIN User ON `Order`.user_id = User.user_id
                          WHERE User.user_name = ?";

        $stmt_order_info = $conn->prepare($sql_order_info);
        $stmt_order_info->bind_param("s", $user_name);
        $stmt_order_info->execute();
        $result_order_info = $stmt_order_info->get_result();
        $order_info = $result_order_info->fetch_assoc();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["order_phone"]) && isset($_POST["order_address"])) {
            $new_phone = $_POST["order_phone"];
            $new_address = $_POST["order_address"];

            $sql_update = "UPDATE `Order` SET order_phone = ?, order_address = ? WHERE order_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssi", $new_phone, $new_address, $order_info['order_id']);

            if ($stmt_update->execute()) {
                echo "<script>alert('ยืนยันการสั่งซื้อ');</script>";
                $order_info['order_phone'] = $new_phone;
                $order_info['order_address'] = $new_address;

                // ส่งข้อมูลไปที่หน้า payment_completed.php
                $order_id = $order_info['order_id'];
                header("Location: payment_completed.php?user_name=" . $user_name . "&order_id=" . $order_id . "&total=" . $total);
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
    <title>ชำระเงิน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="navbar">
        <div class="logo">
            <a href="home.php?user_name=<?php echo $user_name; ?>">
                <img src="css/LOGOpizza.png" alt="">
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
            <a class="user-name" href="login.php" style="text-decoration: none;">
                <h1>สวัสดี, <?php echo $user_name; ?>!</h1>
            </a>
        </div>
    </div>

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
                        <th>เสิร์ฟ</th>
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
            <p><strong>ราคารวมทั้งหมด: <?php echo $total; ?></strong></p>
            <div class="row justify-content-center">
                <button type="submit" class="btn btn-primary">ยืนยันการสั่งซื้อ</button>
            </div>
        </div>
     </form>
    </div>
</body>

</html>