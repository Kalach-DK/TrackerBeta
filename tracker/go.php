<?php
include 'db.php';

function logDebug($message) {
    $logFile = __DIR__ . '/propeller_debug.log'; // file will appear in same folder as this PHP file
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// --- 1️⃣ Required: get traffic source click ID (PropellerAds) ---
$click_id = $_GET['clickid'] ?? '';  // Propeller click ID
if (!$click_id) die("Missing clickid");

// --- 2️⃣ Campaign external ID ---
if (!isset($_GET['cid'])) die("No campaign specified");
$cid = $_GET['cid'];

// --- 3️⃣ Optional tracking macros ---
$zoneid     = $_GET['zoneid']     ?? null;
$campaignid = $_GET['campaignid'] ?? null;
$bannerid   = $_GET['bannerid']   ?? null;
$os         = $_GET['os']         ?? null;
$browser    = $_GET['browser']    ?? null;
$cost       = $_GET['cost']       ?? 0;

// --- 4️⃣ Find campaign ---
$stmt = $conn->prepare("SELECT * FROM campaigns WHERE campaign_external_id = ?");
$stmt->bind_param("s", $cid);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) die("Campaign not found");
$campaign = $res->fetch_assoc();

function propellerApiRequest($method, $endpoint, $data = null, $conn) {
    $query = $conn->query("SELECT api_key FROM traffic_sources WHERE name = 'PropellerAds' LIMIT 1");
    $keyRow = $query ? $query->fetch_assoc() : null;
if (!$keyRow || empty($keyRow['api_key'])) {
    logDebug("❌ Propeller API key not found in traffic_sources");
    return false;
}

    $apiKey = trim($keyRow['api_key']);
    $url = "https://ssp-api.propellerads.com/v5/$endpoint";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ]);
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

if ($error) {
    logDebug("❌ CURL error: $error");
}

logDebug("🔍 Propeller API [$endpoint] HTTP $httpcode | Response: $response");


    return [
        'code' => $httpcode,
        'response' => json_decode($response, true)
    ];
}


if ($campaign['view_limit'] > 0 && $campaign['current_views'] >= $campaign['view_limit']) {
    $externalId = (int)$campaign['campaign_external_id'];

    // --- ✅ Correct API call to stop campaign ---
    $result = propellerApiRequest(
        'PUT',
        'adv/campaigns/stop',
        [
            'campaign_ids' => [ "$externalId" ]
        ],
        $conn
    );

    // Log the API response
logDebug("Stopped PropellerAds campaign {$externalId}: " . json_encode($result));


    // --- Redirect visitor to fallback ---
   // header("Location: https://www.google.com");
    //exit;
}



// --- 6️⃣ Pick random offer ---
$offer_ids = array_filter(array_map('intval', explode(',', $campaign['offer_ids'])));
if (empty($offer_ids)) die("No offers in campaign");

$rand_offer_id = $offer_ids[array_rand($offer_ids)];
$offerRes = $conn->query("SELECT * FROM offers WHERE id = ".$rand_offer_id);
$offer = $offerRes->fetch_assoc();
if (!$offer) die("Offer not found");

// --- 7️⃣ Determine redirect link ---
$final_url = $offer['affiliate_link'];
if (!empty($offer['article_urls'])) {
  $list = json_decode($offer['article_urls'], true);
  if (is_array($list) && count($list) > 0) {
    $final_url = $list[array_rand($list)];
  }
}

// --- 8️⃣ Log click using Propeller clickid as main ID ---
$ip = $_SERVER['REMOTE_ADDR'];
$ua = $_SERVER['HTTP_USER_AGENT'];
$campaignId = $campaign['id'];

$stmt = $conn->prepare("
  INSERT INTO clicks 
  (click_id, campaign_id, offer_id, ip, user_agent, zoneid, campaignid, bannerid, os, browser, cost, created_at)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("siisssssssd", $click_id, $campaignId, $rand_offer_id, $ip, $ua, $zoneid, $campaignid, $bannerid, $os, $browser, $cost);
$stmt->execute();

// --- 9️⃣ Increment campaign counter ---
$conn->query("UPDATE campaigns SET current_views = current_views + 1 WHERE id = ".$campaignId);

// --- 🔟 Append your click ID to Oponia offer link as sub_id ---
// Fetch affiliate program name
$affStmt = $conn->prepare("SELECT name FROM affiliate_programs WHERE id = ?");
$affStmt->bind_param("i", $offer['affiliate_program_id']);
$affStmt->execute();
$affResult = $affStmt->get_result();
$affiliate = $affResult->fetch_assoc();
$affiliate_name = strtolower(trim($affiliate['name'] ?? ''));

// --- 🔟 Append tracking parameter ---
$append = (strpos($final_url, '?') === false) ? '?' : '&';

if ($affiliate_name === 'yieldkit') {
    // YieldKit tracking
    $final_url .= $append . "yk_tag=" . urlencode($click_id);
} else {
    // Default Oponia tracking
    $final_url .= $append . "placementId=" . urlencode($click_id);
}



// --- 🔁 Redirect to Oponia ---
header("Location: ".$final_url);
exit;




?>
