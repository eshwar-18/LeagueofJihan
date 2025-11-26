<?php
/**
 * CPS510 A9 - Group 57 - Populate Tables Script
 * 
 * Inserts sample data into all 10 tables
 * Maintains referential integrity by inserting in correct order
 */

require_once 'config.php';

function insertData($conn, $data, $tableName) {
    $inserted = 0;
    $skipped = 0;
    foreach ($data as $sql) {
        $stmt = oci_parse($conn, $sql);
        if (@oci_execute($stmt)) {
            $inserted++;
        } else {
            $skipped++;
        }
    }
    return ['inserted' => $inserted, 'skipped' => $skipped, 'table' => $tableName];
}

$conn = getDBConnection();
$messages = [];
$totalInserted = 0;
$alreadyExists = 0;

// 1. Insert Users (must be first - no dependencies)
$users_data = [
    "INSERT INTO Users VALUES (1, 'John', 'Doe', 'john.doe@torontomu.ca', '416-555-0001', 'Student')",
    "INSERT INTO Users VALUES (2, 'Jane', 'Smith', 'jane.smith@torontomu.ca', '416-555-0002', 'Staff')",
    "INSERT INTO Users VALUES (3, 'Bob', 'Johnson', 'bob.j@torontomu.ca', '416-555-0003', 'Student')",
    "INSERT INTO Users VALUES (4, 'Alice', 'Williams', 'alice.w@torontomu.ca', '416-555-0004', 'Staff')",
    "INSERT INTO Users VALUES (5, 'Charlie', 'Brown', 'charlie.b@torontomu.ca', '416-555-0005', 'Student')",
    "INSERT INTO Users VALUES (6, 'Diana', 'Prince', 'diana.p@torontomu.ca', '416-555-0006', 'Staff')",
    "INSERT INTO Users VALUES (7, 'Eve', 'Taylor', 'eve.t@torontomu.ca', '416-555-0007', 'Student')",
    "INSERT INTO Users VALUES (8, 'Frank', 'Miller', 'frank.m@torontomu.ca', '416-555-0008', 'Student')"
];

$inserted = 0;
$skipped = 0;
foreach ($users_data as $sql) {
    $stmt = oci_parse($conn, $sql);
    if (@oci_execute($stmt)) {
        $inserted++;
    } else {
        $skipped++;
    }
}
$totalInserted += $inserted;
$alreadyExists += $skipped;
if ($inserted > 0) {
    $messages[] = ['type' => 'success', 'text' => "✓ Users: $inserted new records inserted" . ($skipped > 0 ? " ($skipped already exist)" : "")];
} else {
    $messages[] = ['type' => 'info', 'text' => "ℹ Users: All $skipped records already exist"];
}

// 2. Insert Department (no dependencies)
$dept_data = [
    "INSERT INTO Department VALUES ('Computer Science', 75000.00)",
    "INSERT INTO Department VALUES ('Information Technology', 70000.00)",
    "INSERT INTO Department VALUES ('Business', 65000.00)",
    "INSERT INTO Department VALUES ('Engineering', 72000.00)"
];

$result = insertData($conn, $dept_data, 'Department');
$totalInserted += $result['inserted'];
$alreadyExists += $result['skipped'];
if ($result['inserted'] > 0) {
    $messages[] = ['type' => 'success', 'text' => "✓ Department: {$result['inserted']} new records" . ($result['skipped'] > 0 ? " ({$result['skipped']} already exist)" : "")];
} else {
    $messages[] = ['type' => 'info', 'text' => "ℹ Department: All {$result['skipped']} records already exist"];
}

// 3. Insert Staff (depends on Users, Department)
$staff_data = [
    "INSERT INTO Staff (StaffID, Department, Position, HireDate) VALUES (2, 'Computer Science', 'Professor', TO_DATE('2020-09-01', 'YYYY-MM-DD'))",
    "INSERT INTO Staff (StaffID, Department, Position, HireDate) VALUES (4, 'Information Technology', 'Lab Instructor', TO_DATE('2021-01-15', 'YYYY-MM-DD'))",
    "INSERT INTO Staff (StaffID, Department, Position, HireDate) VALUES (6, 'Business', 'Admin Assistant', TO_DATE('2019-06-01', 'YYYY-MM-DD'))"
];

$result = insertData($conn, $staff_data, 'Staff');
$totalInserted += $result['inserted'];
$alreadyExists += $result['skipped'];
if ($result['inserted'] > 0) {
    $messages[] = ['type' => 'success', 'text' => "✓ Staff: {$result['inserted']} new records" . ($result['skipped'] > 0 ? " ({$result['skipped']} already exist)" : "")];
} else {
    $messages[] = ['type' => 'info', 'text' => "ℹ Staff: All {$result['skipped']} records already exist"];
}

// 4. Insert Student (depends on Users)
$student_data = [
    "INSERT INTO Student VALUES (1, 'Computer Science', 3, 3.5)",
    "INSERT INTO Student VALUES (3, 'Business Administration', 2, 3.8)",
    "INSERT INTO Student VALUES (5, 'Computer Engineering', 4, 3.2)",
    "INSERT INTO Student VALUES (7, 'Information Technology', 1, 3.9)",
    "INSERT INTO Student VALUES (8, 'Software Engineering', 2, 3.6)"
];

$result = insertData($conn, $student_data, 'Student');
$totalInserted += $result['inserted'];
$alreadyExists += $result['skipped'];
if ($result['inserted'] > 0) {
    $messages[] = ['type' => 'success', 'text' => "✓ Student: {$result['inserted']} new records" . ($result['skipped'] > 0 ? " ({$result['skipped']} already exist)" : "")];
} else {
    $messages[] = ['type' => 'info', 'text' => "ℹ Student: All {$result['skipped']} records already exist"];
}

// 5. Insert Product (no dependencies)
$product_data = [
    "INSERT INTO Product VALUES (101, 'Laptop Dell XPS 13', 'High-performance ultrabook', 1299.99, 15)",
    "INSERT INTO Product VALUES (102, 'Wireless Mouse Logitech', 'Ergonomic wireless mouse', 29.99, 50)",
    "INSERT INTO Product VALUES (103, 'USB-C Hub', '7-in-1 USB-C adapter', 49.99, 30)",
    "INSERT INTO Product VALUES (104, 'Mechanical Keyboard', 'RGB mechanical keyboard', 89.99, 25)",
    "INSERT INTO Product VALUES (105, 'Monitor 27-inch', '4K UHD monitor', 399.99, 10)",
    "INSERT INTO Product VALUES (106, 'Webcam HD', '1080p webcam', 79.99, 20)",
    "INSERT INTO Product VALUES (107, 'Headphones Sony', 'Noise-cancelling headphones', 249.99, 18)",
    "INSERT INTO Product VALUES (108, 'Laptop Stand', 'Aluminum laptop stand', 39.99, 40)"
];

$result = insertData($conn, $product_data, 'Product');
$totalInserted += $result['inserted'];
$alreadyExists += $result['skipped'];
if ($result['inserted'] > 0) {
    $messages[] = ['type' => 'success', 'text' => "✓ Product: {$result['inserted']} new records" . ($result['skipped'] > 0 ? " ({$result['skipped']} already exist)" : "")];
} else {
    $messages[] = ['type' => 'info', 'text' => "ℹ Product: All {$result['skipped']} records already exist"];
}

// 6. Insert Orders (depends on Users)
$orders_data = [
    "INSERT INTO Orders (OrderID, UserID, OrderDate, Status) VALUES (1001, 1, TO_DATE('2025-11-01', 'YYYY-MM-DD'), 'Completed')",
    "INSERT INTO Orders (OrderID, UserID, OrderDate, Status) VALUES (1002, 3, TO_DATE('2025-11-05', 'YYYY-MM-DD'), 'Completed')",
    "INSERT INTO Orders (OrderID, UserID, OrderDate, Status) VALUES (1003, 5, TO_DATE('2025-11-10', 'YYYY-MM-DD'), 'Pending')",
    "INSERT INTO Orders (OrderID, UserID, OrderDate, Status) VALUES (1004, 7, TO_DATE('2025-11-12', 'YYYY-MM-DD'), 'Completed')",
    "INSERT INTO Orders (OrderID, UserID, OrderDate, Status) VALUES (1005, 1, TO_DATE('2025-11-15', 'YYYY-MM-DD'), 'Pending')",
    "INSERT INTO Orders (OrderID, UserID, OrderDate, Status) VALUES (1006, 8, TO_DATE('2025-11-16', 'YYYY-MM-DD'), 'Cancelled')"
];

$result = insertData($conn, $orders_data, 'Orders');
$totalInserted += $result['inserted'];
$alreadyExists += $result['skipped'];
if ($result['inserted'] > 0) {
    $messages[] = ['type' => 'success', 'text' => "✓ Orders: {$result['inserted']} new records" . ($result['skipped'] > 0 ? " ({$result['skipped']} already exist)" : "")];
} else {
    $messages[] = ['type' => 'info', 'text' => "ℹ Orders: All {$result['skipped']} records already exist"];
}

// 7. Insert Order_Product (depends on Orders, Product)
$order_product_data = [
    "INSERT INTO Order_Product VALUES (1001, 101, 1)",
    "INSERT INTO Order_Product VALUES (1001, 102, 2)",
    "INSERT INTO Order_Product VALUES (1002, 103, 1)",
    "INSERT INTO Order_Product VALUES (1002, 104, 1)",
    "INSERT INTO Order_Product VALUES (1003, 105, 1)",
    "INSERT INTO Order_Product VALUES (1004, 106, 1)",
    "INSERT INTO Order_Product VALUES (1004, 107, 1)",
    "INSERT INTO Order_Product VALUES (1005, 108, 2)",
    "INSERT INTO Order_Product VALUES (1006, 102, 1)"
];

$result = insertData($conn, $order_product_data, 'Order_Product');
$totalInserted += $result['inserted'];
$alreadyExists += $result['skipped'];
if ($result['inserted'] > 0) {
    $messages[] = ['type' => 'success', 'text' => "✓ Order_Product: {$result['inserted']} new records" . ($result['skipped'] > 0 ? " ({$result['skipped']} already exist)" : "")];
} else {
    $messages[] = ['type' => 'info', 'text' => "ℹ Order_Product: All {$result['skipped']} records already exist"];
}

// 8. Insert Payment (depends on Orders)
$payment_data = [
    "INSERT INTO Payment (PaymentID, OrderID, Amount, PaymentDate, PaymentMethod) VALUES (5001, 1001, 1359.97, TO_DATE('2025-11-01', 'YYYY-MM-DD'), 'Credit Card')",
    "INSERT INTO Payment (PaymentID, OrderID, Amount, PaymentDate, PaymentMethod) VALUES (5002, 1002, 139.98, TO_DATE('2025-11-05', 'YYYY-MM-DD'), 'Debit Card')",
    "INSERT INTO Payment (PaymentID, OrderID, Amount, PaymentDate, PaymentMethod) VALUES (5003, 1004, 329.98, TO_DATE('2025-11-12', 'YYYY-MM-DD'), 'Credit Card')"
];

$result = insertData($conn, $payment_data, 'Payment');
$totalInserted += $result['inserted'];
$alreadyExists += $result['skipped'];
if ($result['inserted'] > 0) {
    $messages[] = ['type' => 'success', 'text' => "✓ Payment: {$result['inserted']} new records" . ($result['skipped'] > 0 ? " ({$result['skipped']} already exist)" : "")];
} else {
    $messages[] = ['type' => 'info', 'text' => "ℹ Payment: All {$result['skipped']} records already exist"];
}

// 9. Insert Report (depends on Staff)
$report_data = [
    "INSERT INTO Report (ReportID, StaffID, ReportType, GeneratedDate) VALUES (3001, 2, 'Sales Report', TO_DATE('2025-11-01', 'YYYY-MM-DD'))",
    "INSERT INTO Report (ReportID, StaffID, ReportType, GeneratedDate) VALUES (3002, 2, 'Inventory Report', TO_DATE('2025-11-10', 'YYYY-MM-DD'))",
    "INSERT INTO Report (ReportID, StaffID, ReportType, GeneratedDate) VALUES (3003, 4, 'User Activity Report', TO_DATE('2025-11-15', 'YYYY-MM-DD'))",
    "INSERT INTO Report (ReportID, StaffID, ReportType, GeneratedDate) VALUES (3004, 6, 'Financial Report', TO_DATE('2025-11-16', 'YYYY-MM-DD'))"
];

$result = insertData($conn, $report_data, 'Report');
$totalInserted += $result['inserted'];
$alreadyExists += $result['skipped'];
if ($result['inserted'] > 0) {
    $messages[] = ['type' => 'success', 'text' => "✓ Report: {$result['inserted']} new records" . ($result['skipped'] > 0 ? " ({$result['skipped']} already exist)" : "")];
} else {
    $messages[] = ['type' => 'info', 'text' => "ℹ Report: All {$result['skipped']} records already exist"];
}

// 10. Insert Review (depends on Users, Product)
$review_data = [
    "INSERT INTO Review (ReviewID, UserID, ProductID, Rating, ReviewComment, ReviewDate) VALUES (4001, 1, 101, 5, 'Excellent laptop, very fast!', TO_DATE('2025-11-02', 'YYYY-MM-DD'))",
    "INSERT INTO Review (ReviewID, UserID, ProductID, Rating, ReviewComment, ReviewDate) VALUES (4002, 3, 103, 4, 'Good adapter, works well', TO_DATE('2025-11-06', 'YYYY-MM-DD'))",
    "INSERT INTO Review (ReviewID, UserID, ProductID, Rating, ReviewComment, ReviewDate) VALUES (4003, 7, 106, 5, 'Great webcam quality', TO_DATE('2025-11-13', 'YYYY-MM-DD'))",
    "INSERT INTO Review (ReviewID, UserID, ProductID, Rating, ReviewComment, ReviewDate) VALUES (4004, 1, 102, 4, 'Comfortable mouse', TO_DATE('2025-11-03', 'YYYY-MM-DD'))",
    "INSERT INTO Review (ReviewID, UserID, ProductID, Rating, ReviewComment, ReviewDate) VALUES (4005, 5, 105, 5, 'Amazing display!', TO_DATE('2025-11-11', 'YYYY-MM-DD'))"
];

$result = insertData($conn, $review_data, 'Review');
$totalInserted += $result['inserted'];
$alreadyExists += $result['skipped'];
if ($result['inserted'] > 0) {
    $messages[] = ['type' => 'success', 'text' => "✓ Review: {$result['inserted']} new records" . ($result['skipped'] > 0 ? " ({$result['skipped']} already exist)" : "")];
} else {
    $messages[] = ['type' => 'info', 'text' => "ℹ Review: All {$result['skipped']} records already exist"];
}

// 11. Insert ReturnRequest (depends on Orders, Product)
$return_data = [
    "INSERT INTO ReturnRequest (ReturnID, OrderID, ProductID, RequestDate, Status) VALUES (6001, 1002, 104, TO_DATE('2025-11-07', 'YYYY-MM-DD'), 'Approved')",
    "INSERT INTO ReturnRequest (ReturnID, OrderID, ProductID, RequestDate, Status) VALUES (6002, 1006, 102, TO_DATE('2025-11-17', 'YYYY-MM-DD'), 'Pending')"
];

$result = insertData($conn, $return_data, 'ReturnRequest');
$totalInserted += $result['inserted'];
$alreadyExists += $result['skipped'];
if ($result['inserted'] > 0) {
    $messages[] = ['type' => 'success', 'text' => "✓ ReturnRequest: {$result['inserted']} new records" . ($result['skipped'] > 0 ? " ({$result['skipped']} already exist)" : "")];
} else {
    $messages[] = ['type' => 'info', 'text' => "ℹ ReturnRequest: All {$result['skipped']} records already exist"];
}

oci_commit($conn);
oci_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Populate Tables - CPS510 A9</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
        }
        .message.info {
            background: #fef3c7;
            border-left-color: #f59e0b;
        }
        .summary {
            margin: 20px 0;
            padding: 20px;
            background: #f0f9ff;
            border-radius: 10px;
            border-left: 4px solid #3b82f6;
        }
        .summary h3 {
            color: #3b82f6;
            margin-bottom: 10px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.3s;
        }
        .back-btn:hover {
            transform: translateY(-2px);
        }
        .stats {
            margin: 30px 0;
            padding: 20px;
            background: #f8fafc;
            border-radius: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Populate Database Tables</h1>
        
        <div class="stats">
            <h3>Sample Data Summary</h3>
            <p><strong><?php echo $totalInserted; ?></strong> new records inserted</p>
            <?php if ($alreadyExists > 0): ?>
            <p><strong><?php echo $alreadyExists; ?></strong> records already existed (skipped)</p>
            <?php endif; ?>
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
