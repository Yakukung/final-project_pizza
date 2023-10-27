<?php
if (isset($_GET['user_name']) && isset($_GET['order_id'])) {
    $user_name = $_GET['user_name'];
    $new_order_id = $_GET['order_id'];
}
?>

<div class="navbar">
    <div class="logo">
        <a href="home.php?user_name=<?php echo $user_name; ?>&order_id=<?php echo $new_order_id; ?>">
            <img src="css/LOGOpizza.png" alt="">
        </a>
    </div>

    <div class="search-bar" style="padding-top: 1rem;">
    <form action="search.php" method="GET">
        <input type="text" name="search_query" placeholder="ค้นหา...">
        <input type="hidden" name="user_name" value="<?php echo $user_name; ?>">
        <input type="hidden" name="order_id" value="<?php echo $new_order_id; ?>">
        <button type="submit"><i class="bi bi-search"></i></button>
    </form>
</div>



    <div class="basket">
        <a class="btn btn-box" href="order.php?user_name=<?php echo $user_name; ?>&order_id=<?php echo $new_order_id; ?>">
            <i class="bi bi-box2-fill"></i>
            <?php
            // ดึงข้อมูลออเดอร์จากฐานข้อมูล
            $sql = "SELECT `Order`.*, SUM(Basket.amount * Item.Price) AS total_amount
                    FROM `Order`
                    INNER JOIN User ON `Order`.user_id = User.user_id
                    LEFT JOIN Basket ON `Order`.order_id = Basket.order_id
                    LEFT JOIN Item ON Basket.item_id = Item.item_id
                    WHERE User.user_name = ? AND `Order`.total IS NOT NULL
                    GROUP BY `Order`.order_id
                    ORDER BY `Order`.order_id ASC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $user_name);
            $stmt->execute();
            $result = $stmt->get_result();

            // นับจำนวนรายการออเดอร์
            $order_count = $result->num_rows;

            if ($order_count > 0){
                echo '<span class="order-count">' . $order_count . '</span>';
            }
            ?>
        </a>
        <a class="btn btn-basket" href="basket.php?user_name=<?php echo $user_name; ?>&order_id=<?php echo $new_order_id; ?>">
            <i class="bi bi-basket-fill"></i>
            <?php
            // ดึงจำนวนสินค้าในตะกร้าของผู้ใช้
$sql_count_items = "SELECT COUNT(*) AS item_count FROM Basket WHERE order_id = ?";
$stmt_count_items = $conn->prepare($sql_count_items);
$stmt_count_items->bind_param("i", $new_order_id);
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
        <a class="user-image">
            <i class="bi bi-person-circle"></i>
        </a>
        <a class="user-name">
        <h3>สวัสดี, <?php echo $user_name; ?>!</h3>
    </a>
    <a class="logout" button type="submit" class="btn btn-primary" href="javascript:void(0);" style="text-decoration: none;" onclick="confirmLogout()">
        <p>ออกจากระบบ</p>
    </a>
    </div>
</div>

<script>
function confirmLogout() {
    var result = confirm("คุณแน่ใจหรือไม่ที่จะออกจากระบบ?");
    if (result) {
        // หากผู้ใช้คลิก OK ให้นำไปยังหน้าล็อกเอาท์
        window.location.href = "login.php";
    }
}
</script>