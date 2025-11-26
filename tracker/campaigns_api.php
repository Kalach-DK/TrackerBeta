<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit('OK');

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = $_POST['name'] ?? '';
$traffic_source_id = $_POST['traffic_source_id'] ?: null;
$campaign_external_id = $_POST['campaign_external_id'] ?? '';
$country = $_POST['country'] ?? null;
$offers = $_POST['offers'] ?? [];
$view_limit = (int)($_POST['view_limit'] ?? 0);

$offer_ids = implode(',', array_map('intval', $offers));

if ($id > 0) {
    // --- Update existing campaign ---
    $stmt = $conn->prepare("UPDATE campaigns SET name=?, campaign_external_id=?, traffic_source_id=?, country=?, offer_ids=?, view_limit=? WHERE id=?");
    $stmt->bind_param("ssissii", $name, $campaign_external_id, $traffic_source_id, $country, $offer_ids, $view_limit, $id);
    $stmt->execute();
} else {
    // --- Insert new campaign ---
    $stmt = $conn->prepare("INSERT INTO campaigns (name, campaign_external_id, traffic_source_id, country, offer_ids, view_limit) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissi", $name, $campaign_external_id, $traffic_source_id, $country, $offer_ids, $view_limit);
    $stmt->execute();
}

header('Location: /tracker/campaigns.php');
exit;
