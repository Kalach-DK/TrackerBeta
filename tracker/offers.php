<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<?php include 'db.php'; ?>

<div class="tracker-main">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
    <h2>Offers</h2>
    <button class="button" data-modal-open="#offerModal">+ New Offer</button>
  </div>

  <div class="section">
    <table class="table">
      <thead><tr><th>ID</th><th>Name</th><th>Affiliate Program</th><th>Country</th><th>Affiliate Link</th><th>Created</th></tr></thead>
      <tbody>
<?php
$res = $conn->query("SELECT o.*, ap.name AS ap_name FROM offers o LEFT JOIN affiliate_programs ap ON o.affiliate_program_id = ap.id ORDER BY o.id DESC");
if($res && $res->num_rows){
  while($r = $res->fetch_assoc()){
    echo "<tr>";
    echo "<td>{$r['id']}</td>";
    echo "<td>".htmlspecialchars($r['name'])."</td>";
    echo "<td>".htmlspecialchars($r['ap_name'] ?? '-')."</td>";
    echo "<td>".htmlspecialchars($r['country'])."</td>";
    echo "<td><a href=\"".htmlspecialchars($r['affiliate_link'])."\" target=\"_blank\">Visit</a></td>";
    echo "<td>{$r['created_at']}</td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='6' class='small'>No offers yet.</td></tr>";
}
?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal" id="offerModal">
  <div class="panel">
    <h3>Create Offer</h3>
    <form method="POST" action="/tracker/offers_api.php">
      <label>Offer name</label>
      <input class="input" name="name" required>

      <label>Affiliate program (choose existing ID or leave empty)</label>
      <select class="input" name="affiliate_program_id">
        <option value="">-- none --</option>
        <?php
          $aps = $conn->query("SELECT id,name FROM affiliate_programs ORDER BY id DESC");
          while($a = $aps->fetch_assoc()){
            echo "<option value=\"{$a['id']}\">".htmlspecialchars($a['name'])."</option>";
          }
        ?>
      </select>

      <label>Country</label>
      <input class="input" name="country">

      <label>Affiliate Link</label>
      <input class="input" name="affiliate_link" required>

      <label>Article URLs (optional)</label>
      <div id="article-container"></div>
      <div style="margin-bottom:12px">
        <button type="button" class="button small-ghost" onclick="addArticleField('#article-container')">+ add article URL</button>
      </div>

      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="button" data-modal-close>Cancel</button>
        <button class="button" type="submit">Save Offer</button>
      </div>
    </form>
  </div>
</div>

<?php include 'includes/footer.php'; ?>