<?php require __DIR__.'/php/bootstrap.php'; $me=require_login(); $q=db()->prepare('SELECT * FROM users WHERE id=?');$q->execute([$me['id']]);$u=$q->fetch(); page_header('My health dashboard',true); ?>
<div class="dash-head"><div><span class="eyebrow">PERSONAL HEALTH SPACE</span><h1>Hello, <?=e($u['first_name'])?>.</h1><p class="muted">Your account is protected. Review your profile details below.</p></div><span class="status-pill">● Account verified</span></div>
<div class="stats"><article><small>ACCOUNT STATUS</small><b>Verified</b><span>OTP protected</span></article><article><small>SECURITY</small><b>Active</b><span>Secure sign-in</span></article><article><small>PROFILE</small><b>Health</b><span>Personal record</span></article></div>
<div class="panel"><h2>Health profile</h2><div class="profile-grid">
<div><small>Full name</small><b><?=e($u['first_name'].' '.$u['middle_name'].' '.$u['family_name'])?></b></div><div><small>Birthdate / Age</small><b><?=e($u['birthdate'])?> · <?=e((string)$u['age'])?></b></div>
<div><small>Blood type</small><b><?=e($u['blood_type'])?></b></div><div><small>Emergency contact</small><b><?=e($u['emergency_contact'])?></b></div>
<div><small>PhilHealth number</small><b><?=e(decrypt_field($u['philhealth_enc']))?></b></div><div><small>Known allergies</small><b><?=e($u['known_allergies']?:'Not provided')?></b></div>
<div class="full"><small>Address</small><b><?=e($u['purok_street'].', '.$u['barangay'].', '.$u['city'].', '.$u['province'].', '.$u['country'].' '.$u['zip_code'])?></b></div>
</div></div>
<div class="security-note"><b>Security reminder</b><p>Never share your password or one-time code. If you notice unfamiliar sign-in activity, reset your password immediately.</p><a href="forgot.php">Reset password</a></div>
<?php page_footer(); ?>
