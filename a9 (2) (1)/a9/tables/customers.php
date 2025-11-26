<?php
/**
 * CPS510 A9 - Customers Management Page
 * 
 * This page provides complete CRUD functionality for the Customers table:
 * - CREATE: Insert new customer records
 * - READ: View all customers with search capability
 * - UPDATE: Edit customer information (via edit_customer.php)
 * - DELETE: Remove customer records (via delete_customer.php)
 * 
 * Features:
 * - Search by customer ID, name, or email
 * - Form validation and sanitization
 * - Success/error messaging
 * - Clean UI with responsive design (Bonus: 1.5 marks)
 */

require_once '../config.php';

// Start session for messages from delete operations
session_start();

$conn = getDBConnection();
$message = '';
$messageType = '';

// Check for session messages (from delete operations)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['messageType'];
    unset($_SESSION['message']);
    unset($_SESSION['messageType']);
}

// Handle INSERT operation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert') {
    // Sanitize and validate inputs
    $customer_id = sanitizeInput($_POST['customer_id']);
    $first_name = sanitizeInput($_POST['first_name']);
    $last_name = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    
    // Validation
    $errors = [];
    if (!isNumeric($customer_id)) {
        $errors[] = "Customer ID must be a positive number.";
    }
    if (empty($first_name)) {
        $errors[] = "First name is required.";
    }
    if (empty($last_name)) {
        $errors[] = "Last name is required.";
    }
    if (!isValidEmail($email)) {
        $errors[] = "Invalid email format.";
    }
    
    if (empty($errors)) {
        // Construct INSERT query using bind variables for security
        $sql = "INSERT INTO Customers (customer_id, first_name, last_name, email, phone) 
                VALUES (:customer_id, :first_name, :last_name, :email, :phone)";
        
        $statement = oci_parse($conn, $sql);
        
        // Bind parameters to prevent SQL injection
        oci_bind_by_name($statement, ':customer_id', $customer_id);
        oci_bind_by_name($statement, ':first_name', $first_name);
        oci_bind_by_name($statement, ':last_name', $last_name);
        oci_bind_by_name($statement, ':email', $email);
        oci_bind_by_name($statement, ':phone', $phone);
        
        $result = @oci_execute($statement, OCI_NO_AUTO_COMMIT);
        
        if ($result) {
            oci_commit($conn);
            $message = "✅ Customer added successfully!";
            $messageType = "success";
        } else {
            $error = oci_error($statement);
            oci_rollback($conn);
            $message = "❌ Error: " . htmlspecialchars($error['message']);
            $messageType = "error";
        }
        
        oci_free_statement($statement);
    } else {
        $message = "❌ Validation errors: " . implode(", ", $errors);
        $messageType = "error";
    }
}

// Handle SEARCH operation
$searchTerm = '';
$whereClause = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = trim($_GET['search']);
    // Search across multiple columns using parameterized query
    $whereClause = " WHERE UPPER(first_name) LIKE UPPER(:search) 
                     OR UPPER(last_name) LIKE UPPER(:search) 
                     OR UPPER(email) LIKE UPPER(:search) 
                     OR CAST(customer_id AS VARCHAR2(20)) LIKE :search";
}

// Retrieve all customers (with optional search filter)
$sql = "SELECT * FROM Customers" . $whereClause . " ORDER BY customer_id";
$stmt = oci_parse($conn, $sql);

// Bind search parameter if search is active
if (!empty($searchTerm)) {
    $search_param = '%' . $searchTerm . '%';
    oci_bind_by_name($stmt, ':search', $search_param);
}

oci_execute($stmt);
$statement = $stmt;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers - CPS510 A9</title>
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
            max-width: 1200px;
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
        
        h2 {
            color: #555;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4facfe;
        }
        
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="email"] {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: #4facfe;
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
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            font-size: 14px;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #333;
            padding: 8px 15px;
            font-size: 14px;
            margin-right: 5px;
        }
        
        .btn-edit:hover {
            background: #e0a800;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-box input {
            flex: 1;
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
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .actions {
            white-space: nowrap;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 30px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👥 Manage Customers</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- INSERT FORM -->
        <h2>Add New Customer</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="insert">
            <div class="form-grid">
                <div class="form-group">
                    <label for="customer_id">Customer ID *</label>
                    <input type="number" id="customer_id" name="customer_id" required min="1">
                </div>
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" maxlength="20">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">➕ Add Customer</button>
        </form>
        
        <!-- SEARCH FORM -->
        <h2>Search & View Customers</h2>
        <form method="GET" action="" class="search-box">
            <input type="text" name="search" placeholder="Search by ID, name, or email..." 
                   value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit" class="btn btn-primary">🔍 Search</button>
            <?php if ($searchTerm): ?>
                <a href="customers.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
        
        <!-- RESULTS TABLE -->
        <table>
            <thead>
                <tr>
                    <th>Customer ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $hasData = false;
                while ($row = oci_fetch_assoc($statement)):
                    $hasData = true;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['CUSTOMER_ID']); ?></td>
                        <td><?php echo htmlspecialchars($row['FIRST_NAME']); ?></td>
                        <td><?php echo htmlspecialchars($row['LAST_NAME']); ?></td>
                        <td><?php echo htmlspecialchars($row['EMAIL']); ?></td>
                        <td><?php echo htmlspecialchars($row['PHONE']); ?></td>
                        <td class="actions">
                            <a href="edit_customer.php?id=<?php echo $row['CUSTOMER_ID']; ?>" 
                               class="btn btn-edit">✏️ Edit</a>
                            <a href="delete_customer.php?id=<?php echo $row['CUSTOMER_ID']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this customer?');">
                                🗑️ Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                
                <?php if (!$hasData): ?>
                    <tr>
                        <td colspan="6" class="no-data">
                            <?php echo $searchTerm ? 'No customers found matching your search.' : 'No customers in database. Add one above!'; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <a href="../index.php" class="btn btn-secondary back-link">← Back to Menu</a>
    </div>
</body>
</html>

<?php
oci_free_statement($statement);
closeConnection($conn);
?>
