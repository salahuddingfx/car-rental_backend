# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability, please email **salauddinkaderappy@gmail.com** directly. Do NOT open a public GitHub issue.

- We will respond within 48 hours
- We will work with you to understand and address the issue
- We will credit reporters in the changelog (unless you prefer anonymity)

## Supported Versions

| Version | Supported |
|---------|-----------|
| 1.0.x | Yes |

## Security Features

| Feature | Implementation |
|---------|---------------|
| **Authentication** | Laravel Sanctum token-based with 24h expiry |
| **Rate Limiting** | Auth endpoints: 5 attempts/60s. General: 30 req/min |
| **CSRF Protection** | Sanctum stateful domains + token validation |
| **SQL Injection** | Eloquent ORM (parameterized queries) |
| **XSS Prevention** | React frontend auto-escapes + `SecurityHeaders` middleware |
| **CORS** | Whitelist only `FRONTEND_URL` + `ADMIN_URL` with credentials |
| **Authorization** | 3 Policies: CarPolicy, BookingPolicy, UserPolicy (role-based) |
| **Input Validation** | Form Request validation on all endpoints |
| **File Upload** | MIME validation, path traversal protection, size limits |
| **Security Headers** | X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, CSP, HSTS (prod) |
| **Email Verification** | Signed URLs with 60min expiry |
| **Admin Guard** | `AdminOnly` middleware on all admin routes |
| **Soft Deletes** | Data preserved on user/car/booking deletion |
| **Audit Trail** | `TrackAnalytics` middleware logs all API requests |

## Best Practices

- Never commit `.env` files or secrets
- Use environment variables for all sensitive data
- Validate and sanitize all user input
- Use HTTPS in production
- Keep dependencies updated (`composer update`)
- Run `./vendor/bin/pint` for code style
- Use `php artisan test` before deploying
