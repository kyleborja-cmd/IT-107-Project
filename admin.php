<?php require __DIR__.'/php/bootstrap.php'; $admin=require_admin(); log_event('ADMIN_VIEWED_DASHBOARD',null,$admin['id']); page_header('Security monitoring',true); ?>
<div class="dash-head"><div><span class="eyebrow">ADMINISTRATOR · READ ONLY</span><h1>Security monitoring</h1><p class="muted">Live visibility into authentication and system security events.</p></div><span class="status-pill">● Monitoring active</span></div>
<div id="admin-data"><div class="stats"><article><small>SUCCESSFUL LOG-INS</small><b>—</b></article><article><small>FAILED LOG-INS</small><b>—</b></article><article><small>LOCKED ACCOUNTS</small><b>—</b></article><article><small>BLOCKED IPs</small><b>—</b></article><article><small>OTPs SENT / FAILED</small><b>—</b></article><article><small>ALERT EMAILS</small><b>—</b></article></div></div>
<div class="panel"><h2>Failed log-ins · Last 24 hours</h2><canvas id="failsChart" height="90"></canvas></div>
<div class="panel"><h2>Top IP addresses</h2><div id="top-ips">Loading…</div></div>
<div class="panel"><h2>Security flags</h2><div id="flags">Loading…</div></div>
<div class="panel"><h2>Latest events</h2><div class="filter-row"><input id="filter-user" placeholder="Filter username"><input id="filter-ip" placeholder="Filter IP"><select id="filter-event"><option value="">All event types</option><option>LOGIN_FAILED</option><option>LOGIN_SUCCESS</option><option>OTP_FAILED</option><option>IP_BLOCKED</option><option>LOCKOUT_TIER_1</option><option>LOCKOUT_TIER_2</option><option>LOCKOUT_TIER_3</option></select></div><div id="events">Loading…</div></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script><script src="js/admin.js" defer></script>
<?php page_footer(); ?>
