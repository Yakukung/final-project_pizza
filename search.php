<?php
require_once "dbconfig.php"; // นำเข้าไฟล์การเชื่อมต่อกับฐานข้อมูล

// ตรวจสอบการส่งค้นหาจากฟอร์ม
if (isset($_GET['search_query']) && isset($_GET['user_name']) && isset($_GET['order_id'])) {
    $search_query = $_GET['search_query'];
    $user_name = $_GET['user_name'];
    $new_order_id = $_GET['order_id'];

    // ดึงข้อมูลพิซซ่าจากฐานข้อมูล
    $sql = "SELECT *
            FROM Pizza
            WHERE pizza_name LIKE ? OR detail LIKE ?";
    
    $stmt = $conn->prepare($sql);
    $search_query = "%" . $search_query . "%"; // เพิ่ม "%" ทั้งด้านหน้าและด้านหลังของคำค้นหา
    $stmt->bind_param("ss", $search_query, $search_query);
    $stmt->execute();
    $result_search = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
      
</head>
<body>
<?php
    include "navbar.php";
?>

    <div class="container-pizza">
        <h1 style="margin-top:  2rem; margin-left: 2rem;">ผลการค้นหาของคุณ...</h1>
         <div class="card-grid-pizza"style="margin-top: 1rem;">
        <?php
        // ตรวจสอบว่ามีผลลัพธ์จากการค้นหาหรือไม่
        if ($result_search->num_rows > 0) {
            while ($pizzaData = $result_search->fetch_assoc()) {
                $pizza_id = $pizzaData['pizza_id'];
                $pizza_image = $pizzaData['pizza_image'];
                $pizza_name = $pizzaData['pizza_name'];
                $pizza_details = $pizzaData['detail'];
                $pizza_price = $pizzaData['pizza_price'];
                ?>
                <div class="card-column" style="width: 320px;">
                        <a href=''>
                            <img src='<?php echo  $pizza_image; ?>' class='card-img-pizza' alt='Pizza Image' style="max-width: 100%; height: auto;">
                        </a>
                        <div class="card-body">
                             <h1><?php echo $pizza_name; ?></h1>
                             <h2><?php echo  $pizza_details; ?></h2>

                             <div class="price_and_btn">
                             <h3>฿<?php echo $pizza_price; ?></h3>
                             <a class="btn btn-add-product" href="pizza_item.php?pizza_id=<?php echo $pizza_id; ?>&user_name=<?php echo $user_name; ?>&order_id=<?php echo $new_order_id; ?>"" style="margin-right: 1rem; color: white; background-color: #67927A; border: none;">
                                <i class="bi bi-basket3-fill"></i> เพิ่มสินค้า
                            </a>
                        </div>
                        </div>
                        
                </div>
            <?php
            }
        } else {
            echo "<p>No results found.</p>";
        }
        ?>
    </div>
</body>
</html>
