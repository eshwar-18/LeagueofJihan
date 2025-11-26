<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: returns.php');
    exit;
}

$conn = getDBConnection();
$return_id = $_GET['id'];

$sql = "DELETE FROM ReturnRequest WHERE ReturnID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $return_id);
oci_execute($stmt);
oci_commit($conn);
oci_close($conn);

header('Location: returns.php');
exit;
?>
