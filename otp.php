<?php
require __DIR__.'/php/bootstrap.php'; csrf_check();
$uid=(int)($_SESSION['pending_user']??0); $purpose=$_SESSION['pending_purpose']??'';
if(!$uid || !in_array($purpose,['register','login','reset'],true)) redirect('login.php');
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST') {
 if(($_POST['action']??'verify')==='resend') {
  $q=db()->prepare('SELECT * FROM otps WHERE user_id=? AND purpose=? ORDER BY id DESC LIMIT 1');$q->execute([$uid,$purpose]);$o=$q->fetch();
  if($o && time()-strtotime($o['last_sent_at'])<60) $msg='Please wait before requesting another code.';
  elseif($o && (int)$o['resend_count']>=3) $msg='Resend limit reached. Please wait 15 minutes.';
  else { $u=db()->prepare('SELECT email FROM users WHERE id=?');$u->execute([$uid]);$email=$u->fetchColumn(); send_otp($uid,$email,$purpose); db()->prepare('UPDATE otps SET resend_count=resend_count+1 WHERE id=(SELECT id FROM (SELECT id FROM otps WHERE user_id=? AND purpose=? ORDER BY id DESC LIMIT 1) x)')->execute([$uid,$purpose]); $msg='A new code has been sent.'; }
 } elseif(verify_otp($uid,$purpose,trim($_POST['otp']??''))) {
  $u=db()->prepare('SELECT * FROM users WHERE id=?');$u->execute([$uid]);$user=$u->fetch();
  if($purpose==='register'){db()->prepare('UPDATE users SET is_verified=1 WHERE id=?')->execute([$uid]);log_event('ACCOUNT_VERIFIED',$user['username'],$uid);flash('Your account has been verified. You can now sign in.');unset($_SESSION['pending_user'],$_SESSION['pending_purpose']);redirect('login.php');}
  if($purpose==='login'){db()->prepare('UPDATE users SET failed_attempts=0,lockout_until=NULL,last_failure_at=NULL WHERE id=?')->execute([$uid]);session_regenerate_id(true);$_SESSION['user_id']=$uid;$_SESSION['role']=$user['role'];$_SESSION['name']=$user['first_name'];$_SESSION['last_activity']=time();log_event('LOGIN_SUCCESS',$user['username'],$uid);unset($_SESSION['pending_user'],$_SESSION['pending_purpose']);redirect('dashboard.php');}
  if($purpose==='reset'){$_SESSION['reset_verified']=$uid;unset($_SESSION['pending_user'],$_SESSION['pending_purpose']);redirect('change_password.php');}
 } else { $msg='The code is invalid, expired, or has reached its three-attempt limit.'; }
}
page_header('Verify one-time code'); ?>
<div class="panel narrow"><span class="eyebrow">TWO-STEP VERIFICATION</span><h1>Check your inbox</h1><p>Enter the six-digit code sent to your registered email. It expires in 3 minutes.</p><?php if($msg)echo '<div class="notice">'.e($msg).'</div>';?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>6-digit code<input name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required></label><button class="btn primary wide">Verify code</button></form><form method="post" class="resend"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="resend"><button class="btn ghost">Resend OTP</button></form></div>
<?php page_footer(); ?>
