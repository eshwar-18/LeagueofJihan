<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$conn = getDBConnection();
$product_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE Product SET Name = :name, Description = :desc, Price = :price, StockQuantity = :stock WHERE ProductID = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':name', $_POST['name']);
    oci_bind_by_name($stmt, ':desc', $_POST['description']);
    oci_bind_by_name($stmt, ':price', $_POST['price']);
    oci_bind_by_name($stmt, ':stock', $_POST['stock']);
    oci_bind_by_name($stmt, ':id', $product_id);
    oci_execute($stmt);
    oci_commit($conn);
    oci_close($conn);
    header('Location: products.php');
    exit;
}

$sql = "SELECT * FROM Product WHERE ProductID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $product_id);
oci_execute($stmt);
$product = oci_fetch_array($stmt, OCI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 40px; }
        h1 { color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; margin-right: 10px; }
        .back-btn { background: #6c757d; display: inline-block; padding: 12px 30px; color: white; text-decoration: none; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Product</h1>
        <form method="POST">
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['NAME']); ?>" required>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" rows="3"><?php echo htmlspecialchars($product['DESCRIPTION']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Price:</label>
                <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['PRICE']); ?>" required>
            </div>
            <div class="form-group">
                <label>Stock Quantity:</label>
                <input type="number" name="stock" value="<?php echo htmlspecialchars($product['STOCKQUANTITY']); ?>" required>
            </div>
            <button type="submit">Update Product</button>
            <a href="products.php" class="back-btn">Cancel</a>
        </form>
    </div>
</body>
</html>
<?php oci_close($conn); ?>
