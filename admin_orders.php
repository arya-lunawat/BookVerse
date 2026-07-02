<?php
include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
  header('location:login.php');
}

if (isset($_POST['update_order'])) {
  $order_update_id = $_POST['order_id'];
  $update_payment = $_POST['update_payment'];

  mysqli_query($conn, "UPDATE `orders` SET payment_status = '$update_payment' WHERE id = '$order_update_id'") or die('query failed');

  $message[] = 'Order payment status has been updated';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders</title>
  <link rel="stylesheet" href="admin1.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'admin_header.php'; ?>

<!-- Pending Orders Section -->
<section class="admin_orders">
  <h1 class="title">Pending Orders</h1>
  <div class="admin_box_container">
    <?php
    $select_orders = mysqli_query($conn, "SELECT * FROM `orders` WHERE payment_status = 'pending'") or die('query failed');

    if (mysqli_num_rows($select_orders) > 0) {
      while ($fetch_orders = mysqli_fetch_assoc($select_orders)) {
    ?>
    <div class="admin_box">
      <p>User Id : <span><?php echo $fetch_orders['user_id'] ?></span></p>
      <p>Placed On : <span><?php echo $fetch_orders['placed_on'] ?></span></p>
      <p>Name : <span><?php echo $fetch_orders['name'] ?></span></p>
      <p>Number : <span><?php echo $fetch_orders['number'] ?></span></p>
      <p>Email : <span><?php echo $fetch_orders['email'] ?></span></p>
      <p>Books Purchased : <span><?php echo $fetch_orders['total_products'] ?></span></p>
      <p>Total Price : <span><?php echo $fetch_orders['total_price'] ?></span></p>

      <form action="" method="post">
        <input type="hidden" name="order_id" value="<?php echo $fetch_orders['id']; ?>">
        <select name="update_payment" required>
          <option value="" disabled selected><?php echo $fetch_orders['payment_status']; ?></option>
          <option value="pending">pending</option>
          <option value="completed">completed</option>
        </select>
        <input type="submit" value="Update" name="update_order" class="option-btn">
      </form>
    </div>
    <?php
      }
    } else {
      echo '<p class="empty">No pending orders yet!</p>';
    }
    ?>
  </div>
</section>

<!-- Completed Orders Section -->
<section class="admin_orders">
  <h1 class="title">Completed Orders</h1>
  <div class="admin_box_container">
    <?php
    $select_completed_orders = mysqli_query($conn, "SELECT * FROM `orders` WHERE payment_status = 'completed'") or die('query failed');

    if (mysqli_num_rows($select_completed_orders) > 0) {
      while ($fetch_orders = mysqli_fetch_assoc($select_completed_orders)) {
    ?>
    <div class="admin_box">
      <p>User Id : <span><?php echo $fetch_orders['user_id'] ?></span></p>
      <p>Placed On : <span><?php echo $fetch_orders['placed_on'] ?></span></p>
      <p>Name : <span><?php echo $fetch_orders['name'] ?></span></p>
      <p>Number : <span><?php echo $fetch_orders['number'] ?></span></p>
      <p>Email : <span><?php echo $fetch_orders['email'] ?></span></p>
      <p>Books Purchased : <span><?php echo $fetch_orders['total_products'] ?></span></p>
      <p>Total Price : <span><?php echo $fetch_orders['total_price'] ?></span></p>
      <p>Payment Status : <span><?php echo $fetch_orders['payment_status'] ?></span></p>
    </div>
    <?php
      }
    } else {
      echo '<p class="empty">No completed orders yet!</p>';
    }
    ?>
  </div>
</section>

<script src="admin_js.js"></script>
<script src="https://kit.fontawesome.com/eedbcd0c96.js" crossorigin="anonymous"></script>

</body>
</html>
