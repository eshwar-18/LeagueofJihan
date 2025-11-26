<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: staff.php');
    exit;
}

$conn = getDBConnection();
$staff_id = $_GET['id'];

$sql = "DELETE FROM Staff WHERE StaffID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $staff_id);
oci_execute($stmt);
oci_commit($conn);
oci_close($conn);

header('Location: staff.php');
exit;
?>
