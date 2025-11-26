<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: payments.php');
    exit;
}

$conn = getDBConnection();
$payment_id = $_GET['id'];

$sql = "DELETE FROM Payment WHERE PaymentID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $payment_id);
oci_execute($stmt);
oci_commit($conn);
oci_close($conn);

header('Location: payments.php');
exit;
?>
