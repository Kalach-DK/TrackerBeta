<?php
include 'db.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST') exit('OK');

$name = $_POST['name'] ?? '';
$api_key = $_POST['api_key'] ?? null;

$stmt = $conn->prepare("INSERT INTO traffic_sources (name, api_key) VALUES (?, ?)");
$stmt->bind_param("ss", $name, $api_key);
$stmt->execute();

header('Location: /tracker/traffic_sources.php');
exit;