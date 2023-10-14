<?php
$servername = "localhost";
$user_name = "project_pizza";
$password = "abc123";
$dbname = "project_pizza";

$conn = new mysqli($servername, $user_name, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
