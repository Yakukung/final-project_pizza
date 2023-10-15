<?php
require_once "dbconfig.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$sql = "SELECT `Order`.order_id, User.user_name,
                GROUP_CONCAT(Pizza.pizza_name SEPARATOR ', ') as pizza_names, 
                GROUP_CONCAT(Size.size_name SEPARATOR ', ') as size_names, 
                GROUP_CONCAT(Crust.crust_name SEPARATOR ', ') as crust_names,
                GROUP_CONCAT(Basket.amount SEPARATOR ', ') as amounts,
                GROUP_CONCAT(Item.Price SEPARATOR ', ') as price,
                `Order`.total, `Order`.payment_status, `Order`.status 
    FROM `Order`
    INNER JOIN User ON `Order`.user_id = User.user_id
    INNER JOIN Basket ON `Order`.order_id = Basket.order_id
    INNER JOIN Item ON Basket.item_id = Item.item_id
    INNER JOIN Pizza ON Item.pizza_id = Pizza.pizza_id
    INNER JOIN Size ON Pizza.pizza_id = Size.size_id
    INNER JOIN Crust ON Pizza.pizza_id = Crust.crust_id
    GROUP BY `Order`.order_id";

$result = $conn->query($sql);

$user_name = $_GET['user_name'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["order_id"]) && isset($_POST["payment_status"]) && isset($_POST["status"])) {
    $order_id = $_POST["order_id"];
    $payment_status = $_POST["payment_status"];
    $status = $_POST["status"];

    // คำสั่ง SQL สำหรับอัปเดตสถานะการชำระเงินในตาราง Order
    $sql_update_status = "UPDATE `Order` SET payment_status = ?, status = ? WHERE order_id = ?";
    $stmt_update_status = $conn->prepare($sql_update_status);
    $stmt_update_status->bind_param("ssi", $payment_status, $status, $order_id);

    if ($stmt_update_status->execute()) {
        header("Location: owner_dashboard.php?user_name=" . $user_name);
        exit();
    } else {
        echo '<div class="alert alert-danger text-center" role="alert">ไม่สามารถอัปเดตสถานะได้</div>';
    }

    $stmt_update_status->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="navbar">
    <div class="logo">
        <a href="owner_dashboard.php?user_name=<?php echo $user_name; ?>">
            <img src="css/LOGOpizza.png" alt="">
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
    <h1>รายการที่ลูกค้าสั่งซื้อทั้งหมด</h1>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="card mb-5" style="background-color: #FAFAFA; padding: 2rem; border-radius: 65px; border: none; margin: 1rem 10rem">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title" style="font-size: 2rem;">Order ID: ' . $row["order_id"] . '</h5>';
            echo '<p class="card-text" style="font-size: 1.2rem;">ชื่อลูกค้า: ' . $row["user_name"] . '</p>';

            // รายการพิซซ่าแยกเป็นคอลัมเพื่อความชัดเจน
            $pizza_names = explode(', ', $row['pizza_names']);
            $size_names = explode(', ', $row['size_names']);
            $crust_names = explode(', ', $row['crust_names']);
            $amounts = explode(', ', $row['amounts']);
            $price = explode(', ', $row['price']);

            // แสดงรายการพิซซ่าในตาราง
            echo '<table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ชื่อพิซซ่า</th>
                        <th>ขนาด</th>
                        <th>ขอบ</th>
                        <th>จำนวน</th>
                        <th>ราคา</th>
                    </tr>
                </thead>
                <tbody>';

            for ($i = 0; $i < count($pizza_names); $i++) {
                echo '<tr>
                        <td>' . $pizza_names[$i] . '</td>
                        <td>' . $size_names[$i] . '</td>
                        <td>' . $crust_names[$i] . '</td>
                        <td>' . $amounts[$i] . '</td>
                        <td>' . $price[$i] . '</td>
                    </tr>';
            }

            echo '</tbody></table>';

                            echo '<p class="card-text">ยอดรวมทั้งหมด: ' . $row["total"] . '</p>';
                                         echo "<form method='POST' action='owner_dashboard.php?user_name=$user_name'>";
                                         echo '<div class="row justify-content-center">';
                                            echo "<input type='hidden' name='order_id' value='" . $row["order_id"] . "'>";
                                                echo '<div class="col-3">';
                                                echo "<label for='payment_status'>สถานะชำระเงิน</label>";
                                                    echo "<select class='form-control' name='payment_status'>";
                                                        echo "<option value='ยังไม่จ่าย' " . ($row["payment_status"] == "ยังไม่จ่าย" ? "selected" : "") . ">ยังไม่จ่าย</option>";
                                                        echo "<option value='จ่ายแล้ว' " . ($row["payment_status"] == "จ่ายแล้ว" ? "selected" : "") . ">จ่ายแล้ว</option>";
                                                    echo "</select>";
                                                   echo '</div>';

                                                echo '<div class="col-3">';
                                                echo "<label for='status'>สถานะจัดส่ง</label>";
                                                    echo "<select class='form-control' name='status'>";
                                                        echo "<option value='1' " . ($row["status"] == "1" ? "selected" : "") . ">ยังไม่ส่ง</option>";
                                                        echo "<option value='2' " . ($row["status"] == "2" ? "selected" : "") . ">ส่งแล้ว</option>";
                                                    echo "</select>";
                                                echo '</div>';
                                            echo "<button type='submit' style='margin: auto;'class='btn btn-primary'>บันทึก</button>";
                                        echo "</form>";
                                     echo '</div>';
                echo '</div>';
            echo '</div>';
        }
    } else {
        echo "<p>ไม่มีรายการสั่งซื้อ</p>";
    }
    ?>
</div>
</body>
</html>
