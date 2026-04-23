<div align="center">

# 🌍 FreeCause

**A modern, multi-language online petition platform built with Laravel 12**

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-38BDF8?style=flat-square&logo=tailwindcss)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

*Empower people to create, sign, and share petitions for causes that matter.*

</div>

---

## 📖 Table of Contents

- [Overview](#-overview)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Getting Started](#-getting-started)
  - [Prerequisites](#prerequisites)
  - [Quick Setup](#quick-setup)
  - [Manual Setup](#manual-setup)
- [Development](#-development)
- [Configuration](#-configuration)
- [Admin Panel](#-admin-panel)
- [Internationalization](#-internationalization)
- [Security](#-security)
- [Performance](#-performance)
- [Queue Workers](#-queue-workers)
- [CI/CD](#-cicd)
- [License](#-license)

---

## 🌐 Overview

FreeCause is a full-featured online petition platform that lets users create and sign petitions in 14 languages. It ships with a powerful admin panel, Google OAuth login, email verification, two-factor authentication, spam protection, and a robust role/permission system — all built on top of Laravel 12.

---

## ✨ Features

### For Users
- 📝 **Create Petitions** — Rich petition creation flow with cover images and categories
- ✍️ **Sign & Comment** — Sign petitions and leave optional public comments
- 🔍 **Full-text Search** — Fast search across petition titles and content
- 🌙 **Dark Mode** — User-selectable light/dark theme
- 👤 **User Profiles** — Public profile pages showing created and signed petitions
- 🔐 **Authentication** — Email/password and Google OAuth login
- ✅ **Email Verification** — Account verification via email link
- 🔑 **Two-Factor Auth (2FA)** — Optional TOTP-based second factor

### For Admins
- 📊 **Dashboard** — At-a-glance stats (petitions, signatures, users, activity)
- 📋 **Petition Management** — Review, approve, edit, or remove petitions
- 👥 **User Management** — Manage users, assign levels and permissions
- 🗂️ **Category Management** — Create and translate petition categories
- 📄 **Page Management** — Editable static pages (About, Terms, etc.)
- 🌍 **Language & Translations** — Add/remove locales, edit UI strings in-browser
- 🛡️ **Spam Controls** — Ban IPs, block users, manage spam reports
- 💾 **Database Backups** — One-click database backup and download
- 📁 **Data Import** — Bulk import legacy petition data
- 📜 **Audit Logs** — Full admin action log trail
- ⚙️ **Global Settings** — SMTP, branding, OAuth credentials, announcement banners
- 🩺 **System Health** — Built-in health check endpoint (`/up`)

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| **Framework** | [Laravel 12](https://laravel.com) |
| **Language** | PHP 8.2+ |
| **Frontend CSS** | [Tailwind CSS v4](https://tailwindcss.com) |
| **Asset Bundler** | [Vite 7](https://vitejs.dev) |
| **Database** | MySQL / MariaDB / SQLite |
| **Cache / Queue** | Database, Redis, or File |
| **OAuth** | [Laravel Socialite](https://laravel.com/docs/socialite) (Google) |
| **PDF Generation** | [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) |
| **Image Processing** | [intervention/image-laravel](https://image.intervention.io) |
| **Process Manager** | Supervisor (queue workers) |
| **CI/CD** | Jenkins |

---

## 📁 Project Structure

```
freecause/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # All admin panel controllers
│   │   │   └── ...             # Public-facing controllers
│   │   └── Middleware/         # CSP, locale, IP blocking, audit log, etc.
│   ├── Models/                 # Eloquent models
│   ├── Services/               # Business logic services
│   └── helpers.php             # Global helper functions
├── database/
│   ├── migrations/             # Database schema history
│   ├── factories/              # Model factories for testing
│   └── seeders/                # Database seeders
├── resources/
│   ├── css/                    # Tailwind CSS entry point
│   ├── js/                     # JavaScript entry point
│   ├── lang/                   # PHP translation files (14 locales)
│   └── views/                  # Blade templates
├── routes/
│   └── web.php                 # All HTTP routes (public + admin)
├── docs/                       # Internal development docs
├── freecause-queue.conf        # Supervisor config for queue workers
├── Jenkinsfile                 # CI/CD pipeline definition
└── vite.config.js              # Vite + Tailwind build config
```

---

## 🚀 Getting Started

### Prerequisites

- **PHP** 8.2 or higher (with extensions: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd`)
- **Composer** 2.x
- **Node.js** 20+ and **npm**
- **MySQL** 8.0+ / **MariaDB** 10.6+ (or SQLite for local dev)
- **Redis** *(optional — for caching and queues)*

---

### Quick Setup

The fastest way to get running locally uses the built-in `composer setup` script:

```bash
git clone <repository-url> freecause
cd freecause

# Runs: composer install, .env copy, key:generate, migrate, npm install, npm run build
composer run setup
```

Then start all dev services in one command:

```bash
composer run dev
```

This starts **PHP dev server**, **queue worker**, **Pail log viewer**, and **Vite** concurrently.

Visit: [http://localhost:8000](http://localhost:8000)

---

### Manual Setup

If you prefer full control over each step:

**1. Install PHP dependencies**
```bash
composer install
```

**2. Install Node dependencies**
```bash
npm install
```

**3. Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Set up your database**

Edit `.env` and set your database credentials:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=freecause
DB_USERNAME=root
DB_PASSWORD=your_password
```

> For **SQLite** (quick local dev): set `DB_CONNECTION=sqlite` and leave other `DB_*` values blank.
> Laravel will create `database/database.sqlite` automatically.

**5. Run migrations**
```bash
php artisan migrate
```

**6. Create the first admin account**
```bash
php artisan admin:create
```

**7. Link storage**
```bash
php artisan storage:link
```

**8. Build frontend assets**
```bash
npm run build          # Production build
# or
npm run dev            # Vite dev server with HMR
```

**9. Start the development server**
```bash
php artisan serve
```

---

## 💻 Development

### Running all services together

```bash
composer run dev
```

This single command uses `concurrently` to launch:

| Process | Description |
|---|---|
| `php artisan serve` | Laravel HTTP server on port 8000 |
| `php artisan queue:listen` | Processes background jobs |
| `php artisan pail` | Real-time log viewer |
| `npm run dev` | Vite HMR dev server |

### Running tests

```bash
composer run test
# or
php artisan test
```

### Code style

The project uses [Laravel Pint](https://laravel.com/docs/pint) for PSR-12 formatting:

```bash
./vendor/bin/pint
```

---

## ⚙️ Configuration

### Key `.env` Variables

```dotenv
# Application
APP_NAME=FreeCause
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_DATABASE=freecause

# Cache & Sessions
CACHE_STORE=database        # Options: database, redis, file
SESSION_DRIVER=database     # Options: database, redis, file, cookie

# Queue
QUEUE_CONNECTION=database   # Options: database, redis, sync

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=secret

# Google OAuth (optional)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

### Admin Global Settings

Many settings can be configured directly from the admin panel without touching `.env`:

- **Branding** — Site name, logo, primary color
- **SMTP** — Mail host, port, credentials, sender address
- **Google OAuth** — Client ID and secret
- **Email Templates** — Verification and notification emails
- **Announcement Banner** — Sitewide notice banner

---

## 🔧 Admin Panel

Access the admin panel at: **`/admin/login`**

| Section | URL | Description |
|---|---|---|
| Dashboard | `/admin` | Statistics overview |
| Petitions | `/admin/petitions` | Manage all petitions |
| Signatures | `/admin/signatures` | View and moderate signatures |
| Users | `/admin/users` | Manage user accounts |
| User Levels | `/admin/user-levels` | Define roles/levels |
| Permissions | `/admin/permissions` | Assign admin permissions |
| Categories | `/admin/categories` | Petition categories |
| Pages | `/admin/pages` | Static content pages |
| Languages | `/admin/languages` | Manage supported locales |
| Translations | `/admin/translations` | Edit UI translation strings |
| Spam / IPs | `/admin/spam` | IP bans and spam management |
| Backup | `/admin/backup` | Download database backup |
| Logs | `/admin/logs` | Admin audit log |
| System | `/admin/system` | Health info and system tools |
| Settings | `/admin/settings` | Global site configuration |

---

## 🌍 Internationalization

FreeCause supports **14 languages** out of the box:

| Code | Language |
|---|---|
| `da` | Danish |
| `de` | German |
| `el` | Greek |
| `en` | English |
| `es` | Spanish |
| `fr` | French |
| `it` | Italian |
| `nl` | Dutch |
| `pl` | Polish |
| `pt` | Portuguese |
| `ro` | Romanian |
| `ru` | Russian |
| `sv` | Swedish |
| `tr` | Turkish |

All public routes are prefixed with the locale: `/{locale}/petitions`, `/{locale}/start`, etc.

**Translation files** live in `resources/lang/{locale}/messages.php`.  
**Dynamic content** (petition titles/content, categories, pages) is stored in dedicated translation tables in the database and managed through the Admin Panel > Translations.

---

## 🔒 Security

| Feature | Description |
|---|---|
| **CSP Headers** | Content Security Policy via `AddSecurityHeaders` middleware |
| **Rate Limiting** | Laravel's built-in rate limiter on auth and form routes |
| **IP Banning** | `BlockBannedIp` middleware reads from `banned_ips` table |
| **User Banning** | `BlockBannedUser` middleware blocks suspended accounts |
| **Email Verification** | Account must verify email before acting |
| **2FA** | TOTP-based two-factor authentication |
| **Admin Audit Log** | Every admin action is recorded via `AdminAuditLog` middleware |
| **Permission Checks** | `CheckPermission` middleware guards individual admin routes |
| **No Cache Headers** | Sensitive admin pages served with `no-store` cache directives |

---

## ⚡ Performance

- **Full-text indexes** on `petition_translations` for fast keyword search
- **Composite indexes** on petitions, signatures, and users for common query patterns
- **Lazy-loaded images** in listing views
- **Multi-level caching** — settings, translations, and computed stats are cached
- **Optimized autoloader** — Composer is configured with `optimize-autoloader: true`
- **`php artisan optimize`** run on every production deployment

---

## 📬 Queue Workers

Background jobs (e.g. sending verification emails, processing imports) are handled by Laravel queues. In production, a **Supervisor** process keeps workers alive.

The config file `freecause-queue.conf` can be copied to `/etc/supervisor/conf.d/`:

```ini
[program:freecause-queue]
command=php /var/www/freecause/backend/artisan queue:work --sleep=3 --tries=3 --max-time=3600
numprocs=2
user=www-data
autostart=true
autorestart=true
stdout_logfile=/var/www/freecause/backend/storage/logs/queue.log
```

Apply changes:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start freecause-queue:*
```

---

## 🚢 CI/CD

The project uses a **Jenkins** pipeline (see `Jenkinsfile`) with two stages:

### Build
```bash
npm ci && npm run build
```

### Deploy
Connects to the VPS over SSH and runs:

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link --force
php artisan optimize:clear && php artisan optimize
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart freecause-queue:*
curl -sf https://<host>/up   # smoke test
```

The health check endpoint at `/up` must return a successful response or the deployment is marked as failed.

---

## 📄 License

This project is open-sourced software licensed under the [MIT License](LICENSE).
```
