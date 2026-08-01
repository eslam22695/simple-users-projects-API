# Task Management API

A RESTful API for a simple Task Management System, built with **Laravel 12** and **Sanctum** authentication. Users manage their own projects, each project holds multiple tasks, and a dashboard endpoint aggregates key statistics.

## Features

- Token-based authentication with Laravel Sanctum (register, login, logout)
- Full CRUD for Projects and nested Tasks
- Task filtering by status and priority, plus title search
- Dashboard endpoint with aggregated statistics
- Authorization via Policies — users access only their own data
- Soft deletes on projects and tasks
- Overdue-task notifications dispatched through a queued job on a daily schedule
- Consistent JSON response envelope for both success and error cases

## Tech Stack

- PHP 8.2+ / Laravel 12
- MySQL
- Laravel Sanctum (API tokens)
- Pest (feature tests)
- Larastan / PHPStan level 6 (static analysis)
- Scramble (OpenAPI documentation)

## Architecture

The codebase follows a layered structure:

```
Controller  ->  Service  ->  Repository (interface + Eloquent impl)  ->  Model
```

- **Form Requests** handle validation.
- **Resources** shape the JSON output.
- **Policies** enforce ownership-based authorization.
- **Enums** back the status and priority fields.
- **DTOs** carry aggregated dashboard data.

---

## Installation

### Requirements

- PHP 8.2 or higher
- Composer
- MySQL
- (Optional) Redis for queues

### Steps

```bash
# 1. Clone the repository
git clone git@github.com:eslam22695/simple-users-projects-API.git
cd simple-users-projects-API

# 2. Install dependencies
composer install

# 3. Set up the environment file
cp .env.example .env
php artisan key:generate

# 4. Configure the database (see Environment Setup below), then run:
php artisan migrate:fresh --seed

# 5. Serve the application
php artisan serve
```

The API is now available at `http://localhost:8000/api`.

### Running with Docker (Laravel Sail)

A `compose.yaml` is included. With Docker installed:

\```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate:fresh --seed
\```

The API will be available at `http://localhost`.

---

## Environment Setup

Update the following values in your `.env` file:

```dotenv
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
```

### Seeded Test User

After seeding, log in with:

- **Email:** `test@example.com`
- **Password:** `password`

---

## Running the Queue & Scheduler

Overdue-task notifications are dispatched via a queued job scheduled daily.

```bash
# Process queued jobs
php artisan queue:work

# Manually trigger the overdue check (normally run by the scheduler)
php artisan tasks:notify-overdue
```

In production, add a single cron entry running `php artisan schedule:run` every minute.

---

## Running Tests

```bash
php artisan test
```

Tests run against an in-memory SQLite database and cover authentication, CRUD, authorization, filtering, and the dashboard.

---

## Static Analysis

```bash
./vendor/bin/pint          # Code style
./vendor/bin/phpstan analyse   # Static analysis (level 6)
```

---

## API Documentation

Interactive OpenAPI documentation is generated automatically by Scramble:

- **UI:** `http://localhost:8000/docs/api`
- **OpenAPI JSON:** `http://localhost:8000/docs/api.json`

A Postman collection and exported OpenAPI spec are available in the `docs/` folder.

---

## API Endpoints

All endpoints are prefixed with `/api`. Protected routes require a
`Authorization: Bearer {token}` header.

### Authentication

| Method | Endpoint         | Description         | Auth |
|--------|------------------|---------------------|------|
| POST   | `/auth/register` | Register a new user | No   |
| POST   | `/auth/login`    | Log in, get a token | No   |
| POST   | `/auth/logout`   | Revoke current token| Yes  |

### Projects

| Method | Endpoint          | Description        | Auth |
|--------|-------------------|--------------------|------|
| GET    | `/projects`       | List projects      | Yes  |
| POST   | `/projects`       | Create a project   | Yes  |
| GET    | `/projects/{id}`  | View a project     | Yes  |
| PUT    | `/projects/{id}`  | Update a project   | Yes  |
| DELETE | `/projects/{id}`  | Delete a project   | Yes  |

### Tasks (nested under projects)

| Method | Endpoint                              | Description       | Auth |
|--------|---------------------------------------|-------------------|------|
| GET    | `/projects/{project}/tasks`           | List tasks        | Yes  |
| POST   | `/projects/{project}/tasks`           | Create a task     | Yes  |
| GET    | `/projects/{project}/tasks/{task}`    | View a task       | Yes  |
| PUT    | `/projects/{project}/tasks/{task}`    | Update a task     | Yes  |
| DELETE | `/projects/{project}/tasks/{task}`    | Delete a task     | Yes  |

**Task list query parameters:**

| Parameter  | Type   | Description                          |
|------------|--------|--------------------------------------|
| `status`   | string | Filter by status (`todo`, `in_progress`, `done`) |
| `priority` | string | Filter by priority (`low`, `medium`, `high`)     |
| `search`   | string | Search by title                      |
| `per_page` | int    | Results per page (1–100, default 15) |

Example: `GET /api/projects/1/tasks?status=todo&priority=high&search=login`

### Dashboard

| Method | Endpoint     | Description                | Auth |
|--------|--------------|----------------------------|------|
| GET    | `/dashboard` | Aggregated user statistics | Yes  |

Returns: total projects, active projects, total tasks, completed tasks,
pending tasks, and overdue tasks.

---

## Response Format

All responses share a consistent envelope.

**Success:**

```json
{
  "success": true,
  "message": "Operation successful.",
  "data": { }
}
```

**Error:**

```json
{
  "success": false,
  "message": "This action is unauthorized.",
  "errors": null
}
```

**Validation error (422):**

```json
{
  "success": false,
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

---

## HTTP Status Codes

| Code | Meaning               |
|------|-----------------------|
| 200  | OK                    |
| 201  | Created               |
| 401  | Unauthenticated       |
| 403  | Forbidden             |
| 404  | Not Found             |
| 422  | Validation Error      |
| 429  | Too Many Requests     |

---

## Database Schema

- **users** — `id`, `name`, `email`, `password`, timestamps
- **projects** — `id`, `user_id`, `name`, `description`, `status`, timestamps, `deleted_at`
- **tasks** — `id`, `project_id`, `title`, `description`, `priority`, `status`, `due_date`, timestamps, `deleted_at`

**Relationships:**

- User `hasMany` Projects
- Project `hasMany` Tasks