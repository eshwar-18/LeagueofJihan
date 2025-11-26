<?php
/**
 * CPS510 A9 - Payments CRUD Page
 */
require_once '../config.php';

$error_message = '';
$success_message = '';

// Handle search
$searchTerm = '';
$whereClause = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    $whereClause = " WHERE CAST(p.PaymentID AS VARCHAR2(20)) LIKE :search
                     OR CAST(o.OrderID AS VARCHAR2(20)) LIKE :search
                     OR UPPER(u.FirstName) LIKE UPPER(:search)
                     OR UPPER(u.LastName) LIKE UPPER(:search)";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $sql = "INSERT INTO Payment (PaymentID, OrderID, Amount, PaymentDate, PaymentMethod) 
                VALUES (:payid, :payoid, :payamt, TO_DATE(:paydate, 'YYYY-MM-DD'), :paymethod)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':payid', $_POST['payment_id']);
        oci_bind_by_name($stmt, ':payoid', $_POST['order_id']);
        oci_bind_by_name($stmt, ':payamt', $_POST['amount']);
        oci_bind_by_name($stmt, ':paydate', $_POST['payment_date']);
        oci_bind_by_name($stmt, ':paymethod', $_POST['payment_method']);
        
        if (@oci_execute($stmt)) {
            oci_commit($conn);
            $success_message = "✓ Payment added successfully!";
        } else {
            $error = oci_error($stmt);
            if ($error['code'] == 1) {
                $error_message = "Error: Payment ID {$_POST['payment_id']} already exists!";
            } elseif ($error['code'] == 2291) {
                $error_message = "Error: Order ID {$_POST['order_id']} does not exist!";
            } elseif ($error['code'] == 2290) {
                $error_message = "Error: Amount must be positive OR invalid payment method!";
            } else {
                $error_message = "Error: " . $error['message'];
            }
        }
    }
    oci_close($conn);
    if (empty($error_message) && empty($success_message)) {
        header('Location: payments.php');
        exit;
    }
}

$conn = getDBConnection();
$sql = "SELECT p.*, o.OrderID, u.FirstName, u.LastName 
        FROM Payment p 
        JOIN Orders o ON p.OrderID = o.OrderID 
        JOIN Users u ON o.UserID = u.UserID
        $whereClause
        ORDER BY p.PaymentID DESC";

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
    <title>Manage Payments</title>
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
        <h1>💵 Manage Payments</h1>
        
        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <h2>Add New Payment</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Payment ID:</label>
                <input type="number" name="payment_id" required>
            </div>
            <div class="form-group">
                <label>Order ID:</label>
                <input type="number" name="order_id" required>
            </div>
            <div class="form-group">
                <label>Amount:</label>
                <input type="number" step="0.01" name="amount" required>
            </div>
            <div class="form-group">
                <label>Payment Date:</label>
                <input type="date" name="payment_date" required>
            </div>
            <div class="form-group">
                <label>Payment Method:</label>
                <select name="payment_method" required>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="Cash">Cash</option>
                </select>
            </div>
            <button type="submit">Record Payment</button>
        </form>

        <h2 style="margin-top: 40px;">Search & View Payments</h2>
        <form method="GET" action="" class="search-box">
            <input type="text" name="search" placeholder="Search by Payment ID, Order ID, or Customer name..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit" class="btn-primary">🔍 Search</button>
            <?php if ($searchTerm): ?>
                <a href="payments.php" class="btn-secondary">Clear</a>
            <?php endif; ?>
        </form>

        <h2>All Payments</h2>
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Method</th>
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
                    <td><?php echo htmlspecialchars($row['PAYMENTID']); ?></td>
                    <td><?php echo htmlspecialchars($row['ORDERID']); ?></td>
                    <td><?php echo htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['LASTNAME']); ?></td>
                    <td>$<?php echo number_format($row['AMOUNT'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['PAYMENTDATE']); ?></td>
                    <td><?php echo htmlspecialchars($row['PAYMENTMETHOD']); ?></td>
                    <td class="actions">
                        <a href="edit_payment.php?id=<?php echo $row['PAYMENTID']; ?>">Edit</a>
                        <a href="delete_payment.php?id=<?php echo $row['PAYMENTID']; ?>" onclick="return confirm('Delete?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if (!$hasData): ?>
                    <tr><td colspan="7" style="text-align:center; padding:20px;">No payments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <a href="../index.php" class="back-btn">← Back to Menu</a>
    </div>
</body>
</html>
<?php oci_close($conn); ?>
