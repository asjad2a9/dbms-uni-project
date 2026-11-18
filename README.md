# 🏋️ IronPulse Gym — Gym Management System

A mid-level **Gym Management System** built with **Laravel 11 + Blade + MySQL**.
No Redis, no Node build step, no queues. It runs on **shared cPanel hosting** with only PHP and MySQL.

It has two parts:

1. **Public website** (`/`): landing page with features, live membership plans, trainers, and a "Book a free visit" form (saved as an inquiry).
2. **Admin panel** (`/admin`): a dashboard plus pages for members, subscriptions, payments, attendance, plans, trainers, expenses, inquiries, reports and staff users.

The UI uses Bootstrap 5, Bootstrap Icons and Chart.js from a CDN, so there is nothing to compile. Every page is responsive: on phones the sidebar becomes a slide-in menu and tables scroll sideways.

---

## ✨ Features

| Module | What it does |
|---|---|
| **Auth** | Session login/logout, "remember me", disabled accounts blocked, roles `admin` / `staff` |
| **Dashboard** | KPI cards, income vs expense chart (6 months), 7-day attendance chart, subscriptions expiring within 7 days, recent payments, newest members |
| **Members** | CRUD, search/filter, auto member code (`GYM-00001`), BMI, assigned trainer, profile page with subscription, payment and attendance history, optional plan and payment at registration |
| **Subscriptions** | Assign or renew plans, end date set automatically, discount, paid/due tracking, cancel, auto-expire, "expiring soon" filter |
| **Payments** | Record payments (cash/card/bank/online), auto invoice no (`INV-YYYYMM-00001`), printable receipt, date and method filters |
| **Attendance** | Quick check-in, check-out, daily log, warning when a member has no active plan |
| **Membership Plans** | Pricing cards, features list, active/inactive, protected from deletion while in use |
| **Trainers** | CRUD, specialization, salary, assigned members |
| **Expenses** | Categorised expenses with date filters and totals |
| **Inquiries** | Leads from the website form, status workflow, "convert to member" |
| **Reports** | Yearly income, expenses, profit, new members and visits per month, with charts and a printable table |
| **Staff Users** | Admin-only management of staff accounts |
| **Profile** | Update own details and password |

---

## 🗄️ Database Schema

📐 **ERD, keys and relationships:** [docs/ERD.md](docs/ERD.md) · DDL: [docs/schema.sql](docs/schema.sql) · Example JOIN queries: [docs/queries.sql](docs/queries.sql)

![ERD](docs/erd.png)

```
users              (id, name, email, phone, role[admin|staff], is_active, password, ...)
membership_plans   (id, name, duration_days, price, description, features, is_active)
trainers           (id, name, email, phone, gender, specialization, experience_years, salary, joined_at, bio, status)
members            (id, member_code, name, email, phone, gender, dob, address, emergency_contact,
                    height_cm, weight_kg, trainer_id → trainers, joined_at, status, notes)
subscriptions      (id, member_id → members, membership_plan_id → membership_plans,
                    start_date, end_date, amount, discount, status[active|expired|cancelled])
payments           (id, invoice_no, member_id → members, subscription_id → subscriptions,
                    amount, method, paid_at, reference, notes, received_by → users)
attendances        (id, member_id → members, date, check_in, check_out)
expenses           (id, title, category, amount, spent_on, notes)
inquiries          (id, name, email, phone, membership_plan_id → membership_plans, message, status)
sessions, password_reset_tokens  (Laravel defaults)
```

**Relationships:** Member *belongsTo* Trainer · Member *hasMany* Subscriptions / Payments / Attendances · Subscription *belongsTo* Plan and *hasMany* Payments · Payment *belongsTo* User (received by).
Foreign keys use `cascadeOnDelete` / `nullOnDelete` / `restrictOnDelete` where each makes sense.

---

## 🚀 Local Setup

Requirements: PHP 8.2+ with `pdo_mysql`, Composer, MySQL 5.7+ or MariaDB 10.3+.

```bash
git clone <this-repo> gym && cd gym
composer install
cp .env.example .env
php artisan key:generate
# create an empty MySQL database (e.g. gym_db) and set DB_* in .env
php artisan migrate --seed
php artisan serve
```

Open http://127.0.0.1:8000 for the website and http://127.0.0.1:8000/login for the admin panel.

### Demo logins (from the seeder)

| Role | Email | Password |
|---|---|---|
| Admin | `admin@gym.test` | `password` |
| Staff | `staff@gym.test` | `password` |

The seeder adds 4 plans, 5 trainers, 30 members with a year of subscriptions, payments and attendance, 12 months of expenses, and 5 inquiries.

---

## ☁️ Deploying to Shared cPanel Hosting

1. **Build locally:** run `composer install --no-dev --optimize-autoloader`, then zip the whole project, including `vendor/`.
2. **Upload:** in cPanel → *File Manager*, upload and extract into a folder **outside** `public_html`, e.g. `/home/USER/gym`.
3. **Point the web root to `public/`.** Pick one:
   - **Addon domain or subdomain:** set its document root to `/home/USER/gym/public`.
   - **Main domain:** copy the contents of `gym/public/` into `public_html/`, then edit `public_html/index.php`:
     ```php
     require __DIR__.'/../gym/vendor/autoload.php';
     $app = require_once __DIR__.'/../gym/bootstrap/app.php';
     // and the maintenance line:
     if (file_exists($maintenance = __DIR__.'/../gym/storage/framework/maintenance.php')) {
     ```
4. **Database:** cPanel → *MySQL® Databases*. Create a database and a user, and give the user **ALL PRIVILEGES** on it.
5. **.env:** copy `.env.example` to `.env` and set:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_DATABASE=cpaneluser_gym
   DB_USERNAME=cpaneluser_gymuser
   DB_PASSWORD=********
   SESSION_DRIVER=file
   CACHE_STORE=file
   QUEUE_CONNECTION=sync
   ```
6. **Run migrations:**
   - With *Terminal* or SSH: `php artisan key:generate && php artisan migrate --seed --force && php artisan config:cache && php artisan route:cache && php artisan view:cache`
   - Without SSH: run the commands locally against an empty MySQL database, export it with phpMyAdmin, and import it on the server. Generate `APP_KEY` locally and paste it into `.env`.
7. **Permissions:** `storage/` and `bootstrap/cache/` must be writable (755 or 775).
8. **Optional daily auto-expire:** subscriptions already expire automatically whenever the dashboard or subscriptions page loads, so no cron job is needed.

> After you log in, change the demo passwords from **My Profile**, or create real users under **Staff Users**.

---

## 📁 Project Structure (key parts)

```
app/Http/Controllers/
  AuthController.php, SiteController.php
  Admin/ Dashboard, Member, Subscription, Payment, Attendance, MembershipPlan,
         Trainer, Expense, Inquiry, Report, User, Profile controllers
app/Http/Middleware/EnsureAdmin.php      # 'admin' middleware alias
app/Models/                              # 9 Eloquent models
config/gym.php                           # currency, address, phone, hours
database/migrations/                     # 9 migrations
database/seeders/DatabaseSeeder.php      # demo data
public/css/admin.css                     # theme (no build step)
resources/views/
  layouts/admin.blade.php, partials/, auth/login, site/home, admin/<module>/...
routes/web.php
```

## ⚙️ Customisation

In `.env`: `APP_NAME="IronPulse Gym"`, `GYM_CURRENCY=Rs`, `GYM_ADDRESS`, `GYM_PHONE`, `GYM_EMAIL`, `GYM_HOURS`.
To change the brand colour, edit `--brand` in `public/css/admin.css`.

## 🧰 Tech Stack

Laravel 11 · PHP 8.2+ · MySQL · Blade · Bootstrap 5.3 · Bootstrap Icons · Chart.js 4. All front-end assets load from a CDN.
