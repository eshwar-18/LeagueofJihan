<?php
/**
 * CPS510 A9 - Query 1: JOIN Query
 * 
 * Query Description:
 * This query retrieves all orders with customer and product details.
 * It demonstrates MULTI-TABLE JOIN across Orders, Users, Order_Product, and Product.
 * 
 * SQL Concept: JOIN Operation
 * - Combines rows from 4 tables using multiple INNER JOINs
 * - Shows relational database capability to link related data
 * - Essential for comprehensive order analysis
 * 
 * Business Value:
 * - Complete order details with customer info and product breakdown
 * - Useful for order fulfillment, inventory, and sales analysis
 */

require_once '../config.php';

$conn = getDBConnection();

/**
 * JOIN Query: Orders with Customer and Product Information
 * 
 * This query uses INNER JOIN across 4 tables:
 * - Orders, Users, Order_Product, Product
 * - Shows order ID, date, status, customer name, product details
 * - Calculates line item subtotal (Quantity * Price)
 */
$sql = "
SELECT 
    o.OrderID,
    o.OrderDate,
    o.Status AS OrderStatus,
    u.UserID,
    u.FirstName || ' ' || u.LastName AS CustomerName,
    u.Email,
    p.ProductID,
    p.Name AS ProductName,
    op.Quantity,
    p.Price,
    (op.Quantity * p.Price) AS LineTotal
FROM Orders o
INNER JOIN Users u ON o.UserID = u.UserID
INNER JOIN Order_Product op ON o.OrderID = op.OrderID
INNER JOIN Product p ON op.ProductID = p.ProductID
ORDER BY o.OrderDate DESC, o.OrderID DESC
";

$statement = executeQuery($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query 1: Orders with Customer Info - CPS510 A9</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .query-info {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .query-info h2 {
            color: #1976D2;
            margin-bottom: 10px;
        }
        
        .query-info p {
            margin: 10px 0;
            line-height: 1.6;
        }
        
        .sql-box {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
            white-space: pre-wrap;
        }
        
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
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 30px;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        
        .total {
            font-weight: 600;
            color: #28a745;
        }
        
        .summary {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .summary strong {
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Query 1: Orders with Customer and Product Details (JOIN)</h1>
        
        <div class="query-info">
            <h2>Query Description</h2>
            <p><strong>Type:</strong> MULTI-TABLE INNER JOIN Query</p>
            <p><strong>Purpose:</strong> Retrieve complete order details including customer information and product breakdown.</p>
            <p><strong>Tables Involved:</strong></p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li><strong>Orders (o):</strong> Order transactions</li>
                <li><strong>Users (u):</strong> Customer information</li>
                <li><strong>Order_Product (op):</strong> Order line items</li>
                <li><strong>Product (p):</strong> Product details and pricing</li>
            </ul>
            <p><strong>Join Conditions:</strong> o.UserID = u.UserID, o.OrderID = op.OrderID, op.ProductID = p.ProductID</p>
            <p><strong>Business Value:</strong> Complete order breakdown showing what each customer ordered, quantities, and line totals.</p>
        </div>
        
        <div class="sql-box">SELECT 
    o.OrderID, o.OrderDate, o.Status,
    u.FirstName || ' ' || u.LastName AS CustomerName,
    u.Email,
    p.Name AS ProductName,
    op.Quantity,
    p.Price,
    (op.Quantity * p.Price) AS LineTotal
FROM Orders o
INNER JOIN Users u ON o.UserID = u.UserID
INNER JOIN Order_Product op ON o.OrderID = op.OrderID
INNER JOIN Product p ON op.ProductID = p.ProductID
ORDER BY o.OrderDate DESC</div>
        
        <?php
        // Count and calculate summary statistics
        $rowCount = 0;
        $totalRevenue = 0;
        $results = [];
        
        while ($row = oci_fetch_assoc($statement)) {
            $results[] = $row;
            $rowCount++;
            $totalRevenue += $row['LINETOTAL'];
        }
        ?>
        
        <div class="summary">
            <strong>Query Summary:</strong> Found <?php echo $rowCount; ?> order line item(s) | 
            Total Revenue: $<?php echo number_format($totalRevenue, 2); ?>
        </div>
        
        <h2 style="margin-top: 30px; color: #555;">Query Results</h2>
        
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($results) > 0): ?>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['ORDERID']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['ORDERDATE'], 0, 10)); ?></td>
                            <td><?php echo htmlspecialchars($row['ORDERSTATUS']); ?></td>
                            <td><?php echo htmlspecialchars($row['CUSTOMERNAME']); ?></td>
                            <td><?php echo htmlspecialchars($row['PRODUCTNAME']); ?></td>
                            <td><?php echo htmlspecialchars($row['QUANTITY']); ?></td>
                            <td>$<?php echo number_format($row['PRICE'], 2); ?></td>
                            <td class="total">$<?php echo number_format($row['LINETOTAL'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-data">No orders found in the database.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <a href="../index.php" class="btn btn-secondary">← Back to Menu</a>
    </div>
</body>
</html>

<?php
oci_free_statement($statement);
closeConnection($conn);
?>
