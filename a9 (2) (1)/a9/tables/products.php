<?php
/**
 * CPS510 A9 - Products CRUD Page (with Search)
 */
require_once '../config.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $sql = "INSERT INTO Product (ProductID, Name, Description, Price, StockQuantity) 
                VALUES (:pid, :pname, :pdesc, :pprice, :pstock)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':pid', $_POST['product_id']);
        oci_bind_by_name($stmt, ':pname', $_POST['name']);
        oci_bind_by_name($stmt, ':pdesc', $_POST['description']);
        oci_bind_by_name($stmt, ':pprice', $_POST['price']);
        oci_bind_by_name($stmt, ':pstock', $_POST['stock']);
        
        if (@oci_execute($stmt)) {
            oci_commit($conn);
            $success_message = "✓ Product added successfully!";
        } else {
            $error = oci_error($stmt);
            if ($error['code'] == 1) {
                $error_message = "Error: Product ID {$_POST['product_id']} already exists!";
            } elseif ($error['code'] == 1400) {
                $error_message = "Error: Required fields cannot be empty!";
            } elseif ($error['code'] == 2290) {
                $error_message = "Error: Price must be positive and stock cannot be negative!";
            } else {
                $error_message = "Error: " . $error['message'];
            }
        }
    }
    oci_close($conn);

    if (empty($error_message) && empty($success_message)) {
        header('Location: products.php');
        exit;
    }
}

$conn = getDBConnection();

/* --------------------------
   SEARCH FEATURE
--------------------------- */
$search = '';
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search = trim($_GET['search']);

    $sql = "SELECT * FROM Product
            WHERE LOWER(Name) LIKE LOWER(:s)
               OR LOWER(Description) LIKE LOWER(:s)
               OR CAST(ProductID AS VARCHAR2(50)) LIKE :s
            ORDER BY ProductID";

    $stmt = oci_parse($conn, $sql);
    $search_term = '%' . $search . '%';
    oci_bind_by_name($stmt, ':s', $search_term);
    oci_execute($stmt);
    $result = $stmt;
} else {
    $sql = "SELECT * FROM Product ORDER BY ProductID";
    $result = executeQuery($conn, $sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        h1 { color: #333; margin-bottom: 30px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
        .actions a { margin: 0 5px; color: #667eea; text-decoration: none; }
        .error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            color: #166534;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Manage Products</h1>

        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <h2>Add New Product</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Product ID:</label>
                <input type="number" name="product_id" required>
            </div>
            <div class="form-group">
                <label>Name:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Description:</label>
                <textarea name="description" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Price:</label>
                <input type="number" step="0.01" name="price" required>
            </div>
            <div class="form-group">
                <label>Stock Quantity:</label>
                <input type="number" name="stock" required>
            </div>
            <button type="submit">Add Product</button>
        </form>

        <!-- SEARCH BAR -->
        <form method="GET" style="margin-top: 30px; margin-bottom: 15px;">
            <input 
                type="text" 
                name="search" 
                placeholder="Search by name, description, or ID..."
                value="<?php echo htmlspecialchars($search); ?>"
                style="padding: 10px; width: 300px; border-radius: 8px; border: 1px solid #ccc;"
            >
            <button type="submit">Search</button>
            <a href="products.php" 
               style="margin-left: 10px; padding: 10px 20px; background: #6c757d; color: white; border-radius: 8px; text-decoration: none;">
               Clear
            </a>
        </form>

        <h2>All Products</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_array($result, OCI_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['PRODUCTID']); ?></td>
                    <td><?php echo htmlspecialchars($row['NAME']); ?></td>
                    <td><?php echo htmlspecialchars($row['DESCRIPTION']); ?></td>
                    <td>$<?php echo number_format($row['PRICE'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['STOCKQUANTITY']); ?></td>
                    <td class="actions">
                        <a href="edit_product.php?id=<?php echo $row['PRODUCTID']; ?>">Edit</a>
                        <a href="delete_product.php?id=<?php echo $row['PRODUCTID']; ?>" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="../index.php" class="back-btn">← Back to Menu</a>
    </div>
</body>
</html>
<?php oci_close($conn); ?>
