<?php
include 'db.php';

// --- Fetch PropellerAds API key ---
$query = $conn->query("SELECT api_key FROM traffic_sources WHERE name = 'PropellerAds' LIMIT 1");
$keyRow = $query ? $query->fetch_assoc() : null;
if (!$keyRow || empty($keyRow['api_key'])) {
    error_log("[CRON] ❌ Propeller API key not found.");
    exit;
}
$apiKey = trim($keyRow['api_key']);

// --- Function to call PropellerAds API ---
function propellerApiRequest($method, $endpoint, $data, $apiKey) {
    $url = "https://ssp-api.propellerads.com/v5/$endpoint";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'response' => json_decode($response, true)];
}

// --- Step 1: Add current_views to total_views before resetting ---
$conn->query("UPDATE campaigns SET total_views = total_views + current_views");

// --- Step 2: Get all PropellerAds campaigns to restart ---
$res = $conn->query("SELECT campaign_external_id FROM campaigns WHERE view_limit > 0");
if ($res && $res->num_rows > 0) {
    $ids = [];
    while ($row = $res->fetch_assoc()) {
        $ids[] = (int)$row['campaign_external_id'];
    }

    if (!empty($ids)) {
        // --- Step 3: Start all those campaigns again via Propeller API ---
        $result = propellerApiRequest('PUT', 'adv/campaigns/start', ['campaign_ids' => $ids], $apiKey);
        error_log("[CRON] ✅ Restarted campaigns: " . json_encode($result));
    }
}

// --- Step 4: Reset local counts ---
$conn->query("UPDATE campaigns SET current_views = 0");

// --- Step 5: Log success ---
error_log("[CRON] ✅ Added current_views to total_views and reset counters.");

echo "✅ Campaign views added to total, reset, and campaigns restarted.\n";
?>
