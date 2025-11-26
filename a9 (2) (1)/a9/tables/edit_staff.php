<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: staff.php');
    exit;
}

$conn = getDBConnection();
$staff_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE Staff SET Department = :dept, Position = :pos, HireDate = TO_DATE(:hdate, 'YYYY-MM-DD') WHERE StaffID = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':dept', $_POST['department']);
    oci_bind_by_name($stmt, ':pos', $_POST['position']);
    oci_bind_by_name($stmt, ':hdate', $_POST['hire_date']);
    oci_bind_by_name($stmt, ':id', $staff_id);
    oci_execute($stmt);
    oci_commit($conn);
    oci_close($conn);
    header('Location: staff.php');
    exit;
}

$sql = "SELECT s.*, d.Department FROM Staff s JOIN Department d ON s.Department = d.Department WHERE s.StaffID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $staff_id);
oci_execute($stmt);
$staff = oci_fetch_array($stmt, OCI_ASSOC);

$dept_sql = "SELECT Department FROM Department ORDER BY Department";
$dept_result = executeQuery($conn, $dept_sql);
$departments = [];
while ($row = oci_fetch_array($dept_result, OCI_ASSOC)) {
    $departments[] = $row['DEPARTMENT'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Staff</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 40px; }
        h1 { color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; margin-right: 10px; }
        .back-btn { background: #6c757d; display: inline-block; padding: 12px 30px; color: white; text-decoration: none; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Staff</h1>
        <form method="POST">
            <div class="form-group">
                <label>Department:</label>
                <select name="department" required>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo $staff['DEPARTMENT'] == $dept ? 'selected' : ''; ?>><?php echo htmlspecialchars($dept); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Position:</label>
                <input type="text" name="position" value="<?php echo htmlspecialchars($staff['POSITION']); ?>" required>
            </div>
            <div class="form-group">
                <label>Hire Date:</label>
                <input type="date" name="hire_date" value="<?php echo htmlspecialchars(substr($staff['HIREDATE'], 0, 10)); ?>" required>
            </div>
            <button type="submit">Update Staff</button>
            <a href="staff.php" class="back-btn">Cancel</a>
        </form>
    </div>
</body>
</html>
<?php oci_close($conn); ?>
