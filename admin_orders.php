<?php
include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
  header('location:login.php');
}

// Handle CSV Export
if (isset($_POST['export_csv'])) {
  $select_orders = mysqli_query($conn, "SELECT * FROM `orders` WHERE payment_status = 'paid' OR payment_status = 'completed'") or die('query failed');
  
  // Set headers for CSV download
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d_H-i-s') . '.csv"');
  
  // Open output stream
  $output = fopen('php://output', 'w');
  
  // Write CSV header
  fputcsv($output, array('Order ID', 'User ID', 'Customer Name', 'Email', 'Contact Number', 'Books Purchased', 'Total Payment (₹)', 'Date', 'Payment Status'));
  
  // Write data rows
  while ($fetch_orders = mysqli_fetch_assoc($select_orders)) {
    fputcsv($output, array(
      $fetch_orders['id'],
      $fetch_orders['user_id'],
      $fetch_orders['name'],
      $fetch_orders['email'],
      $fetch_orders['number'],
      $fetch_orders['total_products'],
      $fetch_orders['total_price'],
      $fetch_orders['placed_on'],
      $fetch_orders['payment_status']
    ));
  }
  
  fclose($output);
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders</title>
  <link rel="stylesheet" href="admin.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'admin_header.php'; ?>

<!-- Orders Section -->
<section class="admin_orders">
  <h1 class="title">Orders</h1>
  
  <div class="export_section">
    <form action="" method="post" style="margin-bottom: 1rem;">
      <input type="submit" name="export_csv" value="Export as CSV" class="export-btn">
    </form>
  </div>
  
  <div class="admin_box_container">
    <?php
    $select_orders = mysqli_query($conn, "SELECT * FROM `orders` WHERE payment_status = 'paid' OR payment_status = 'completed'") or die('query failed');

    if (mysqli_num_rows($select_orders) > 0) {
      while ($fetch_orders = mysqli_fetch_assoc($select_orders)) {
    ?>
    <div class="admin_box">
      <p>Order ID : <span><?php echo $fetch_orders['id'] ?></span></p>
      <p>User Id : <span><?php echo $fetch_orders['user_id'] ?></span></p>
      <p>Placed On : <span><?php echo $fetch_orders['placed_on'] ?></span></p>
      <p>Customer Name : <span><?php echo $fetch_orders['name'] ?></span></p>
      <p>Contact Number : <span><?php echo $fetch_orders['number'] ?></span></p>
      <p>Email : <span><?php echo $fetch_orders['email'] ?></span></p>
      
      <div class="books_purchased_section">
        <p><strong>Books Purchased :</strong></p>
        <div class="books_list">
          <?php
            $books = array_map('trim', explode(',', $fetch_orders['total_products']));
            foreach($books as $book) {
              echo '<span class="book_item">• ' . htmlspecialchars($book) . '</span><br>';
            }
          ?>
        </div>
      </div>
      
      <p class="total_amount">Total Payment : <span class="price_highlight">₹<?php echo number_format($fetch_orders['total_price'], 2); ?></span></p>
    </div>
    <?php
      }
    } else {
      echo '<p class="empty">No orders yet!</p>';
    }
    ?>
  </div>
</section>

<script src="admin_js.js"></script>
<script src="https://kit.fontawesome.com/eedbcd0c96.js" crossorigin="anonymous"></script>

</body>
</html>
