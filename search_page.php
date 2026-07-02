<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
  header('location:login.php');
}

if (isset($_POST['add_to_cart'])) {

  $product_name = $_POST['product_name'];
  $product_price = $_POST['product_price'];
  $product_image = $_POST['product_image'];
  $product_quantity = $_POST['product_quantity'];

  $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

  if (mysqli_num_rows($check_cart_numbers) > 0) {
    $message[] = 'already added to cart!';
  } else {
    mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
    $message[] = 'product added to cart!';
  }
};

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Page</title>

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

.pro_meta {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.pro_meta p {
  position: static;
  background-color: transparent;
  margin: 0;
  padding: 0;
  color: white;
  font-size: 0.95rem;
}

.pro_meta p.price {
  background-color: rgb(210, 139, 16);
  color: black;
  padding: 0.2rem 0.8rem;
  border-radius: 5px;
}

.pro_meta p.author {
  text-align: right;
}

  </style>
</head>

<body>

  <?php
  include 'user_header.php';
  ?>

  <section class="search_cont">
    <form action="" method="get">
      <input type="text" name="search" placeholder="Search Products......" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
      <input type="submit" value="Search" class="product_btn">
    </form>
  </section>

  <section class="products_cont">
    <div class="pro_box_cont">
      <?php
      if (isset($_GET['search'])) {
        $search_item = trim(mysqli_real_escape_string($conn, $_GET['search']));

        if ($search_item === '') {
          echo '<p class="empty">Search Something!</p>';
        } else {
          $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE name LIKE '%{$search_item}%' OR category LIKE '%{$search_item}%' OR author_name LIKE '%{$search_item}%'") or die('query failed');

          if (mysqli_num_rows($select_products) > 0) {
            while ($fetch_products = mysqli_fetch_assoc($select_products)) {
      ?>
            <div class="pro_box">
              <img src="./uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
              <h3 class="book-title">
                <a href="book_details.php?id=<?php echo $fetch_products['id']; ?>">
                  <?php echo $fetch_products['name']; ?>
                </a>
              </h3>
              <div class="pro_meta">
                <p class="price">Rs. <?php echo $fetch_products['price']; ?>/-</p>
                <p class="author">by <?php echo $fetch_products['author_name']; ?></p>
              </div>
              <div class="stars">
                <?php
                $rating = $fetch_products['ratings'];
                for ($i = 1; $i <= 5; $i++) {
                  echo ($i <= round($rating)) ? '★' : '☆';
                }
                echo " ($rating)";
                ?>
              </div>
              <a href="reader.php?id=<?php echo $fetch_products['id']; ?>&sample=1" class="product_btn">Read Sample</a>
            </div>

      <?php
          }
        } else {
          echo '<p class="empty">No result found!</p>';
        }
      }
    } else {
      echo '<p class="empty">Search Something!</p>';
    }
      ?>
    </div>
  </section>

  <?php
  include 'footer.php';
  ?>

  <script src="script.js"></script>

</body>

</html>