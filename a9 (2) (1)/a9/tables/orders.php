<?php
/**
 * CPS510 A9 - Orders CRUD Page
 */
require_once '../config.php';

$error_message = '';
$success_message = '';

// Handle search
$searchTerm = '';
$whereClause = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    $whereClause = " WHERE CAST(o.OrderID AS VARCHAR2(20)) LIKE :search
                     OR UPPER(u.FirstName) LIKE UPPER(:search)
                     OR UPPER(u.LastName) LIKE UPPER(:search)";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $sql = "INSERT INTO Orders (OrderID, UserID, OrderDate, Status) 
                VALUES (:oid, :ouid, TO_DATE(:odate, 'YYYY-MM-DD'), :ostatus)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':oid', $_POST['order_id']);
        oci_bind_by_name($stmt, ':ouid', $_POST['user_id']);
        oci_bind_by_name($stmt, ':odate', $_POST['order_date']);
        oci_bind_by_name($stmt, ':ostatus', $_POST['status']);
        
        if (@oci_execute($stmt)) {
            oci_commit($conn);
            $success_message = "✓ Order added successfully!";
        } else {
            $error = oci_error($stmt);
            if ($error['code'] == 1) {
                $error_message = "Error: Order ID {$_POST['order_id']} already exists!";
            } elseif ($error['code'] == 2291) {
                $error_message = "Error: User ID {$_POST['user_id']} does not exist!";
            } elseif ($error['code'] == 2290) {
                $error_message = "Error: Status must be 'Pending', 'Completed', or 'Cancelled'!";
            } else {
                $error_message = "Error: " . $error['message'];
            }
        }
    }
    oci_close($conn);
    if (empty($error_message) && empty($success_message)) {
        header('Location: orders.php');
        exit;
    }
}

$conn = getDBConnection();
$sql = "SELECT o.*, u.FirstName, u.LastName 
        FROM Orders o 
        JOIN Users u ON o.UserID = u.UserID
        $whereClause
        ORDER BY o.OrderID DESC";

if (!empty($searchTerm)) {
    $stmt = oci_parse($conn, $sql);
    $search_param = '%' . $searchTerm . '%';
    oci_bind_by_name($stmt, ':search', $search_param);
    oci_execute($stmt);
    $result = $stmt;
} else {
    $result = executeQuery($conn, $sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 40px; }
        h1 { color: #333; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .back-btn { display: inline-block; margin-top: 20px; padding: 12px 30px; background: #6c757d; color: white; text-decoration: none; border-radius: 8px; }
        .actions a { margin: 0 5px; color: #667eea; text-decoration: none; }
        .status-pending { color: #f59e0b; font-weight: 600; }
        .status-completed { color: #22c55e; font-weight: 600; }
        .status-cancelled { color: #ef4444; font-weight: 600; }
        .error { background: #fef2f2; border-left: 4px solid #ef4444; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .success { background: #f0fdf4; border-left: 4px solid #22c55e; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .search-box { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-box input { flex: 1; padding: 10px; border-radius: 5px; border: 1px solid #ddd; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-secondary { background: #6c757d; color: white; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
<div class="container">
    <h1>🛍️ Manage Orders</h1>
    
    <?php if ($error_message): ?>
        <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    
    <h2>Create New Order</h2>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label>Order ID:</label>
            <input type="number" name="order_id" required>
        </div>
        <div class="form-group">
            <label>User ID:</label>
            <input type="number" name="user_id" required>
        </div>
        <div class="form-group">
            <label>Order Date:</label>
            <input type="date" name="order_date" required>
        </div>
        <div class="form-group">
            <label>Status:</label>
            <select name="status" required>
                <option value="Pending">Pending</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>
        <button type="submit">Create Order</button>
    </form>

    <h2 style="margin-top:40px;">Search & View Orders</h2>
    <form method="GET" action="" class="search-box">
        <input type="text" name="search" placeholder="Search by Order ID or Customer name..." value="<?php echo htmlspecialchars($searchTerm); ?>">
        <button type="submit" class="btn-primary">🔍 Search</button>
        <?php if ($searchTerm): ?>
            <a href="orders.php" class="btn-secondary">Clear</a>
        <?php endif; ?>
    </form>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $hasData = false;
        while ($row = oci_fetch_array($result, OCI_ASSOC)):
            $hasData = true;
        ?>
            <tr>
                <td><?php echo htmlspecialchars($row['ORDERID']); ?></td>
                <td><?php echo htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['LASTNAME']); ?></td>
                <td><?php echo htmlspecialchars(substr($row['ORDERDATE'],0,10)); ?></td>
                <td class="status-<?php echo strtolower($row['STATUS']); ?>"><?php echo htmlspecialchars($row['STATUS']); ?></td>
                <td class="actions">
                    <a href="edit_order.php?id=<?php echo $row['ORDERID']; ?>">Edit</a>
                    <a href="delete_order.php?id=<?php echo $row['ORDERID']; ?>" onclick="return confirm('Delete?')">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        <?php if (!$hasData): ?>
            <tr><td colspan="5" style="text-align:center; padding:20px;">No orders found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <a href="../index.php" class="back-btn">← Back to Menu</a>
</div>
</body>
</html>
<?php oci_close($conn); ?>
