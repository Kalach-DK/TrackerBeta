<?php
include 'db.php';

// Your PropellerAds API token
$token = "SELECT FROM campaigns WHERE API";
//echo($token)

$ch = curl_init();

// 1️⃣ Fetch all campaigns with limits
$sql = "SELECT * FROM campaigns WHERE view_limit > 0 AND current_views >= view_limit";
$res = $conn->query($sql);

if($res && $res->num_rows > 0){
  while($c = $res->fetch_assoc()){
    $prop_id = $c['campaign_external_id'];
    echo "Pausing campaign: {$prop_id}<br>";

    // 2️⃣ Call Propeller API to pause
    $url = "https://ssp-api.propellerads.com/v5/adv/campaigns/{$prop_id}";
    $data = json_encode(["status" => "stopped"]);

    $curl = curl_init($url);
    curl_setopt_array($curl, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => "PATCH",
      CURLOPT_POSTFIELDS => $data,
      CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
      ]
    ]);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    echo "Response ($httpCode): $response<br>";

    // 3️⃣ Update DB status (optional)
    if($httpCode == 200){
      $conn->query("UPDATE campaigns SET status='paused' WHERE id=" . intval($c['id']));
    }
  }
} else {
  echo "No campaigns to pause.<br>";
}
?>
