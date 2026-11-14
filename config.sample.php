<?php
// Copy outside the web root to a private config.php. Never commit real values.
return [
    'db' => [
        'host' => '127.0.0.1',
        'name' => 'health_secure',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'Health Secure System',
        'base_url' => 'http://localhost/health_secure_system',
        'timezone' => 'Asia/Manila',
        'session_name' => 'HSSSESSID',
        'encryption_key' => 'REPLACE_WITH_EXACTLY_32_RANDOM_BYTES',
        'error_log' => 'C:/xampp/private/health_secure_errors.log',
    ],
    'captcha' => [
        'site_key' => 'YOUR_RECAPTCHA_V2_SITE_KEY',
        'secret_key' => 'YOUR_RECAPTCHA_V2_SECRET_KEY',
    ],
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => 'your-inbox@gmail.com',
        'password' => 'YOUR_GMAIL_APP_PASSWORD',
        'encryption' => 'tls',
        'from_email' => 'your-inbox@gmail.com',
        'from_name' => 'Health Secure System',
    ],
];
