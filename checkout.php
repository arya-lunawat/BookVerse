<?php
include 'config.php';
session_start();

$user_id=$_SESSION['user_id'];

if(!isset($user_id)){
  header('location:login.php');
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout Page</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="home.css">

</head>
<body>
  
<?php
include 'user_header.php';
?>

<section class="display_order">
  <h2>Ordered Products</h2>
  <?php
    $grand_total=0;
    $select_tbr=mysqli_query($conn,"SELECT * FROM `tbr_list` WHERE user_id='$user_id'") or die('query failed');

    if(mysqli_num_rows($select_tbr)>0){
      while($fetch_tbr=mysqli_fetch_assoc($select_tbr)){
        $total_price=$fetch_tbr['price'];
        $grand_total+=$total_price;
      
  ?>
  <div class="single_order_product">
    <img src="./uploaded_img/<?php echo$fetch_tbr['image'];?>" alt="">
    <div class="single_des">
    <h3><?php echo $fetch_tbr['name'];?></h3>
    <p>Rs. <?php echo $fetch_tbr['price'];?></p>
    </div>

  </div>
  

  <?php
  }
}else{
  echo '<p class="empty">your TBR list is empty</p>';
}
  ?>
  <div class="checkout_grand_total"> GRAND TOTAL : <span>$<?php echo $grand_total; ?>/-</span> </div>
</section>



<section class="contact_us">
  <h2>Proceed to Payment</h2>
  <p>Your details will be used from your account.</p>
  <form action="create_checkout_session.php" method="post">
    <input type="hidden" name="cart_checkout" value="1">
    <input type="submit" value="Pay Now" class="product_btn">
  </form>
</section>
<?php
include 'footer.php';
?>
<script src="https://kit.fontawesome.com/eedbcd0c96.js" crossorigin="anonymous"></script>

<script src="script.js"></script>

</body>
</html>