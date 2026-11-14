# Health Secure System — Seed Table

D1 = 0; D2 = 0

| Parameter | Formula | Result |
|---|---|---:|
| Minimum username | 6 + (D1 mod 3) | 6 |
| Maximum username | 16 + (D2 mod 5) | 16 |
| Minimum password | 8 + (D2 mod 4) | 8 |
| Maximum name length | 20 + D2 | 20 |
| Forgot Password appears | 2 + (D1 mod 2) | 2 failures |
| Lockout tier 1 | 15 + D1 | 15 sec at 3 |
| Lockout tier 2 | 30 + (2 × D1) | 30 sec at 6 |
| Lockout tier 3 | 60 + (4 × D1) | 60 sec at 9 |
| OTP expiry | 3 + (D2 mod 3) | 3 minutes |
| Idle timeout | 5 + (D1 mod 5) | 5 minutes |
| IP block | 8 + D2 | 8 failures / 10 min |
| Auth questions | Q1, Q4, Q6 | See handout question bank |
