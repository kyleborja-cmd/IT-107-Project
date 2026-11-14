<?php
function send_mail(string $to,string $subject,string $html): bool {
 $vendor=dirname(__DIR__).'/vendor/autoload.php';
 if(!is_file($vendor)) { error_log('PHPMailer missing. Run composer install.'); return false; }
 require_once $vendor;
 try {
  $s=app_config()['smtp']; $m=new PHPMailer\PHPMailer\PHPMailer(true);
  $m->isSMTP(); $m->Host=$s['host']; $m->SMTPAuth=true; $m->Username=$s['username']; $m->Password=$s['password'];
  $m->SMTPSecure=$s['encryption']; $m->Port=(int)$s['port']; $m->setFrom($s['from_email'],$s['from_name']);
  $m->addAddress($to); $m->isHTML(true); $m->Subject=$subject; $m->Body=$html; $m->AltBody=strip_tags($html); $m->send(); return true;
 } catch(Throwable $e) { error_log((string)$e); return false; }
}
function send_security_alert(array $user,int $attempts): void {
 $html='<p>Failed sign-in attempts on your account.</p><p>Attempts: '.(int)$attempts.'<br>Date/time: '.date('Y-m-d H:i:s').' PHT<br>IP: '.e($_SERVER['REMOTE_ADDR']??'').'<br>Device: '.e($_SERVER['HTTP_USER_AGENT']??'').'</p><p>If this was not you, reset your password now.</p>';
 if(send_mail($user['email'],'Failed sign-in attempts on your account',$html)) log_event('ALERT_EMAIL_SENT',$user['username'],(int)$user['id'],'Lockout alert');
}
