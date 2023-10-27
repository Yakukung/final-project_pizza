<?php
require_once "dbconfig.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$customer_name = $_GET['customer_name']; // เปลี่ยนตัวแปรเป็น 'customer_name'

$owner_name = $_GET['owner_name']; 

// ดึงรายการออร์เดอร์ของลูกค้าคนนั้น
$sql = "SELECT `Order`.order_id, 
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
    INNER JOIN Size ON Item.size_id = Size.size_id
    INNER JOIN Crust ON Item.crust_id = Crust.crust_id
    WHERE User.user_name = ? AND `Order`.total IS NOT NULL
    GROUP BY `Order`.order_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $customer_name);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// ตรวจสอบส่วนของการอัปเดตสถานะการชำระเงิน
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["order_id"]) && isset($_POST["payment_status"]) && isset($_POST["status"])) {
    $order_id = $_POST["order_id"];
    $payment_status = $_POST["payment_status"];
    $status = $_POST["status"];

    // คำสั่ง SQL สำหรับอัปเดตสถานะการชำระเงินในตาราง Order
    $sql_update_payment_status = "UPDATE `Order` SET payment_status = ?, status = ? WHERE order_id = ?";
    $stmt_update_payment_status = $conn->prepare($sql_update_payment_status);
    $stmt_update_payment_status->bind_param("sii", $payment_status, $status, $order_id);
    
    if ($stmt_update_payment_status->execute()) {
        echo '<div class="alert alert-success text-center" role="alert"> อัพเดตข้อมูลเรียบร้อย</div>';
        echo '<script>
            setTimeout(function() {
                window.location = window.location.href; // รีโหลดหน้าเว็บปัจจุบันครั้งเดียว
            }, 2000); // รอเวลา 1 วินาทีก่อนรีโหลด
        </script>';
    } else {
        echo '<div class="alert alert-danger text-center" role="alert">ไม่สามารถอัปเดตข้อมูลได้</div>';
    }

    // ปิดการเชื่อมต่อกับฐานข้อมูลสำหรับ payment_status
    $stmt_update_payment_status->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Owner Dashboard Detail: <?php echo $customer_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
<body>
    <div class="navbar">
        <div class="logo">
            <a href="owner_dashboard.php?user_name=<?php echo $owner_name?>">
                <img src="css/LOGOpizza.png" alt="">
            </a>
        </div>
        <div class="nav-user">
            <a class="user-image">
                <i class="bi bi-person-circle"></i>
            </a>
            <a class="user-name">
                <h3>สวัสดี, <?php echo $owner_name; ?>!</h3>
            </a>
            <a class="logout" href="javascript:void(0);" style="text-decoration: none;" onclick="confirmLogout()">
                <p>ออกจากระบบ</p>
            </a>
        </div>
    </div>
    <div class="container-back-home" style="width: 100%; margin-top: 1rem; padding-left: 4rem;">
    <a class="btn btn-success"style="border: none; border-radius: 15px; padding: 1rem;" href="owner_dashboard.php?user_name=<?php echo $owner_name?>">
        <i class="bi bi-arrow-up-left-square-fill"></i> กลับไปหน้าแรก
    </a>
 </div>
    <div class="container mt-5">
        <h1>คำสั่งซื้อทั้งหมดของ: <?php echo $customer_name; ?></h1>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="card mb-5" style="background-color: #FAFAFA; padding: 2rem; border-radius: 65px; border: none; margin: 1rem 10rem">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title" style="font-size: 2rem;">Order ID: ' . $row["order_id"] . '</h5>';
    
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
    
                echo '<p class="card-text" style="text-align: end;">ยอดรวมทั้งหมด: ' . $row["total"] . '</p>';
                echo "<form method='POST' action='owner_dashboard_detail.php?owner_name=$owner_name&customer_name=$customer_name'>"; // เปลี่ยน $user_name เป็น $customer_name
                echo '<div class="row justify-content-center">';
                echo "<input type='hidden' name='order_id' value='" . $row["order_id"] . "'>";
                echo '<div class="col-3">';
                echo "<label for='payment_status'>สถานะชำระเงิน</label>";
                echo "<select class='form-control' name='payment_status'>";
                echo "<option value='ยังไม่ชำระเงิน' " . ($row["payment_status"] == "ยังไม่ชำระเงิน" ? "selected" : "") . ">ยังไม่ชำระเงิน</option>";
                echo "<option value='ชำระเงินแล้ว' " . ($row["payment_status"] == "ชำระเงินแล้ว" ? "selected" : "") . ">ชำระเงินแล้ว</option>";
                echo "</select>";
                echo '</div>';
                echo '<div class="col-3">';
                echo "<label for='status'>สถานะจัดส่ง</label>";
                echo "<select class='form-control' name='status'>";
                echo "<option value='1' " . ($row["status"] == "1" ? "selected" : "") . ">ยังไม่ส่ง</option>";
                echo "<option value='2' " . ($row["status"] == "2" ? "selected" : "") . ">ส่งแล้ว</option>";
                echo "</select>";
                echo '</div>';
                echo "<button type='submit' style='margin: auto;' class='btn btn-primary'>บันทึก</button>";
                echo '</div>';
                echo '</form>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo "<p>ไม่มีรายการออร์เดอร์สำหรับ $customer_name</p>";
        }
        ?>
    </div>
</body>
<script>
function confirmLogout() {
    var result = confirm("คุณแน่ใจหรือไม่ที่จะออกจากระบบ?");
    if (result) {
        // หากผู้ใช้คลิก OK ให้นำไปยังหน้าล็อกเอาท์
        window.location.href = "login.php";
    }
}
</script>


</html>
