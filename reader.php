<?php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit;
}

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sample = isset($_GET['sample']) ? 1 : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$current_page = $page; // Default from GET or 1

if ($book_id <= 0) {
    die('Invalid book ID');
}

// Verify access
if ($sample) {
    $select = mysqli_query($conn, "SELECT name, sample_pdf, page_count FROM products WHERE id='$book_id'") or die('query failed');
    if (mysqli_num_rows($select) == 0) {
        die('Sample not available');
    }
    $book = mysqli_fetch_assoc($select);
    $pdf_url = "pdf_access.php?id=$book_id&sample=1";
    $total_pages = $book['page_count'] ?? 10; // Default for sample
    $is_sample = true;
    $current_page = $page; // For samples, use GET page
} else {
    $owned = mysqli_query($conn, "SELECT p.name, p.pdf_file, p.page_count FROM purchased_books pb JOIN products p ON pb.product_id = p.id WHERE pb.user_id='$user_id' AND pb.product_id='$book_id'") or die('query failed');
    if (mysqli_num_rows($owned) == 0) {
        die('Access denied. You must purchase this book to read it.');
    }
    $book = mysqli_fetch_assoc($owned);
    $pdf_url = "pdf_access.php?id=$book_id";
    $total_pages = $book['page_count'];

    // Get current progress
    $progress_query = mysqli_query($conn, "SELECT current_page FROM reading_progress WHERE user_id='$user_id' AND product_id='$book_id'") or die('query failed');
    $current_page = (int)(mysqli_fetch_assoc($progress_query)['current_page'] ?? 1);
    if ($page > 1) $current_page = $page;
    $is_sample = false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_progress' && !$is_sample) {
    $new_page = (int)$_POST['page'];
    $update_query = mysqli_query($conn, "INSERT INTO reading_progress (user_id, product_id, current_page, total_pages) VALUES ('$user_id', '$book_id', '$new_page', '$total_pages') 
                                         ON DUPLICATE KEY UPDATE current_page = '$new_page', last_read = CURRENT_TIMESTAMP") or die('query failed');
    echo json_encode(['success' => true]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read Book - BookVerse</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
    <style>
        body { background: url("https://images2.alphacoders.com/261/26102.jpg") no-repeat center center fixed; color:white; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        .reader-container { max-width: 1000px; margin: 2rem auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden; }
        .reader-header { background: rgba(88, 58, 1, 0.89); color: #fff; padding: 1rem; text-align: center; }
        .reader-header h1 { margin: 0; font-size: 1.8rem; }
        .reader-toolbar { background: #333; color: #fff; padding: 0.5rem; display: flex; justify-content: space-between; align-items: center; }
        .toolbar-btn { background: #555; color: #fff; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; margin: 0 0.2rem; }
        .toolbar-btn:hover { background: #666; }
        .page-info { font-size: 0.9rem; }
        #pdf-viewer { width: 100%; height: 80vh; border: none; }
        .back-btn { background: rgb(172, 118, 2); color: #111; padding: 0.6rem 1rem; text-decoration: none; border-radius: 6px; display: inline-block; margin: 1rem; }
        .back-btn:hover { background: #rgb(129, 88, 1); color: #fff; }
        @media (max-width: 768px) { .reader-toolbar { flex-direction: column; gap: 0.5rem; } #pdf-viewer { height: 60vh; } }
    </style>
    <script>
        var currentPage = <?php echo json_encode($current_page); ?>;
        var totalPages = <?php echo json_encode($total_pages); ?>;
        var isSample = <?php echo json_encode($is_sample); ?>;
        var bookId = <?php echo json_encode($book_id); ?>;
        var pdfUrl = <?php echo json_encode($pdf_url); ?>;
    </script>
</head>
<body>

<div class="reader-container">
    <div class="reader-header">
        <h1>Reading: <?php echo htmlspecialchars($book['name']); ?></h1>
        <?php if ($is_sample): ?>
            <p>Sample Preview</p>
        <?php endif; ?>
    </div>
    <div class="reader-toolbar">
        <div>
            <button class="toolbar-btn" id="goto-page">Bookmark Page</button>
            <?php if (!$is_sample): ?>
                <button class="toolbar-btn" id="mark-read">Mark as Read</button>
            <?php endif; ?>
            <?php if ($is_sample): ?>
                <a href="cart.php?add_to_cart=1&id=<?php echo $book_id; ?>" class="toolbar-btn">Add to Cart</a>
            <?php endif; ?>
        </div>
        <div class="page-info">
            Page: <span id="current-page"><?php echo $current_page; ?></span> / <span id="total-pages"><?php echo $total_pages; ?></span>
        </div>
    </div>
    <iframe id="pdf-viewer" src="<?php echo $pdf_url . '#page=' . $current_page; ?>" width="100%" height="600px"></iframe>
</div>

<a href="<?php echo $is_sample ? 'book_details.php?id=' . $book_id : 'library.php'; ?>" class="back-btn">
    <i class="fas fa-arrow-left"></i> Back to <?php echo $is_sample ? 'Details' : 'Library'; ?>
</a>



<?php include 'footer.php'; ?>

<script>
function updatePage(newPage) {
    if (newPage < 1 || newPage > totalPages) return false;
    currentPage = newPage;
    document.getElementById('current-page').textContent = currentPage;
    document.getElementById('pdf-viewer').src = pdfUrl + '#page=' + currentPage;
    if (!isSample) {
        saveProgress(currentPage);
    }
    return true;
}

function saveProgress(page) {
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=save_progress&page=' + page
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Failed to save progress.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving progress.');
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Go to Page button
    document.getElementById('goto-page').addEventListener('click', function() {
        var page = prompt("Enter page number (1 to " + totalPages + "):");
        if (page !== null) {
            page = parseInt(page);
            if (!isNaN(page) && page >= 1 && page <= totalPages) {
                updatePage(page);
            } else {
                alert("Invalid page number.");
            }
        }
    });

    // Mark as Read
    document.getElementById('mark-read').addEventListener('click', function() {
        updatePage(totalPages);
        alert('Progress saved! Book marked as read.');
    });
});
</script>

</body>
</html>
