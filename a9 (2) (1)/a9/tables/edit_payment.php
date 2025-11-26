<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: payments.php');
    exit;
}

$conn = getDBConnection();
$payment_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE Payment SET OrderID = :oid, Amount = :amt, PaymentDate = TO_DATE(:pdate, 'YYYY-MM-DD'), PaymentMethod = :method WHERE PaymentID = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':oid', $_POST['order_id']);
    oci_bind_by_name($stmt, ':amt', $_POST['amount']);
    oci_bind_by_name($stmt, ':pdate', $_POST['payment_date']);
    oci_bind_by_name($stmt, ':method', $_POST['payment_method']);
    oci_bind_by_name($stmt, ':id', $payment_id);
    oci_execute($stmt);
    oci_commit($conn);
    oci_close($conn);
    header('Location: payments.php');
    exit;
}

$sql = "SELECT * FROM Payment WHERE PaymentID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $payment_id);
oci_execute($stmt);
$payment = oci_fetch_array($stmt, OCI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Payment</title>
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
        <h1>Edit Payment</h1>
        <form method="POST">
            <div class="form-group">
                <label>Order ID:</label>
                <input type="number" name="order_id" value="<?php echo htmlspecialchars($payment['ORDERID']); ?>" required>
            </div>
            <div class="form-group">
                <label>Amount:</label>
                <input type="number" step="0.01" name="amount" value="<?php echo htmlspecialchars($payment['AMOUNT']); ?>" required>
            </div>
            <div class="form-group">
                <label>Payment Date:</label>
                <input type="date" name="payment_date" value="<?php echo htmlspecialchars(substr($payment['PAYMENTDATE'], 0, 10)); ?>" required>
            </div>
            <div class="form-group">
                <label>Payment Method:</label>
                <select name="payment_method" required>
                    <option value="Credit Card" <?php echo $payment['PAYMENTMETHOD'] == 'Credit Card' ? 'selected' : ''; ?>>Credit Card</option>
                    <option value="Debit Card" <?php echo $payment['PAYMENTMETHOD'] == 'Debit Card' ? 'selected' : ''; ?>>Debit Card</option>
                    <option value="Cash" <?php echo $payment['PAYMENTMETHOD'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                </select>
            </div>
            <button type="submit">Update Payment</button>
            <a href="payments.php" class="back-btn">Cancel</a>
        </form>
    </div>
</body>
</html>
<?php oci_close($conn); ?>
