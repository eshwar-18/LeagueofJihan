<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$conn = getDBConnection();
$user_id = $_GET['id'];

$sql = "DELETE FROM Users WHERE UserID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $user_id);
oci_execute($stmt);
oci_commit($conn);
oci_close($conn);

header('Location: users.php');
exit;
?>
