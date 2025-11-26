<?php
/**
 * CPS510 A9 - Group 57 - Drop Tables Script
 * 
 * Drops all tables and views in correct order (reverse of dependencies)
 */

require_once 'config.php';

$conn = getDBConnection();
$messages = [];

// Drop views first
$views = ['Staff_Report_Summary', 'VW_TOP_RATED_PRODUCTS', 'VW_SALES_SUMMARY'];
foreach ($views as $view) {
    $sql = "DROP VIEW $view";
    $stmt = oci_parse($conn, $sql);
    if (@oci_execute($stmt)) {
        $messages[] = ['type' => 'success', 'text' => "✓ View $view dropped successfully"];
    } else {
        $error = oci_error($stmt);
        if ($error['code'] == 942) { // ORA-00942: table or view does not exist
            $messages[] = ['type' => 'info', 'text' => "ℹ View $view does not exist (already dropped)"];
        } else {
            $messages[] = ['type' => 'error', 'text' => "✗ Error dropping view $view: " . $error['message']];
        }
    }
}

// Drop tables in reverse order (children first, parents last)
$tables = [
    'ReturnRequest',
    'Review',
    'Report',
    'Payment',
    'Order_Product',
    'Orders',
    'Product',
    'Student',
    'Staff',
    'Department',
    'Users'
];

foreach ($tables as $table) {
    $sql = "DROP TABLE $table CASCADE CONSTRAINTS";
    $stmt = oci_parse($conn, $sql);
    if (@oci_execute($stmt)) {
        $messages[] = ['type' => 'success', 'text' => "✓ Table $table dropped successfully"];
    } else {
        $error = oci_error($stmt);
        if ($error['code'] == 942) {
            $messages[] = ['type' => 'info', 'text' => "ℹ Table $table does not exist (already dropped)"];
        } else {
            $messages[] = ['type' => 'error', 'text' => "✗ Error dropping table $table: " . $error['message']];
        }
    }
}

oci_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drop Tables - CPS510 A9</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .message {
            padding: 12px 20px;
            margin: 10px 0;
            border-radius: 8px;
            background: #fef2f2;
            border-left: 4px solid #ef4444;
        }
        .message.success {
            background: #f0fdf4;
            border-left-color: #22c55e;
        }
        .message.info {
            background: #fef3c7;
            border-left-color: #f59e0b;
        }
        .back-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .back-btn:hover {
            transform: translateY(-2px);
        }
        .warning {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>❌ Drop All Tables</h1>
        
        <div class="warning">
            <strong>⚠️ Warning:</strong> All tables and data have been removed!
        </div>

        <?php foreach ($messages as $message): ?>
            <div class="message <?php echo $message['type']; ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endforeach; ?>

        <div style="text-align: center;">
            <a href="index.php" class="back-btn">← Back to Menu</a>
        </div>
    </div>
</body>
</html>
