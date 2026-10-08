# MindSpace — Mental Wellness Web Application

MindSpace is a web application intended to help university students and young adults manage day-to-day wellbeing. It brings together private mood and journal tools with a community feed for wellness routines.

## Features

- User registration, login, email verification, and account recovery
- Mood entries, mood dashboards, and report exports
- Private journal entries
- Community feed for wellness routines, including comments, replies, reactions, bookmarks, search, and filters
- Routine recommendations and notifications
- Administrative tools for user management, reports, moderation, and analytics

These features are described in the project routes, modules, and commit history.

## My contribution

Lenny Mwaura Mwangi led the core application logic and feature functionality. The Git history also records contributions from other project contributors.

## Technology

- PHP and Laravel
- Blade templates, Tailwind CSS, and Vite
- Database connection is configured in `.env`; this README does not assume a specific database server

## Run locally

Requirements: PHP, Composer, Node.js, npm, and a database supported by the Laravel configuration.

1. Install dependencies with `composer install`.
2. Copy `.env.example` to `.env` and configure the database connection.
3. Generate an application key with `php artisan key:generate`.
4. Create the configured database and run `php artisan migrate`.
5. Install and build frontend assets with `npm install` and `npm run build`.
6. Start the development server with `php artisan serve`.

The application URL is printed by Artisan. Browser end-to-end tests are configured in the repository; see `playwright.config.js` and the package scripts for the current command.
