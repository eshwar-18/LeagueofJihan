<?php
/**
 * CPS510 A9 - Group 57 - Create Tables Script
 * 
 * Creates all 10 normalized tables + 3 views
 * Schema in 3NF/BCNF from A6-A8
 * 
 * Tables: Users, Department, Staff, Student, Product, Orders,
 *         Order_Product, Payment, Report, Review, ReturnRequest
 * Views: Staff_Report_Summary, VW_TOP_RATED_PRODUCTS, VW_SALES_SUMMARY
 */

require_once 'config.php';

$conn = getDBConnection();
$messages = [];

// Table 1: USERS
$sql_users = "
CREATE TABLE Users (
    UserID NUMBER PRIMARY KEY,
    FirstName VARCHAR2(50) NOT NULL,
    LastName VARCHAR2(50) NOT NULL,
    Email VARCHAR2(100) UNIQUE NOT NULL,
    Phone VARCHAR2(15),
    Role VARCHAR2(20) CHECK (Role IN ('Student','Staff'))
)
";

// Table 2: DEPARTMENT
$sql_department = "
CREATE TABLE Department (
    Department VARCHAR2(50) PRIMARY KEY,
    Salary NUMBER(10,2) CHECK (Salary >= 0)
)
";

// Table 3: STAFF
$sql_staff = "
CREATE TABLE Staff (
    StaffID NUMBER PRIMARY KEY,
    Department VARCHAR2(50) NOT NULL,
    Position VARCHAR2(50),
    HireDate DATE DEFAULT SYSDATE,
    CONSTRAINT fk_staff_user FOREIGN KEY (StaffID)
        REFERENCES Users(UserID) ON DELETE CASCADE,
    CONSTRAINT fk_staff_department FOREIGN KEY (Department)
        REFERENCES Department(Department)
)
";

// Table 4: STUDENT
$sql_student = "
CREATE TABLE Student (
    StudentID NUMBER PRIMARY KEY,
    Major VARCHAR2(50),
    YearLevel NUMBER(1) CHECK (YearLevel BETWEEN 1 AND 5),
    GPA NUMBER(3,2) CHECK (GPA BETWEEN 0 AND 4),
    CONSTRAINT fk_student_user FOREIGN KEY (StudentID)
        REFERENCES Users(UserID) ON DELETE CASCADE
)
";

// Table 5: PRODUCT
$sql_product = "
CREATE TABLE Product (
    ProductID NUMBER PRIMARY KEY,
    Name VARCHAR2(100) NOT NULL,
    Description VARCHAR2(255),
    Price NUMBER(10,2) CHECK (Price > 0),
    StockQuantity NUMBER CHECK (StockQuantity >= 0)
)
";

// Table 6: ORDERS
$sql_orders = "
CREATE TABLE Orders (
    OrderID NUMBER PRIMARY KEY,
    UserID NUMBER NOT NULL,
    OrderDate DATE DEFAULT SYSDATE,
    Status VARCHAR2(20) CHECK (Status IN ('Pending','Completed','Cancelled')),
    CONSTRAINT fk_orders_user FOREIGN KEY (UserID)
        REFERENCES Users(UserID) ON DELETE CASCADE
)
";

// Table 7: ORDER_PRODUCT
$sql_order_product = "
CREATE TABLE Order_Product (
    OrderID NUMBER NOT NULL,
    ProductID NUMBER NOT NULL,
    Quantity NUMBER NOT NULL CHECK (Quantity > 0),
    CONSTRAINT pk_order_product PRIMARY KEY (OrderID, ProductID),
    CONSTRAINT fk_op_order FOREIGN KEY (OrderID)
        REFERENCES Orders(OrderID) ON DELETE CASCADE,
    CONSTRAINT fk_op_product FOREIGN KEY (ProductID)
        REFERENCES Product(ProductID)
)
";

// Table 8: PAYMENT
$sql_payment = "
CREATE TABLE Payment (
    PaymentID NUMBER PRIMARY KEY,
    OrderID NUMBER NOT NULL,
    Amount NUMBER(10,2) NOT NULL CHECK (Amount > 0),
    PaymentDate DATE DEFAULT SYSDATE,
    PaymentMethod VARCHAR2(20) CHECK (PaymentMethod IN ('Credit Card','Debit Card','Cash')),
    CONSTRAINT fk_payment_order FOREIGN KEY (OrderID)
        REFERENCES Orders(OrderID) ON DELETE CASCADE
)
";

// Table 9: REPORT
$sql_report = "
CREATE TABLE Report (
    ReportID NUMBER PRIMARY KEY,
    StaffID NUMBER NOT NULL,
    ReportType VARCHAR2(50) NOT NULL,
    GeneratedDate DATE DEFAULT SYSDATE,
    CONSTRAINT fk_report_staff FOREIGN KEY (StaffID)
        REFERENCES Staff(StaffID) ON DELETE CASCADE
)
";

// Table 10: REVIEW
$sql_review = "
CREATE TABLE Review (
    ReviewID NUMBER PRIMARY KEY,
    UserID NUMBER NOT NULL,
    ProductID NUMBER NOT NULL,
    Rating NUMBER CHECK (Rating BETWEEN 1 AND 5),
    ReviewComment VARCHAR2(255),
    ReviewDate DATE DEFAULT SYSDATE,
    CONSTRAINT fk_review_user FOREIGN KEY (UserID)
        REFERENCES Users(UserID) ON DELETE CASCADE,
    CONSTRAINT fk_review_product FOREIGN KEY (ProductID)
        REFERENCES Product(ProductID)
)
";

// Table 11: RETURNREQUEST
$sql_return = "
CREATE TABLE ReturnRequest (
    ReturnID NUMBER PRIMARY KEY,
    OrderID NUMBER NOT NULL,
    ProductID NUMBER NOT NULL,
    RequestDate DATE DEFAULT SYSDATE,
    Status VARCHAR2(20) CHECK (Status IN ('Pending','Approved','Rejected')),
    CONSTRAINT fk_return_order FOREIGN KEY (OrderID)
        REFERENCES Orders(OrderID) ON DELETE CASCADE,
    CONSTRAINT fk_return_product FOREIGN KEY (ProductID)
        REFERENCES Product(ProductID)
)
";

// View 1: Staff Report Summary
$sql_view1 = "
CREATE OR REPLACE VIEW Staff_Report_Summary AS
SELECT s.StaffID,
       u.FirstName || ' ' || u.LastName AS StaffName,
       COUNT(r.ReportID) AS ReportCount,
       MAX(r.GeneratedDate) AS LastGenerated
FROM Staff s
JOIN Users u ON s.StaffID = u.UserID
LEFT JOIN Report r ON s.StaffID = r.StaffID
GROUP BY s.StaffID, u.FirstName, u.LastName
";

// View 2: Top Rated Products
$sql_view2 = "
CREATE OR REPLACE VIEW VW_TOP_RATED_PRODUCTS AS
SELECT p.ProductID,
       p.Name AS ProductName,
       AVG(r.Rating) AS AvgRating,
       COUNT(r.ReviewID) AS ReviewCount
FROM Product p
JOIN Review r ON p.ProductID = r.ProductID
GROUP BY p.ProductID, p.Name
HAVING COUNT(r.ReviewID) >= 1
ORDER BY AvgRating DESC
";

// View 3: Sales Summary
$sql_view3 = "
CREATE OR REPLACE VIEW VW_SALES_SUMMARY AS
SELECT p.ProductID,
       p.Name AS ProductName,
       SUM(op.Quantity) AS TotalUnitsSold,
       SUM(op.Quantity * p.Price) AS TotalRevenue
FROM Product p
JOIN Order_Product op ON p.ProductID = op.ProductID
GROUP BY p.ProductID, p.Name
ORDER BY TotalRevenue DESC
";

// Execute table creation
$tables = [
    'Users' => $sql_users,
    'Department' => $sql_department,
    'Staff' => $sql_staff,
    'Student' => $sql_student,
    'Product' => $sql_product,
    'Orders' => $sql_orders,
    'Order_Product' => $sql_order_product,
    'Payment' => $sql_payment,
    'Report' => $sql_report,
    'Review' => $sql_review,
    'ReturnRequest' => $sql_return
];

foreach ($tables as $name => $sql) {
    $stmt = oci_parse($conn, $sql);
    if (@oci_execute($stmt)) {
        $messages[] = ['type' => 'success', 'text' => "✓ Table $name created successfully"];
    } else {
        $error = oci_error($stmt);
        if ($error['code'] == 955) { // ORA-00955: name is already used
            $messages[] = ['type' => 'info', 'text' => "ℹ Table $name already exists"];
        } else {
            $messages[] = ['type' => 'error', 'text' => "✗ Failed to create table $name: " . $error['message']];
        }
    }
}

// Execute view creation
$views = [
    'Staff_Report_Summary' => $sql_view1,
    'VW_TOP_RATED_PRODUCTS' => $sql_view2,
    'VW_SALES_SUMMARY' => $sql_view3
];

foreach ($views as $name => $sql) {
    $stmt = oci_parse($conn, $sql);
    if (@oci_execute($stmt)) {
        $messages[] = ['type' => 'success', 'text' => "✓ View $name created successfully"];
    } else {
        $error = oci_error($stmt);
        if ($error['code'] == 955) {
            $messages[] = ['type' => 'info', 'text' => "ℹ View $name already exists (replaced)"];
        } else {
            $messages[] = ['type' => 'error', 'text' => "✗ Failed to create view $name: " . $error['message']];
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
    <title>Create Tables - CPS510 A9</title>
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
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
        }
        .message.success {
            background: #f0fdf4;
            border-left-color: #22c55e;
        }
        .message.error {
            background: #fef2f2;
            border-left-color: #ef4444;
        }
        .message.info {
            background: #fef3c7;
            border-left-color: #f59e0b;
        }
        .back-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .stats h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Create Database Tables</h1>
        
        <div class="stats">
            <h3>Schema Summary</h3>
            <p><strong>11 Tables</strong> + <strong>3 Views</strong> created</p>
            <p>Normalized to 3NF/BCNF</p>
        </div>

        <?php foreach ($messages as $message): ?>
            <div class="message <?php echo $message['type']; ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endforeach; ?>

        <div style="text-align: center;">
            <a href="index.php" class="back-btn">← Back to Menu</a>
            <a href="populate.php" class="back-btn">Next: Populate Tables →</a>
        </div>
    </div>
</body>
</html>
