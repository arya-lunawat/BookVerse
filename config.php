<?php
$conn = new mysqli(
    getenv('MYSQLHOST'),
    getenv('MYSQLUSER'),
    getenv('MYSQLPASSWORD'),
    getenv('MYSQLDATABASE'),
    getenv('MYSQLPORT')
);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

mysqli_set_charset($conn, 'utf8mb4');

$stripe_secret_key = getenv('STRIPE_SECRET_KEY');
$stripe_publishable_key = getenv('STRIPE_PUBLISHABLE_KEY');
?>
