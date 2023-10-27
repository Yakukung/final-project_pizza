<?php
require_once "dbconfig.php";
session_start();

if (isset($_GET['user_name']) && isset($_GET['order_id'])) {
    $user_name = $_GET['user_name'];
    $new_order_id = $_GET['order_id'];
    // ค้นหารายการสินค้าในตะกร้าของผู้ใช้
    $sql_basket = "SELECT Basket.amount, Item.*, Pizza.pizza_image, Pizza.pizza_name, Size.size_name, Crust.crust_name
                    FROM Basket
                    INNER JOIN Item ON Basket.item_id = Item.item_id
                    INNER JOIN Pizza ON Item.pizza_id = Pizza.pizza_id
                    INNER JOIN Size ON Item.size_id = Size.size_id
                    INNER JOIN Crust ON Item.crust_id = Crust.crust_id
                    INNER JOIN `Order` ON Basket.order_id = `Order`.order_id
                    INNER JOIN User ON `Order`.user_id = User.user_id
                    WHERE User.user_name = ? AND Basket.order_id = ?
                    ORDER BY Basket.item_id ASC";

    $stmt_basket = $conn->prepare($sql_basket);
    $stmt_basket->bind_param("si", $user_name, $new_order_id);
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
                    header("Location: basket.php?user_name=" . $user_name . "&order_id=" . $new_order_id);

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
                    header("Location: basket.php?user_name=" . $user_name . "&order_id=" . $new_order_id);
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
<?php
    include "navbar.php";
?>
<?php
    include "btn_back_home.php";
?>
<div class="container mt-5" style="background-color: #FAFAFA; padding: 2rem; border-radius: 65px;">
    <h2 class="text-center mb-4">ตะกร้าสินค้า</h2>
    <?php
    if ($result_basket->num_rows > 0) {
        echo '<table class="table table-bordered">
            <thead>
                <tr>
                    <th>รหัสสินค้า</th>
                    <th>รูปสินค้า</th>
                    <th>ชื่อพิซซ่า</th>
                    <th>ขนาด</th>
                    <th>ขอบ</th>
                    <th>จำนวน</th>
                    <th>ราคารวม</th>
                    <th>การดำเนินการ</th>
                </tr>
            </thead>
            <tbody>';

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
                        <form method="post" action="">
                            <input type="hidden" name="item_id" value="' . $row['item_id'] . '">
                            <button class="btn btn-sm btn-danger" name="action" value="delete" onclick="return confirm(\'คุณแน่ใจหรือไม่ที่จะลบสินค้านี้ออกจากตะกร้า?\')">ลบ</button>
                        </form>
                    </td>
                </tr>';
        }

        echo '</tbody>
            <tfoot>
                <tr>
                    <td colspan="6" align="right"><strong>รวมทั้งหมด:</strong></td>
                    <td>';

        // เพิ่มโค้ด PHP เพื่อคำนวณราคารวมทั้งหมดของสินค้าในตะกร้า
        $total = 0;
        $result_basket->data_seek(0); // นำ cursor กลับไปที่ตำแหน่งแรกของข้อมูลตะกร้า
        while ($row = $result_basket->fetch_assoc()) {
            $total += ($row['Price'] * $row['amount']);
        }

        echo '<strong>' . $total . '</strong>
                    </td>
                    <td>
                        <form method="post" action="payment.php?user_name=' . $user_name . '&total=' . $total . '&order_id=' . $new_order_id . '">
                            <button type="submit" class="btn btn-primary" name="user_name" value="' . $user_name . '" >ชำระเงิน</button>
                        </form>
                    </td>
                </tr>
            </tfoot>
        </table>';
    } else {
        echo '<div class="text-center">ยังไม่มีสินค้าในตะกร้า</div>';
    }
    ?>
</div>
</body>
</html>
