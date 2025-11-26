<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: students.php');
    exit;
}

$conn = getDBConnection();
$student_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "UPDATE Student SET Major = :major, YearLevel = :year, GPA = :gpa WHERE StudentID = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':major', $_POST['major']);
    oci_bind_by_name($stmt, ':year', $_POST['year']);
    oci_bind_by_name($stmt, ':gpa', $_POST['gpa']);
    oci_bind_by_name($stmt, ':id', $student_id);
    oci_execute($stmt);
    oci_commit($conn);
    oci_close($conn);
    header('Location: students.php');
    exit;
}

$sql = "SELECT * FROM Student WHERE StudentID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $student_id);
oci_execute($stmt);
$student = oci_fetch_array($stmt, OCI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 40px; }
        h1 { color: #333; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px 30px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; margin-right: 10px; }
        .back-btn { background: #6c757d; display: inline-block; padding: 12px 30px; color: white; text-decoration: none; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Student</h1>
        <form method="POST">
            <div class="form-group">
                <label>Major:</label>
                <input type="text" name="major" value="<?php echo htmlspecialchars($student['MAJOR']); ?>" required>
            </div>
            <div class="form-group">
                <label>Year Level (1-5):</label>
                <input type="number" name="year" min="1" max="5" value="<?php echo htmlspecialchars($student['YEARLEVEL']); ?>" required>
            </div>
            <div class="form-group">
                <label>GPA (0.00-4.00):</label>
                <input type="number" name="gpa" step="0.01" min="0" max="4" value="<?php echo htmlspecialchars($student['GPA']); ?>" required>
            </div>
            <button type="submit">Update Student</button>
            <a href="students.php" class="back-btn">Cancel</a>
        </form>
    </div>
</body>
</html>
<?php oci_close($conn); ?>
