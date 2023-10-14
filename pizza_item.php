<?php
require_once "dbconfig.php";

if (isset($_GET['pizza_id']) && isset($_GET['user_name'])) {
    $pizza_id = $_GET['pizza_id'];
    $user_name = $_GET['user_name'];

    $sql_pizza = "SELECT * FROM Pizza WHERE pizza_id = $pizza_id";
    $result_pizza = $conn->query($sql_pizza);

    if ($result_pizza->num_rows > 0) {
        $pizzaData = $result_pizza->fetch_assoc();
        $pizza_name = $pizzaData['pizza_name'];
        $pizza_price = $pizzaData['pizza_price'];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // รับค่าขนาดและขอบจากฟอร์ม
            $size_id = $_POST['size_id'];
            $crust_id = $_POST['crust_id'];

            // ดึงราคาขนาดจากตาราง Size
            $sql_size_price = "SELECT size_price FROM Size WHERE size_id = ?";
            $stmt_size = $conn->prepare($sql_size_price);
            $stmt_size->bind_param("i", $size_id);
            $stmt_size->execute();
            $stmt_size->bind_result($size_price);
            $stmt_size->fetch();
            $stmt_size->close();

            // ดึงราคาขอบจากตาราง Crust
            $sql_crust_price = "SELECT crust_price FROM Crust WHERE crust_id = ?";
            $stmt_crust = $conn->prepare($sql_crust_price);
            $stmt_crust->bind_param("i", $crust_id);
            $stmt_crust->execute();
            $stmt_crust->bind_result($crust_price);
            $stmt_crust->fetch();
            $stmt_crust->close();

            // คำนวณราคารวม
            $total = $pizza_price + $size_price + $crust_price;

            // หาค่า order_id จากตาราง "Order" โดยใช้ user_name
            $sql_find_order_id = "SELECT order_id FROM `Order` WHERE user_id = (SELECT user_id FROM User WHERE user_name = ?)";
            $stmt_find_order_id = $conn->prepare($sql_find_order_id);
            $stmt_find_order_id->bind_param("s", $user_name);
            $stmt_find_order_id->execute();
            $stmt_find_order_id->bind_result($order_id);
            $stmt_find_order_id->fetch();
            $stmt_find_order_id->close();

            // ตรวจสอบว่ามีรายการในตะกร้าที่มีคุณสมบัติเหมือนกันหรือไม่
            $sql_check_item = "SELECT item_id, amount FROM Basket 
                               WHERE order_id = ? 
                               AND item_id IN (SELECT item_id FROM Item WHERE pizza_id = ? AND size_id = ? AND crust_id = ?)";

            $stmt_check_item = $conn->prepare($sql_check_item);
            $stmt_check_item->bind_param("iiii", $order_id, $pizza_id, $size_id, $crust_id);
            $stmt_check_item->execute();
            $stmt_check_item->store_result();

            if ($stmt_check_item->num_rows > 0) {
                // หากมีรายการที่เหมือนกันอยู่แล้ว
                $stmt_check_item->bind_result($existing_item_id, $existing_amount);
                $stmt_check_item->fetch();

                // ทำการอัปเดตจำนวนสินค้าเพิ่มขึ้น 1
                $new_amount = $existing_amount + 1;

                $sql_update_item = "UPDATE Basket SET amount = ? WHERE item_id = ?";
                $stmt_update_item = $conn->prepare($sql_update_item);
                $stmt_update_item->bind_param("ii", $new_amount, $existing_item_id);

                if ($stmt_update_item->execute()) {
                    echo '<div class="alert alert-success text-center" role="alert">เพิ่มสินค้าลงในตะกร้าสำเร็จ!</div>';
                } else {
                    echo '<div class="alert alert-danger text-center" role="alert">เกิดข้อผิดพลาดในการอัปเดตจำนวนสินค้า</div>';
                }
            } else {
                // หากไม่มีรายการเหมือนกันอยู่
                // ให้เพิ่มรายการใหม่เหมือนเดิม
                $sql_insert_item = "INSERT INTO Item (pizza_id, size_id, crust_id, price) VALUES (?, ?, ?, ?)";
                $stmt_insert_item = $conn->prepare($sql_insert_item);
                $stmt_insert_item->bind_param("iiid", $pizza_id, $size_id, $crust_id, $total);

                if ($stmt_insert_item->execute()) {
                    $item_id = $stmt_insert_item->insert_id;
                    $stmt_insert_item->close();

                    $amount = 1;

                    $sql_insert_basket = "INSERT INTO Basket (order_id, item_id, amount) VALUES (?, ?, ?)";
                    $stmt_insert_basket = $conn->prepare($sql_insert_basket);
                    $stmt_insert_basket->bind_param("iii", $order_id, $item_id, $amount);

                    if ($stmt_insert_basket->execute()) {
                        echo '<div class="alert alert-success text-center" role="alert">เพิ่มสินค้าลงในตะกร้าสำเร็จ!</div>';
                    } else {
                        echo '<div class="alert alert-danger text-center" role="alert">เกิดข้อผิดพลาดในการเพิ่มสินค้าลงในตะกร้า</div>';
                    }
                    $stmt_insert_basket->close();
                } else {
                    echo '<div class="alert alert-danger text-center" role="alert">เกิดข้อผิดพลาดในการเพิ่มสินค้าลงในระบบ</div>';
                }
            }
            $stmt_check_item->close();
        }
    } else {
        echo '<div class="alert alert-danger text-center" role="alert">ไม่พบข้อมูลพิซซ่า</div>';
    }
} else {
    echo '<div class="alert alert-danger text-center" role="alert">ข้อมูลไม่ถูกต้อง</div>';
}

$conn->close();
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
<div class="navbar">
    <div class="logo">
         <a href="home.php?user_name=<?php echo $user_name; ?>">
        <img src="css/LOGOpizza.png"alt="">
     </div>
    <div class="basket">
        <a class="btn btn-basket" href="basket.php?user_name=<?php echo $user_name; ?>">
            <i class="bi bi-basket3-fill"></i>
        </a>
    </div>
    <div class="nav-user">
        <a class="user-image" href="login.php">
            <i class="bi bi-person-circle"></i>
        </a>
        <a class="user-name" href="login.php" style="text-decoration: none;">
            <h1>สวัสดี, <?php echo $user_name; ?></h1>
        </a>
    </div>
</div>
<div class="container-pizza_item">
    <div class="card-pizza_item">
        <?php
        echo "<img src='" . $pizzaData['pizza_image'] . "' alt='" . $pizza_name . "' style='max-width: 100%; height: auto;' />";
        echo "<h1>$pizza_name</h1>";

        if (isset($pizzaData['detail'])) {
            echo "<p>รายละเอียด: " . $pizzaData['detail'] . "</p>";
        }
        ?>
        <div class="pizza-select">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="size_id">เลือกขนาด:</label>
                    <select id="size_id" name="size_id" class="form-control" required onchange="calculatetotalPrice()">
                        <?php
                        // สร้างลูปเพื่อแสดงตัวเลือกขนาด
                        $sizes = [
                            ['id' => 1, 'name' => 'S', 'price' => 0],
                            ['id' => 2, 'name' => 'M', 'price' => 10],
                            ['id' => 3, 'name' => 'L', 'price' => 20],
                            ['id' => 4, 'name' => 'XL', 'price' => 30]
                        ];

                        foreach ($sizes as $size) {
                            echo '<option value="' . $size['id'] . '" data-price="' . $size['price'] . '">' . $size['name'] . '</option>';
                        }
                        ?>
                    </select>

                    <label for="crust_id">เลือกขอบ:</label>
                    <select id="crust_id" name="crust_id" class="form-control" required onchange="calculatetotalPrice()">
                        <?php
                        // สร้างลูปเพื่อแสดงตัวเลือกขอบ
                        $crusts = [
                            ['id' => 1, 'name' => 'บางกรอบ', 'price' => 0],
                            ['id' => 2, 'name' => 'หนานุ่ม', 'price' => 10],
                            ['id' => 3, 'name' => 'ขอบชีส', 'price' => 20]
                        ];

                        foreach ($crusts as $crust) {
                            echo '<option value="' . $crust['id'] . '" data-price="' . $crust['price'] . '">' . $crust['name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <!-- เพิ่ม input hidden สำหรับราคาขนาดและขอบ -->
                <input type="hidden" id="size_price" name="size_price" value="0">
                <input type="hidden" id="crust_price" name="crust_price" value="0">
                    <div class="card_total">
                        <p>ราคารวมทั้งหมด</p>
                        <div id="total" style="font-size: 2rem;  font-weight: bold; color: #4aa774;">฿<?php echo $pizza_price; ?></div>
                    </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">เพิ่มใส่ตะกร้า</button>
                        </div>
            </form>
        </div>
    </div>
</div>

<script>
    function calculatetotalPrice() {
        const sizeSelect = document.getElementById("size_id");
        const crustSelect = document.getElementById("crust_id");
        const sizePriceInput = document.getElementById("size_price");
        const crustPriceInput = document.getElementById("crust_price");
        const totalPriceDiv = document.getElementById("total");

        const selectedSizePrice = parseInt(sizeSelect.options[sizeSelect.selectedIndex].getAttribute("data-price"));
        const selectedCrustPrice = parseInt(crustSelect.options[crustSelect.selectedIndex].getAttribute("data-price"));

        sizePriceInput.value = selectedSizePrice;
        crustPriceInput.value = selectedCrustPrice;

        const pizzaPrice = <?php echo $pizza_price; ?>;
        const total = pizzaPrice + selectedSizePrice + selectedCrustPrice;

        totalPriceDiv.innerText = `฿${total}`;
    }
</script>
</body>
</html>
