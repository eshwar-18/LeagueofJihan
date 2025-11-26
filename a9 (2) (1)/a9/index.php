<?php
/**
 * CPS510 A9 - Group 57 - Main Menu
 * Complete E-Commerce Database Management System
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CPS510 A9 - E-Commerce Database</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
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
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
            font-size: 1.1em;
        }
        .menu-section {
            margin-bottom: 30px;
        }
        .menu-section h2 {
            color: #667eea;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .menu-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-decoration: none;
            border-radius: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
            display: block;
            text-align: center;
            font-weight: 600;
        }
        .menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .db-operations {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .table-operations {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .query-operations {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
        }
        .schema-info {
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .schema-info h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛒 E-Commerce Database Management</h1>
        <p class="subtitle">CPS510 Assignment 9 - Group 57</p>
        
        <div class="schema-info">
            <h3>📊 Database Schema</h3>
            <p><strong>11 Tables</strong>: Users, Department, Staff, Student, Product, Orders, Order_Product, Payment, Report, Review, ReturnRequest</p>
            <p><strong>3 Views</strong>: Staff Report Summary, Top Rated Products, Sales Summary</p>
            <p><strong>Normalization</strong>: 3NF/BCNF compliant</p>
        </div>
        
        <!-- Database Operations -->
        <div class="menu-section">
            <h2>📊 Database Operations</h2>
            <div class="menu-grid">
                <a href="create_tables.php" class="menu-item db-operations">
                    ➕ Create Tables
                </a>
                <a href="drop_tables.php" class="menu-item db-operations">
                    ❌ Drop Tables
                </a>
                <a href="populate.php" class="menu-item db-operations">
                    📝 Populate Tables
                </a>
            </div>
        </div>
        
        <!-- Table Management -->
        <div class="menu-section">
            <h2>🗂️ Manage Tables (CRUD Operations)</h2>
            <div class="menu-grid">
                <a href="tables/users.php" class="menu-item table-operations">
                    👥 Manage Users
                </a>
                <a href="tables/staff.php" class="menu-item table-operations">
                    👔 Manage Staff
                </a>
                <a href="tables/students.php" class="menu-item table-operations">
                    🎓 Manage Students
                </a>
                <a href="tables/products.php" class="menu-item table-operations">
                    📦 Manage Products
                </a>
                <a href="tables/orders.php" class="menu-item table-operations">
                    🛍️ Manage Orders
                </a>
                <a href="tables/payments.php" class="menu-item table-operations">
                    💳 Manage Payments
                </a>
                <a href="tables/reviews.php" class="menu-item table-operations">
                    ⭐ Manage Reviews
                </a>
                <a href="tables/returns.php" class="menu-item table-operations">
                    🔄 Manage Returns
                </a>
            </div>
        </div>
        
        <!-- Query Execution -->
        <div class="menu-section">
            <h2>🔍 Analytical Queries</h2>
            <div class="menu-grid">
                <a href="queries/query1.php" class="menu-item query-operations">
                    📊 Query 1: Order Details (JOIN)
                </a>
                <a href="queries/query2.php" class="menu-item query-operations">
                    💰 Query 2: Sales by Product (Aggregation)
                </a>
                <a href="queries/query3.php" class="menu-item query-operations">
                    🎯 Query 3: Top Customers (Subquery)
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>CPS510 - Database Systems I</strong></p>
            <p>Toronto Metropolitan University - Fall 2025</p>
        </div>
    </div>
</body>
</html>
