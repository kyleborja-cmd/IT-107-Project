<?php
require __DIR__.'/bootstrap.php'; require_admin(); header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
$today=db()->query("SELECT
 (SELECT COUNT(*) FROM activity_log WHERE event_type='LOGIN_SUCCESS' AND DATE(created_at)=CURDATE()) successes,
 (SELECT COUNT(*) FROM activity_log WHERE event_type='LOGIN_FAILED' AND DATE(created_at)=CURDATE()) failures,
 (SELECT COUNT(*) FROM users WHERE lockout_until>NOW()) locked,
 (SELECT COUNT(*) FROM ip_blocks WHERE blocked_until>NOW()) blocked,
 (SELECT COUNT(*) FROM activity_log WHERE event_type='OTP_SENT' AND DATE(created_at)=CURDATE()) otp_sent,
 (SELECT COUNT(*) FROM activity_log WHERE event_type='OTP_FAILED' AND DATE(created_at)=CURDATE()) otp_failed,
 (SELECT COUNT(*) FROM activity_log WHERE event_type='ALERT_EMAIL_SENT' AND DATE(created_at)=CURDATE()) alerts")->fetch();
$hours=db()->query("SELECT DATE_FORMAT(created_at,'%H:00') label,COUNT(*) total FROM activity_log WHERE event_type='LOGIN_FAILED' AND created_at>=NOW()-INTERVAL 24 HOUR GROUP BY HOUR(created_at) ORDER BY HOUR(created_at)")->fetchAll();
$ips=db()->query("SELECT ip_address,COUNT(*) failures,IF(ip_address IN (SELECT ip_address FROM ip_blocks WHERE blocked_until>NOW()),'Blocked','Clear') status FROM activity_log WHERE event_type IN ('LOGIN_FAILED','OTP_FAILED') AND created_at>=NOW()-INTERVAL 24 HOUR GROUP BY ip_address ORDER BY failures DESC LIMIT 5")->fetchAll();
$flags=db()->query('SELECT pattern_key,target_value,severity,details,created_at FROM security_flags ORDER BY created_at DESC LIMIT 20')->fetchAll();
$where=[];$params=[];
if(!empty($_GET['username'])){$where[]='username_typed LIKE ?';$params[]='%'.$_GET['username'].'%';}
if(!empty($_GET['ip'])){$where[]='ip_address LIKE ?';$params[]='%'.$_GET['ip'].'%';}
if(!empty($_GET['event'])){$where[]='event_type=?';$params[]=$_GET['event'];}
$sql='SELECT id,event_type,username_typed,ip_address,user_agent,details,created_at FROM activity_log'.($where?' WHERE '.implode(' AND ',$where):'').' ORDER BY id DESC LIMIT 50';
$q=db()->prepare($sql);$q->execute($params);$events=$q->fetchAll();
echo json_encode(['summary'=>$today,'hours'=>$hours,'ips'=>$ips,'flags'=>$flags,'events'=>$events],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
