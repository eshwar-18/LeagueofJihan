<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: students.php');
    exit;
}

$conn = getDBConnection();
$student_id = $_GET['id'];

$sql = "DELETE FROM Student WHERE StudentID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $student_id);
oci_execute($stmt);
oci_commit($conn);
oci_close($conn);

header('Location: students.php');
exit;
?>
