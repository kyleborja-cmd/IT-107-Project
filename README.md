# Health Secure System

A PHP 8.1+ / MySQL implementation scaffold for the IT 107 Secure Registration, Authentication and Monitoring System.

## Important setup
1. Put this folder under your XAMPP `htdocs` directory (for example `C:\xampp\htdocs\health_secure_system`).
2. Create a MySQL database and import `database/schema.sql`.
3. Copy `config.sample.php` to `config.php` **outside the web root**. Recommended: `C:\xampp\private\health_secure_config.php`, then set `HSS_CONFIG_PATH` in Apache/PHP environment or edit `php/bootstrap.php` to point to it. Never commit real secrets.
4. Run `composer install` in this project to install PHPMailer.
5. Configure SMTP, Google reCAPTCHA v2 site key and secret key, DB credentials, and a 32-byte encryption key in the private config.
6. Configure Apache so only this project directory is web-accessible, and block `scripts/`, `config.php`, `.env`, and directory listing. The monitor must be run from CLI only.
7. Create the admin manually using the SQL instructions below. Never create admin accounts through registration.

## Admin account
Register a normal account, verify it, then run this SQL in phpMyAdmin after checking its user ID:
`UPDATE users SET role='admin' WHERE id = YOUR_USER_ID;`
The admin still signs in using the regular password + OTP flow.

## Google reCAPTCHA v2
Create a reCAPTCHA v2 Checkbox site in Google reCAPTCHA Admin. Put the site key in the private config and secret key only in the private config. Server verification is in `php/security.php`. For local development, register `localhost` as a domain.

## Email
PHPMailer is used for registration, login and password-reset OTPs and security alerts. Use a Gmail App Password or an instructor-approved Mailpit/MailHog SMTP inbox. Never put SMTP credentials in Git.

## Run monitoring
From the project directory: `php scripts/monitor.php`

## Seed values (D1=0, D2=0)
- Username 6–16; password 8–64; each name field max 20
- Forgot Password link after 2 failures
- Lockout: 3 failures = 15 sec; 6 = 30 sec; 9 = 60 sec
- OTP expiry 3 minutes; resend cooldown 60 seconds; 3 resends per 15 minutes
- Idle timeout 5 minutes; IP block after 8 failures in 10 minutes
- Security questions Q1, Q4, Q6

## Notes
This is a working educational starter implementation, not a production-certified medical-record platform. Have your instructor review it before handling real personal or health information. Use fictional test data only. Complete the proposal approval, threat model, design decisions, privacy note, validation-message mapping, weekly journal and required 30 meaningful commits from the handout. Understand and be ready to explain every feature during the live demo.
