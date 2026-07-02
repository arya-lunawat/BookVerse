<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];
if (!isset($user_id)) {
    header('location:login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Cancelled</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="home.css">
</head>
<body>

<?php include 'user_header.php'; ?>

<section class="contact_us">
  <h2>Payment Cancelled</h2>
  <p class="message">Your payment was cancelled. You can try again.</p>
  <a href="cart.php" class="product_btn">Back to Cart</a>
  <a href="shop.php" class="product_btn">Continue Shopping</a>
</section>

<?php include 'footer.php'; ?>

</body>
</html>
