<?php
/**
 * CPS510 A9 - Returns CRUD Page
 */
require_once '../config.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $sql = "INSERT INTO ReturnRequest (ReturnID, OrderID, ProductID, RequestDate, Status) 
                VALUES (:retid, :retoid, :retpid, TO_DATE(:retdate, 'YYYY-MM-DD'), :retstatus)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':retid', $_POST['return_id']);
        oci_bind_by_name($stmt, ':retoid', $_POST['order_id']);
        oci_bind_by_name($stmt, ':retpid', $_POST['product_id']);
        oci_bind_by_name($stmt, ':retdate', $_POST['request_date']);
        oci_bind_by_name($stmt, ':retstatus', $_POST['status']);
        
        if (@oci_execute($stmt)) {
            oci_commit($conn);
            $success_message = "✓ Return request added successfully!";
        } else {
            $error = oci_error($stmt);
            if ($error['code'] == 1) {
                $error_message = "Error: Return ID {$_POST['return_id']} already exists!";
            } elseif ($error['code'] == 2291) {
                $error_message = "Error: Invalid Order ID or Product ID!";
            } elseif ($error['code'] == 2290) {
                $error_message = "Error: Status must be 'Pending', 'Approved', or 'Rejected'!";
            } else {
                $error_message = "Error: " . $error['message'];
            }
        }
    }
    oci_close($conn);
    if (empty($error_message) && empty($success_message)) {
        header('Location: returns.php');
        exit;
    }
}

$conn = getDBConnection();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT rr.*, p.Name AS ProductName, u.FirstName, u.LastName 
        FROM ReturnRequest rr
        JOIN Orders o ON rr.OrderID = o.OrderID 
        JOIN Product p ON rr.ProductID = p.ProductID 
        JOIN Users u ON o.UserID = u.UserID";

if ($search !== '') {
    $sql .= " WHERE 
                LOWER(rr.ReturnID) LIKE LOWER(:search)
                OR LOWER(rr.OrderID) LIKE LOWER(:search)
                OR LOWER(p.Name) LIKE LOWER(:search)
                OR LOWER(rr.Status) LIKE LOWER(:search)
                OR LOWER(u.FirstName || ' ' || u.LastName) LIKE LOWER(:search)";
}

$sql .= " ORDER BY rr.ReturnID DESC";

$stmt = oci_parse($conn, $sql);

if ($search !== '') {
    $search_param = "%$search%";
    oci_bind_by_name($stmt, ":search", $search_param);
}

oci_execute($stmt);

$result = $stmt;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Returns</title>
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
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        input, select {
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
        .actions a {
            margin: 0 5px;
            color: #667eea;
            text-decoration: none;
        }
        .status-pending { color: #f59e0b; font-weight: 600; }
        .status-approved { color: #22c55e; font-weight: 600; }
        .status-rejected { color: #ef4444; font-weight: 600; }
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
        <h1>🔄 Manage Return Requests</h1>
        
        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <h2>Create New Return Request</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Return ID:</label>
                <input type="number" name="return_id" required>
            </div>
            <div class="form-group">
                <label>Order ID:</label>
                <input type="number" name="order_id" required>
            </div>
            <div class="form-group">
                <label>Product ID:</label>
                <input type="number" name="product_id" required>
            </div>
            <div class="form-group">
                <label>Request Date:</label>
                <input type="date" name="request_date" required>
            </div>
            <div class="form-group">
                <label>Status:</label>
                <select name="status" required>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <button type="submit">Submit Return Request</button>
        </form>

        <h2 style="margin-top: 40px;">All Return Requests</h2>

        <!-- 🔍 SEARCH BAR ADDED HERE -->
        <form method="GET" style="margin: 20px 0;">
            <input 
                type="text" 
                name="search" 
                placeholder="Search return requests..." 
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                style="width: 300px; padding: 10px; border: 1px solid #ccc; border-radius: 8px;"
            >
            <button type="submit" style="
                padding: 10px 20px; 
                border: none; 
                border-radius: 8px; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;">
                Search
            </button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Return ID</th>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Request Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_array($result, OCI_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['RETURNID']); ?></td>
                    <td><?php echo htmlspecialchars($row['ORDERID']); ?></td>
                    <td><?php echo htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['LASTNAME']); ?></td>
                    <td><?php echo htmlspecialchars($row['PRODUCTNAME']); ?></td>
                    <td><?php echo htmlspecialchars($row['REQUESTDATE']); ?></td>
                    <td class="status-<?php echo strtolower($row['STATUS']); ?>">
                        <?php echo htmlspecialchars($row['STATUS']); ?>
                    </td>
                    <td class="actions">
                        <a href="edit_return.php?id=<?php echo $row['RETURNID']; ?>">Edit</a>
                        <a href="delete_return.php?id=<?php echo $row['RETURNID']; ?>" onclick="return confirm('Delete?')">Delete</a>
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
