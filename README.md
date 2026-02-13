# WearMe

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![React](https://img.shields.io/badge/React-18-61DAFB?style=flat&logo=react)](https://reactjs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5-3178C6?style=flat&logo=typescript)](https://www.typescriptlang.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2.0-9553E9?style=flat)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.0-06B6D4?style=flat&logo=tailwindcss)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

WearMe is an AI-powered virtual wardrobe and try-on platform that lets you upload clothing photos, create a digital wardrobe, and virtually try on outfits using artificial intelligence. Perfect for wardrobe organization, outfit planning, trip packing, and discovering new style combinations.

## About

WearMe combines modern web technologies with AI-powered virtual try-on capabilities to help users manage their wardrobe digitally. Upload your clothing items and model photos, then use AI to see how different outfit combinations look on you before getting dressed. Create lookbooks, plan outfits for trips, get AI-powered outfit suggestions, and share your favorite looks with friends.

## Features

### Digital Wardrobe Management
Organize your clothing with smart categorization (upper/lower/dress), detailed metadata (brand, material, size, measurements, color tags), and bulk upload capabilities. Import items directly from Amazon URLs with automatic metadata extraction. Supports up to 200 garments per user with duplicate detection.

### AI Virtual Try-On
Powered by Kling AI, generate realistic virtual try-on images with single or multi-garment combinations. Chain try-ons using previous results as a base, apply style preferences with custom prompts, and generate animated try-on videos. Browse your try-on history and save favorites.

### Smart Outfit Planning
Get AI-powered outfit suggestions based on occasion context with color harmony scoring and fit analysis. Create named lookbooks with descriptions and cover images, organize looks with drag-and-drop reordering, and share publicly with emoji reactions.

### Trip Planning
Build packing lists for specific trips with date ranges, assign garments to days and occasions, and track packing progress with check-off functionality.

### Social Sharing
Share lookbooks and try-on results via public token-based links with emoji reactions (heart, fire, thumbs up/down) and view count tracking. Privacy controls let you toggle public/private visibility.

### Export & Backup
Export your complete wardrobe data as JSON with optional images and try-on results packaged in a ZIP archive (500MB limit, 24-hour expiry).

### Internationalization
Full support for English and Spanish with 17 translation namespaces covering all application features.

## Tech Stack

### Backend
- **Laravel 11** - Modern PHP web framework
- **PHP 8.2+** - Server-side language
- **Sanctum** - API authentication
- **Laravel Breeze** - Authentication scaffolding
- **Socialite** - Google OAuth integration
- **SQLite/MySQL** - Database options
- **Database Queue** - Background job processing

### Frontend
- **React 18** - UI library
- **TypeScript** - Type-safe JavaScript
- **Inertia.js 2.0** - Modern monolith architecture
- **Tailwind CSS 4.0** - Utility-first CSS
- **Headless UI** - Accessible UI components
- **Vite 7** - Build tool
- **i18next** - Internationalization

### External Services
- **Kling AI API** - Virtual try-on generation
- **Gemini** - Alternative AI provider (optional)

### Development & Testing
- **PHPUnit** - 334 feature and unit tests
- **Playwright** - End-to-end testing
- **Docker DevContainer** - Containerized development environment (PHP 8.3-FPM, MySQL 8.0, Redis 7, Mailpit)

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- SQLite or MySQL database
- Kling AI API credentials (required for try-on features)

## Quick Start

### Using Composer Scripts

```bash
# One-time setup: install dependencies, configure .env, generate key, migrate database
composer setup

# Development: runs serve, queue worker, pail logger, and vite dev server in parallel
composer dev
```

### Manual Setup

```bash
# Install PHP dependencies
composer install

# Copy environment file and generate application key
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Create storage symlink
php artisan storage:link

# Install frontend dependencies and build assets
npm install
npm run build

# Start development server (in separate terminals)
php artisan serve
php artisan queue:work
```

### Docker DevContainer

The project includes a complete Docker development environment:

```bash
cd .devcontainer
docker-compose up -d
```

Includes PHP 8.3-FPM, MySQL 8.0, Redis 7, and Mailpit for email testing.

## Configuration

### Required Environment Variables

```env
APP_NAME=WearMe
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite

# Kling AI (required for virtual try-on)
KLING_ACCESS_KEY=your_access_key
KLING_SECRET_KEY=your_secret_key
TRYON_PROVIDER=kling

# Optional: Gemini provider
GEMINI_API_KEY=your_api_key

# Optional: Google OAuth
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### Queue Configuration

The application uses database-driven queues for background processing. Ensure a queue worker is running:

```bash
php artisan queue:work --tries=3 --timeout=300
```

Background jobs include:
- **ProcessTryOn** - Submit try-on requests to AI provider
- **PollProviderTask** - Poll for try-on results (30 attempts, 10s delay)
- **ProcessTryOnVideo** - Generate animated try-on videos
- **PollKlingTask** - Poll for video generation results
- **GenerateOutfitSuggestion** - AI-powered outfit combinations
- **ProcessBulkGarment** - Background bulk garment uploads
- **GenerateExport** - Create wardrobe export archives

## Development

### Available Commands

```bash
# Development server with hot reload
npm run dev

# Build for production
npm run build

# Run PHP tests (334 tests)
php artisan test

# Run E2E tests
npm run test:e2e

# Interactive E2E testing
npm run test:e2e:ui

# Run queue worker
php artisan queue:work

# Restart queue workers (after code changes)
php artisan queue:restart

# Monitor logs
php artisan pail
```

### Testing

The application includes comprehensive test coverage:

- **334 PHPUnit tests** - Feature and unit tests covering all major functionality
- **28 feature test files** - Controllers, services, jobs, and policies
- **4 unit test files** - Core business logic
- **Playwright E2E tests** - Full user journey testing

Test environment uses in-memory SQLite, array cache, synchronous queue, and array mail driver for fast, isolated testing.

## Project Structure

```
app/
├── Contracts/          # Interface definitions (TryOnProviderContract)
├── Enums/              # Enumeration classes (GarmentCategory, ProcessingStatus)
├── Http/
│   ├── Controllers/    # 17 application controllers
│   ├── Requests/       # Form validation requests
│   └── Resources/      # Inertia API resources
├── Jobs/               # 7 background queue jobs
├── Models/             # 15 Eloquent models
├── Policies/           # Authorization policies
└── Services/           # 13 business logic services
    ├── TryOn/          # Try-on orchestration
    ├── KlingApi/       # Kling AI integration
    └── Scraper/        # Amazon import scraping

resources/
├── js/
│   ├── Components/     # Reusable React components
│   ├── i18n/locales/   # Translation files (en/, es/)
│   ├── Layouts/        # Page layout components
│   ├── Pages/          # 37 Inertia page components
│   └── types/          # TypeScript type definitions
└── css/                # Application styles

tests/
├── Feature/            # 28 feature test files
└── Unit/               # 4 unit test files

database/
├── migrations/         # Database schema migrations
└── seeders/            # Database seeders
```

## Internationalization

WearMe supports multiple languages through i18next:

- **English** (default)
- **Spanish** (Español)

Translation files are organized into 17 namespaces: `common`, `wardrobe`, `tryon`, `outfits`, `lookbooks`, `share`, `processing`, `packing`, `export`, `nav`, `import`, `auth`, `dashboard`, `photos`, `profile`, `videos`, and `welcome`.

Language preference is stored in cookies with automatic browser detection fallback.

## License

WearMe is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Getting Help

For issues or questions, please check the documentation in the codebase or review the test files for usage examples.
