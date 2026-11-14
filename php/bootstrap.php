<?php
declare(strict_types=1);
$privateConfig = getenv('HSS_CONFIG_PATH') ?: dirname(__DIR__, 3) . '/private/health_secure_config.php';
if (!is_file($privateConfig)) {
    http_response_code(500);
    exit('Private configuration is missing. See README.md.');
}
$config = require $privateConfig;
date_default_timezone_set($config['app']['timezone'] ?? 'Asia/Manila');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', $config['app']['error_log']);
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/ https://cdn.jsdelivr.net; frame-src https://www.google.com/recaptcha/; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self' https://www.google.com/recaptcha/");
session_name($config['app']['session_name']);
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/mail.php';
$dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}";
try {
 $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
  PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES=>false
 ]);
} catch (Throwable $e) {
 error_log((string)$e);
 http_response_code(500); exit('The system is temporarily unavailable.');
}
function app_config(): array { global $config; return $config; }
function db(): PDO { global $pdo; return $pdo; }
function e(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function csrf_token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_check(): void {
 if ($_SERVER['REQUEST_METHOD']==='POST' && !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
  log_event('CSRF_REJECTED', $_POST['username'] ?? null, null, 'CSRF token mismatch');
  http_response_code(403); exit('Request rejected.');
 }
}
function log_event(string $type, ?string $username=null, ?int $userId=null, string $details=''): void {
 $q=db()->prepare('INSERT INTO activity_log(event_type,username_typed,user_id,ip_address,user_agent,details) VALUES(?,?,?,?,?,?)');
 $q->execute([$type,$username,$userId,substr($_SERVER['REMOTE_ADDR'] ?? 'CLI',0,45),substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI',0,255),$details]);
}
function redirect(string $path): never { header('Location: '.$path); exit; }
function flash(string $message): void { $_SESSION['flash']=$message; }
function take_flash(): string { $m=$_SESSION['flash']??''; unset($_SESSION['flash']); return $m; }
function require_login(): array {
 if (empty($_SESSION['user_id'])) redirect('login.php');
 $idle=5*60;
 if (time()-(int)($_SESSION['last_activity']??time())>$idle) {
  $id=(int)$_SESSION['user_id']; session_unset(); session_destroy(); log_event('SESSION_TIMEOUT',null,$id); redirect('login.php');
 }
 $_SESSION['last_activity']=time();
 return ['id'=>(int)$_SESSION['user_id'],'role'=>$_SESSION['role']??'user','name'=>$_SESSION['name']??'User'];
}
function require_admin(): array {
 $u=require_login();
 if ($u['role']!=='admin') { log_event('ADMIN_ACCESS_DENIED',null,$u['id']); http_response_code(403); exit('Access denied'); }
 return $u;
}
function page_header(string $title, bool $logged=false): void {
 $app=e(app_config()['app']['name']);
 echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.e($title).' · '.$app.'</title><link rel="stylesheet" href="css/style.css"></head><body><div class="brandline">'.$app.'</div><header class="topbar"><a class="logo" href="index.php">✚ HealthSecure</a><nav>';
 if($logged) echo '<a href="dashboard.php">Dashboard</a><a href="logout.php">Log out</a>';
 else { echo '<a href="index.php">Home</a><a href="register.php">Register</a><a href="login.php">Log-in</a>'; }
 echo '</nav></header><main class="wrap">';
 $f=take_flash(); if($f) echo '<div class="notice">'.e($f).'</div>';
}
function page_footer(): void { echo '</main><footer>© '.date('Y').' Health Secure System · Your health information deserves protection.</footer></body></html>'; }
