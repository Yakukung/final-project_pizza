<?php
require_once "dbconfig.php";
session_start();

if (isset($_GET['user_name'])) {
    $user_name = $_GET['user_name'];

    // Query to retrieve order information
    $sql = "SELECT `Order`.*, SUM(Basket.amount * Item.Price) AS total_amount
            FROM `Order`
            INNER JOIN User ON `Order`.user_id = User.user_id
            LEFT JOIN Basket ON `Order`.order_id = Basket.order_id
            LEFT JOIN Item ON Basket.item_id = Item.item_id
            WHERE User.user_name = ?
            GROUP BY `Order`.order_id
            ORDER BY `Order`.order_id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_name);
    $stmt->execute();
    $result = $stmt->get_result();

    // ค้นหารายการสินค้าในตะกร้าของผู้ใช้
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
}

// Handle order cancellation
if (isset($_POST['cancel_order'])) {
    $order_id_to_cancel = $_POST['order_id'];

    // Prepare and execute a SQL query to delete the order
    $sql_cancel_order = "DELETE FROM `Order` WHERE order_id = ? AND user_id = (SELECT user_id FROM User WHERE user_name = ?)";
    $stmt_cancel_order = $conn->prepare($sql_cancel_order);
    $stmt_cancel_order->bind_param("is", $order_id_to_cancel, $user_name);

    if ($stmt_cancel_order->execute()) {
        // Order cancellation was successful
        // ส่งกลับไปที่ order.php หลังจากลบออร์เดอร์
        header("Location: order.php?user_name=" . $user_name);
        exit();
    } else {
        // Order cancellation failed
        echo "Order cancellation failed.";
    }

    // Close the prepared statement for order cancellation
    $stmt_cancel_order->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>รายการออร์เดอร์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
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
            <?php
                    // ดึงข้อมูลออเดอร์จากฐานข้อมูล
                $sql = "SELECT `Order`.*, SUM(Basket.amount * Item.Price) AS total_amount
                FROM `Order`
                INNER JOIN User ON `Order`.user_id = User.user_id
                LEFT JOIN Basket ON `Order`.order_id = Basket.order_id
                LEFT JOIN Item ON Basket.item_id = Item.item_id
                WHERE User.user_name = ?
                GROUP BY `Order`.order_id
                ORDER BY `Order`.order_id DESC";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $user_name);
                $stmt->execute();
                $result = $stmt->get_result();

                // นับจำนวนรายการออเดอร์
                $order_count = $result->num_rows;

                if ($order_count > 0){
                    echo '<span class="order-count">' .$order_count. '</span>';
                }
            ?>
           
    </a>

         <a class="btn btn-basket" href="basket.php?user_name=<?php echo $user_name; ?>">
             <i class="bi bi-basket-fill"></i>
             <?php
                         // ดึงจำนวนสินค้าในตะกร้าของผู้ใช้
                $sql_count_items = "SELECT COUNT(*) AS item_count FROM Basket
                INNER JOIN `Order` ON Basket.order_id = `Order`.order_id
                INNER JOIN User ON `Order`.user_id = User.user_id
                WHERE User.user_name = ?";
                $stmt_count_items = $conn->prepare($sql_count_items);
                $stmt_count_items->bind_param("s", $user_name);
                $stmt_count_items->execute();
                $result_count_items = $stmt_count_items->get_result();

                    if ($result_count_items->num_rows > 0) {
                        $count_row = $result_count_items->fetch_assoc();
                        $item_count = $count_row['item_count'];
                        echo '<span class="item-count">' . $item_count . '</span>';
                    }
                ?>
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
<div class="container mt-5" style="background-color: #FAFAFA; padding: 2rem; border-radius: 65px;">
    <h2 class="text-center mb-4">รายการออร์เดอร์</h2>
    <?php
    if ($result->num_rows > 0) {
    ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>รหัสออร์เดอร์</th>
                <th>วันที่สั่งซื้อ</th>
                <th>ยอดรวม (บาท)</th>
                <th>สถานะการจัดส่ง</th>
                <th>สถานะชำระเงิน</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()) {
                echo '<tr>
                        <td>' . $row['order_id'] . '</td>
                        <td>' . $row['order_date'] . '</td>
                        <td>' . $row['total_amount'] . '</td>
                        <td>' . $row['status'] . '</td>
                        <td>' . $row['payment_status'] . '</td>
                    </tr>';
            }
            mysqli_data_seek($result, 0);
            ?>
        </tbody>
    </table>
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
            while ($row = $result_basket->fetch_assoc()) {
                echo '<tr>
                        <td>' . $row['item_id'] . '</td>
                        <td><img src="' . $row['pizza_image'] . '" alt="' . $row['pizza_name'] . '" style="max-width: 100px;"></td>
                        <td>' . $row['pizza_name'] . '</td>
                        <td>' . $row['size_name'] . '</td>
                        <td>' . $row['crust_name'] . '</td>
                        <td>
                            <form method="post" action="">
                                <input type="hidden" name="item_id" value="' . $row['item_id'] . '">
                                <span>' . $row['amount'] . '</span>
                            </form>
                        </td>
                        <td>' . ($row['Price'] * $row['amount']) . '</td>
                      </tr>';
            }
            ?>
        </tbody>
    </table>
    <?php
    } else {
        echo '<p class="text-center">คุณยังไม่มีออร์เดอร์</p>';
    }
    ?>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="btn-cancel-order" style="text-align: end; margin-right: 2rem;">
                    <form method="post" action="">
                    <input type="hidden" name="order_id" value="' . $row['order_id'] . '">
                    <button type="submit" class="btn btn-danger" name="cancel_order">ยกเลิกออร์เดอร์</button>
                </form>
            </div>';
        }
        mysqli_data_seek($result, 0);
    }
    ?>
</div>


</body>
</html>
