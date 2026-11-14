<?php
require __DIR__.'/php/bootstrap.php';csrf_check();$uid=(int)($_SESSION['reset_verified']??0);if(!$uid)redirect('forgot.php');$msg='';
if($_SERVER['REQUEST_METHOD']==='POST') {
 $p=$_POST['password']??'';$c=$_POST['confirm_password']??'';
 if(strlen($p)<8||strlen($p)>64||!preg_match('/[A-Z]/',$p)||!preg_match('/[a-z]/',$p)||!preg_match('/\d/',$p))$msg='Use 8–64 characters with uppercase, lowercase and a number.';
 elseif($p!==$c)$msg='Mismatch Password.';
 else {
  $q=db()->prepare('SELECT password_hash FROM users WHERE id=?');$q->execute([$uid]);$old=$q->fetchColumn();
  $h=db()->prepare('SELECT password_hash FROM password_history WHERE user_id=? ORDER BY id DESC LIMIT 3');$h->execute([$uid]);$recent=$h->fetchAll(PDO::FETCH_COLUMN);
  if(password_verify($p,$old))$msg='You cannot reuse a recent password.';
  foreach($recent as $rh)if(password_verify($p,$rh))$msg='You cannot reuse a recent password.';
  if(!$msg){db()->prepare('INSERT INTO password_history(user_id,password_hash) VALUES(?,?)')->execute([$uid,$old]);db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($p,PASSWORD_DEFAULT),$uid]);db()->prepare('INSERT INTO password_history(user_id,password_hash) VALUES(?,?)')->execute([$uid,password_hash($p,PASSWORD_DEFAULT)]);log_event('PASSWORD_CHANGED',null,$uid);$u=db()->prepare('SELECT email FROM users WHERE id=?');$u->execute([$uid]);send_mail($u->fetchColumn(),'Your password was changed','<p>Your password was changed successfully.</p>');unset($_SESSION['reset_verified'],$_SESSION['recover_user']);flash('Successfully Change Password.');redirect('login.php');}
 }
}
page_header('Change password'); ?>
<div class="panel narrow"><h1>Choose a new password</h1><?php if($msg)echo '<div class="error">'.e($msg).'</div>';?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Enter Password<input type="password" name="password" required></label><label>Re-enter Password<input type="password" name="confirm_password" required></label><button class="btn primary wide">Save new password</button></form></div>
<?php page_footer(); ?>
