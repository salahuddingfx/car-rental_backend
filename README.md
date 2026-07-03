# Apex Ride — Laravel Backend API

> Laravel 13 + Sanctum 4 REST API for a car rental platform. Shared hosting optimized (sync driver, polling, no WebSocket/Docker).

## Tech Stack

- **PHP** 8.3+
- **Laravel** 13.8
- **Sanctum** 4.3 (API token auth)
- **Database:** SQLite (default), MySQL/PostgreSQL supported
- **Queue:** Database (sync driver for shared hosting)

---

## Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/     # 16 API controllers
│   │   └── Middleware/           # 4 middleware (AdminOnly, RateLimitAuth, TrackAnalytics, SecurityHeaders)
│   ├── Models/                  # 18 Eloquent models
│   ├── Events/                  # 3 events (BookingConfirmed, BookingCancelled, PaymentReceived)
│   ├── Listeners/               # 3 listeners (send emails on events)
│   ├── Mail/                    # 3 mailables (BookingConfirmed, BookingCancelled, PaymentReceived)
│   ├── Policies/                # 3 policies (CarPolicy, BookingPolicy, UserPolicy)
│   └── Providers/               # AppServiceProvider (event-listener registration)
├── database/
│   ├── migrations/              # 26 migrations
│   └── seeders/                 # DatabaseSeeder
├── routes/
│   └── api.php                  # All API routes (public, auth, admin)
└── config/
    ├── cors.php                 # CORS: FRONTEND_URL + ADMIN_URL
    ├── sanctum.php              # Token expiry: 24h
    └── filesystems.php          # public_uploads disk
```

---

## Models & Relationships

| Model | Relationships | Notes |
|-------|--------------|-------|
| [`User`](app/Models/User.php) | `hasMany(Car)`, `hasMany(Booking)`, `hasMany(LoyaltyPoint)`, `hasMany(Referral)` | MustVerifyEmail, role enum (user/host/driver/company/admin), loyalty points, referral code, tier, license fields |
| [`Car`](app/Models/Car.php) | `belongsTo(User)`, `hasMany(Booking)`, `hasMany(Review)` | Geo fields (lat/lng), features/images JSON, rating, reviews_count |
| [`Booking`](app/Models/Booking.php) | `belongsTo(Car)`, `belongsTo(User)`, `belongsTo(Payment)` | booking_ref (unique), status enum, payment_status, driver_info JSON |
| [`Payment`](app/Models/Payment.php) | `belongsTo(Booking)`, `belongsTo(User)` | Methods: bkash/nagad/cod/bank_transfer. Status: pending/processing/completed/failed/refunded |
| [`Review`](app/Models/Review.php) | `belongsTo(User)`, `belongsTo(Car)`, `belongsTo(Booking)` | Sub-ratings (car_condition, driver_rating, value_rating, cleanliness), photos JSON, host_response, helpful_count |
| [`ReviewLink`](app/Models/ReviewLink.php) | `belongsTo(Booking)`, `belongsTo(Car)`, `belongsTo(User)` | Token-based, 30-day expiry, single-use |
| [`Category`](app/Models/Category.php) | `hasMany(Car, 'category', 'name')` | Auto-slug from name. 8 seeded: SUV, Sedan, Luxury, Sports, Supercar, Electric, Hatchback, Van |
| [`LoyaltyPoint`](app/Models/LoyaltyPoint.php) | `belongsTo(User)`, `belongsTo(Booking)` | Types: earned/redeemed/bonus/referral/expired |
| [`Referral`](app/Models/Referral.php) | `belongsTo(User as referrer)`, `belongsTo(User as referee)` | Status: pending/completed/expired |
| [`Chat`](app/Models/Chat.php) | `belongsTo(User)`, `hasMany(ChatMessage)` | Guest chat system with unread count |
| [`ChatMessage`](app/Models/ChatMessage.php) | `belongsTo(Chat)` | Polymorphic sender (sender_type + sender_id) |
| [`AnalyticsEvent`](app/Models/AnalyticsEvent.php) | None | Request tracking: path, method, status, device, browser, OS, response_time_ms |
| [`BlogPost`](app/Models/BlogPost.php) | None | CMS blog. Slug-based, published flag |
| [`Offer`](app/Models/Offer.php) | None | Promotional offers with CTA |
| [`CmsContent`](app/Models/CmsContent.php) | None | Key-value CMS store with JSON values |
| [`Faq`](app/Models/Faq.php) | None | FAQ entries with sort order |
| [`Timeline`](app/Models/Timeline.php) | None | Company journey/process timeline |
| [`ProcessStep`](app/Models/ProcessStep.php) | None | "How it works" steps |

### Entity Relationship Diagram

```
User ──hasMany──> Car ──hasMany──> Booking ──belongsTo──> Payment
  │                │                   │
  │                └──hasMany──> Review │
  │                                    └──belongsTo──> User
  ├──hasMany──> Booking
  ├──hasMany──> LoyaltyPoint
  └──hasMany──> Referral (referrer_id / referee_id)

Category ──hasMany(by name)──> Car

ReviewLink ──belongsTo──> Booking, Car, User

Chat ──hasMany──> ChatMessage
```

---

## Controllers

| Controller | File | Purpose | Key Connections |
|-----------|------|---------|-----------------|
| `AuthController` | [Api/AuthController.php](app/Http/Controllers/Api/AuthController.php) | Register, login, admin login, logout, me | User, Referral, Sanctum tokens |
| `CarController` | [Api/CarController.php](app/Http/Controllers/Api/CarController.php) | CRUD + geolocation search (Haversine) | Car, CarPolicy |
| `BookingController` | [Api/BookingController.php](app/Http/Controllers/Api/BookingController.php) | CRUD + cancel + status update | Booking, Car, dispatches BookingConfirmed/BookingCancelled events, calls LoyaltyController |
| `PaymentController` | [Api/PaymentController.php](app/Http/Controllers/Api/PaymentController.php) | Initiate, verify (admin), list | Payment, Booking, dispatches PaymentReceived event |
| `ReviewController` | [Api/ReviewController.php](app/Http/Controllers/Api/ReviewController.php) | CRUD + helpful + respond + car rating update | Review, Booking, Car |
| `ReviewLinkController` | [Api/ReviewLinkController.php](app/Http/Controllers/Api/ReviewLinkController.php) | Generate (admin), verify (public), submit (public) | ReviewLink, Review, Booking, Car |
| `UserController` | [Api/UserController.php](app/Http/Controllers/Api/UserController.php) | Profile CRUD, license upload/verify | User, UserPolicy |
| `CategoryController` | [Api/CategoryController.php](app/Http/Controllers/Api/CategoryController.php) | Public (active) + admin CRUD | Category |
| `LoyaltyController` | [Api/LoyaltyController.php](app/Http/Controllers/Api/LoyaltyController.php) | Points, tiers, referrals, leaderboard | LoyaltyPoint, Referral, User |
| `BlogController` | [Api/BlogController.php](app/Http/Controllers/Api/BlogController.php) | Public (published) + admin CRUD | BlogPost |
| `CmsController` | [Api/CmsController.php](app/Http/Controllers/Api/CmsController.php) | CMS content, FAQs, offers, timelines, steps | CmsContent, Faq, Offer, Timeline, ProcessStep |
| `ChatController` | [Api/ChatController.php](app/Http/Controllers/Api/ChatController.php) | Guest chat + admin reply/close | Chat, ChatMessage |
| `FileUploadController` | [Api/FileUploadController.php](app/Http/Controllers/Api/FileUploadController.php) | Single/multi file upload, delete | public_uploads disk |
| `AdminStatsController` | [Api/AdminStatsController.php](app/Http/Controllers/Api/AdminStatsController.php) | Admin dashboard stats | User, Car, Booking |
| `AnalyticsController` | [Api/AnalyticsController.php](app/Http/Controllers/Api/AnalyticsController.php) | Traffic analytics (pages, devices, errors, referrers) | AnalyticsEvent, User, Car, Booking |
| `EmailVerificationController` | [Api/EmailVerificationController.php](app/Http/Controllers/Api/EmailVerificationController.php) | Send/verify/check email | User, signed URLs |
| `DashboardStatsController` | [Api/DashboardStatsController.php](app/Http/Controllers/Api/DashboardStatsController.php) | User-facing dashboard stats | Booking, Car |

---

## Middleware

| Middleware | File | Alias | Purpose |
|-----------|------|-------|---------|
| `AdminOnly` | [Middleware/AdminOnly.php](app/Http/Middleware/AdminOnly.php) | `admin` | Rejects non-admin users (403) |
| `RateLimitAuth` | [Middleware/RateLimitAuth.php](app/Http/Middleware/RateLimitAuth.php) | `throttle.auth` | 5 auth attempts per 60s per email/IP |
| `TrackAnalytics` | [Middleware/TrackAnalytics.php](app/Http/Middleware/TrackAnalytics.php) | Global | Logs every API request to `analytics_events` table. Parses device/browser/OS |
| `SecurityHeaders` | [Middleware/SecurityHeaders.php](app/Http/Middleware/SecurityHeaders.php) | Global | X-Frame-Options, CSP, HSTS (prod), etc. |

---

## Events & Listeners (Email System)

```
BookingConfirmed ──> SendBookingConfirmedEmail ──> Mail\BookingConfirmed ──> user
BookingCancelled ──> SendBookingCancelledEmail ──> Mail\BookingCancelled ──> user
PaymentReceived  ──> SendPaymentReceivedEmail  ──> Mail\PaymentReceived  ──> user
```

- Registered in [`AppServiceProvider`](app/Providers/AppServiceProvider.php)
- Sync driver (shared hosting compatible, no queue worker needed)
- HTML inline templates with colored headers (blue=confirmed, red=cancelled, green=payment)

---

## Policies

| Policy | File | Abilities |
|--------|------|-----------|
| `CarPolicy` | [Policies/CarPolicy.php](app/Policies/CarPolicy.php) | `update`, `delete` — owner or admin |
| `BookingPolicy` | [Policies/BookingPolicy.php](app/Policies/BookingPolicy.php) | `view` — owner or admin. `cancel` — owner only |
| `UserPolicy` | [Policies/UserPolicy.php](app/Policies/UserPolicy.php) | `update` — self or admin. `delete` — admin only |

---

## API Routes

### Public (no auth)

| Method | Endpoint | Controller | Notes |
|--------|----------|-----------|-------|
| POST | `/auth/register` | [AuthController@register](app/Http/Controllers/Api/AuthController.php) | `throttle.auth` |
| POST | `/auth/login` | [AuthController@login](app/Http/Controllers/Api/AuthController.php) | `throttle.auth` |
| POST | `/admin/login` | [AuthController@adminLogin](app/Http/Controllers/Api/AuthController.php) | `throttle.auth` |
| GET | `/email/verify/{id}/{hash}` | [EmailVerificationController@verify](app/Http/Controllers/Api/EmailVerificationController.php) | Signed URL |
| GET | `/cars` | [CarController@index](app/Http/Controllers/Api/CarController.php) | Geolocation filter |
| GET | `/cars/{car}` | [CarController@show](app/Http/Controllers/Api/CarController.php) | |
| GET | `/categories` | [CategoryController@index](app/Http/Controllers/Api/CategoryController.php) | Active only |
| GET | `/blog` | [BlogController@index](app/Http/Controllers/Api/BlogController.php) | Published only |
| GET | `/blog/{slug}` | [BlogController@show](app/Http/Controllers/Api/BlogController.php) | |
| GET | `/cms` | [CmsController@getCmsContent](app/Http/Controllers/Api/CmsController.php) | |
| GET | `/cms/{key}` | [CmsController@getCmsByKey](app/Http/Controllers/Api/CmsController.php) | |
| GET | `/reviews` | [ReviewController@index](app/Http/Controllers/Api/ReviewController.php) | |
| GET | `/reviews/car/{carId}` | [ReviewController@byCar](app/Http/Controllers/Api/ReviewController.php) | |
| POST | `/reviews/{review}/helpful` | [ReviewController@helpful](app/Http/Controllers/Api/ReviewController.php) | `throttle:10,1` |
| POST | `/bookings/lookup` | [BookingController@lookup](app/Http/Controllers/Api/BookingController.php) | `throttle:10,1` |
| POST | `/chats` | [ChatController@store](app/Http/Controllers/Api/ChatController.php) | Guest chat, `throttle:20,1` |
| GET | `/chats/by-guest` | [ChatController@byGuest](app/Http/Controllers/Api/ChatController.php) | |
| POST | `/chats/{chat}/messages` | [ChatController@sendMessage](app/Http/Controllers/Api/ChatController.php) | `throttle:30,1` |
| GET | `/chats/{chat}/messages` | [ChatController@messages](app/Http/Controllers/Api/ChatController.php) | |
| POST | `/chats/{chat}/read` | [ChatController@markRead](app/Http/Controllers/Api/ChatController.php) | |
| GET | `/review-link/verify/{token}` | [ReviewLinkController@verify](app/Http/Controllers/Api/ReviewLinkController.php) | |
| POST | `/review-link/submit/{token}` | [ReviewLinkController@submit](app/Http/Controllers/Api/ReviewLinkController.php) | `throttle:5,1`, no auth |

### Authenticated (`auth:sanctum`)

| Method | Endpoint | Controller |
|--------|----------|-----------|
| POST | `/auth/logout` | [AuthController@logout](app/Http/Controllers/Api/AuthController.php) |
| GET | `/auth/me` | [AuthController@me](app/Http/Controllers/Api/AuthController.php) |
| PUT | `/profile` | [UserController@updateProfile](app/Http/Controllers/Api/UserController.php) |
| PUT | `/profile/license` | [UserController@updateLicense](app/Http/Controllers/Api/UserController.php) |
| PUT | `/auth/password` | [UserController@updatePassword](app/Http/Controllers/Api/UserController.php) |
| POST | `/email/verify` | [EmailVerificationController@send](app/Http/Controllers/Api/EmailVerificationController.php) |
| GET | `/email/check` | [EmailVerificationController@check](app/Http/Controllers/Api/EmailVerificationController.php) |
| GET | `/dashboard/stats` | [DashboardStatsController@index](app/Http/Controllers/Api/DashboardStatsController.php) |
| POST | `/upload` | [FileUploadController@upload](app/Http/Controllers/Api/FileUploadController.php) |
| POST | `/upload/multiple` | [FileUploadController@uploadMultiple](app/Http/Controllers/Api/FileUploadController.php) |
| DELETE | `/upload` | [FileUploadController@destroy](app/Http/Controllers/Api/FileUploadController.php) |
| POST | `/cars` | [CarController@store](app/Http/Controllers/Api/CarController.php) |
| PUT | `/cars/{car}` | [CarController@update](app/Http/Controllers/Api/CarController.php) |
| DELETE | `/cars/{car}` | [CarController@destroy](app/Http/Controllers/Api/CarController.php) |
| GET | `/bookings` | [BookingController@index](app/Http/Controllers/Api/BookingController.php) |
| POST | `/bookings` | [BookingController@store](app/Http/Controllers/Api/BookingController.php) |
| GET | `/bookings/{booking}` | [BookingController@show](app/Http/Controllers/Api/BookingController.php) |
| POST | `/bookings/{booking}/cancel` | [BookingController@cancel](app/Http/Controllers/Api/BookingController.php) |
| POST | `/bookings/{booking}/payment` | [PaymentController@initiate](app/Http/Controllers/Api/PaymentController.php) |
| GET | `/payments` | [PaymentController@myPayments](app/Http/Controllers/Api/PaymentController.php) |
| GET | `/payments/{payment}` | [PaymentController@show](app/Http/Controllers/Api/PaymentController.php) |
| POST | `/reviews` | [ReviewController@store](app/Http/Controllers/Api/ReviewController.php) |
| GET | `/loyalty/balance` | [LoyaltyController@balance](app/Http/Controllers/Api/LoyaltyController.php) |
| GET | `/loyalty/history` | [LoyaltyController@history](app/Http/Controllers/Api/LoyaltyController.php) |
| GET | `/loyalty/tier` | [LoyaltyController@tier](app/Http/Controllers/Api/LoyaltyController.php) |
| POST | `/referral/generate` | [LoyaltyController@generateReferral](app/Http/Controllers/Api/LoyaltyController.php) |
| GET | `/referral/stats` | [LoyaltyController@referralStats](app/Http/Controllers/Api/LoyaltyController.php) |
| POST | `/referral/apply` | [LoyaltyController@applyReferral](app/Http/Controllers/Api/LoyaltyController.php) |

### Admin (`auth:sanctum` + `admin` middleware, prefix `/admin`)

| Method | Endpoint | Controller |
|--------|----------|-----------|
| GET | `/admin/stats` | [AdminStatsController@index](app/Http/Controllers/Api/AdminStatsController.php) |
| GET | `/admin/users` | [UserController@index](app/Http/Controllers/Api/UserController.php) |
| GET | `/admin/users/{user}` | [UserController@show](app/Http/Controllers/Api/UserController.php) |
| PUT | `/admin/users/{user}` | [UserController@update](app/Http/Controllers/Api/UserController.php) |
| DELETE | `/admin/users/{user}` | [UserController@destroy](app/Http/Controllers/Api/UserController.php) |
| GET | `/admin/bookings` | [BookingController@all](app/Http/Controllers/Api/BookingController.php) |
| PUT | `/admin/bookings/{booking}/status` | [BookingController@updateStatus](app/Http/Controllers/Api/BookingController.php) |
| GET | `/admin/payments/pending` | [PaymentController@pending](app/Http/Controllers/Api/PaymentController.php) |
| GET | `/admin/payments/all` | [PaymentController@all](app/Http/Controllers/Api/PaymentController.php) |
| PUT | `/admin/payments/{payment}/verify` | [PaymentController@verify](app/Http/Controllers/Api/PaymentController.php) |
| PUT | `/admin/reviews/{review}/respond` | [ReviewController@respond](app/Http/Controllers/Api/ReviewController.php) |
| DELETE | `/admin/reviews/{review}` | [ReviewController@destroy](app/Http/Controllers/Api/ReviewController.php) |
| GET | `/admin/review-links` | [ReviewLinkController@index](app/Http/Controllers/Api/ReviewLinkController.php) |
| POST | `/admin/review-links` | [ReviewLinkController@generate](app/Http/Controllers/Api/ReviewLinkController.php) |
| GET | `/admin/loyalty/leaderboard` | [LoyaltyController@leaderboard](app/Http/Controllers/Api/LoyaltyController.php) |
| GET | `/admin/analytics/overview` | [AnalyticsController@overview](app/Http/Controllers/Api/AnalyticsController.php) |
| GET | `/admin/chats` | [ChatController@adminIndex](app/Http/Controllers/Api/ChatController.php) |
| POST | `/admin/chats/{chat}/reply` | [ChatController@adminReply](app/Http/Controllers/Api/ChatController.php) |
| POST | `/admin/chats/{chat}/close` | [ChatController@adminClose](app/Http/Controllers/Api/ChatController.php) |
| GET | `/admin/chats/unread` | [ChatController@adminUnread](app/Http/Controllers/Api/ChatController.php) |
| CRUD | `/admin/blog/*` | [BlogController](app/Http/Controllers/Api/BlogController.php) |
| POST | `/admin/cms` | [CmsController@upsertCms](app/Http/Controllers/Api/CmsController.php) |
| CRUD | `/admin/faq/*` | [CmsController](app/Http/Controllers/Api/CmsController.php) |
| CRUD | `/admin/reviews/*` | [CmsController](app/Http/Controllers/Api/CmsController.php) |
| CRUD | `/admin/offers/*` | [CmsController](app/Http/Controllers/Api/CmsController.php) |
| CRUD | `/admin/timelines/*` | [CmsController](app/Http/Controllers/Api/CmsController.php) |
| CRUD | `/admin/steps/*` | [CmsController](app/Http/Controllers/Api/CmsController.php) |
| CRUD | `/admin/categories/*` | [CategoryController](app/Http/Controllers/Api/CategoryController.php) |

---

## Migrations

| # | Migration | Tables | Purpose |
|---|-----------|--------|---------|
| 1 | `0001_01_01_000000_create_users_table` | users, password_reset_tokens, sessions | Core users with role, avatar, balance, license fields |
| 2 | `0001_01_01_000001_create_cache_table` | cache, cache_locks | Laravel cache |
| 3 | `0001_01_01_000002_create_jobs_table` | jobs, job_batches, failed_jobs | Queue |
| 4 | `2026_07_02_082732_create_personal_access_tokens_table` | personal_access_tokens | Sanctum |
| 5 | `2026_07_02_082749_create_cars_table` | cars | Cars with features/images JSON |
| 6 | `2026_07_02_082750_create_bookings_table` | bookings | Bookings with booking_ref |
| 7 | `2026_07_02_082750_create_reviews_table` | reviews | Base reviews |
| 8 | `2026_07_02_082750_create_blog_posts_table` | blog_posts | Blog |
| 9 | `2026_07_02_082750_create_faqs_table` | faqs | FAQ |
| 10 | `2026_07_02_082750_create_cms_contents_table` | cms_contents | CMS key-value |
| 11 | `2026_07_02_082751_create_offers_table` | offers | Offers |
| 12 | `2026_07_02_082751_create_timelines_table` | timelines | Timeline |
| 13 | `2026_07_02_082751_create_process_steps_table` | process_steps | Process steps |
| 14 | `2026_07_02_082752_update_cars_category_to_string` | cars (alter) | Category enum → string |
| 15 | `2026_07_02_100000_create_chats_table` | chats, chat_messages | Chat system |
| 16 | `2026_07_03_034719_create_payments_table` | payments | Payments |
| 17 | `2026_07_03_034905_add_payment_fields_to_bookings_table` | bookings (alter) | payment_status, payment_id |
| 18 | `2026_07_03_043409_add_geo_fields_to_cars_table` | cars (alter) | latitude, longitude |
| 19 | `2026_07_03_044033_enhance_reviews_table` | reviews (alter) | Sub-ratings, photos, host_response, helpful |
| 20 | `2026_07_03_044721_create_loyalty_points_table` | loyalty_points | Loyalty ledger |
| 21 | `2026_07_03_044721_create_referrals_table` | referrals | Referral tracking |
| 22 | `2026_07_03_044807_add_loyalty_fields_to_users_table` | users (alter) | loyalty_points, referral_code, tier |
| 23 | `2026_07_03_050000_add_license_image_to_users_table` | users (alter) | license_image |
| 24 | `2026_07_03_051000_create_analytics_events_table` | analytics_events | Request tracking |
| 25 | `2026_07_03_052000_create_review_links_table` | review_links | Token-based review invitations |
| 26 | `2026_07_03_053000_create_categories_table` | categories | Car categories with slug |

---

## Seeders

[`DatabaseSeeder`](database/seeders/DatabaseSeeder.php) creates:
- 1 admin user, 1 test user, 1 host user
- 8 cars (Toyota Camry, Honda CR-V, BMW 5 Series, Mercedes-Benz GLC, Toyota HiAce, Hyundai i20, Nissan Patrol, Honda Civic)
- 17 CMS content entries, 6 FAQs, 2 offers, 6 reviews, 5 timelines, 5 process steps

---

## Key Features

### Payment Flow
1. User creates booking → dispatches `BookingConfirmed` event → email sent
2. User initiates payment (bKash/Nagad/COD) → Payment record created
3. User sends money manually → uploads screenshot
4. Admin verifies → dispatches `PaymentReceived` event → email sent

### Loyalty & Referral System
- 4 tiers: Bronze (0) → Silver (500) → Gold (2000) → Platinum (5000)
- Earn points per booking, referrals, bonuses
- Referral codes generate unique links, bonus on referee's first booking

### Geolocation Search
- Haversine formula in [`CarController@index`](app/Http/Controllers/Api/CarController.php)
- Filter cars by latitude/longitude radius
- OpenStreetMap/Leaflet (no API key needed)

### Email System
- 3 events → 3 listeners → 3 mailables
- Sync driver (no queue worker needed on shared hosting)
- HTML inline templates with colored headers

### Analytics
- [`TrackAnalytics`](app/Http/Middleware/TrackAnalytics.php) middleware logs every API request
- Parses device, browser, OS from User-Agent
- [`AnalyticsController`](app/Http/Controllers/Api/AnalyticsController.php) aggregates traffic, errors, endpoints, devices

### Review System
- Token-based review links (30-day expiry, single-use, no auth required)
- Sub-ratings (car_condition, driver_rating, value_rating, cleanliness)
- Photo uploads, host responses, helpful votes
- Auto-updates car average rating

---

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan db:seed
php artisan storage:link
php artisan serve
```

### Environment Variables

```env
APP_NAME="Apex Ride"
APP_ENV=local
APP_URL=http://localhost:8000

FRONTEND_URL=http://localhost:5173
ADMIN_URL=http://localhost:5174

DB_CONNECTION=sqlite

MAIL_MAILER=log
QUEUE_CONNECTION=database
```

---

## Dependencies

### Production
- `laravel/framework` ^13.8
- `laravel/sanctum` ^4.3
- `laravel/tinker` ^3.0

### Dev
- `phpunit/phpunit` ^12.5
- `laravel/pint` ^1.27 (code style)
- `laravel/pail` ^1.2 (log viewer)
- `fakerphp/faker` ^1.23

---

## Security

See [SECURITY.md](SECURITY.md) for vulnerability reporting and security features.

---

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for setup, code style, commit messages, and PR process.

---

## Code of Conduct

See [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md)

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md)

---

## Authors

- **Salah Uddin Kader** - *Full Stack Developer* - [GitHub](https://github.com/salahuddingfx)

---

## Acknowledgments

- [Laravel](https://laravel.com) - The web framework used
- [Sanctum](https://laravel.com/docs/sanctum) - API authentication
- [OpenStreetMap](https://www.openstreetmap.org) - Free mapping (no API key)
- [Leaflet](https://leafletjs.com) - Mobile-friendly maps
- [Recharts](https://recharts.org) - React charting library
- All contributors and testers

---

## License

See [LICENSE](LICENSE)
