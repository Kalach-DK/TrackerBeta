<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<?php include 'db.php'; ?>

<div class="tracker-main">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
    <h2>Traffic Sources</h2>
  </div>

  <div class="section" style="margin-bottom:12px">
    <form method="POST" action="/tracker/traffic_sources_api.php">
      <label>Name</label>
      <input class="input" name="name" required>
      <label>API Key (optional)</label>
      <input class="input" name="api_key">
      <div style="display:flex;justify-content:flex-end"><button class="button">Save</button></div>
    </form>
  </div>

  <div class="section">
    <table class="table">
      <thead><tr><th>ID</th><th>Name</th><th>API Key</th><th>Created</th></tr></thead>
      <tbody>
<?php
$res = $conn->query("SELECT * FROM traffic_sources ORDER BY id DESC");
if($res && $res->num_rows){
  while($r = $res->fetch_assoc()){
    echo "<tr>";
    echo "<td>{$r['id']}</td>";
    echo "<td>".htmlspecialchars($r['name'])."</td>";
    echo "<td>".htmlspecialchars(substr($r['api_key'],0,64))."</td>";
    echo "<td>{$r['created_at']}</td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='4' class='small'>No affiliate programs yet.</td></tr>";
}
?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>