<?php
if (isset($_GET['user_name']) && isset($_GET['order_id'])) {
    $user_name = $_GET['user_name'];
    $new_order_id = $_GET['order_id'];
}
?>

<div class="container-back-home" style="width: 100%; margin-top: 1rem; padding-left: 4rem;">
    <a class="btn btn-success"style="border: none; border-radius: 15px; padding: 1rem;" href="home.php?user_name=<?php echo $user_name; ?>&order_id=<?php echo $new_order_id; ?>">
        <i class="bi bi-arrow-up-left-square-fill"></i> กลับไปหน้าแรก
    </a>
 </div>