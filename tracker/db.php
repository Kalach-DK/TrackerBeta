<?php
// db.php - update these with Hostinger values
$DB_HOST = 'localhost';
$DB_USER = 'u491781300_Admin';
$DB_PASS = 'Gk46hdsp';
$DB_NAME = 'u491781300_Database';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
  die("DB_CONN_ERROR: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>