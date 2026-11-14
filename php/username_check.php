<?php
require __DIR__.'/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
$name=trim($_GET['username']??'');
if(!preg_match('/^[A-Za-z][A-Za-z0-9]{5,15}$/',$name)){
 echo json_encode(['valid'=>false,'message'=>'Username must be 6–16 characters.']); exit;
}
$q=db()->prepare('SELECT 1 FROM users WHERE username=? LIMIT 1');
$q->execute([$name]);
$taken=(bool)$q->fetchColumn();
echo json_encode(['valid'=>!$taken,'message'=>$taken?'Username is taken.':'Username is available.']);
