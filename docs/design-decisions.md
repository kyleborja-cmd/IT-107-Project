# Design Decisions — draft for student review

1. **Password strength:** Passwords require at least 8 characters, uppercase, lowercase and a digit. This gives a simple, demonstrable minimum strength rule. The server enforces it; the browser can provide immediate feedback.
2. **CAPTCHA failure:** A failed or missing CAPTCHA is logged as CAPTCHA_FAILED and rejected. It does not increment the user's account password-failure counter because the user has not demonstrated a password attempt; IP-level abuse remains observable.
3. **Lockout:** Use the seeded progressive tiers exactly: 3 failures / 15 seconds, 6 / 30 seconds, 9 / 60 seconds. The database stores the lockout expiry, so refreshes and cookie deletion do not bypass it. Successful password + OTP login resets the counter.
4. **Back button:** Disabling browser navigation is not an access control. Every protected request checks the server session and role; logout destroys the session and sends no-cache headers.
5. **Security answers:** Answers are normalized to lowercase and hashed with password_hash because they are secrets used to verify identity, not values that need to be displayed.
6. **Activity log:** Application code only inserts activity-log rows. The admin dashboard is read-only; database privileges should also deny UPDATE and DELETE on this table where possible.
7. **JavaScript files:** Browser-loaded JavaScript cannot be hidden from the visitor. It contains no secrets. Real protection is enforced server-side in PHP.
8. **Hashing vs encryption:** Passwords, answers and OTPs are hashed because the system only compares them. PhilHealth number is encrypted using AES-256-CBC with a random IV because the owner must be able to view it again.
9. **CAPTCHA secret and credentials:** Kept in a private config outside the web root and excluded from Git. Only the public site key appears in HTML.
10. **Health data minimization:** PhilHealth number, blood type, emergency contact and allergies are included as Health Secure System-specific fields, matching the health-center example in the handout. The project is a class prototype and should use fictional data only.
