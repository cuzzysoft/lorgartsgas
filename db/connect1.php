<?php
$conn = mysqli_connect("localhost","root","","coiscost_dbase");

// Check connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error($conn);
  exit();
}
?>