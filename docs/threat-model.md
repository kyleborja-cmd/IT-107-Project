# Threat Model — starter mapping

| Threat | Protection in project | Test |
|---|---|---|
| SQL injection | PDO prepared statements | `' OR '1'='1` |
| XSS | htmlspecialchars / JSON escaping | `<script>alert(1)</script>` |
| CSRF | Session token on POST forms | Remove token |
| Username enumeration | Generic login failure message | Compare unknown user and wrong password |
| IP brute force | 8 failures in 10 minutes blocks IP | Test repeated failures |
| Session theft / fixation | HttpOnly, SameSite, session ID regeneration | Inspect cookie and session |
| Password reuse | Last-password history | Try current/recent password |
| Automated registration | Honeypot and reCAPTCHA v2 | Fill hidden field / remove CAPTCHA token |
| Missing accountability | Activity log | Inspect event rows |
| Information leakage | Generic errors and server error log | Submit invalid values |
| Secret exposure | Private config and .gitignore | Search Git history |
| Clickjacking / MIME sniffing | X-Frame-Options and nosniff | Inspect response headers |
| Database exposure | AES-256 encryption of PhilHealth field | Inspect stored value |
