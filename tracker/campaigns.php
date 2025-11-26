<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<?php include 'db.php'; ?>

<div class="tracker-main">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
    <h2>Campaigns</h2>
    <button class="button" data-modal-open="#campaignModal">+ New Campaign</button>
  </div>

  <div class="section">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>External ID</th>
          <th>Offers</th>
          <th>Total Views</th>
          <th>Current Views</th>
          <th>Limit</th>
          <th>URL</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
<?php
$cs = $conn->query("SELECT c.*, ts.name AS ts_name FROM campaigns c LEFT JOIN traffic_sources ts ON c.traffic_source_id = ts.id ORDER BY c.id DESC");
if($cs && $cs->num_rows){
  while($c = $cs->fetch_assoc()){
    $offers_text = htmlspecialchars($c['offer_ids']);
    $url = "https://".$_SERVER['HTTP_HOST']."/tracker/go.php?cid=".urlencode($c['campaign_external_id'])
      ."&clickid={SUBID}&zoneid={zoneid}&campaignid={campaignid}&bannerid={bannerid}"
      ."&cost={cost}&os={os}&browser={browser}&connection_type={connection.type}&isp={isp}";

    echo "<tr>";
    echo "<td>{$c['id']}</td>";
    echo "<td>".htmlspecialchars($c['name'])."</td>";
    echo "<td>".htmlspecialchars($c['campaign_external_id'])."</td>";
    echo "<td>{$offers_text}</td>";
    echo "<td> {$c['total_views']}</td>";
    echo "<td>{$c['current_views']}</td>";
    echo "<td>".($c['view_limit'] ?: '∞')."</td>";
    echo "<td><code>{$url}</code></td>";
    echo "<td>
      <button class='button small' data-modal-open='#editCampaignModal' 
        data-id='{$c['id']}'
        data-name='".htmlspecialchars($c['name'])."'
        data-external='".htmlspecialchars($c['campaign_external_id'])."'
        data-country='".htmlspecialchars($c['country'])."'
        data-offers='".htmlspecialchars($c['offer_ids'])."'
        data-limit='{$c['view_limit']}'
        data-source='{$c['traffic_source_id']}'>
        Edit
      </button>
    </td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='8' class='small'>No campaigns yet.</td></tr>";
}
?>
      </tbody>
    </table>
  </div>
</div>

<!-- Create Campaign Modal -->
<div class="modal" id="campaignModal">
  <div class="panel">
    <h3>Create Campaign</h3>
    <form method="POST" action="/tracker/campaigns_api.php">
      <label>Campaign name</label>
      <input class="input" name="name" required>

      <label>Traffic Source</label>
      <select class="input" name="traffic_source_id">
        <option value="">-- none --</option>
<?php
$ts = $conn->query("SELECT id,name FROM traffic_sources ORDER BY id DESC");
while($t = $ts->fetch_assoc()){
  echo "<option value=\"{$t['id']}\">".htmlspecialchars($t['name'])."</option>";
}
?>
      </select>

      <label>Campaign external ID (from the traffic source)</label>
      <input class="input" name="campaign_external_id" required>

      <label>Country</label>
      <input class="input" name="country">

      <label>Select offers (hold ctrl to multi-select)</label>
      <select class="input" name="offers[]" multiple size="6" required>
<?php
$of = $conn->query("SELECT id,name FROM offers ORDER BY id DESC");
while($o = $of->fetch_assoc()){
  echo "<option value=\"{$o['id']}\">".htmlspecialchars($o['name'])."</option>";
}
?>
      </select>

      <label>View cap (0 = unlimited)</label>
      <input class="input" name="view_limit" type="number" min="0" value="0">

      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
        <button type="button" class="button" data-modal-close>Cancel</button>
        <button class="button" type="submit">Save Campaign</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Campaign Modal -->
<div class="modal" id="editCampaignModal">
  <div class="panel">
    <h3>Edit Campaign</h3>
    <form method="POST" action="/tracker/campaigns_api.php">
      <input type="hidden" name="id" id="edit_id">

      <label>Campaign name</label>
      <input class="input" name="name" id="edit_name" required>

      <label>Traffic Source</label>
      <select class="input" name="traffic_source_id" id="edit_source">
        <option value="">-- none --</option>
<?php
$ts2 = $conn->query("SELECT id,name FROM traffic_sources ORDER BY id DESC");
while($t2 = $ts2->fetch_assoc()){
  echo "<option value=\"{$t2['id']}\">".htmlspecialchars($t2['name'])."</option>";
}
?>
      </select>

      <label>Campaign external ID</label>
      <input class="input" name="campaign_external_id" id="edit_external" required>

      <label>Country</label>
      <input class="input" name="country" id="edit_country">

      <label>Select offers</label>
      <select class="input" name="offers[]" id="edit_offers" multiple size="6" required>
<?php
$of2 = $conn->query("SELECT id,name FROM offers ORDER BY id DESC");
while($o2 = $of2->fetch_assoc()){
  echo "<option value=\"{$o2['id']}\">".htmlspecialchars($o2['name'])."</option>";
}
?>
      </select>

      <label>View cap</label>
      <input class="input" name="view_limit" type="number" id="edit_limit" min="0">

      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
        <button type="button" class="button" data-modal-close>Cancel</button>
        <button class="button" type="submit">Update Campaign</button>
      </div>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('[data-modal-open="#editCampaignModal"]').forEach(btn => {
  btn.addEventListener('click', () => {
    document.getElementById('edit_id').value = btn.dataset.id;
    document.getElementById('edit_name').value = btn.dataset.name;
    document.getElementById('edit_external').value = btn.dataset.external;
    document.getElementById('edit_country').value = btn.dataset.country;
    document.getElementById('edit_limit').value = btn.dataset.limit;
    document.getElementById('edit_source').value = btn.dataset.source;

    // handle offers (multi-select)
    const offers = btn.dataset.offers ? btn.dataset.offers.split(',') : [];
    const offerSelect = document.getElementById('edit_offers');
    for (let option of offerSelect.options) {
      option.selected = offers.includes(option.value);
    }
  });
});
</script>

<?php include 'includes/footer.php'; ?>
