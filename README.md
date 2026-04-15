# Safely — Compliance Task Manager

A Laravel application for managing operational compliance tasks. Site managers can create tasks, assign them to users, set due dates, and track completion or non-compliance with corrective action notes.

## Tech Stack

- PHP 8.3 / Laravel 13
- MySQL 8.4
- Blade + Bootstrap 5 + jQuery + Vite
- Spatie Activity Log for audit trails

## Features

- Create, edit, and view tasks with title, description, due date, assigned user, and priority (low / medium / high)
- Task statuses: pending, completed, non-compliant
- Corrective action notes required when marking a task as non-compliant
- Dashboard with filters for status, assigned user, and due date (today / overdue / all)
- Overdue and due-today row highlighting
- AJAX-powered task editing and quick status updates (no full page reload)
- Modal-based task detail view with activity log history
- Queued email notification when a task is marked non-compliant
- REST API with JSON resources (`/api/tasks`)
- Database seeders: 5 users and 50 tasks

## Requirements

- PHP >= 8.3
- Composer
- Node.js >= 18 and npm
- MySQL 8.x
- Docker & Docker Compose (optional, for Sail)

## Setup

### Option 1 — Local (without Docker)

Requires PHP >= 8.3, Composer, Node >= 18, npm, and a running MySQL server.

```bash
git clone https://github.com/itamarack/safely-systems.git
cd safely-systems
cp .env.example .env
```

Update the database settings in `.env` to point to your local MySQL:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

Then run the setup:

```bash
composer setup
```

`composer setup` handles everything in one step:

1. `composer install`
2. Copies `.env.example` → `.env` (if `.env` doesn't exist)
3. Generates the app key
4. Runs migrations and seed database
5. `npm install`
6. `npm run build`

Start the dev environment:

```bash
composer dev
```

This launches four processes in parallel via `concurrently`:

| Process | What it does |
|---------|-------------|
| `php artisan serve` | App server on http://localhost:8000 |
| `php artisan queue:listen` | Queue worker for notifications |
| `php artisan pail` | Real-time log tail |
| `npm run dev` | Vite dev server with HMR |

The app will be available at **http://localhost:8000**.

---

### Option 2 — Docker (Laravel Sail)

Requires Docker and Docker Compose. No local PHP or Node install needed.

```bash
git clone https://github.com/itamarack/safely-systems.git
cd safely-systems
```

Copy the environment file and configure it for Sail's MySQL container:

```bash
cp .env.example .env
```

Update the database settings in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

Install dependencies and start the containers:

```bash
composer install
./vendor/bin/sail up -d
```

Run the setup inside the container:

```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

The app will be available at **http://localhost** (port 80).

To start the queue worker for non-compliance notifications:

```bash
./vendor/bin/sail artisan queue:listen
```

To stop the containers:

```bash
./vendor/bin/sail down
```

## Usage

### Web Dashboard

Navigate to `/` to see the dashboard. From there you can:

- Filter tasks by status, assigned user, or due date
- Click a task title to view details and activity log in a modal
- Click the pencil icon to edit a task (AJAX modal, no page reload)
- Use quick-action buttons to mark tasks as completed, non-compliant, or reset to pending
- When marking non-compliant, a corrective action textarea appears and is required

### API Endpoints

All endpoints are prefixed with `/api/tasks`.

| Method  | URI                    | Description          |
|---------|------------------------|----------------------|
| GET     | `/api/tasks`           | List tasks (paginated, filterable) |
| POST    | `/api/tasks`           | Create a task        |
| GET     | `/api/tasks/{id}`      | Show a single task   |
| PUT     | `/api/tasks/{id}`      | Update a task        |
| PATCH   | `/api/tasks/{id}/status` | Update status only |

Query parameters for filtering: `status`, `user_id`, `due_filter` (today / overdue).

## Project Structure

```
app/
├── Data/              TaskFilters DTO for clean filter passing
├── Enums/             TaskStatus, TaskPriority, DueFilter (backed enums)
├── Events/            TaskUpdated event
├── Http/
│   ├── Controllers/   Web + Api TaskController
│   ├── Requests/      StoreTask, UpdateTask, UpdateTaskStatus form requests
│   └── Resources/     TaskResource, TaskCollection, UserResource (API)
├── Listeners/         SendNonComplianceNotification (queued)
├── Models/            Task, User
├── Notifications/     TaskNonCompliantNotification (queued mail)
├── Providers/         AppServiceProvider (Bootstrap 5 paginator)
└── Repositories/      TaskRepository (query + pagination)
```

## Key Design Decisions

- **Backed enums** for status, priority, and due filters — type-safe, with helper methods for labels and badge classes.
- **Form Request classes** handle all validation, including the conditional `corrective_action` requirement via `required_if:status,non_compliant`.
- **TaskFilters DTO** keeps filter logic out of controllers; the `Task::scopeWithFilters` scope applies them at the query level.
- **Repository pattern** for task queries — keeps controllers thin and query logic reusable.
- **Event/Listener pattern** for non-compliance notifications — the `SendNonComplianceNotification` listener is queued, so it doesn't block the request.
- **Spatie Activity Log** tracks field-level changes on tasks automatically.
- **Blade partials** (`_table`, `_show`, `_status_badge`) for reusable, AJAX-friendly view fragments.

## Running Tests

```bash
composer test
```

Or directly:

```bash
php artisan test
```

## If I Had Another 2–3 Hours

1. **Expand test coverage** — The current suite covers the core API lifecycle, validation, filters, and notification dispatch. With more time I'd add web controller tests, edge cases around due date filtering, and test the activity log integration.
2. **Authentication & authorization** — Scaffold login with Laravel Breeze and add gate/policy checks so only the assigned user or a manager can update tasks. Essential for any real SaaS product.
3. **Redis queue driver** — Swap from database queue to Redis for better throughput on background jobs like non-compliance notifications, and add failed job retry handling.
4. **Dashboard summary stats** — Add counters at the top of the dashboard (total pending, overdue, completed today, non-compliant) for a quick at-a-glance overview without scrolling.
5. **API authentication & rate limiting** — Wire up Sanctum token-based auth and add rate limiting middleware to protect the API endpoints for external integrations.

## AI Usage Note

AI-assisted tools (Claude) were used during development.

**What I used AI for:**
- Scaffolding boilerplate (migrations, factories, seeders, form requests)
- Generating Blade view markup
- Assisted in drafting this README

**Where it helped:**
- Accelerated repetitive setup work (migration schemas, factory definitions, validation rules) so I could focus more time on architecture and UX decisions
- Helped generate consistent Bootstrap markup across multiple Blade partials

**What I reviewed or changed manually:**
- All architectural decisions (controllers, enums, DTO pattern, repository pattern, event/listener pattern) were deliberate choices, not AI-suggested
- Reviewed and adjusted every generated file for consistency, naming conventions, and Laravel best practices
- Manually tested the full workflow end-to-end (create, filter, edit, status updates, non-compliance flow)
- Tuned the Spatie Activity Log configuration and verified field-level change tracking

**Trade-offs or shortcuts:**
- No authentication — kept out of scope to focus on the core compliance workflow within the time limit
- Mail driver set to `log` — notifications are queued and dispatched but not sent to a real SMTP server
