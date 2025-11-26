<?php
// postback.php (robust, debug-friendly)

include 'db.php';

// optional secret check (uncomment if you want)
// $secret = $_GET['secret'] ?? '';
// if ($secret !== 'Kj496upR') die('Forbidden');

// 1) Capture all plausible incoming parameters (be flexible)
$tracker_click_id = $_GET['tracker_click_id'] ?? $_GET['racker_click_id'] ?? $_GET['clickid'] ?? $_GET['sub_id'];   // what you configured: tracker_click_id={SUB_ID}
$payout            = isset($_GET['payout']) ? floatval($_GET['payout']) : (isset($_GET['amount']) ? floatval($_GET['amount']) : 0);
$status            = ($_GET['STATE'] ?? 'unknown');

// 2) Debug logging (temporary) - saves raw request to file so you can inspect what network actually sent
$logLine = date('Y-m-d H:i:s') . " | " . $_SERVER['REMOTE_ADDR'] . " | " . json_encode($_GET) . PHP_EOL;
file_put_contents(__DIR__ . '/postback_incoming.log', $logLine, FILE_APPEND);

// 3) Find the click row
$click_row = null;

// If affiliate sends our internal numeric tracker id (tracker_click_id), accept both numeric and string
if (!empty($tracker_click_id)) {
    $stmt = $conn->prepare("SELECT * FROM clicks WHERE click_id = ?");
    $stmt->bind_param("s", $tracker_click_id);
    $stmt->execute();
    $g = $stmt->get_result();
    $click_row = $g->fetch_assoc();
}


// If nothing found and external_clickid provided, try lookup by click_id column
if (!$click_row && $external_clickid) {
    // always treat external IDs as strings
    $stmt = $conn->prepare("SELECT * FROM clicks WHERE click_id = ?");
    $stmt->bind_param("s", $external_clickid);
    $stmt->execute();
    $g = $stmt->get_result();
    $click_row = $g->fetch_assoc();
}

// 4) If still not found, log and return OK (or return error if you prefer)
if (!$click_row) {
    // write a debug line so you can inspect mismatches
    file_put_contents(__DIR__ . '/postback_missing.log', date('Y-m-d H:i:s') . " | NOT FOUND | " . json_encode($_GET) . PHP_EOL, FILE_APPEND);
    // respond 200 (many networks expect OK) but give small message so you can test in browser
    http_response_code(200);
    echo "Click not found";
    exit;
}

// 5) We have a matching click row — extract ids
$campaign_id = $click_row['campaign_id'] ?? null;
$offer_id    = $click_row['offer_id'] ?? null;
$db_click_pk = $click_row['id'];           // DB primary key (int)
$db_click_id = $click_row['tracker_click_id'] ?? null; // external click token you stored

// 6) Insert conversion (use prepared stmt)
$raw = json_encode($_REQUEST);
$ins = $conn->prepare("
  INSERT INTO conversions (click_id, campaign_id, offer_id, payout, status, raw_params)
  VALUES (?, ?, ?, ?, ?, ?)
");
$ins->bind_param("iiidss", $db_click_id, $campaign_id, $offer_id, $payout, $status, $raw);
$ins->execute();

// 7) Update clicks table payout/status (so reports show it)
$upd = $conn->prepare("UPDATE clicks SET payout = ?, status = ? WHERE id = ?");
$upd->bind_param("dsi", $payout, $status, $db_click_id);
$upd->execute();

// 8) Log success (optional)
file_put_contents(__DIR__ . '/postback_ok.log', date('Y-m-d H:i:s') . " | OK | click_id_db={$db_click_id} | click_id_token=" . ($db_click_pk ?: '-') . " | " . json_encode($_GET) . PHP_EOL, FILE_APPEND);

// 9) Respond OK to affiliate
echo "OK";
