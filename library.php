<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
}

if (isset($_GET['return'])) {
    $book_id = $_GET['return'];
    mysqli_query($conn, "DELETE FROM purchased_books WHERE user_id='$user_id' AND product_id='$book_id'") or die('query failed');
    header('location:library.php');
}

$select_purchased = mysqli_query($conn, "SELECT p.*, pb.purchased_on, rp.current_page, rp.total_pages
                                        FROM purchased_books pb
                                        JOIN products p ON pb.product_id = p.id
                                        LEFT JOIN reading_progress rp ON pb.user_id = rp.user_id AND pb.product_id = rp.product_id
                                        WHERE pb.user_id = '$user_id'
                                        ORDER BY pb.purchased_on DESC") or die('query failed');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library - BookVerse</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
    <style>
        .library-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .library-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .library-header h1 {
            color: #fff;
            font-size: 2.5rem;
        }
        .library-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .library-book {
            background: rgba(88, 58, 1, 0.89);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        .library-book:hover {
            transform: translateY(-5px);
        }
        .library-book img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        .library-book h3 {
            color: #fff;
            margin-bottom: 0.5rem;
            font-size: 1.0rem;
        }
        .library-book p {
            color: #ccc;
            margin-bottom: 0.5rem;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #333;
            border-radius: 4px;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        .progress-fill {
            height: 100%;
            background: #4CAF50;
            transition: width 0.3s ease;
        }
        .continue-btn {
            background: rgb(172, 118, 2);
            color: #111;
            padding: 0.6rem 1rem;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
            transition: background 0.3s ease;
        }
        .continue-btn:hover {
            background: #rgb(129, 88, 1);
            color: #fff;
        }
        .return-btn {
            background: #dc3545;
            color: #fff;
            padding: 0.6rem 1rem;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
            margin-top: 0.5rem;
            transition: background 0.3s ease;
        }
        .return-btn:hover {
            background: #c82333;
        }
        .no-books {
            text-align: center;
            color: #fff;
            font-size: 1.2rem;
            padding: 2rem;
        }
    </style>
</head>
<body>

<?php include 'user_header.php'; ?>

<div class="library-container">
    <div class="library-header">
        <h1>My Library</h1>
        <p>Your purchased books and reading progress</p>
    </div>

    <?php
    if (mysqli_num_rows($select_purchased) > 0) {
    ?>
    <div class="library-grid">
        <?php
        while ($book = mysqli_fetch_assoc($select_purchased)) {
            $progress_percent = $book['total_pages'] > 0 ? round(($book['current_page'] / $book['total_pages']) * 100, 1) : 0;
        ?>
        <div class="library-book">
            <img src="./uploaded_img/<?php echo $book['image']; ?>" alt="<?php echo $book['name']; ?>">
            <h3><?php echo $book['name']; ?></h3>
            <p>by <?php echo $book['author_name']; ?></p>
            <p>Rs. <?php echo $book['price']; ?> | <?php echo $book['page_count']; ?> pages</p>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%;"></div>
            </div>
            <p>Progress: <?php echo $book['current_page']; ?> / <?php echo $book['total_pages']; ?> (<?php echo $progress_percent; ?>%)</p>
            <a href="reader.php?id=<?php echo $book['id']; ?>&page=<?php echo $book['current_page']; ?>" class="continue-btn">
                <i class="fas fa-book-open"></i> Continue Reading
            </a>
            <a href="library.php?return=<?php echo $book['id']; ?>" class="return-btn" onclick="return confirm('Are you sure you want to return this book?');">
                <i class="fas fa-undo"></i> Return Book
            </a>
        </div>
        <?php
        }
        ?>
    </div>
    <?php
    } else {
    ?>
    <div class="no-books">
        <p>You haven't purchased any books yet. <a href="shop.php" style="color: gold;">Browse our collection</a> to get started!</p>
    </div>
    <?php
    }
    ?>
</div>

<?php include 'footer.php'; ?>

<script src="https://kit.fontawesome.com/eedbcd0c96.js" crossorigin="anonymous"></script>
<script src="script.js"></script>

</body>
</html>
