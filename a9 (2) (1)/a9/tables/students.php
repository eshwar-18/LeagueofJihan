<?php
/**
 * CPS510 A9 - Students CRUD Page (with Search)
 */
require_once '../config.php';

$error_message = '';
$success_message = '';

// -----------------------------
// HANDLE ADDING STUDENT
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDBConnection();
    
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $sql = "INSERT INTO Student (StudentID, Major, YearLevel, GPA) 
                VALUES (:stid, :stmajor, :styear, :stgpa)";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ':stid', $_POST['student_id']);
        oci_bind_by_name($stmt, ':stmajor', $_POST['major']);
        oci_bind_by_name($stmt, ':styear', $_POST['year']);
        oci_bind_by_name($stmt, ':stgpa', $_POST['gpa']);
        
        if (@oci_execute($stmt)) {
            oci_commit($conn);
            $success_message = "✓ Student added successfully!";
        } else {
            $error = oci_error($stmt);
            if ($error['code'] == 1) {
                $error_message = "Error: Student ID {$_POST['student_id']} already exists!";
            } elseif ($error['code'] == 2291) {
                $error_message = "Error: Student ID must exist in Users table first!";
            } elseif ($error['code'] == 2290) {
                $error_message = "Error: Year must be 1-5 and GPA must be 0-4!";
            } else {
                $error_message = "Error: " . $error['message'];
            }
        }
    }
    oci_close($conn);

    if (empty($error_message) && empty($success_message)) {
        header('Location: students.php');
        exit;
    }
}

// -----------------------------
// HANDLE SEARCH
// -----------------------------
$search = '';
$whereClause = '';

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search = trim($_GET['search']);
    $search_like = '%' . $search . '%';

    $whereClause = "WHERE 
        LOWER(u.FirstName) LIKE LOWER(:s) OR
        LOWER(u.LastName) LIKE LOWER(:s) OR
        LOWER(u.Email) LIKE LOWER(:s) OR
        LOWER(s.Major) LIKE LOWER(:s) OR
        TO_CHAR(s.StudentID) LIKE :s";
}

$conn = getDBConnection();

$sql = "SELECT s.*, u.FirstName, u.LastName, u.Email 
        FROM Student s 
        JOIN Users u ON s.StudentID = u.UserID 
        $whereClause
        ORDER BY s.StudentID";

$stmt = oci_parse($conn, $sql);

if ($whereClause !== '') {
    oci_bind_by_name($stmt, ':s', $search_like);
}

oci_execute($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Students</title>
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
        .search-bar {
            margin: 20px 0;
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
        <h1>🎓 Manage Students</h1>

        <?php if ($error_message): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <!-- SEARCH BAR -->
        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>

        <h2>Add New Student</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Student ID (must exist in Users):</label>
                <input type="number" name="student_id" required>
            </div>
            <div class="form-group">
                <label>Major:</label>
                <input type="text" name="major" required>
            </div>
            <div class="form-group">
                <label>Year Level (1-5):</label>
                <input type="number" min="1" max="5" name="year" required>
            </div>
            <div class="form-group">
                <label>GPA (0.00-4.00):</label>
                <input type="number" step="0.01" min="0" max="4" name="gpa" required>
            </div>
            <button type="submit">Add Student</button>
        </form>

        <h2 style="margin-top: 40px;">All Students</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Major</th>
                    <th>Year</th>
                    <th>GPA</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_array($stmt, OCI_ASSOC)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['STUDENTID']); ?></td>
                    <td><?php echo htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['LASTNAME']); ?></td>
                    <td><?php echo htmlspecialchars($row['EMAIL']); ?></td>
                    <td><?php echo htmlspecialchars($row['MAJOR']); ?></td>
                    <td><?php echo htmlspecialchars($row['YEARLEVEL']); ?></td>
                    <td><?php echo number_format($row['GPA'], 2); ?></td>
                    <td class="actions">
                        <a href="edit_student.php?id=<?php echo $row['STUDENTID']; ?>">Edit</a>
                        <a href="delete_student.php?id=<?php echo $row['STUDENTID']; ?>" onclick="return confirm('Delete?')">Delete</a>
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
