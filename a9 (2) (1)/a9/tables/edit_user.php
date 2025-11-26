<?php
require_once '../config.php';
session_start();

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$conn = getDBConnection();
$user_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE Users SET FirstName = :fname, LastName = :lname, Email = :email, Phone = :phone, Role = :role WHERE UserID = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':fname', $_POST['first_name']);
    oci_bind_by_name($stmt, ':lname', $_POST['last_name']);
    oci_bind_by_name($stmt, ':email', $_POST['email']);
    oci_bind_by_name($stmt, ':phone', $_POST['phone']);
    oci_bind_by_name($stmt, ':role', $_POST['role']);
    oci_bind_by_name($stmt, ':id', $user_id);
    oci_execute($stmt);
    oci_commit($conn);
    oci_close($conn);
    header('Location: users.php');
    exit;
}

$sql = "SELECT * FROM Users WHERE UserID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $user_id);
oci_execute($stmt);
$user = oci_fetch_array($stmt, OCI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
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
        <h1>Edit User</h1>
        <form method="POST">
            <div class="form-group">
                <label>First Name:</label>
                <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['FIRSTNAME']); ?>" required>
            </div>
            <div class="form-group">
                <label>Last Name:</label>
                <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['LASTNAME']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['EMAIL']); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone:</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['PHONE']); ?>">
            </div>
            <div class="form-group">
                <label>Role:</label>
                <select name="role" required>
                    <option value="Student" <?php echo $user['ROLE'] == 'Student' ? 'selected' : ''; ?>>Student</option>
                    <option value="Staff" <?php echo $user['ROLE'] == 'Staff' ? 'selected' : ''; ?>>Staff</option>
                </select>
            </div>
            <button type="submit">Update User</button>
            <a href="users.php" class="back-btn">Cancel</a>
        </form>
    </div>
</body>
</html>
<?php oci_close($conn); ?>
