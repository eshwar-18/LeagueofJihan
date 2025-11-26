<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: reviews.php');
    exit;
}

$conn = getDBConnection();
$review_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE Review SET UserID = :uid, ProductID = :pid, Rating = :rating, ReviewComment = :comment, ReviewDate = TO_DATE(:rdate, 'YYYY-MM-DD') WHERE ReviewID = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':uid', $_POST['user_id']);
    oci_bind_by_name($stmt, ':pid', $_POST['product_id']);
    oci_bind_by_name($stmt, ':rating', $_POST['rating']);
    oci_bind_by_name($stmt, ':comment', $_POST['comment']);
    oci_bind_by_name($stmt, ':rdate', $_POST['review_date']);
    oci_bind_by_name($stmt, ':id', $review_id);
    oci_execute($stmt);
    oci_commit($conn);
    oci_close($conn);
    header('Location: reviews.php');
    exit;
}

$sql = "SELECT * FROM Review WHERE ReviewID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $review_id);
oci_execute($stmt);
$review = oci_fetch_array($stmt, OCI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Review</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 40px; }
        h1 { color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; margin-right: 10px; }
        .back-btn { background: #6c757d; display: inline-block; padding: 12px 30px; color: white; text-decoration: none; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Review</h1>
        <form method="POST">
            <div class="form-group">
                <label>User ID:</label>
                <input type="number" name="user_id" value="<?php echo htmlspecialchars($review['USERID']); ?>" required>
            </div>
            <div class="form-group">
                <label>Product ID:</label>
                <input type="number" name="product_id" value="<?php echo htmlspecialchars($review['PRODUCTID']); ?>" required>
            </div>
            <div class="form-group">
                <label>Rating (1-5):</label>
                <select name="rating" required>
                    <?php for($i = 5; $i >= 1; $i--): ?>
                        <option value="<?php echo $i; ?>" <?php echo $review['RATING'] == $i ? 'selected' : ''; ?>><?php echo $i; ?> Stars</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Review Comment:</label>
                <textarea name="comment" rows="3" required><?php echo htmlspecialchars($review['REVIEWCOMMENT']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Review Date:</label>
                <input type="date" name="review_date" value="<?php echo htmlspecialchars(substr($review['REVIEWDATE'], 0, 10)); ?>" required>
            </div>
            <button type="submit">Update Review</button>
            <a href="reviews.php" class="back-btn">Cancel</a>
        </form>
    </div>
</body>
</html>
<?php oci_close($conn); ?>
