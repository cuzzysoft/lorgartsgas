<?php
include('../../db/connect.php');

$my = mysqli_query($conn, "select price from products where id = 5");
if($row = mysqli_fetch_assoc($my)){
$price = $row['price'];
echo $price;
}