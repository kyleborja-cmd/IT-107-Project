<?php
require __DIR__.'/php/bootstrap.php'; csrf_check(); $msg='';
if($_SERVER['REQUEST_METHOD']==='POST') {
 if(!captcha_verify()) $msg='Please complete CAPTCHA verification.';
 else {
  $username=trim($_POST['username']??'');$q=db()->prepare('SELECT * FROM users WHERE username=?');$q->execute([$username]);$u=$q->fetch();
  if($u) { $_SESSION['recover_user']=(int)$u['id']; log_event('RESET_REQUESTED',$username,(int)$u['id']); redirect('security_question.php'); }
  $msg='If the account can be verified, recovery instructions will be provided.';
 }
}
page_header('Forgot password'); ?>
<div class="panel narrow"><span class="eyebrow">ACCOUNT RECOVERY</span><h1>Recover access</h1><p class="muted">Enter your username to begin secure recovery.</p><?php if($msg)echo '<div class="notice">'.e($msg).'</div>';?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Username<input name="username" required></label><div class="g-recaptcha" data-sitekey="<?=e(app_config()['captcha']['site_key'])?>"></div><script src="https://www.google.com/recaptcha/api.js" async defer></script><button class="btn primary wide">Continue</button></form></div>
<?php page_footer(); ?>
