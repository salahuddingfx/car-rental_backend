# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-07-03

### Core Features

- User registration with email verification
- Car listing with CRUD operations
- Booking system with status tracking
- Manual payment verification (bKash/Nagad/COD)
- Review system with sub-ratings and photos
- Token-based review links (30-day expiry)
- Dynamic car categories (8 seeded)
- CMS content management (blog, FAQs, offers, timelines)

### Advanced Features

- Loyalty points system with 4 tiers (Bronze/Silver/Gold/Platinum)
- Referral program with unique codes and bonus tracking
- Geolocation search with Haversine formula (OpenStreetMap/Leaflet)
- Real-time polling (30s interval, no WebSocket)
- Analytics tracking middleware (device, browser, OS, response time)
- Guest chat system with admin reply
- File upload with MIME validation

### Security

- Sanctum token auth with 24h expiry
- Role-based access control (user/host/driver/company/admin)
- 3 authorization policies (Car, Booking, User)
- Rate limiting on auth, reviews, chat, uploads
- Security headers (XSS, CSP, HSTS, X-Frame-Options)
- Path traversal protection on file uploads
- Signed URL email verification
- Admin-only middleware on all admin routes

### Email System

- Booking confirmed notification
- Booking cancelled notification
- Payment received notification
- Sync driver (shared hosting compatible)
