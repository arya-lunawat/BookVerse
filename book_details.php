<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];
if (!isset($user_id)) {
    header('location:login.php');
}

if (isset($_GET['id'])) {
    $book_id = $_GET['id'];
    $select = mysqli_query($conn, "SELECT * FROM products WHERE id='$book_id'") or die('query failed');
    if (mysqli_num_rows($select) > 0) {
        $book = mysqli_fetch_assoc($select);
    } else {
        header('location:home.php');
    }
} else {
    header('location:home.php');
}

if (isset($_POST['add_to_cart'])) {
    $pro_id = intval($_POST['product_id']);
    $pro_name = $_POST['product_name'];
    $pro_price = intval($_POST['product_price']);
    $pro_quantity = intval($_POST['product_quantity']);
    $pro_image = $_POST['product_image'];

    // Check if already in cart
    $check_stmt = $conn->prepare("SELECT id FROM `cart` WHERE product_id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $pro_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message[] = 'Already added to cart!';
    } else {
        // Insert into cart
        $insert_stmt = $conn->prepare("INSERT INTO `cart` (user_id, product_id, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("iisiis", $user_id, $pro_id, $pro_name, $pro_price, $pro_quantity, $pro_image);
        if ($insert_stmt->execute()) {
            $message[] = 'Book added to cart!';
        } else {
            $message[] = 'Failed to add to cart: ' . $insert_stmt->error;
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
  <title>Book Details</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="home.css">
  <style>
    .popup-container {
      max-width: 800px;
      margin: 3rem auto;
      background: rgba(88, 58, 1, 0.89);
      padding: 2rem;
      box-shadow: 0 0 20px rgba(0,0,0,0.2);
      border-radius: 12px;
      display: flex;
      flex-wrap: wrap;
      gap: 2rem;
      align-items: center;
      animation: fadeIn 0.5s ease;
    }

    .popup-image {
      flex: 1 1 40%;
    }

    .popup-image img {
      width: 100%;
      border-radius: 10px;
    }

    .popup-details {
      flex: 1 1 55%;
    }

    .popup-details h2 {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }

    .popup-details .author {
      color:rgb(243, 220, 159);
      margin-bottom: 1rem;
    }

    .stars {
      color: #f7b500;
      font-size: 1.4rem;
      letter-spacing: 1px;
      margin-bottom: 1rem;
    }

    .popup-details p.price {
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }

    .popup-details form {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .popup-details input[type="number"] {
      width: 60px;
      padding: 0.3rem;
    }

    .product_btn {
        background-color:rgb(172, 118, 2);
        color: #111;
        border: none;
        padding: 0.6rem 1rem;
        cursor: pointer;
        border-radius: 6px;
        transition: background 0.3s ease;
    }

    .product_btn:hover {
        background-color: #rgb(129, 88, 1);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<?php include 'user_header.php'; ?>

<div class="popup-container">
  <div class="popup-image">
      <img src="./uploaded_img/<?php echo $book['image']; ?>" alt="">
  </div>
  <div class="popup-details">
      <h2><?php echo $book['name']; ?></h2>
      <div class="author"><strong>Author:</strong> <?php echo $book['author_name']; ?></div>
      <div class="category"><strong>Category:</strong> <?php echo $book['category'] ?? 'N/A'; ?></div>
    <div class="stars" style="font-family: 'Poppins', sans-serif; font-size: 1.2rem;">
  <?php
    $rating = $book['ratings'];
    $rounded = round($rating);
    for ($i = 1; $i <= 5; $i++) {
        echo ($i <= $rounded) ? '★' : '☆';
    }
    echo " <span style='font-size: 1rem; color: white ;'>($rating)</span>";
  ?>
    </div>

      <p class="price">Rs. <?php echo $book['price']; ?>/-</p>
      <p style="margin-bottom: 1rem; color: #fff;"><strong>Description:</strong> <?php echo $book['description']; ?></p>
      <p style="margin-bottom: 1rem; color: #fff;"><strong>Pages:</strong> <?php echo $book['page_count'] ?? 'N/A'; ?> | <strong>Size:</strong> <?php echo $book['file_size'] ?? 'N/A'; ?> MB | <strong>ISBN:</strong> <?php echo $book['isbn'] ?? 'N/A'; ?></p>

      <?php
      $owned = mysqli_query($conn, "SELECT * FROM purchased_books WHERE user_id='$user_id' AND product_id='$book_id'") or die('query failed');
      $is_owned = mysqli_num_rows($owned) > 0;
      ?>

      <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="reader.php?sample=1&id=<?php echo $book['id']; ?>" class="product_btn">Read Sample</a>
        <?php if($is_owned): ?>
          <a href="reader.php?id=<?php echo $book['id']; ?>" class="product_btn">Read Now</a>
        <?php else: ?>
          <form action="create_checkout_session.php" method="post" style="display: inline-block; margin-right: 10px;">
              <input type="hidden" name="single_book" value="1">
              <input type="hidden" name="product_id" value="<?php echo $book['id']; ?>">
              <input type="text" name="name" required placeholder="Name" style="width: 80px; padding: 0.3rem; margin-right: 5px;">
              <input type="text" name="number" required placeholder="Phone" style="width: 80px; padding: 0.3rem; margin-right: 5px;">
              <input type="email" name="email" required placeholder="Email" style="width: 100px; padding: 0.3rem; margin-right: 5px;">
              <input type="submit" value="Buy Now" class="product_btn">
          </form>
          <form action="" method="post" style="display: inline;">
              <input type="hidden" name="product_id" value="<?php echo $book['id']; ?>">
              <input type="hidden" name="product_name" value="<?php echo $book['name']; ?>">
              <input type="hidden" name="product_price" value="<?php echo $book['price']; ?>">
              <input type="hidden" name="product_image" value="<?php echo $book['image']; ?>">
              <input type="hidden" name="product_quantity" value="1">
              <input type="submit" name="add_to_cart" value="Add to Cart" class="product_btn">
          </form>
        <?php endif; ?>
      </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<script>
    // Auto-remove message after 5 seconds
    setTimeout(() => {
        const message = document.querySelector('.message');
        if (message) {
            message.remove();
        }
    }, 5000);
</script>

</body>
</html>
