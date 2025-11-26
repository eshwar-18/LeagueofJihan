<?php
/**
 * CPS510 A9 - Users CRUD Page (with Search)
 */
require_once '../config.php';

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle form submissions
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $sql = "INSERT INTO Users (UserID, FirstName, LastName, Email, Phone, Role) 
                    VALUES (:v1, :v2, :v3, :v4, :v5, :v6)";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ':v1', $_POST['user_id']);
            oci_bind_by_name($stmt, ':v2', $_POST['first_name']);
            oci_bind_by_name($stmt, ':v3', $_POST['last_name']);
            oci_bind_by_name($stmt, ':v4', $_POST['email']);
            oci_bind_by_name($stmt, ':v5', $_POST['phone']);
            oci_bind_by_name($stmt, ':v6', $_POST['role']);
            
            if (@oci_execute($stmt)) {
                oci_commit($conn);
                $success_message = "✓ User added successfully!";
            } else {
                $error = oci_error($stmt);
                if ($error['code'] == 1) {
                    $error_message = "Error: User ID {$_POST['user_id']} or Email already exists!";
                } elseif ($error['code'] == 1400) {
                    $error_message = "Error: Required fields cannot be empty!";
                } elseif ($error['code'] == 2290) {
                    $error_message = "Error: Invalid role! Must be 'Student' or 'Staff'.";
                } else {
                    $error_message = "Error: " . $error['message'];
                }
            }
        }
    }
    oci_close($conn);
    if (empty($error_message) && empty($success_message)) {
        header('Location: users.php');
        exit;
    }
}

// Fetch all users + search filter
$conn = getDBConnection();

$sql = "
    SELECT * FROM Users
    WHERE 
        LOWER(FirstName) LIKE LOWER(:s) OR
        LOWER(LastName) LIKE LOWER(:s) OR
        LOWER(Email) LIKE LOWER(:s) OR
        TO_CHAR(UserID) LIKE :s
    ORDER BY UserID
";

$stmt = oci_parse($conn, $sql);
$search_param = '%' . $search . '%';
oci_bind_by_name($stmt, ':s', $search_param);
oci_execute($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
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

        /* Search Bar */
        .search-bar {
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
        }
        .search-bar input {
            padding: 10px;
            flex: 1;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .search-bar button {
            padding: 10px 20px;
            background: #764ba2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .form-group { margin-bottom: 15px; }
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
        button:hover { opacity: 0.9; }
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
    </style>
</head>
<body>
    <div class="container">
        <h1>👥 Manage Users</h1>

        <?php if ($error_message): ?>
            <div class="error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <h2>Add New User</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label>User ID:</label>
                <input type="number" name="user_id" required>
            </div>
            <div class="form-group">
                <label>First Name:</label>
                <input type="text" name="first_name" required>
            </div>
            <div class="form-group">
                <label>Last Name:</label>
                <input type="text" name="last_name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Phone:</label>
                <input type="text" name="phone">
            </div>
            <div class="form-group">
                <label>Role:</label>
                <select name="role" required>
                    <option value="Student">Student</option>
                    <option value="Staff">Staff</option>
                </select>
            </div>
            <button type="submit">Add User</button>
        </form>

        <!-- SEARCH BAR -->
        <h2 style="margin-top: 40px;">Search & View Users</h2>
        <form class="search-bar" method="GET">
            <input type="text" name="search" placeholder="Search by ID, name, or email..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
            <?php if ($search): ?>
                <a href="users.php" style="margin-left: 10px; padding: 10px 20px; background: #6c757d; color: white; border-radius: 8px; text-decoration: none;">Clear</a>
            <?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_array($stmt, OCI_ASSOC)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['USERID']) ?></td>
                    <td><?= htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['LASTNAME']) ?></td>
                    <td><?= htmlspecialchars($row['EMAIL']) ?></td>
                    <td><?= htmlspecialchars($row['PHONE']) ?></td>
                    <td><?= htmlspecialchars($row['ROLE']) ?></td>
                    <td class="actions">
                        <a href="edit_user.php?id=<?= $row['USERID'] ?>">Edit</a>
                        <a href="delete_user.php?id=<?= $row['USERID'] ?>" onclick="return confirm('Delete this user?')">Delete</a>
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
