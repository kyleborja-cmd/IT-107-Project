<?php
require __DIR__.'/php/bootstrap.php'; csrf_check(); $error='';
if($_SERVER['REQUEST_METHOD']==='POST') {
 $username=trim($_POST['username']??''); $pass=$_POST['password']??'';
 if(ip_is_blocked()) $error='Access temporarily unavailable. Please try again later.';
 elseif(!captcha_verify()) $error='Please complete CAPTCHA verification.';
 else {
  $q=db()->prepare('SELECT * FROM users WHERE username=? LIMIT 1'); $q->execute([$username]); $u=$q->fetch();
  if($u && $u['lockout_until'] && strtotime($u['lockout_until'])>time()) $error='Account temporarily locked. Please wait for the countdown.';
  elseif($u && password_verify($pass,$u['password_hash']) && (int)$u['is_verified']===1) {
   if(send_otp((int)$u['id'],$u['email'],'login')) { $_SESSION['pending_user']=(int)$u['id']; $_SESSION['pending_purpose']='login'; redirect('otp.php'); }
   $error='Unable to send verification code. Please try again later.';
  } else { record_login_failure($u?:null,$username); $error='Invalid username or password.'; }
 }
}
page_header('Log in'); ?>
<section class="auth-layout"><div><span class="eyebrow">WELCOME BACK</span><h1>Sign in with<br><em>confidence.</em></h1><p class="muted">Your password is only the first step. A one-time code helps verify it is really you.</p></div>
<form method="post" class="panel auth-card"><h2>Account sign-in</h2><?php if($error)echo '<div class="error">'.e($error).'</div>';?>
<input type="hidden" name="csrf" value="<?=e(csrf_token())?>">
<label>Username<input name="username" required minlength="6" maxlength="16" value="<?=e($_POST['username']??'')?>"></label>
<label>Password<input id="pw" type="password" name="password" required maxlength="64"></label><label class="check"><input type="checkbox" onclick="document.getElementById('pw').type=this.checked?'text':'password'"> Show Password</label>
<div class="g-recaptcha" data-sitekey="<?=e(app_config()['captcha']['site_key'])?>"></div><script src="https://www.google.com/recaptcha/api.js" async defer></script>
<button class="btn primary wide">Continue securely</button><p class="muted small">After two failed attempts, use <a href="forgot.php">Forgot Password? Reset Here</a>.</p></form></section>
<?php page_footer(); ?>
