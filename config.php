<?php
$servername = "sql110.infinityfree.com";
$username = "if0_39218287";
$password = "EDUV4815682";
$dbname = "if0_39218287_c_2_c_platform";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
