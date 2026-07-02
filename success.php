<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];
if (!isset($user_id)) {
    header('location:login.php');
    exit;
}

require_once 'vendor/autoload.php';
\Stripe\Stripe::setApiKey($stripe_secret_key);

$session_id = $_GET['session_id'];
if (!$session_id) {
    die('Invalid session');
}

try {
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    if ($session->payment_status === 'paid') {
        $metadata = $session->metadata;
        $product_ids = explode(',', $metadata['product_ids']);
        $type = $metadata['type'];
        $name = $metadata['name'];
        $number = $metadata['number'];
        $email = $metadata['email'];
        $placed_on = date('d-M-Y');
        $total_products = '';
        $total_price = $session->amount_total / 100; // Convert from paise

        // Build total_products string
        foreach ($product_ids as $pid) {
            $prod_query = mysqli_query($conn, "SELECT name FROM products WHERE id='$pid'") or die('query failed: ' . mysqli_error($conn));
            $prod = mysqli_fetch_assoc($prod_query);
            $total_products .= $prod['name'] . ', ';
        }
        $total_products = rtrim($total_products, ', ');
        $total_products = mysqli_real_escape_string($conn, $total_products);

        // Insert order
        $order_query = mysqli_query($conn, "INSERT INTO orders (user_id, name, number, email, method, address, total_products, total_price, placed_on, payment_status) VALUES ('$user_id', '$name', '$number', '$email', 'Stripe', 'N/A', '$total_products', '$total_price', '$placed_on', 'paid')") or die('query failed: ' . mysqli_error($conn));

        // Insert purchased_books, avoid duplicates
        foreach ($product_ids as $pid) {
            $check = mysqli_query($conn, "SELECT id FROM purchased_books WHERE user_id='$user_id' AND product_id='$pid'") or die('query failed: ' . mysqli_error($conn));
            if (mysqli_num_rows($check) == 0) {
                mysqli_query($conn, "INSERT INTO purchased_books (user_id, product_id) VALUES ('$user_id', '$pid')") or die('query failed: ' . mysqli_error($conn));
            }
        }

        // Clear TBR if cart
        if ($type === 'cart') {
            mysqli_query($conn, "DELETE FROM tbr_list WHERE user_id='$user_id'") or die('query failed: ' . mysqli_error($conn));
        }

        $message[] = 'Payment successful! Order placed.';
    } else {
        $message[] = 'Payment not completed.';
    }
} catch (Exception $e) {
    $message[] = 'Error verifying payment: ' . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Payment Success</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="home.css">
</head>
<body>

<?php include 'user_header.php'; ?>

<section class="contact_us">
  <h2>Payment Status</h2>
  <a href="orders.php" class="product_btn">View Orders</a>
  <a href="my_library.php" class="product_btn">Go to My Library</a>
  <a href="shop.php" class="product_btn">Continue Shopping</a>
</section>

<?php include 'footer.php'; ?>

</body>
</html>
