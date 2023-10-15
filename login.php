<?php
    require_once "dbconfig.php";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $position = $_POST['position'];

        // เพิ่มเงื่อนไขความปลอดภัยที่ดีขึ้นเพื่อป้องกันการโจมตี SQL Injection
        $email = mysqli_real_escape_string($conn, $email);
        $password = mysqli_real_escape_string($conn, $password);

        if ($position == '1') {
            $sql = "SELECT * FROM User 
                    WHERE email = '$email'
                    AND password = '$password' 
                    AND position = '$position'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $user_name = $row['user_name'];
                $user_id = $row['user_id'];

                header("Location: owner_dashboard.php?user_name=$user_name");
                exit();
            } else {
                echo '<div class="alert alert-danger text-center" role="alert">เข้าสู่ระบบไม่สำเร็จ</div>';
            }
        } elseif ($position == '2') {
            $sql = "SELECT * FROM User 
                    WHERE email = '$email' 
                    AND password = '$password' 
                    AND position = '$position'";
            $result = $conn->query($sql);
        
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $user_name = $row['user_name'];
                $user_id = $row['user_id'];
                $user_phone = $row['phone'];
                $user_address = $row['address'];
        
                // ตรวจสอบว่าผู้ใช้นี้มีรายการสั่งซื้ออยู่แล้วหรือไม่
                $sql_check_order = "SELECT * FROM `Order` WHERE `user_id` = $user_id";
                $result_check_order = $conn->query($sql_check_order);
        
                if ($result_check_order->num_rows == 0) {
                    // ถ้าไม่มีรายการสั่งซื้อ ให้สร้างบันทึกใหม่ในตาราง "Order" โดยใช้ NOW() เพื่อรวมวันที่และเวลา
                    $sql_insert_order = "INSERT INTO `Order` (`user_id`, `order_date`, `order_name`, `order_phone`, `order_address`)
                                        VALUES ($user_id, NOW(), '$user_name', '$user_phone', '$user_address')";
                    $conn->query($sql_insert_order);
                }
        
                header("Location: home.php?user_name=$user_name");
                exit();
            } else {
                echo '<div class="alert alert-danger text-center" role="alert">เข้าสู่ระบบไม่สำเร็จ</div>';
            }
        }  
    }
    $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- เชื่อมโยงไปยังไฟล์ CSS ของ Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body{
            background-color: #820300;
        }
    </style>
</head>
<body>
    <div class="container-login">
        <div class="card-login">
            <div class="logo-login">
                <img src="css/LOGOpizza.png" alt="">
            </div>
            <div class="card-title">
                <h1>ล็อคอินเข้าสู่ระบบ</h1>
            </div>
            <div class="card-body">
                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="position">เลือกประเภท:</label>
                        <select id="position" name="position" class="form-control" required>
                            <option value="1">เจ้าของร้าน</option>
                            <option value="2">ลูกค้า</option>
                        </select>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">เข้าสู่ระบบ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
