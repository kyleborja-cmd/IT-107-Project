<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit('Page Not Found');}
require dirname(__DIR__).'/php/bootstrap.php';
$since=date('Y-m-d H:i:s',time()-86400);$new=0;$already=0;
function flag(PDO $pdo,string $key,string $target,string $severity,string $details,int &$new,int &$already):void{
 $q=$pdo->prepare('INSERT IGNORE INTO security_flags(pattern_key,target_value,severity,details,flag_date) VALUES(?,?,?,?,CURDATE())');
 $q->execute([$key,$target,$severity,$details]); if($q->rowCount())$new++;else $already++;
}
$q=db()->prepare("SELECT ip_address,COUNT(*) n,COUNT(DISTINCT username_typed) users FROM activity_log WHERE event_type='LOGIN_FAILED' AND created_at>=? GROUP BY ip_address HAVING n>=8");
$q->execute([$since]);foreach($q as $r)flag(db(),'IP_OVER_THRESHOLD',$r['ip_address'],'HIGH',$r['n'].' failures across '.$r['users'].' usernames',$new,$already);
$q=db()->prepare("SELECT username_typed,COUNT(*) n,COUNT(DISTINCT ip_address) ips FROM activity_log WHERE event_type='LOGIN_FAILED' AND created_at>=? AND username_typed IS NOT NULL GROUP BY username_typed HAVING ips>=3");
$q->execute([$since]);foreach($q as $r)flag(db(),'ACCOUNT_TARGETED',$r['username_typed'],'MED',$r['n'].' failures from '.$r['ips'].' IP addresses',$new,$already);
$q=db()->query("SELECT user_id,COUNT(*) n FROM activity_log WHERE event_type='OTP_FAILED' AND created_at>=NOW()-INTERVAL 10 MINUTE AND user_id IS NOT NULL GROUP BY user_id HAVING n>=5");
foreach($q as $r)flag(db(),'OTP_GUESSING',(string)$r['user_id'],'MED',$r['n'].' failed OTPs in 10 minutes',$new,$already);
$q=db()->query("SELECT id,user_id,created_at FROM activity_log WHERE event_type='LOGIN_SUCCESS' AND created_at>=NOW()-INTERVAL 24 HOUR");
foreach($q as $r){$h=(int)date('G',strtotime($r['created_at']));if($h>=0&&$h<5)flag(db(),'OFF_HOURS_LOGIN',(string)$r['user_id'],'MED','Successful login at '.date('Y-m-d H:i:s',strtotime($r['created_at'])).' PHT',$new,$already);}
echo 'Health Secure System Monitoring Report - '.date('Y-m-d H:i:s').' PHT'.PHP_EOL;
echo "Scanned activity events since $since".PHP_EOL."New flags written: $new | Already flagged today: $already".PHP_EOL;
