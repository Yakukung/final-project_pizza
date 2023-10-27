<?php
require_once "dbconfig.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$owner_name = $_GET['user_name'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>เลือกลูกค้าเพื่อดูรายการออร์เดอร์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
    <style>
    .card-grid {
        display: grid;
        grid-template-columns: auto auto auto auto;
        grid-gap: 20px;
    }

    .customer-card {
        margin-top: 2rem;
        padding: 1rem;
        transition: transform 0.6s, box-shadow 0.3s;
        box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.2);
        text-align: center;
        border-radius: 30px;
    }

    .customer-card:hover {
        transform: scale(1.10);
        box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.4);
    }

    .order-count{
            width: 23px;
            text-align: center;
            position: absolute;
            transform: translate(-63%, -50%);
            font-size: 0.9rem; background-color: red;
            color: white;
            border-radius: 90px;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <div class="logo">
            <a href="owner_dashboard.php?user_name=<?php echo $owner_name; ?>">
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
    <div class="container mt-5">
        <h1>เลือกลูกค้าเพื่อดูรายการออร์เดอร์</h1>
        <div class="card-grid">
            <?php
            // เรียกคำ SQL ที่เลือกลูกค้า (ไม่ใช่เจ้าของร้าน)
            $sql = "SELECT user_name FROM User WHERE user_name != 'เจ้าของร้าน'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $customer_name = $row["user_name"];

                    // ดึงข้อมูลออเดอร์จากฐานข้อมูล
                    $sql = "SELECT `Order`.*, COALESCE(SUM(Basket.amount * Item.Price), 0) AS total_amount
                    FROM `Order`
                    INNER JOIN User ON `Order`.user_id = User.user_id
                    LEFT JOIN Basket ON `Order`.order_id = Basket.order_id
                    LEFT JOIN Item ON Basket.item_id = Item.item_id
                    WHERE User.user_name = ? AND `Order`.total IS NOT NULL
                    GROUP BY `Order`.order_id
                    ORDER BY `Order`.order_id ASC";

                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $customer_name);
                    $stmt->execute();
                    $result_order = $stmt->get_result();

                    // นับจำนวนรายการออเดอร์
                    $order_count = $result_order->num_rows;

                    echo '<div class="customer-card">';
                    echo "<h3>$customer_name</h3>";
                    echo '<button class="btn btn-primary" onclick="window.location.href=\'owner_dashboard_detail.php?owner_name=' . $owner_name . '&customer_name=' . $customer_name . '\'">ดูรายการออร์เดอร์</button>';

                    // เช็คว่ามีรายการออร์เดอร์หรือไม่
                    if ($order_count > 0) {
                        echo '<span class="order-count">' . $order_count . '</span>';
                    } else {
                        echo '<span class="order-count">0</span>';
                    }

                    echo '</div>';
                }
            } else {
                echo "<p>ไม่มีข้อมูลลูกค้า</p>";
            }
            ?>
        </div>
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
