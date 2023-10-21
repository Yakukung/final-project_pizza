<?php
require_once "dbconfig.php";
session_start();

if (isset($_GET['user_name']) && isset($_GET['new_order_id'])) {
    $user_name = $_GET['user_name'];
    $new_order_id = $_GET['new_order_id'];

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
<style>
    h4{
        font-weight: 900;
        font-size: 2rem;
        margin-bottom: 1rem;
    }
    p{
        font-size: 1.1rem;
        line-height: 0.7;
    }
</style>
<body>
    <?php
    include "navbar.php";
    ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4" style="font-weight: 900; font-size: 3rem;;">รายการสั่งซื้อของคุณ</h2>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
           
                            // ตรวจสอบสถานะการจัดส่งและตั้งค่าไอคอนและสีตามสถานะ
                $delivery_status = $row['status'];
                $delivery_icon = ($delivery_status == 1) ? '<i class="bi bi-exclamation-circle text-danger"></i>' : '<i class="bi bi-check-circle text-success"></i>';
                $delivery_color = ($delivery_status == 1) ? 'text-danger' : 'text-success';

               // ตรวจสอบสถานะการชำระเงินและตั้งค่าไอคอนและสีตามสถานะ
                $payment_status = $row['payment_status'];
                $payment_icon = ($payment_status == 'ยังไม่ชำระเงิน') ? '<i class="bi bi-exclamation-circle text-danger"></i>' : '<i class="bi bi-check-circle text-success"></i>';
                $payment_color = ($payment_status == 'ยังไม่ชำระเงิน') ? 'text-danger' : 'text-success';


                echo '<div class="order-details" style="background-color: #FAFAFA; padding: 4rem 4rem 2rem 4rem; border-radius: 65px; margin: 4rem 8rem 4rem 8rem;">
                    <h4>รหัสออร์เดอร์: ' . $row['order_id'] . '</h4>
                    <p>วันที่สั่งซื้อ: ' . $row['order_date'] . '</p>
                    <p>สถานะการจัดส่ง: ' . $delivery_icon . ' <span class="' . $delivery_color . '">' . ($delivery_status == 1 ? 'ยังไม่จัดส่ง' : 'จัดส่งแล้ว') . '</span></p>
                    <p>สถานะชำระเงิน: ' . $payment_icon . ' <span class="' . $payment_color . '">' . ($payment_status == 1 ? 'ยังไม่ชำระเงิน' : 'ชำระเงินแล้ว') . '</span></p>';



                // ดึงรายการสินค้าในตะกร้าของออร์เดอร์ปัจจุบัน
                $order_id_to_show = $row['order_id'];
                $sql_order_items = "SELECT Item.*, Pizza.pizza_image, Pizza.pizza_name, Size.size_name, Crust.crust_name, Basket.amount
                                    FROM Basket
                                    INNER JOIN Item ON Basket.item_id = Item.item_id
                                    INNER JOIN Pizza ON Item.pizza_id = Pizza.pizza_id
                                    INNER JOIN Size ON Item.size_id = Size.size_id
                                    INNER JOIN Crust ON Item.crust_id = Crust.crust_id
                                    WHERE Basket.order_id = ?";
                $stmt_order_items = $conn->prepare($sql_order_items);
                $stmt_order_items->bind_param("i", $order_id_to_show);
                $stmt_order_items->execute();
                $result_order_items = $stmt_order_items->get_result();

                // เริ่มตารางสำหรับรายการสินค้า
                echo '<table class="table table-bordered" style="margin-top: 2rem;">
                        <thead>
                            <tr>
                                <th>รหัสสินค้า</th>
                                <th>รูปสินค้า</th>
                                <th>ชื่อพิซซ่า</th>
                                <th>ขนาด</th>
                                <th>ขอบ</th>
                                <th>จำนวน</th>
                                <th>ราคา</th>
                            </tr>
                        </thead>
                        <tbody>';

                while ($item_row = $result_order_items->fetch_assoc()) {
                    echo '<tr>
                            <td>' . $item_row['item_id'] . '</td>
                            <td><img src="' . $item_row['pizza_image'] . '" alt="' . $item_row['pizza_name'] . '" style="max-width: 100px;"></td>
                            <td>' . $item_row['pizza_name'] . '</td>
                            <td>' . $item_row['size_name'] . '</td>
                            <td>' . $item_row['crust_name'] . '</td>
                            <td>' . $item_row['amount'] . '</td>
                            <td>' . ($item_row['Price'] * $item_row['amount']) . '</td>
                        </tr>';
                }

                // ปิดตารางรายการสินค้า
                echo '</tbody>
                    </table>';
                    echo '<p style="text-align: end; margin-right: 2rem; font-weight: 900;">ยอดรวม: ' . $row['total_amount'] . ' บาท</p>';
                // เพิ่มปุ่มยกเลิกออร์เดอร์
                echo '<div class="btn-cancel-order" style="text-align: end; margin: 1rem 2rem 0rem 0rem;">
                        <form method="post" action="">
                            <input type="hidden" name="order_id" value="' . $row['order_id'] . '">
                            <input type="hidden" name="user_name" value="' . $user_name . '">
                            <button type="submit" class="btn btn-danger" name="cancel_order">ยกเลิกออร์เดอร์</button>
                        </form>
                    </div>';

                    
                // ปิดส่วนของแต่ละออร์เดอร์
                echo '</div>';
            }
        } else {
            echo '<p class="text-center">คุณยังไม่มีออร์เดอร์</p>';
        }
        ?>
    </div>
</body>

</html>
