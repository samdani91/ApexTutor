# TutorKhujo

A tuition-finding platform for Bangladesh that connects guardians and students with verified tutors.

## Features

- Search tutors by subject, class level, location, medium, and budget
- Tutor profiles with education background, salary range, and teaching preferences
- University verification badge and profile completion tracking
- Guardian and tutor registration with role-based dashboards
- Shortlist system with admin-assisted connection confirmation
- Platform feedback with moderated landing page testimonials
- Admin panel with audit logging and notification system

## Tech Stack

**Frontend** — Vue 3, Vite, Tailwind CSS, Vue Router, Pinia

**Backend** — Laravel 11, MySQL, Laravel Queues (notifications), Sanctum (auth)

## Getting Started

### Prerequisites
- Docker & Docker Compose
- Node.js 18+

### Backend
```bash
cd backend
cp .env.example .env
docker compose up -d
docker exec <app-container> php artisan migrate --seed
```

### Frontend
```bash
cd frontend
npm install
npm run dev
```

The frontend runs on `http://localhost:5173` and expects the API at `http://localhost:8000`.
