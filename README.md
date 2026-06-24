<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## MindSpace System Modules

The platform includes the following modules required by the proposal.

### 1) Authentication Module

- Routes: `routes/auth.php`
- Controllers: `app/Http/Controllers/Auth/*`
- Features: registration, login, admin login, password reset, email verification, logout

### 2) Mood Tracking Module

- Routes: `routes/web.php` (`/mood`, `/mood/dashboard`, exports)
- Controller: `app/Http/Controllers/MoodLogController.php`
- Views: `resources/views/mood/*`
- Data models/tables: `MoodLog`, `mood_logs`, `MoodEntry`, `mood_entries`

### 3) Journal Management Module

- Routes: `routes/web.php` (`/journals*`)
- Controller: `app/Http/Controllers/JournalController.php`
- Views: `resources/views/journals/*`
- Data model/table: `Journal`, `journals`

### 4) Community Feed Module

- Routes: `routes/web.php` (`/community`, `/routines*`)
- Controller: `app/Http/Controllers/RoutineController.php`
- Views: `resources/views/routines/*`
- Data models/tables: `Routine`, `Comment`, `SavedRoutine`, `RoutineLike`, `RoutineReaction`

### 5) Recommendation Module

- Recommendation logic: `app/Http/Controllers/RoutineController.php` (`buildRecommendations`)
- Recommendation tracking: `RecommendationHistory` model and `recommendation_history` table
- Personal analytics visibility: `app/Http/Controllers/AnalyticsController.php`

### 6) Notification Module

- Routes: `routes/web.php` (`/notifications*`)
- Controller: `app/Http/Controllers/NotificationController.php`
- Service: `app/Services/NotificationService.php`
- Views: `resources/views/notifications/*`
- Data model/table: `Notification`, `notifications`

### 7) Administrative Management Module

- Admin routes: `routes/web.php` (`/admin/*`)
- Controllers: `app/Http/Controllers/Admin/UserManagementController.php`, `app/Http/Controllers/ReportController.php`, `app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/AnalyticsController.php`
- Views: `resources/views/admin/*`
- Features: user management, suspend/reactivate/delete users, moderation queue, report management, platform analytics, activity history

## Required Pages Coverage

### Public Pages

1. Home page: `/` (`welcome` view)
2. Login page: `/login`
3. Registration page: `/register`

### User Pages

1. Dashboard: `/dashboard`
2. Mood tracker page: `/mood`
3. Journal page: `/journals`
4. Community feed: `/community`
5. Recommendations page: `/recommendations`
6. Notifications page: `/notifications`
7. Profile page: `/profile/view` and `/profile`
8. Saved routines page: `/routines/saved`

### Admin Pages

1. Admin dashboard: `/admin/dashboard`
2. User management: `/admin/users`
3. Reports management: `/admin/reports`
4. Content moderation: `/admin/moderation`
5. Analytics page: `/admin/analytics`

## Non-Functional Requirements Coverage

### 1) Secure

- Password hashing enabled through Laravel hashing (`User` cast + registration hashing).
- Authentication lockout/rate limiting in login request flow.
- Admin/user authorization via middleware and policies.
- Security response headers added with `SecurityHeadersMiddleware`.

### 2) Private

- Profile privacy setting for anonymous sharing preference.
- Anonymous posting can be enforced from server-side controller logic.
- Emotional note content (journal and mood notes) encrypted at rest.

### 3) Easy to use

- Clear top navigation for user/admin tasks and notification badges.
- Personal dashboard, reports, analytics, and recommendations pages are directly accessible.

### 4) Reliable

- Account suspension enforcement with automatic sign-out and session invalidation.
- Input validation across controllers/forms.
- Consistent authorization checks for protected profile actions.

### 5) Fast

- Notification sync is throttled with cache to avoid repeating expensive work every request.
- Unread notification counts are cached and shared to views.

### 6) Scalable

- Database-backed sessions and dedicated `user_sessions` tracking table.
- Query filtering/index-aware route structure for high-read pages (mood, journals, routines, reports).

### 7) Available

- Uses Laravel middleware/session architecture with graceful logout/token regeneration flows.
- Session persistence supports database storage for multi-instance deployment.

### 8) Maintainable

- Separation of concerns across controllers, middleware, services, models, and policies.
- Reusable middleware for security headers, suspension checks, notification sync, and session tracking.

### 9) Cross-browser compatible

- Standards-compliant HTML meta headers including viewport and compatibility metadata.
- Blade/Tailwind UI patterns render consistently across modern browsers.

### 10) Responsive on mobile, tablet, and desktop

- Responsive navigation (desktop and mobile menu variants).
- Layouts and components use Tailwind responsive classes (`sm`, `md`, `lg`) across key pages.

## Reliability, Availability, and Observability

### Health and Readiness Endpoints

- Liveness endpoint: `GET /health`
- Readiness endpoint: `GET /ready`

Readiness validates:

- Database connectivity
- Cache write/read capability
- Queue connection resolution
- Session table availability

Recommended monitor setup:

- Uptime monitor targets `GET /health` every 60 seconds.
- Orchestration/readiness probes target `GET /ready`.
- Alert when `/ready` returns HTTP 503 for 3+ consecutive checks.

### Request Metrics and Performance Signals

- Middleware: `app/Http/Middleware/RequestMetricsMiddleware.php`
- Adds response headers:
	- `Server-Timing` with request duration
	- `X-Response-Time` in milliseconds
- Logs slow requests over `OBSERVABILITY_SLOW_REQUEST_MS` (default: `1000`).
- Tracks per-minute request counters in cache.

Environment variables:

- `OBSERVABILITY_SLOW_REQUEST_MS=1000`
- `HEALTH_CACHE_TTL_SECONDS=5`

## Cross-Browser and Responsive E2E Testing

Playwright configuration runs a compatibility matrix for:

- Desktop Chrome
- Desktop Firefox
- Desktop Safari (WebKit)
- Mobile Chrome (Pixel 5)
- Tablet Safari (iPad Pro)

Files:

- `playwright.config.js`
- `tests/e2e/smoke.spec.js`

Run steps:

1. Install dependencies: `npm install`
2. Install Playwright browsers: `npx playwright install --with-deps`
3. Start app server (example): `php artisan serve`
4. Run tests: `npm run e2e`

## Recommended Feature Extensions Implemented

### User Features

- Forgot password: available via `password.request` route and auth screens.
- Search routines: community feed supports keyword search (`q`) across title/body/contributor.
- Filter routines by mood: community feed supports explicit mood filter (`mood_tag`) and latest mood scope.
- Bookmark favorites: routine bookmark toggle is available in feed (`Bookmark` / `Bookmarked`).
- User achievements/badges: dashboard now surfaces unlocked achievement badges.
- Dark mode: persistent theme toggle is available on authenticated and guest screens.

### Community Features

- Comments: routine-level comments are supported.
- Replies: threaded replies are supported for routine comments.
- Follow favorite contributors: follow/unfollow contributor actions added from routine cards.
- Trending routines: trending sort and a trending routines panel are available in community feed.

### Admin Features

- User suspension: available from user management.
- User banning: ban/unban actions available from user management.
- Audit logs: dedicated admin audit log page with filtering (`/admin/audit-logs`).
- Moderation history: available in reports/admin moderation center and now also written to audit logs.

### Analytics Features

- Weekly mood report: personal analytics weekly card.
- Monthly mood report: personal analytics monthly card.
- Mood streaks: current and longest streak metrics.
- Most improved mood report: best weekly improvement window shown in personal analytics.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
