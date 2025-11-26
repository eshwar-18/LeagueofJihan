<?php
/**
 * CPS510 A9 - Staff CRUD Page
 */
require_once '../config.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $sql = "INSERT INTO Staff (StaffID, Department, Position, HireDate) 
                VALUES (:sid, :sdept, :spos, TO_DATE(:shdate, 'YYYY-MM-DD'))";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':sid', $_POST['staff_id']);
        oci_bind_by_name($stmt, ':sdept', $_POST['department']);
        oci_bind_by_name($stmt, ':spos', $_POST['position']);
        oci_bind_by_name($stmt, ':shdate', $_POST['hire_date']);
        
        if (@oci_execute($stmt)) {
            oci_commit($conn);
            $success_message = "✓ Staff member added successfully!";
        } else {
            $error = oci_error($stmt);
            if ($error['code'] == 1) {
                $error_message = "Error: Staff ID {$_POST['staff_id']} already exists!";
            } elseif ($error['code'] == 2291) {
                $error_message = "Error: Staff ID must exist in Users table or invalid Department!";
            } else {
                $error_message = "Error: " . $error['message'];
            }
        }
    }
    oci_close($conn);
    if (empty($error_message) && empty($success_message)) {
        header('Location: staff.php');
        exit;
    }
}

// =========================
// SEARCH PROCESSING
// =========================

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$whereClause = "";
$params = [];

if ($search !== "") {
    $whereClause = "WHERE 
        LOWER(s.StaffID) LIKE LOWER(:search)
        OR LOWER(u.FirstName) LIKE LOWER(:search)
        OR LOWER(u.LastName) LIKE LOWER(:search)
        OR LOWER(s.Department) LIKE LOWER(:search)
        OR LOWER(s.Position) LIKE LOWER(:search)";
}

// Build SQL
$conn = getDBConnection();
$sql = "SELECT s.*, u.FirstName, u.LastName, u.Email, d.Salary 
        FROM Staff s 
        JOIN Users u ON s.StaffID = u.UserID 
        JOIN Department d ON s.Department = d.Department
        $whereClause
        ORDER BY s.StaffID";

$stmt = oci_parse($conn, $sql);

if ($search !== "") {
    $searchWildcard = "%$search%";
    oci_bind_by_name($stmt, ':search', $searchWildcard);
}

oci_execute($stmt);

// Department dropdown
$dept_sql = "SELECT Department FROM Department ORDER BY Department";
$dept_result = executeQuery($conn, $dept_sql);
$departments = [];
while ($row = oci_fetch_array($dept_result, OCI_ASSOC)) {
    $departments[] = $row['DEPARTMENT'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Staff</title>
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
        label { display: block; margin-bottom: 5px; font-weight: 600; }
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

        /* SEARCH BAR */
        .search-bar {
            margin: 25px 0;
            display: flex;
            gap: 10px;
        }
        .search-bar input {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👔 Manage Staff</h1>
        
        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <h2>Add New Staff Member</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label>Staff ID (must exist in Users):</label>
                <input type="number" name="staff_id" required>
            </div>

            <div class="form-group">
                <label>Department:</label>
                <select name="department" required>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept); ?>">
                            <?php echo htmlspecialchars($dept); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Position:</label>
                <input type="text" name="position" required>
            </div>

            <div class="form-group">
                <label>Hire Date:</label>
                <input type="date" name="hire_date" required>
            </div>

            <button type="submit">Add Staff</button>
        </form>

        <!-- SEARCH BAR -->
        <h2 style="margin-top: 40px;">Search Staff</h2>

        <form method="GET" class="search-bar">
            <input 
                type="text" 
                name="search" 
                placeholder="Search by ID, Name, Department, Position..."
                value="<?php echo htmlspecialchars($search); ?>"
            >
            <button type="submit">Search</button>
        </form>

        <h2>All Staff</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Salary</th>
                    <th>Hire Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_array($stmt, OCI_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['STAFFID']); ?></td>
                    <td><?php echo htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['LASTNAME']); ?></td>
                    <td><?php echo htmlspecialchars($row['DEPARTMENT']); ?></td>
                    <td><?php echo htmlspecialchars($row['POSITION']); ?></td>
                    <td>$<?php echo number_format($row['SALARY'], 2); ?></td>
                    <td><?php echo htmlspecialchars(substr($row['HIREDATE'], 0, 10)); ?></td>
                    <td class="actions">
                        <a href="edit_staff.php?id=<?php echo $row['STAFFID']; ?>">Edit</a>
                        <a href="delete_staff.php?id=<?php echo $row['STAFFID']; ?>" onclick="return confirm('Delete?')">Delete</a>
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
