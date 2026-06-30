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

$line_items = [];
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$base_url = $protocol . $host . ($base_path === '/' ? '' : $base_path);
$success_url = $base_url . '/success.php?session_id={CHECKOUT_SESSION_ID}';
$cancel_url = $base_url . '/cancel.php';

if (isset($_POST['single_book'])) {
    // Single book purchase
    $product_id = $_POST['product_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $number = $_POST['number'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $select = mysqli_query($conn, "SELECT * FROM products WHERE id='$product_id'") or die('query failed');
    if (mysqli_num_rows($select) > 0) {
        $book = mysqli_fetch_assoc($select);
        $line_items[] = [
            'price_data' => [
                'currency' => 'inr',
                'product_data' => [
                    'name' => $book['name'],
                    'description' => substr($book['description'], 0, 100),
                ],
                'unit_amount' => $book['price'] * 100, // Amount in paise
            ],
            'quantity' => 1,
        ];
        $metadata = ['user_id' => $user_id, 'product_ids' => $product_id, 'type' => 'single', 'name' => $name, 'number' => $number, 'email' => $email];
    } else {
        die('Book not found');
    }
} elseif (isset($_POST['cart_checkout'])) {
    // Cart checkout
    $user_query = mysqli_query($conn, "SELECT name, email FROM register WHERE id='$user_id'") or die('query failed');
    $user = mysqli_fetch_assoc($user_query);
    $name = $user['name'];
    $email = $user['email'];
    $number = 'N/A';
    $tbr_query = mysqli_query($conn, "SELECT * FROM tbr_list WHERE user_id='$user_id'") or die('query failed');
    if (mysqli_num_rows($tbr_query) > 0) {
        $product_ids = [];
        while ($item = mysqli_fetch_assoc($tbr_query)) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'inr',
                    'product_data' => [
                        'name' => $item['name'],
                    ],
                    'unit_amount' => $item['price'] * 100,
                ],
                'quantity' => 1,
            ];
            $product_ids[] = $item['product_id'];
        }
        $metadata = ['user_id' => $user_id, 'product_ids' => implode(',', $product_ids), 'type' => 'cart', 'name' => $name, 'number' => $number, 'email' => $email];
    } else {
        die('Cart is empty');
    }
} else {
    die('Invalid request');
}

try {
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $line_items,
        'mode' => 'payment',
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
        'metadata' => $metadata,
    ]);

    header('Location: ' . $session->url);
    exit;
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
