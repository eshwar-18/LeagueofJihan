<?php
/**
 * CPS510 A9 - Query 2: Aggregation Query
 * 
 * Query Description:
 * Product Sales Analysis using GROUP BY and Aggregate Functions
 * Shows total units sold, revenue, and average rating per product
 * 
 * SQL Concepts:
 * - GROUP BY: Groups results by ProductID
 * - COUNT, SUM, AVG: Aggregate functions for analysis
 * - Multiple JOINs with aggregation
 * 
 * Business Value:
 * - Identify best-selling products
 * - Revenue analysis by product
 * - Customer satisfaction (avg rating) correlation with sales
 */

require_once '../config.php';

$conn = getDBConnection();

/**
 * Aggregation Query: Product Sales Summary
 * 
 * Groups by Product and calculates:
 * - Total units sold (SUM)
 * - Total revenue (SUM)
 * - Number of orders (COUNT)
 * - Average rating (AVG)
 * - Number of reviews (COUNT)
 */
$sql = "
SELECT 
    p.ProductID,
    p.Name AS ProductName,
    p.Price AS CurrentPrice,
    p.StockQuantity,
    COALESCE(SUM(op.Quantity), 0) AS TotalUnitsSold,
    COALESCE(SUM(op.Quantity * p.Price), 0) AS TotalRevenue,
    COUNT(DISTINCT op.OrderID) AS NumberOfOrders,
    ROUND(AVG(r.Rating), 2) AS AvgRating,
    COUNT(r.ReviewID) AS ReviewCount
FROM Product p
LEFT JOIN Order_Product op ON p.ProductID = op.ProductID
LEFT JOIN Review r ON p.ProductID = r.ProductID
GROUP BY p.ProductID, p.Name, p.Price, p.StockQuantity
ORDER BY TotalRevenue DESC, TotalUnitsSold DESC
";

$statement = executeQuery($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Query 2: Product Sales Analysis - CPS510 A9</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .query-info h2 {
            color: #e65100;
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
            font-size: 14px;
        }
        
        th, td {
            padding: 12px 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
        
        .revenue {
            font-weight: 600;
            color: #28a745;
        }
        
        .rating {
            color: #ffc107;
            font-weight: 600;
        }
        
        .summary {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .summary strong {
            color: #0c5460;
        }
        
        .highlight {
            background: #fff9c4;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📈 Query 2: Product Sales Analysis (Aggregation)</h1>
        
        <div class="query-info">
            <h2>Query Description</h2>
            <p><strong>Type:</strong> GROUP BY with Aggregation Functions</p>
            <p><strong>Purpose:</strong> Analyze product performance with sales metrics and customer ratings.</p>
            <p><strong>Aggregate Functions Used:</strong></p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li><strong>SUM(op.Quantity):</strong> Total units sold</li>
                <li><strong>SUM(op.Quantity * p.Price):</strong> Total revenue generated</li>
                <li><strong>COUNT(DISTINCT op.OrderID):</strong> Number of orders</li>
                <li><strong>AVG(r.Rating):</strong> Average customer rating</li>
                <li><strong>COUNT(r.ReviewID):</strong> Number of reviews</li>
            </ul>
            <p><strong>Business Value:</strong> Identify top-performing products, revenue drivers, and customer satisfaction metrics. Essential for inventory planning and marketing decisions.</p>
        </div>
        
        <div class="sql-box">SELECT 
    p.ProductID, p.Name, p.Price,
    COALESCE(SUM(op.Quantity), 0) AS TotalUnitsSold,
    COALESCE(SUM(op.Quantity * p.Price), 0) AS TotalRevenue,
    COUNT(DISTINCT op.OrderID) AS NumberOfOrders,
    ROUND(AVG(r.Rating), 2) AS AvgRating,
    COUNT(r.ReviewID) AS ReviewCount
FROM Product p
LEFT JOIN Order_Product op ON p.ProductID = op.ProductID
LEFT JOIN Review r ON p.ProductID = r.ProductID
GROUP BY p.ProductID, p.Name, p.Price, p.StockQuantity
ORDER BY TotalRevenue DESC</div>
        
        <?php
        // Calculate summary statistics
        $rowCount = 0;
        $totalRevenue = 0;
        $totalUnitsSold = 0;
        $results = [];
        
        while ($row = oci_fetch_assoc($statement)) {
            $results[] = $row;
            $rowCount++;
            $totalRevenue += $row['TOTALREVENUE'];
            $totalUnitsSold += $row['TOTALUNITSSOLD'];
        }
        
        $avgRevenuePerProduct = $rowCount > 0 ? $totalRevenue / $rowCount : 0;
        ?>
        
        <div class="summary">
            <strong>Summary Statistics:</strong><br>
            Total Products: <?php echo $rowCount; ?> | 
            Total Units Sold: <?php echo number_format($totalUnitsSold); ?> | 
            Total Revenue: $<?php echo number_format($totalRevenue, 2); ?> | 
            Avg Revenue/Product: $<?php echo number_format($avgRevenuePerProduct, 2); ?>
        </div>
        
        <h2 style="margin-top: 30px; color: #555;">Query Results</h2>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Units Sold</th>
                    <th>Revenue</th>
                    <th>Orders</th>
                    <th>Avg Rating</th>
                    <th>Reviews</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($results) > 0): ?>
                    <?php foreach ($results as $index => $row): ?>
                        <tr <?php echo $index < 3 && $row['TOTALREVENUE'] > 0 ? 'class="highlight"' : ''; ?>>
                            <td><?php echo htmlspecialchars($row['PRODUCTID']); ?></td>
                            <td><?php echo htmlspecialchars($row['PRODUCTNAME']); ?></td>
                            <td>$<?php echo number_format($row['CURRENTPRICE'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['STOCKQUANTITY']); ?></td>
                            <td><?php echo number_format($row['TOTALUNITSSOLD']); ?></td>
                            <td class="revenue">$<?php echo number_format($row['TOTALREVENUE'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['NUMBEROFORDERS']); ?></td>
                            <td class="rating">
                                <?php 
                                if ($row['AVGRATING']) {
                                    echo $row['AVGRATING'] . ' ⭐';
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['REVIEWCOUNT']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="no-data">No products found in the database.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <p style="margin-top: 15px; font-style: italic; color: #666;">
            * Highlighted rows show top 3 revenue-generating products
        </p>
        
        <a href="../index.php" class="btn btn-secondary">← Back to Menu</a>
    </div>
</body>
</html>

<?php
oci_free_statement($statement);
closeConnection($conn);
?>
