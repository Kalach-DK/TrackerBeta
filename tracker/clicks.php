<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<?php include 'db.php'; 

$query = "
  SELECT 
    c.id,
    c.zoneid,
    c.os,
    c.browser,
    c.cost,
    c.payout,
    c.created_at,
    cmp.id AS campaign_id,
    cmp.name AS campaign_name,
    o.id AS offer_id,
    o.name AS offer_name
  FROM clicks c
  LEFT JOIN campaigns cmp ON c.campaign_id = cmp.id
  LEFT JOIN offers o ON c.offer_id = o.id
  ORDER BY cmp.name, o.name, c.created_at DESC
";
$res = $conn->query($query);

// group data by campaign and offer
$data = [];
while ($row = $res->fetch_assoc()) {
  $data[$row['campaign_name']]['offers'][$row['offer_name']][] = $row;
}
?>



<div class="tracker-main">
  <h2>Click Log</h2>

  <div class="section">

    <!-- ✅ Filter form starts here -->
    <form method="GET" style="margin-bottom:12px;">
      <select name="campaign_id" class="input" style="width:180px;">
        <option value="">All Campaigns</option>
        <?php
          $cs = $conn->query("SELECT id,name FROM campaigns ORDER BY name ASC");
          while($c = $cs->fetch_assoc()){
            $selected = ($_GET['campaign_id'] ?? '') == $c['id'] ? 'selected' : '';
            echo "<option value='{$c['id']}' $selected>".htmlspecialchars($c['name'])."</option>";
          }
        ?>
      </select>

      <select name="offer_id" class="input" style="width:180px;">
        <option value="">All Offers</option>
        <?php
          $os = $conn->query("SELECT id,name FROM offers ORDER BY name ASC");
          while($o = $os->fetch_assoc()){
            $selected = ($_GET['offer_id'] ?? '') == $o['id'] ? 'selected' : '';
            echo "<option value='{$o['id']}' $selected>".htmlspecialchars($o['name'])."</option>";
          }
        ?>
      </select>

      <button class="button small-ghost" type="submit">Filter</button>
    </form>
    <!-- ✅ Filter form ends here -->

<?php
// Build filters for campaign & offer
$where = [];
if (!empty($_GET['campaign_id'])) $where[] = "c.campaign_id=".(int)$_GET['campaign_id'];
if (!empty($_GET['offer_id'])) $where[] = "c.offer_id=".(int)$_GET['offer_id'];
$whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

// ✅ Fetch joined data (clicks + campaign + offer)
$query = "
  SELECT 
    c.id,
    c.click_id,
    c.zoneid,
    c.os,
    c.browser,
    c.cost,
    c.payout,
    c.created_at,
    cmp.name AS campaign_name,
    o.name AS offer_name
  FROM clicks c
  LEFT JOIN campaigns cmp ON c.campaign_id = cmp.id
  LEFT JOIN offers o ON c.offer_id = o.id
  $whereSQL
  ORDER BY cmp.name, o.name, c.created_at DESC
";

$res = $conn->query($query);

// ✅ Group data by campaign > offer
$data = [];
if ($res && $res->num_rows) {
  while ($row = $res->fetch_assoc()) {
    $data[$row['campaign_name']]['offers'][$row['offer_name']][] = $row;
  }
} else {
  echo "<p>No clicks recorded yet.</p>";
}
?>

<!-- ✅ Collapsible Table -->
<?php if (!empty($data)): ?>
<table border="1" width="100%" style="border-collapse: collapse;">
  <thead>
    <tr>
      <th>Campaign</th>
      <th>Offer</th>
      <th>Zone</th>
      <th>OS</th>
      <th>Browser</th>
      <th>Cost</th>
      <th>Payout</th>
      <th>click_id</th>
      <th>Created</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($data as $campaign => $offers): ?>
      <tr class="campaign-row" style="background-color: #ddd; cursor: pointer;" onclick="toggleCampaign('<?= md5($campaign) ?>')">
        <td colspan="8"><b>📁 <?= htmlspecialchars($campaign) ?></b></td>
      </tr>
      <tbody id="campaign-<?= md5($campaign) ?>" style="display:none;">
        <?php foreach ($offers['offers'] as $offerName => $clicks): ?>
          <tr class="offer-row" style="background-color: #f2f2f2; cursor: pointer;" onclick="toggleOffer('<?= md5($campaign.$offerName) ?>')">
            <td></td>
            <td colspan="7"><b>📦 <?= htmlspecialchars($offerName) ?></b></td>
          </tr>
          <tbody id="offer-<?= md5($campaign.$offerName) ?>" style="display:none;">
            <?php foreach ($clicks as $click): ?>
              <tr>
                <td></td>
                <td></td>
                <td><?= htmlspecialchars($click['zoneid']) ?></td>
                <td><?= htmlspecialchars($click['os']) ?></td>
                <td><?= htmlspecialchars($click['browser']) ?></td>
                <td><?= htmlspecialchars($click['cost']) ?></td>
                <td><?= htmlspecialchars($click['payout']) ?></td>
                <td><?= htmlspecialchars($click['click_id']) ?></td>
                <td><?= htmlspecialchars($click['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        <?php endforeach; ?>
      </tbody>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<script>
function toggleCampaign(id) {
  const el = document.getElementById('campaign-' + id);
  el.style.display = (el.style.display === 'none') ? 'table-row-group' : 'none';
}
function toggleOffer(id) {
  const el = document.getElementById('offer-' + id);
  el.style.display = (el.style.display === 'none') ? 'table-row-group' : 'none';
}
</script>
  </div>
</div>


<?php include 'includes/footer.php'; ?>
