<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$conn = getDBConnection();
$product_id = $_GET['id'];

$sql = "DELETE FROM Product WHERE ProductID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $product_id);
oci_execute($stmt);
oci_commit($conn);
oci_close($conn);

header('Location: products.php');
exit;
?>
