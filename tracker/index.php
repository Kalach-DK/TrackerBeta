<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="tracker-main">
  <div class="header-cards">
    <div class="card">
      <h3>Incoming Clicks (live)</h3>
      <div class="value" id="incoming-clicks">0</div>
      <div class="small">Clicks that came in in the last X minutes (placeholder)</div>
    </div>
    <div class="card">
      <h3>Revenue</h3>
      <div class="value" id="revenue">$0.00</div>
      <div class="small">Total payout (placeholder)</div>
    </div>
    <div class="card">
      <h3>Profit / ROI</h3>
      <div class="value" id="profit">—</div>
      <div class="small">Placeholder for profit & ROI</div>
    </div>
  </div>

  <div class="section" style="margin-top:8px;">
      
    <h2 style="margin-top:0">Incoming clicks</h2>
    <p class="small">Live incoming clicks will appear here. For now this is a placeholder.</p>
    <table class="table">
      <thead><tr><th>ID</th><th>Campaign</th><th>Offer</th><th>IP</th><th>User Agent</th><th>At</th></tr></thead>
      <tbody id="clicks-tbody">
        <tr><td colspan="6" class="small">No clicks yet (placeholder)</td></tr>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>