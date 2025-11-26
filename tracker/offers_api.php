<?php
include 'db.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') exit('OK');

$name = $_POST['name'] ?? '';
$ap = $_POST['affiliate_program_id'] ?: null;
$country = $_POST['country'] ?? null;
$affiliate_link = $_POST['affiliate_link'] ?? '';
$article_urls = $_POST['article_urls'] ?? []; // array of urls

$article_json = !empty($article_urls) ? json_encode(array_values($article_urls)) : null;

$stmt = $conn->prepare("INSERT INTO offers (name, affiliate_program_id, country, affiliate_link, article_urls) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sisss", $name, $ap, $country, $affiliate_link, $article_json);
$stmt->execute();

header('Location: /tracker/offers.php');
exit;