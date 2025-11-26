<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: returns.php');
    exit;
}

$conn = getDBConnection();
$return_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE ReturnRequest SET OrderID = :oid, ProductID = :pid, RequestDate = TO_DATE(:rdate, 'YYYY-MM-DD'), Status = :status WHERE ReturnID = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':oid', $_POST['order_id']);
    oci_bind_by_name($stmt, ':pid', $_POST['product_id']);
    oci_bind_by_name($stmt, ':rdate', $_POST['request_date']);
    oci_bind_by_name($stmt, ':status', $_POST['status']);
    oci_bind_by_name($stmt, ':id', $return_id);
    oci_execute($stmt);
    oci_commit($conn);
    oci_close($conn);
    header('Location: returns.php');
    exit;
}

$sql = "SELECT * FROM ReturnRequest WHERE ReturnID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $return_id);
oci_execute($stmt);
$return = oci_fetch_array($stmt, OCI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Return Request</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 40px; }
        h1 { color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; margin-right: 10px; }
        .back-btn { background: #6c757d; display: inline-block; padding: 12px 30px; color: white; text-decoration: none; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Return Request</h1>
        <form method="POST">
            <div class="form-group">
                <label>Order ID:</label>
                <input type="number" name="order_id" value="<?php echo htmlspecialchars($return['ORDERID']); ?>" required>
            </div>
            <div class="form-group">
                <label>Product ID:</label>
                <input type="number" name="product_id" value="<?php echo htmlspecialchars($return['PRODUCTID']); ?>" required>
            </div>
            <div class="form-group">
                <label>Request Date:</label>
                <input type="date" name="request_date" value="<?php echo htmlspecialchars(substr($return['REQUESTDATE'], 0, 10)); ?>" required>
            </div>
            <div class="form-group">
                <label>Status:</label>
                <select name="status" required>
                    <option value="Pending" <?php echo $return['STATUS'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo $return['STATUS'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?php echo $return['STATUS'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <button type="submit">Update Return Request</button>
            <a href="returns.php" class="back-btn">Cancel</a>
        </form>
    </div>
</body>
</html>
<?php oci_close($conn); ?>
