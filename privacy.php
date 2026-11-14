<?php require __DIR__.'/php/bootstrap.php'; page_header('Privacy Notice'); ?>
<section class="panel narrow"><span class="eyebrow">DATA PRIVACY · RA 10173</span><h1>Privacy Notice</h1>
<p>Health Secure System collects the identity, contact, account and health-profile information entered during registration.</p>
<h3>Why we collect it</h3><p>To create and protect your account, display your health profile to you, support emergency contact details, verify account ownership and monitor suspicious access.</p>
<h3>How we protect it</h3><p>Passwords, security answers and one-time codes are hashed. The PhilHealth number is encrypted at rest. Access is protected by role checks, OTP verification, CAPTCHA, CSRF protection, prepared SQL statements and activity monitoring.</p>
<h3>Retention</h3><p>For this academic prototype, records are retained in the local database until removed by the project administrator under the approved class procedure. The activity log is append-only in the application.</p>
<h3>Who can access it</h3><p>The account owner can view their own profile. Authorized administrators can view security events, not edit or clear the activity log. Use test data only; this prototype is not approved for real clinical use.</p>
<h3>Contact</h3><p>For this student prototype, contact the system project team through the contact details supplied by your instructor.</p></section>
<?php page_footer(); ?>
