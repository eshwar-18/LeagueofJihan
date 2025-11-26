<?php
/**
 * CPS510 A9 - Query 3: Subquery
 * 
 * Query Description:
 * Find customers who spent more than the average order amount
 * Uses a subquery to calculate the average first, then filters customers
 * 
 * SQL Concepts:
 * - Subquery in WHERE clause
 * - Nested SELECT statement
 * - Comparison with aggregate result
 * 
 * Business Value:
 * - Identify high-value customers
 * - Target VIP customers for special promotions
 * - Understand spending patterns above average
 */

require_once '../config.php';

$conn = getDBConnection();

/**
 * Subquery: Customers with Above-Average Spending
 * 
 * Outer query: Get customer details and their total spending
 * Inner subquery: Calculate average order total across all orders
 * WHERE clause: Filter customers whose spending > average
 */
$sql = "
SELECT 
    u.UserID,
    u.FirstName || ' ' || u.LastName AS CustomerName,
    u.Email,
    u.Role,
    COUNT(o.OrderID) AS TotalOrders,
    SUM(op.Quantity * p.Price) AS TotalSpent,
    ROUND(AVG(op.Quantity * p.Price), 2) AS AvgOrderValue
FROM Users u
INNER JOIN Orders o ON u.UserID = o.UserID
INNER JOIN Order_Product op ON o.OrderID = op.OrderID
INNER JOIN Product p ON op.ProductID = p.ProductID
GROUP BY u.UserID, u.FirstName, u.LastName, u.Email, u.Role
HAVING SUM(op.Quantity * p.Price) > (
    SELECT AVG(LineTotal)
    FROM (
        SELECT SUM(op2.Quantity * p2.Price) AS LineTotal
        FROM Orders o2
        INNER JOIN Order_Product op2 ON o2.OrderID = op2.OrderID
        INNER JOIN Product p2 ON op2.ProductID = p2.ProductID
        GROUP BY o2.OrderID
    )
)
ORDER BY TotalSpent DESC
";

$statement = executeQuery($conn, $sql);

// Calculate the average order amount for display
$avgSql = "
SELECT AVG(LineTotal) AS AvgOrderAmount
FROM (
    SELECT SUM(op.Quantity * p.Price) AS LineTotal
    FROM Orders o
    INNER JOIN Order_Product op ON o.OrderID = op.OrderID
    INNER JOIN Product p ON op.ProductID = p.ProductID
    GROUP BY o.OrderID
)
";
$avgStatement = executeQuery($conn, $avgSql);
$avgRow = oci_fetch_assoc($avgStatement);
$averageOrderAmount = $avgRow['AVGORDERAMOUNT'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query 3: High-Value Customers - CPS510 A9</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
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
            background: #f3e5f5;
            border-left: 4px solid #9c27b0;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .query-info h2 {
            color: #6a1b9a;
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
            font-size: 13px;
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
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
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
        
        .spent {
            font-weight: 600;
            color: #28a745;
        }
        
        .summary {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .summary strong {
            color: #1b5e20;
        }
        
        .avg-badge {
            display: inline-block;
            background: #ffc107;
            color: #333;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .vip-badge {
            display: inline-block;
            background: #9c27b0;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👑 Query 3: High-Value Customers (Subquery)</h1>
        
        <div class="query-info">
            <h2>Query Description</h2>
            <p><strong>Type:</strong> Subquery in HAVING Clause</p>
            <p><strong>Purpose:</strong> Identify customers whose total spending exceeds the average order amount.</p>
            <p><strong>Query Structure:</strong></p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li><strong>Outer Query:</strong> Groups customers and calculates their total spending</li>
                <li><strong>Inner Subquery:</strong> Calculates average order amount across all orders</li>
                <li><strong>HAVING Clause:</strong> Filters groups where TotalSpent > (SELECT AVG...)</li>
            </ul>
            <p><strong>Business Value:</strong> Identify VIP customers for loyalty programs, personalized marketing, and special promotions. These customers drive significant revenue and deserve extra attention.</p>
        </div>
        
        <div class="sql-box">-- Outer query: Customer spending summary
SELECT u.UserID, u.FirstName || ' ' || u.LastName AS Name,
       COUNT(o.OrderID) AS TotalOrders,
       SUM(op.Quantity * p.Price) AS TotalSpent
FROM Users u
INNER JOIN Orders o ON u.UserID = o.UserID
INNER JOIN Order_Product op ON o.OrderID = op.OrderID
INNER JOIN Product p ON op.ProductID = p.ProductID
GROUP BY u.UserID, u.FirstName, u.LastName
-- Inner subquery: Average order amount
HAVING SUM(op.Quantity * p.Price) > (
    SELECT AVG(LineTotal) FROM (
        SELECT SUM(op2.Quantity * p2.Price) AS LineTotal
        FROM Orders o2
        INNER JOIN Order_Product op2 ON o2.OrderID = op2.OrderID
        INNER JOIN Product p2 ON op2.ProductID = p2.ProductID
        GROUP BY o2.OrderID
    )
)
ORDER BY TotalSpent DESC</div>
        
        <?php
        // Calculate summary statistics
        $rowCount = 0;
        $totalSpent = 0;
        $results = [];
        
        while ($row = oci_fetch_assoc($statement)) {
            $results[] = $row;
            $rowCount++;
            $totalSpent += $row['TOTALSPENT'];
        }
        
        $avgSpentPerVIP = $rowCount > 0 ? $totalSpent / $rowCount : 0;
        ?>
        
        <div class="summary">
            <strong>Average Order Amount:</strong> 
            <span class="avg-badge">$<?php echo number_format($averageOrderAmount, 2); ?></span>
            <br><br>
            <strong>High-Value Customers Found:</strong> <?php echo $rowCount; ?> | 
            <strong>Total Spent by VIPs:</strong> $<?php echo number_format($totalSpent, 2); ?> | 
            <strong>Avg per VIP:</strong> $<?php echo number_format($avgSpentPerVIP, 2); ?>
        </div>
        
        <h2 style="margin-top: 30px; color: #555;">Query Results: Customers Above Average Spending</h2>
        
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Total Orders</th>
                    <th>Total Spent</th>
                    <th>Avg Order Value</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($results) > 0): ?>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['USERID']); ?></td>
                            <td><?php echo htmlspecialchars($row['CUSTOMERNAME']); ?></td>
                            <td><?php echo htmlspecialchars($row['EMAIL']); ?></td>
                            <td><?php echo htmlspecialchars($row['ROLE']); ?></td>
                            <td><?php echo htmlspecialchars($row['TOTALORDERS']); ?></td>
                            <td class="spent">$<?php echo number_format($row['TOTALSPENT'], 2); ?></td>
                            <td>$<?php echo number_format($row['AVGORDERVALUE'], 2); ?></td>
                            <td><span class="vip-badge">VIP</span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-data">
                            No customers found with spending above the average order amount.
                            This could mean all customers spend similarly, or there are no orders yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <p style="margin-top: 15px; font-style: italic; color: #666;">
            * Customers shown have total spending exceeding the average order amount of $<?php echo number_format($averageOrderAmount, 2); ?>
        </p>
        
        <a href="../index.php" class="btn btn-secondary">← Back to Menu</a>
    </div>
</body>
</html>

<?php
oci_free_statement($avgStatement);
oci_free_statement($statement);
closeConnection($conn);
?>
