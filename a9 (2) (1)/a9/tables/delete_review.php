<?php
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: reviews.php');
    exit;
}

$conn = getDBConnection();
$review_id = $_GET['id'];

$sql = "DELETE FROM Review WHERE ReviewID = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $review_id);
oci_execute($stmt);
oci_commit($conn);
oci_close($conn);

header('Location: reviews.php');
exit;
?>
