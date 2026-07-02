<?php
include 'config.php';
session_start();

$admin_id=$_SESSION['admin_id'];

if(!isset($admin_id)){
  header('location:login.php');
};

if(isset($_POST['add_products_btn'])){
  $name=mysqli_real_escape_string($conn, $_POST['name']);
  $price=$_POST['price'];
  $image=mysqli_real_escape_string($conn, $_FILES['image']['name']);
  $image_size=$_FILES['image']['size'];
  $image_tmp_name=$_FILES['image']['tmp_name'];
  $image_folder="uploaded_img/".$image;
  $category = mysqli_real_escape_string($conn, $_POST['category']);
  $author_name = mysqli_real_escape_string($conn, $_POST['author_name']);
  $ratings = $_POST['ratings'];
  $description = mysqli_real_escape_string($conn, $_POST['description']);
  $pdf_file = mysqli_real_escape_string($conn, $_FILES['pdf_file']['name']);
  $pdf_tmp = $_FILES['pdf_file']['tmp_name'];
  $sample_pdf = mysqli_real_escape_string($conn, $_FILES['sample_pdf']['name']);
  $sample_tmp = $_FILES['sample_pdf']['tmp_name'];
  $page_count = $_POST['page_count'];
  $file_size = $_POST['file_size'];
  $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);



  $select_product_name = mysqli_query($conn, "SELECT name FROM `products` WHERE name='$name'") or die('query failed');

  if(mysqli_num_rows($select_product_name)>0){
    $message[]='The given product is already added';
  }else{
    $insert_stmt = $conn->prepare("INSERT INTO `products` (name, price, image, category, author_name, ratings, description, pdf_file, sample_pdf, page_count, file_size, isbn) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("sdssssssssss", $name, $price, $image, $category, $author_name, $ratings, $description, $pdf_file, $sample_pdf, $page_count, $file_size, $isbn);
    if($insert_stmt->execute()){
      if($image_size>2000000){
        $message[]='Image size is too large';
      }else{
        move_uploaded_file($image_tmp_name,$image_folder);
        move_uploaded_file($pdf_tmp, "uploaded_pdf/".$pdf_file);
        if(!empty($sample_pdf)){
          move_uploaded_file($sample_tmp, "uploaded_samples/".$sample_pdf);
        }
        $message[]="Product added successfully!";
      }
    }else{
      $message[]="Product failed to be added: " . $insert_stmt->error;
    }
    $insert_stmt->close();
  }
};

if(isset($_GET['delete'])){
  $delete_id=$_GET['delete'];

  $delete_img_query=mysqli_query($conn,"SELECT image, pdf_file, sample_pdf from `products` WHERE id='$delete_id'") or die('query failed');
  $fetch_del_img=mysqli_fetch_assoc($delete_img_query);
  unlink('./uploaded_img/'.$fetch_del_img['image']);
  if(!empty($fetch_del_img['pdf_file'])){
    unlink('./uploaded_pdf/'.$fetch_del_img['pdf_file']);
  }
  if(!empty($fetch_del_img['sample_pdf'])){
    unlink('./uploaded_samples/'.$fetch_del_img['sample_pdf']);
  }

  mysqli_query($conn, "DELETE FROM `products` WHERE id='$delete_id'") or die('query failed');
  header('location:admin_products.php');
}

if(isset($_POST['update_product'])){
  $update_p_id=$_POST['update_p_id'];
  $update_name=$_POST['update_name'];
  $update_price=$_POST['update_price'];
  $update_category=$_POST['update_category'];
  $update_author_name=$_POST['update_author_name'];
  $update_ratings=$_POST['update_ratings'];
  $update_description=$_POST['update_description'];
  $update_page_count=$_POST['update_page_count'];
  $update_file_size=$_POST['update_file_size'];
  $update_isbn=$_POST['update_isbn'];

  mysqli_query($conn,"UPDATE `products` SET name='$update_name', price='$update_price', category='$update_category', author_name='$update_author_name', ratings='$update_ratings', description='$update_description', page_count='$update_page_count', file_size='$update_file_size', isbn='$update_isbn' WHERE id='$update_p_id'") or die('query failed');

  $update_image=$_FILES['update_image']['name'];
  $update_image_tmp_name=$_FILES['update_image']['tmp_name'];
  $update_image_size=$_FILES['update_image']['size'];
  $update_folder='./uploaded_img/'.$update_image;
  $old_image=$_POST['update_old_img'];
  if(!empty($update_image)){
    if($update_image_size>2000000){
      $message[]='Image size is too large';
    }else{
      mysqli_query($conn,"UPDATE `products` SET image='$update_image' WHERE id='$update_p_id'") or die('query failed');

      move_uploaded_file($update_image_tmp_name,$update_folder);
      unlink('./uploaded_img/'.$old_image);

      $message[]="Product updated successfully!";
    }
  }

  $update_pdf_file=$_FILES['update_pdf_file']['name'];
  $update_pdf_tmp_name=$_FILES['update_pdf_file']['tmp_name'];
  $update_pdf_folder='./uploaded_pdf/'.$update_pdf_file;
  $old_pdf=$_POST['update_old_pdf'];
  if(!empty($update_pdf_file)){
    mysqli_query($conn,"UPDATE `products` SET pdf_file='$update_pdf_file' WHERE id='$update_p_id'") or die('query failed');
    move_uploaded_file($update_pdf_tmp_name,$update_pdf_folder);
    if(!empty($old_pdf)){
      unlink('./uploaded_pdf/'.$old_pdf);
    }
  }

  $update_sample_pdf=$_FILES['update_sample_pdf']['name'];
  $update_sample_tmp_name=$_FILES['update_sample_pdf']['tmp_name'];
  $update_sample_folder='./uploaded_samples/'.$update_sample_pdf;
  $old_sample=$_POST['update_old_sample'];
  if(!empty($update_sample_pdf)){
    mysqli_query($conn,"UPDATE `products` SET sample_pdf='$update_sample_pdf' WHERE id='$update_p_id'") or die('query failed');
    move_uploaded_file($update_sample_tmp_name,$update_sample_folder);
    if(!empty($old_sample)){
      unlink('./uploaded_samples/'.$old_sample);
    }
  }

  header('location:admin_products.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products</title>
  <link rel="stylesheet" href="admin.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
include 'admin_header.php';
?>

<section class="admin_add_products">
  <form action="" method="post" enctype="multipart/form-data">
    <h3>Add Product</h3>
    <input type="text" name="name" class="admin_input" placeholder="Enter Product Name" required>

    <input type="number" min="0" name="price" class="admin_input" placeholder="Enter Product Price" required>

    <p style="color: rgb(241, 170, 16);">Choose Cover Image:</p>
    <input type="file" name="image" class="admin_input" accept="image/jpg, image/jpeg, image/png" required style="color: grey;">
    <input type="text" name="category" class="admin_input" placeholder="Enter Category" required>
    <input type="text" name="author_name" class="admin_input" placeholder="Enter Author Name" required>
    <input type="number" step="0.1" name="ratings" class="admin_input" placeholder="Enter Ratings (e.g. 4.5)" required>
    <textarea name="description" class="admin_input" placeholder="Enter Description" required></textarea>
    <p style="color: rgb(241, 170, 16);">Choose Book PDF:</p>
    <input type="file" name="pdf_file" class="admin_input" accept="application/pdf" required style="color: grey;">
    <p style="color: rgb(241, 170, 16);">Choose Sample PDF:</p>
    <input type="file" name="sample_pdf" class="admin_input" accept="application/pdf" style="color: grey;">
    <input type="number" name="page_count" class="admin_input" placeholder="Enter Page Count" required>
    <input type="number" name="file_size" class="admin_input" placeholder="Enter File Size (MB)" required>
    <input type="text" name="isbn" class="admin_input" placeholder="Enter ISBN">

    <input type="submit" name="add_products_btn" class="admin_input" value="Add Product">
  </form>

</section>

<section class="show_products">
  <div class="product_box_cont">
    <?php
      $select_products=mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');

      if(mysqli_num_rows($select_products)>0){
        while($fetch_products=mysqli_fetch_assoc($select_products)){

    ?>

    <div class="product_box">
      <img src="./uploaded_img/<?php echo $fetch_products['image'];?>" alt="">

      <div class="product_name">
      <?php echo $fetch_products['name'];?>
      </div>

      <div class="product_price">Rs. 
      <?php echo $fetch_products['price'];?> /-
      </div>

      <a href="admin_products.php?update=<?php echo $fetch_products['id']?>" class="product_btn">Update</a>

      <a href="admin_products.php?delete=<?php echo $fetch_products['id']?>" class="product_btn product_del_btn" onclick= "return confirm('Are you sure you want to delete this product ?');">Delete</a>
    </div>
    <?php
      }
    }else{
      echo '<p class="empty">No Product added yet!</p>';
    }
    ?>
  </div>
</section>

<section class="edit_product_form">
  <?php
    if(isset($_GET['update'])){
      $update_id=$_GET['update'];
      $update_query=mysqli_query($conn,"SELECT * FROM `products` WHERE id='$update_id'") or die('query failed');
      if(mysqli_num_rows($update_query)>0){
        while($fetch_update=mysqli_fetch_assoc($update_query)){

  ?>

  <form action="" method="post" enctype="multipart/form-data">
    <input type="hidden" name="update_p_id" value="<?php echo $fetch_update['id'];?>">

    <input type="hidden" name="update_old_img" value="<?php echo $fetch_update['image'];?>">
    <input type="hidden" name="update_old_pdf" value="<?php echo $fetch_update['pdf_file'];?>">
    <input type="hidden" name="update_old_sample" value="<?php echo $fetch_update['sample_pdf'];?>">

    <img src="./uploaded_img/<?php echo $fetch_update['image'];?>" alt="">


    <input type="text" name="update_name" value="<?php echo $fetch_update['name'];?>" class="admin_input update_box" required placeholder="Enter Product Name">

    <input type="number" name="update_price" min="0" value="<?php echo $fetch_update['price'];?>" class="admin_input update_box" required placeholder="Enter Product Price">

    <p style="color: rgb(241, 170, 16);">Choose Cover Image:</p>
    <input type="file" name="update_image" class="admin_input update_box" accept="image/jpg, image/jpeg, image/png" style="color: grey;">

    <input type="text" name="update_category" value="<?php echo $fetch_update['category'];?>" class="admin_input update_box" required placeholder="Enter Category">

    <input type="text" name="update_author_name" value="<?php echo $fetch_update['author_name'];?>" class="admin_input update_box" required placeholder="Enter Author Name">

    <input type="number" step="0.1" name="update_ratings" value="<?php echo $fetch_update['ratings'];?>" class="admin_input update_box" required placeholder="Enter Ratings">

    <textarea name="update_description" class="admin_input update_box" required placeholder="Enter Description"><?php echo $fetch_update['description'];?></textarea>

    <p style="color: rgb(241, 170, 16);">Choose Book PDF:</p>
    <input type="file" name="update_pdf_file" class="admin_input update_box" accept="application/pdf" style="color: grey;">

    <p style="color: rgb(241, 170, 16);">Choose Sample PDF:</p>
    <input type="file" name="update_sample_pdf" class="admin_input update_box" accept="application/pdf" style="color: grey;">

    <input type="number" name="update_page_count" value="<?php echo $fetch_update['page_count'];?>" class="admin_input update_box" required placeholder="Enter Page Count">

    <input type="number" name="update_file_size" value="<?php echo $fetch_update['file_size'];?>" class="admin_input update_box" required placeholder="Enter File Size (MB)">

    <input type="text" name="update_isbn" value="<?php echo $fetch_update['isbn'];?>" class="admin_input update_box" placeholder="Enter ISBN">

    <input type="submit" value="update" name="update_product" class="product_btn">
    <input type="reset" value="cancel" id="close_update" class="product_btn product_del_btn">
    
  </form>

  <?php
      }
    }
  }else{
    echo "<script>
    document.querySelector('.edit_product_form').style.display='none';
    </script>";
  }
  ?>

</section>

<script src="admin_js.js"></script>
<script src="https://kit.fontawesome.com/eedbcd0c96.js" crossorigin="anonymous"></script>

</body>
</html>