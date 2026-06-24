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
