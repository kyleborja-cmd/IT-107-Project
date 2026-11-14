<?php
require __DIR__.'/php/bootstrap.php'; csrf_check();$uid=(int)($_SESSION['recover_user']??0);if(!$uid)redirect('forgot.php');$msg='';$questions=questions();
if($_SERVER['REQUEST_METHOD']==='POST') {
 $code=$_POST['question']??'';$q=db()->prepare('SELECT answer_hash FROM auth_answers WHERE user_id=? AND question_code=?');$q->execute([$uid,$code]);$hash=$q->fetchColumn();$answer=mb_strtolower(trim($_POST['answer']??''));
 if($hash && password_verify($answer,$hash)) {
  $u=db()->prepare('SELECT email FROM users WHERE id=?');$u->execute([$uid]);$email=$u->fetchColumn();
  if(send_otp($uid,$email,'reset')){$_SESSION['pending_user']=$uid;$_SESSION['pending_purpose']='reset';redirect('otp.php');}
  $msg='Unable to send a code. Please try again later.';
 } else { $user=db()->prepare('SELECT * FROM users WHERE id=?');$user->execute([$uid]);$u=$user->fetch();record_login_failure($u,$u['username']);$msg='The answer could not be verified.'; }
}
page_header('Security question'); ?>
<div class="panel narrow"><h1>Verify account ownership</h1><?php if($msg)echo '<div class="error">'.e($msg).'</div>';?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><label>Choose the following question<select name="question"><?php foreach($questions as $k=>$v)echo '<option value="'.e($k).'">'.e($v).'</option>';?></select></label><label>Your Answer<input name="answer" required></label><button class="btn primary wide">Verify answer</button></form></div>
<?php page_footer(); ?>
