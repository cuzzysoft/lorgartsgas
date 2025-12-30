
<?php
date_default_timezone_set('Africa/Lagos');

$conn = mysqli_connect(
    "localhost",
    "hrkaynui_root",
    "!5B[B3dK2Gedt9",
    "hrkaynui_lorgartsdb"
);

// Check connection
if (!$conn) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Set MySQL timezone
$conn->query("SET time_zone = '+01:00'");
?>
