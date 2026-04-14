# FreeCause - Online Petition Platform

A multi-language petition platform built with Laravel 12 that enables users to create, sign, and share petitions for causes they care about.

## Features

- **Multi-language Support**: 14 languages (da, de, el, en, es, fr, it, nl, pl, pt, ro, ru, sv, tr)
- **Petition Management**: Create, edit, and manage petitions
- **Signature Collection**: Gather signatures with optional comments
- **Admin Panel**: Full-featured admin dashboard with:
  - Dashboard with statistics
  - User, petition, category, and page management
  - Language and translation management
  - Database backup functionality
  - Spam management (IP banning)
  - System health monitoring
- **Security**: CSP headers, rate limiting, IP blocking
- **Performance**: Caching, lazy loading, optimized queries
- **Dark Mode**: User-selectable dark theme

## Requirements

- PHP 8.2+
- Laravel 12.x
- MySQL/MariaDB or SQLite
- Redis (optional, for caching)

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Copy environment file:
   ```bash
   cp .env.example .env
   ```
4. Generate application key:
   ```bash
   php artisan key:generate
   ```
5. Configure database in `.env`
6. Run migrations:
   ```bash
   php artisan migrate
   ```
7. Build assets:
   ```bash
   npm run build
   ```

## Admin Panel

Access the admin panel at `/admin/login`.

Default admin user can be created via:
```bash
php artisan admin:create
```

## Configuration

### Global Settings (via Admin)
- Site branding (name, logo, colors)
- SMTP configuration
- OAuth (Google) credentials
- Email templates
- Announcement banners

### Environment Variables
- `CACHE_STORE` - Cache driver (database, redis, file)
- `SESSION_DRIVER` - Session driver
- `QUEUE_CONNECTION` - Queue driver for async jobs

## Translation

Translations are managed in:
- `resources/lang/{locale}/messages.php` - UI strings
- Database tables for dynamic content

Add new translations via Admin Panel > Translations

## Security Features

- Content Security Policy (CSP) headers
- Rate limiting on forms
- IP banning for spam prevention
- Email verification support
- Admin audit logging

## Performance Optimizations

- Database query optimization
- Lazy loading for images
- Multi-level caching
- Indexes on frequently queried columns

## License

MIT
