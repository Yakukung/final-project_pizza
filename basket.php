<?php
require_once "dbconfig.php";
session_start();

if (isset($_GET['user_name'])) {
    $user_name = $_GET['user_name'];

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

    if (isset($_POST['item_id']) && isset($_POST['action'])) {
        $item_id = $_POST['item_id'];
        $action = $_POST['action'];

        // ดึงข้อมูลสินค้าจากตะกร้าของผู้ใช้
        $sql_select = "SELECT * FROM Basket WHERE item_id = ?";
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bind_param("i", $item_id);
        $stmt_select->execute();
        $result_select = $stmt_select->get_result();

        if ($result_select->num_rows > 0) {
            $row = $result_select->fetch_assoc(); // ดึงข้อมูลของสินค้า
            $current_amount = $row['amount'];

            // ตรวจสอบ action และดำเนินการอัปเดต amount ในฐานข้อมูลตามคำสั่งที่ต้องการ (increase หรือ decrease)
            if ($action === 'increase') {
                $new_amount = $current_amount + 1;
                $sql_update = "UPDATE Basket SET amount = ? WHERE item_id = ?";
            } elseif ($action === 'decrease') {
                if ($current_amount > 0) {
                    $new_amount = $current_amount - 1;

                    // ตรวจสอบว่าจำนวนใหม่ไม่น้อยกว่า 0
                    if ($new_amount > 0) {
                        $sql_update = "UPDATE Basket SET amount = ? WHERE item_id = ?";
                    } else {
                        // หากจำนวนเท่ากับ 0 ให้ลบสินค้าออก
                        $sql_delete = "DELETE Basket, Item FROM Basket
                                       INNER JOIN Item ON Basket.item_id = Item.item_id
                                       WHERE Basket.item_id = ?";
                        $stmt_delete = $conn->prepare($sql_delete);
                        $stmt_delete->bind_param("i", $item_id);
                        if ($stmt_delete->execute()) {
                            // สำเร็จในการลบสินค้า
                            header("Location: basket.php?user_name=" . $user_name);
                            exit;
                        } else {
                            // ไม่สามารถลบสินค้าได้
                            echo '<div class="alert alert-danger text-center" role="alert">ไม่สามารถลบสินค้าได้</div>';
                        }
                        $stmt_delete->close();
                    }
                }
            } elseif ($action === 'delete') {
                $sql_delete = "DELETE Basket, Item FROM Basket
                               INNER JOIN Item ON Basket.item_id = Item.item_id
                               WHERE Basket.item_id = ?";

                $stmt_delete = $conn->prepare($sql_delete);
                $stmt_delete->bind_param("i", $item_id);
                if ($stmt_delete->execute()) {
                    // สำเร็จในการลบสินค้า
                    header("Location: basket.php?user_name=" . $user_name);
                    exit;
                } else {
                    // ไม่สามารถลบสินค้าได้
                    echo '<div class="alert alert-danger text-center" role="alert">ไม่สามารถลบสินค้าได้</div>';
                }
                $stmt_delete->close();
            }

            if (isset($sql_update)) {
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ii", $new_amount, $item_id);
                if ($stmt_update->execute()) {
                    // สำเร็จในการอัปเดตจำนวนสินค้า
                    header("Location: basket.php?user_name=" . $user_name);
                    exit;
                } else {
                    // ไม่สามารถอัปเดตจำนวนสินค้าได้
                    echo '<div class="alert alert-danger text-center" role="alert">ไม่สามารถอัปเดตจำนวนสินค้าได้</div>';
                }
                $stmt_update->close();
            }
        }
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ตะกร้าสินค้า</title>
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
        <h2 class="text-center mb-4">ตะกร้าสินค้า</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>รหัสสินค้า</th>
                    <th>รุปสินค้า</th>
                    <th>ชื่อพิซซ่า</th>
                    <th>ขนาด</th>
                    <th>ขอบ</th>
                    <th>จำนวน</th>
                    <th>ราคารวม</th>
                    <th>การดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // นำข้อมูลสินค้าในตะกร้ามาแสดง
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
                                    <button class="btn btn-sm btn-success" name="action" value="increase">+</button>
                                    <span>' . $row['amount'] . '</span>
                                    <button class="btn btn-sm btn-danger" name="action" value="decrease">-</button>
                                </form>
                            </td>
                            <td>' . ($row['Price'] * $row['amount']) . '</td>
                            <td>
                                <!-- ปุ่มลบ -->
                                <form method="post" action="">
                                    <input type="hidden" name="item_id" value="' . $row['item_id'] . '">
                                    <button class="btn btn-sm btn-danger" name="action" value="delete" onclick="return confirm(\'คุณแน่ใจหรือไม่ที่จะลบสินค้านี้ออกจากตะกร้า?\')">ลบ</button>
                                </form>
                            </td>
                          </tr>';
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" align="right"><strong>รวมทั้งหมด:</strong></td>
                    <td>
                        <?php
                        // เพิ่มโค้ด PHP เพื่อคำนวณราคารวมทั้งหมดของสินค้าในตะกร้า
                        $total = 0;
                        $result_basket->data_seek(0); // นำ cursor กลับไปที่ตำแหน่งแรกของข้อมูลตะกร้า
                        while ($row = $result_basket->fetch_assoc()) {
                            $total += ($row['Price'] * $row['amount']);
                        }
                        echo '<strong>' . $total . '</strong>';
                        ?>
                    </td>
                    <td>
                        <!-- ปุ่มชำระเงิน -->
                    <form method="post" action="payment.php?user_name=<?php echo $user_name; ?>&total=<?php echo $total; ?>">
                        <button type="submit" class="btn btn-primary" name="user_name" value="<?php echo $user_name; ?>">ชำระเงิน</button>
                    </form>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>
