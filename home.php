<?php
require_once "dbconfig.php";

    $user_name= $_GET['user_name'];
    // ใช้ $order_id ในการเรียกข้อมูลที่คุณต้องการ
    $new_order_id = $_GET['order_id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/style.css">
      
</head>
<body>
<?php
    include "navbar.php";
?>

 <div class="container-advert" style="margin-top: 1rem; width: 100%;">
    <div class="slideshow-grid">
        <div class="slide">
            <img src="https://cdn.1112delivery.com/1112one/public/images/banners/Sep23/Ham_Cheese_1440_TH.jpg" alt="Blurry Image 1">
        </div>
        <div class="slide">
            <img src="https://pizzaoriginalfactory.com/wp-content/uploads/2021/03/%E0%B8%AA%E0%B9%84%E0%B8%A5%E0%B8%94%E0%B9%8C%E0%B9%80%E0%B8%A7%E0%B9%87%E0%B8%9A-Original-pizza-02.jpg" alt="Clear Image 1">
        </div>
        <div class="slide">
            <img src="https://pbs.twimg.com/media/DbXoHWpUQAAQTYe?format=jpg&name=900x900" alt="Blurry Image 2">
        </div>
    </div>
 </div>

    <div class="container-pizza">
        <div class="card-grid-pizza"style="margin-top: 1rem">
            <?php
            $sql_pizza = "SELECT pizza_id, pizza_image, pizza_name, detail, pizza_price
                          FROM Pizza";
            $result_pizza = $conn->query($sql_pizza);

            while ($pizzaData = $result_pizza->fetch_assoc()) {
                $pizza_id = $pizzaData['pizza_id'];
                $pizza_image = $pizzaData['pizza_image'];
                $pizza_name = $pizzaData['pizza_name'];
                $pizza_details = $pizzaData['detail'];
                $pizza_price = $pizzaData['pizza_price'];
                ?>
                <div class="card-column">
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
            ?>
        </div>
    </div>
   
    <script>
        let slideIndex = 0;
        showSlides();

        function showSlides() {
            const slides = document.querySelectorAll('.slide');

            // ซ่อนภาพทั้งหมด
            slides.forEach((slide) => {
                slide.style.display = 'none';
            });

            // เลื่อนสไลด์ไปที่สไลด์ถัดไป
            slideIndex++;

            if (slideIndex > slides.length) {
                slideIndex = 1;
            }

            // แสดงสไลด์ปัจจุบันและให้เกิดเอฟเฟคเลื่อนเข้ามาทางขวา
            slides[slideIndex - 1].style.display = 'block';
            slides[slideIndex - 1].style.animation = 'slide-in-right 0.8s ease-in-out';

            // เรียกใช้ฟังก์ชันนี้ทุก 8 วินาที (4 วินาทีสำหรับแสดงภาพและ 4 วินาทีสำหรับเลื่อนเฟด)
            setTimeout(() => {
                slides[slideIndex - 1].style.animation = '';
                showSlides();
            },  4000);
        }
    </script>
</body>
</html>