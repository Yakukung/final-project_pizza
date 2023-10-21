<?php
$servername = "202.28.34.197";
$user_name = "web66_65011212083";
$password = "65011212083@csmsu";
$dbname = "web66_65011212083";

$conn = new mysqli($servername, $user_name, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
