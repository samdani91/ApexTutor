# TutorKhujo

> A modern tuition-finding platform built for Bangladesh — connecting guardians and students with verified, qualified tutors.

---

## Overview

TutorKhujo simplifies the process of finding a private tutor. Guardians can search by subject, class level, location, medium, and budget. Tutors build detailed profiles that get verified by admins before going live. The platform handles shortlisting, connection requests, and feedback — all in one place.

---

## Features

| Area | Highlights |
|---|---|
| **Search & Discovery** | Filter by class, subject, medium, district, area, salary, teaching style |
| **Tutor Profiles** | Education background, university badge, preferred locations, salary range |
| **Verification** | Admin-reviewed profiles with public verified badge |
| **Shortlist System** | Guardians shortlist tutors; admin assists with connection confirmation |
| **Notifications** | Real-time platform + email notifications for key events (queued) |
| **Feedback** | Users submit platform feedback; admins moderate landing page testimonials |
| **Admin Panel** | Full audit log, user management, feedback moderation, dashboard stats |

---

## Tech Stack

**Frontend**
- Vue 3 (Composition API) + Vite
- Tailwind CSS
- Vue Router · Pinia · Axios

**Backend**
- Laravel 11 · PHP 8.4
- MySQL 8.4
- Laravel Queues (database driver) for async email notifications
- Laravel Sanctum for API authentication

**Infrastructure**
- [Laradock](https://laradock.io/) — Docker environment (Nginx, PHP-FPM, MySQL, phpMyAdmin, Redis)

---

## Getting Started

### Prerequisites

- Docker & Docker Compose
- Node.js 18+
- Git

---

### Backend Setup (Laradock)

```bash
# 1. Clone the repository
git clone https://github.com/samdani91/Tuition_Platform.git
cd Tuition_Platform

# 2. Configure the Laravel environment
cd backend
cp .env.example .env

# 3. Start Laradock containers
cd laradock
docker compose up -d nginx mysql phpmyadmin workspace

# 4. Enter the workspace container
docker compose exec workspace bash

# 5. Inside workspace — install dependencies and set up the app
composer install
php artisan key:generate
php artisan migrate --seed

# 6. Start the queue worker (for email notifications)
php artisan queue:work
```

The API will be available at `http://localhost`.

> phpMyAdmin runs at `http://localhost:8080` (user: `root`, password: `root`)

---

### Frontend Setup

```bash
# From the repo root
cd frontend
npm install
npm run dev
```

The frontend runs at `http://localhost:5173`.

---

## Project Structure

```
Tuition_Platform/
├── backend/          # Laravel 11 API
│   ├── app/
│   │   ├── Http/Controllers/
│   │   ├── Models/
│   │   └── Notifications/
│   ├── database/migrations/
│   ├── routes/api.php
│   └── laradock/     # Docker environment
└── frontend/         # Vue 3 SPA
    └── src/
        ├── views/
        ├── components/
        ├── stores/
        └── api/
```

---

## License

MIT
