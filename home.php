<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
  header('location:login.php');
}

if (isset($_POST['add_to_cart'])) {
  $pro_id = $_POST['product_id'];
  $pro_name = $_POST['product_name'];
  $pro_price = $_POST['product_price'];
  $pro_image = $_POST['product_image'];

  // Check if already in TBR
  $check_stmt = $conn->prepare("SELECT id FROM `tbr_list` WHERE product_id = ? AND user_id = ?");
  $check_stmt->bind_param("ii", $pro_id, $user_id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();

  if ($check_result->num_rows > 0) {
    $message[] = 'Already added to TBR list!';
  } else {
    // Insert into TBR
    $insert_stmt = $conn->prepare("INSERT INTO `tbr_list` (user_id, product_id, name, price, image) VALUES (?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("iisss", $user_id, $pro_id, $pro_name, $pro_price, $pro_image);
    if ($insert_stmt->execute()) {
      $message[] = 'Book added to TBR list!';
    } else {
      $message[] = 'Failed to add to TBR list: ' . $insert_stmt->error;
    }
    $insert_stmt->close();
  }
  $check_stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Page</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="home.css">
  <style>
    .book-title a {
  text-decoration: none;
  color: rgb(241, 170, 16);
  transition: transform 0.3s ease, color 0.3s ease;
  display: inline-block;
}

.book-title a:hover {
  transform: scale(1.1);
  color:rgb(252, 216, 158);
}

  </style>

</head>

<body>

  <?php
  include 'user_header.php';
  ?>

  <section class="home_cont">
    <div class="main_descrip">
      <h1>BookVerse</h1>
      <p>Find, Discover, and Buy Your Favorite Books</p>
      <button onclick="window.location.href='shop.php';">Discover More</button>

    </div>
  </section>

  <section class="products_cont">
    <div class="pro_box_cont">
      <?php
      $select_products = mysqli_query($conn, "SELECT * FROM `products` LIMIT 6") or die('query failed');

      if (mysqli_num_rows($select_products) > 0) {
        while ($fetch_products = mysqli_fetch_assoc($select_products)) {

      ?>
          <form action="" method="post" class="pro_box">
            <img src="./uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
            <h3 class="book-title">
              <a href="book_details.php?id=<?php echo $fetch_products['id']; ?>">
                <?php echo $fetch_products['name']; ?>
              </a>
            </h3>
            <p>Rs. <?php echo $fetch_products['price']; ?>/-</p>
          
            <input type="hidden" name="product_id" value="<?php echo $fetch_products['id']; ?>">
            <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
            <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
            <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">

            <input type="submit" value="Add to Cart" name="add_to_cart" class="product_btn">

          </form>

      <?php
        }
      } else {
        echo '<p class="empty">No Products Added Yet !</p>';
      }
      ?>
    </div>
  </section>

  <section class="about_cont">
    <img src="about.jpeg" alt="">
    <div class="about_descript">
      <h2>Discover Our Story</h2>
      <p>At BookVerse, we are passionate about connecting readers with captivating stories, inspiring ideas, and a world of knowledge. Our bookstore is more than just a place to buy books; it's a haven for book enthusiasts, where the love for literature thrives.
    </p>
    <button class="product_btn" onclick="window.location.href='about.php';">Read More</button>
    </div>
  </section>

  <section class="questions_cont">
    <div class="questions">
    <h2>Have Any Queries?</h2>
    <p>At BookVerse, we value your satisfaction and strive to provide exceptional customer service. If you have any questions, concerns, or inquiries, our dedicated team is here to assist you every step of the way.</p>
    <button class="product_btn" onclick="window.location.href='contact.php';">Contact Us</button>
    </div>
    
  </section>
  <?php
  include 'footer.php';
  ?>
  <script src="https://kit.fontawesome.com/eedbcd0c96.js" crossorigin="anonymous"></script>

  <script src="script.js"></script>

</body>

</html>
