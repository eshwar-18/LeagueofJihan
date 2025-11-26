<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$conn = getDBConnection();
$order_id = $_GET['id'];

$sql = "DELETE FROM Orders WHERE OrderID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $order_id);
oci_execute($stmt);
oci_commit($conn);
oci_close($conn);

header('Location: orders.php');
exit;
?>
