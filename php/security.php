<?php
function captcha_verify(): bool {
 $token=$_POST['g-recaptcha-response']??'';
 if($token==='') { log_event('CAPTCHA_FAILED',$_POST['username']??null,null,'Missing token'); return false; }
 $secret=app_config()['captcha']['secret_key'];
 if(!$secret || str_starts_with($secret,'YOUR_')) return false;
 $ch=curl_init('https://www.google.com/recaptcha/api/siteverify');
 curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query(['secret'=>$secret,'response'=>$token,'remoteip'=>$_SERVER['REMOTE_ADDR']??'']),CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>8]);
 $raw=curl_exec($ch); curl_close($ch);
 $data=json_decode((string)$raw,true);
 if(empty($data['success'])) log_event('CAPTCHA_FAILED',$_POST['username']??null,null,'Google verification failed');
 return !empty($data['success']);
}
function ip_is_blocked(): bool {
 $q=db()->prepare('SELECT blocked_until FROM ip_blocks WHERE ip_address=? AND blocked_until>NOW()');
 $q->execute([$_SERVER['REMOTE_ADDR']??'']); return (bool)$q->fetch();
}
function check_ip_threshold(): void {
 $ip=$_SERVER['REMOTE_ADDR']??'';
 $q=db()->prepare("SELECT COUNT(*) c FROM activity_log WHERE ip_address=? AND event_type IN ('LOGIN_FAILED','OTP_FAILED') AND created_at>=NOW()-INTERVAL 10 MINUTE");
 $q->execute([$ip]); if((int)$q->fetch()['c']>=8) {
  db()->prepare('INSERT INTO ip_blocks(ip_address,blocked_until,reason) VALUES(?,NOW()+INTERVAL 10 MINUTE,?) ON DUPLICATE KEY UPDATE blocked_until=VALUES(blocked_until),reason=VALUES(reason)')->execute([$ip,'8 failures in 10 minutes']);
  log_event('IP_BLOCKED',null,null,'IP blocked for 10 minutes');
 }
}
function lockout_seconds(int $failures): int {
 if($failures>=9) return 60;
 if($failures>=6) return 30;
 if($failures>=3) return 15;
 return 0;
}
function record_login_failure(?array $user, string $username): void {
 log_event('LOGIN_FAILED',$username,$user?(int)$user['id']:null,'Invalid credentials');
 if($user) {
  $n=(int)$user['failed_attempts']+1; $duration=lockout_seconds($n);
  db()->prepare('UPDATE users SET failed_attempts=?,last_failure_at=NOW(),lockout_until=? WHERE id=?')
    ->execute([$n,$duration?date('Y-m-d H:i:s',time()+$duration):null,$user['id']]);
  if(in_array($n,[3,6,9],true)) {
   $tier=(int)($n/3); log_event('LOCKOUT_TIER_'.$tier,$username,(int)$user['id'],"Locked for {$duration} seconds");
   send_security_alert($user,$n);
  }
 }
 check_ip_threshold();
}
function encrypt_field(string $plain): string {
 $key=app_config()['app']['encryption_key']; if(strlen($key)!==32) throw new RuntimeException('Encryption key must be 32 bytes.');
 $iv=random_bytes(16); $cipher=openssl_encrypt($plain,'AES-256-CBC',$key,OPENSSL_RAW_DATA,$iv);
 return base64_encode($iv.$cipher);
}
function decrypt_field(string $encoded): string {
 $raw=base64_decode($encoded,true); if($raw===false || strlen($raw)<17) return '';
 return (string)openssl_decrypt(substr($raw,16),'AES-256-CBC',app_config()['app']['encryption_key'],OPENSSL_RAW_DATA,substr($raw,0,16));
}
function questions(): array {
 return ['Q1'=>'What was the name of your first pet?','Q4'=>'What is your mother’s maiden first name?','Q6'=>'What was the first song you learned by heart?'];
}
function send_otp(int $userId,string $email,string $purpose): bool {
 $otp=(string)random_int(100000,999999);
 db()->prepare('UPDATE otps SET consumed_at=NOW() WHERE user_id=? AND purpose=? AND consumed_at IS NULL')->execute([$userId,$purpose]);
 db()->prepare('INSERT INTO otps(user_id,purpose,otp_hash,expires_at,last_sent_at) VALUES(?,?,?,?,NOW())')
  ->execute([$userId,$purpose,password_hash($otp,PASSWORD_DEFAULT),date('Y-m-d H:i:s',time()+180)]);
 log_event('OTP_SENT',null,$userId,$purpose);
 return send_mail($email, 'Your '.$purpose.' code', '<p>Your Health Secure System code is <strong>'.e($otp).'</strong>.</p><p>It expires in 3 minutes. If you did not request this code, ignore this email and consider changing your password.</p>');
}
function verify_otp(int $userId,string $purpose,string $code): bool {
 $q=db()->prepare('SELECT * FROM otps WHERE user_id=? AND purpose=? AND consumed_at IS NULL ORDER BY id DESC LIMIT 1');
 $q->execute([$userId,$purpose]); $o=$q->fetch();
 if(!$o || strtotime($o['expires_at'])<time() || (int)$o['attempts']>=3) return false;
 if(!password_verify($code,$o['otp_hash'])) {
  db()->prepare('UPDATE otps SET attempts=attempts+1 WHERE id=?')->execute([$o['id']]);
  log_event('OTP_FAILED',null,$userId,'Wrong or expired OTP'); return false;
 }
 db()->prepare('UPDATE otps SET consumed_at=NOW() WHERE id=?')->execute([$o['id']]); return true;
}
