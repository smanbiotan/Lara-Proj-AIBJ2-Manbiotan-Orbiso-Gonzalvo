# Blush & Bloom Salon

**Beauty Salon and Wellness Management Platform** — a multi-page Laravel web application with a customer website, a separate administrator portal, and AI automation through **n8n + Google Gemini**.

> Blush & Bloom Salon is a fictional business created for a school project. Prices, staff profiles and testimonials are demonstration content.

| | |
|---|---|
| **Project** | Lara-Proj-AIBJ2-Manbiotan-Orbiso-Gonzalvo |
| **Section** | AIBJ2 |
| **Group name** | _(add your group name here)_ |
| **Theme** | Local business: a beauty and wellness salon |
| **Live site** | https://blush-and-bloom-salon.vercel.app |
| **Stack** | Laravel 12 · PHP 8.2+ · MySQL (XAMPP, local) / PostgreSQL (Supabase, live) · Blade · HTML/CSS/JavaScript · n8n · Google Gemini · Vercel |
| **Database** | `blush_bloom_salon` |

---

## 1. Group members, modules, and branches

| Member | Name | Assigned module | Branch | Main files |
|---|---|---|---|---|
| Member 1 – Group Leader | **Sherwin Alvin Manbiotan** | Project setup, shared layout, Home page | `dev-member1-Manbiotan` | `resources/views/layouts/app.blade.php`, `resources/views/home.blade.php`, `public/css/layout.css`, `public/css/home.css`, `public/js/layout.js`, `public/js/home.js` |
| Member 2 | **Gerald EJ Gonzalvo** | About page | `dev-member2-Gonzalvo` | `resources/views/about.blade.php`, `public/css/about.css`, `public/js/about.js` |
| Member 3 | **Anthony Josh Orbiso** | Contact page | `dev-member3-Orbiso` | `resources/views/contact.blade.php`, `public/css/contact.css`, `public/js/contact.js` |

---

## 2. Screenshots

| Home | About | Salon |
|---|---|---|
| ![Home](docs/screenshots/01-home.png) | ![About](docs/screenshots/02-about.png) | ![Salon](docs/screenshots/03-salon.png) |

| Service detail | Contact | Booking |
|---|---|---|
| ![Service detail](docs/screenshots/04-service-detail.png) | ![Contact details](docs/screenshots/contact-top.png) | ![Contact form](docs/screenshots/contact-form.png) | ![Contact bottom](docs/screenshots/contact-bottom.png) | ![Validation errors](docs/screenshots/contact-validation.png) | ![Booking](docs/screenshots/06-booking.png) |

| Booking: sign in to continue | Theme switch (circular reveal) | Dark mode |
|---|---|---|
| ![Sign in dialog](docs/screenshots/06b-booking-sign-in.png) | ![Theme reveal](docs/screenshots/09c-theme-reveal.png) | ![Dark](docs/screenshots/09b-dark-mode.png) |

| Sign In | Bloomie (AI assistant) | Mobile menu |
|---|---|---|
| ![Sign in](docs/screenshots/07-sign-in.png) | ![Chatbot](docs/screenshots/08-chatbot.png) | ![Mobile](docs/screenshots/09-mobile-menu.png) |

| Admin dashboard | Appointments | Customers | Services |
|---|---|---|---|
| ![Dashboard](docs/screenshots/10-admin-dashboard.png) | ![Appointments](docs/screenshots/11-admin-appointments.png) | ![Customers](docs/screenshots/12-admin-customers.png) | ![Services](docs/screenshots/13-admin-services.png) |

| Inquiries | Automation logs | Settings | Admin login |
|---|---|---|---|
| ![Inquiries](docs/screenshots/14-admin-inquiries.png) | ![Logs](docs/screenshots/15-admin-automation-logs.png) | ![Settings](docs/screenshots/16-admin-settings.png) | ![Admin login](docs/screenshots/17-admin-login.png) |

> These screenshots were taken on a test setup without internet, so the new Unsplash photos do not appear in them (some older screenshots still show the previous images). Your local site shows the real photos. In the chatbot screenshot the reply came from the database fallback because n8n was not connected. Retake them on your own computer after setting up n8n, if you like.

---

## 3. Features

### Customer website
- Header: **Blush & Bloom Salon | Home | About | Salon | Contact | Sign In | Book Now**. There is no search button and no admin link. Mobile menu with the same links.
- **Home:** full-width hero slideshow, featured services, why choose us, booking steps, 5-star sample testimonials (labelled), contact information.
- **About:** background, mission, vision, core values, staff profile cards, Read More / Read Less, animated counters.
- **Salon:** service catalogue from MySQL with category filters (Hair, Nails, Facial & Skin Care, Spa & Massage, Makeup). Each card shows a photo, description, starting price, duration, *Explore Details* and *Book This Service*.
- **Service detail pages:** `/salon/{slug}` with a slideshow (previous/next, thumbnails, indicators, autoplay that pauses on hover/focus), description, inclusions, benefits, recommended for, preparation, aftercare, FAQs, related services.
- **Booking (step by step):** 1) choose a service, 2) pick a day on the **calendar** (past days, days more than 6 months ahead, and closed days are disabled) and an available time, 3) enter your details. Guests then see a **"Sign in to continue"** dialog (**Continue with Google**, sign in with email, or create an account); their details are kept, and after signing in they land on a **Review** page to confirm. Signed-in customers confirm straight away. Every booking gets a unique reference (e.g. `BB-261116-7KQ2`), starts as **Pending**, and double submissions are ignored. Works without n8n.
- **Customer accounts:** register, sign in with email or **Continue with Google**, "My Account" booking history. This uses a separate `customer` guard.
- **Contact:** JavaScript validation and a server-side save to the `inquiries` table.
- **Bloomie, the floating AI assistant:** bottom-right. Bloomie introduces itself by name, answers questions about services, prices, hours and booking, can check open times and create Pending bookings. The browser calls Laravel `/api/chat`, and Laravel calls n8n + Gemini. If n8n is offline, the assistant answers from database facts only.
- Dark mode switch in the header (right of **Book Now**) with a sun/moon icon and a circular reveal animation (View Transitions API; instant switch in older browsers or with reduced motion). Saved in the browser. Auto-hiding header, back-to-top button, keyboard focus styles.
- **Salon contact details:** blushnbloom.salon@gmail.com · 09414152352 · Butuan City, Agusan del Norte, Philippines · 8:00 AM – 6:00 PM (editable in Admin → Settings).

### Administrator portal (`/admin/login`)
Separate login with its own `admin` guard and rate limiting. Customers cannot open admin pages.

- **Dashboard:** real counts, weekly bookings chart, popular services, recent activity, upcoming visits.
- **Customers:** search, filter, add, edit, delete, appointment history.
- **Appointments:** search, filter by date or status, confirm, reject, cancel, complete, and reschedule (with a conflict check).
- **Inquiries:** search, change status (New / Pending / Resolved), record an admin response.
- **Services:** add or edit services, prices, durations and descriptions, upload photos, enable or disable a service.
- **Automation logs:** every chatbot request, appointment event and email result reported by n8n.
- **Business settings** (from the account menu or My profile): business details and hours, booking slot interval, reminder timing, and Appearance (Light / Dark).
- **Account menu:** click your name at the bottom of the sidebar for **My profile**, **Settings**, **View website**, **Help**, a **Light / Dark** switch (saved to your admin account, with a circular reveal animation), and **Log out**.
- **My profile:** same layout as the customer My Account page: avatar, your details, upcoming appointments, edit name/email, change password, and quick help.

### How availability works
Each service category is one station: hair chair, nail station, facial room, massage room, makeup station. Two Pending or Confirmed appointments in the **same category** cannot overlap, using each service's duration. Different categories can run at the same time. Opening hours, open days and slot interval come from **Admin → Settings**.

---

## 4. Installation (Windows + XAMPP)

### Requirements
- XAMPP with **PHP 8.2 or newer** and MySQL/MariaDB
- Composer
- (Optional, for AI and emails) Node.js 20+ for n8n, or Docker

### Steps
1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Put the project in a folder, e.g. `C:\xampp\htdocs\blush-bloom-salon`.
3. Open a terminal in that folder and install dependencies:
   ```bash
   composer install
   ```
4. Create your environment file and app key:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```
5. Edit `.env` (see [Environment variables](#7-environment-variables)). At minimum set the database values and `SALON_ADMIN_PASSWORD`.

## 5. Database setup (`blush_bloom_salon`)

### Option A: new, empty database
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`.
2. Click **New**, enter `blush_bloom_salon`, choose collation `utf8mb4_unicode_ci`, click **Create**.
3. Run:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```
   `db:seed` creates the six services, default settings, the first admin account, and a few clearly-marked sample records. It is safe to run again; it never deletes data.

### Option B: keep the records from the old `bizflow_ai` database
1. **Back up first:** phpMyAdmin → select `bizflow_ai` → **Export** → *Quick* → **SQL** → Go. Keep the `.sql` file.
2. Copy the data into the new database: select `bizflow_ai` → **Operations** → *Copy database to* → `blush_bloom_salon`, choose **Structure and data**, tick **CREATE DATABASE before copying** → Go.
   (Or create `blush_bloom_salon` and **Import** the `.sql` file you exported.)
3. Set `DB_DATABASE=blush_bloom_salon` in `.env`, then run:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```
   The new migrations only **add** tables and columns. They link old appointments to services, give them booking references, and convert times like `10:00 AM` to `10:00`. No records are removed.

> ⚠️ Never run `php artisan migrate:fresh` on a database with real records. It deletes every table.

### First administrator
- Email: `SALON_ADMIN_EMAIL` (default `admin@blushbloom.salon`)
- Password: `SALON_ADMIN_PASSWORD` from `.env`. If you leave it empty, `db:seed` prints a random one-time password. Write it down and change it in **Admin → Settings**.

## 6. Run the application
```bash
php artisan serve
```
- Customer website: <http://127.0.0.1:8000>
- Admin portal: <http://127.0.0.1:8000/admin/login>

Run the automated tests (they use an in-memory SQLite database and do not touch MySQL):
```bash
php vendor/bin/phpunit
```

## 7. Environment variables

| Variable | Purpose |
|---|---|
| `APP_NAME` | `"Blush & Bloom Salon"` |
| `APP_URL` | e.g. `http://127.0.0.1:8000` |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT` | `mysql`, `127.0.0.1`, `3306` |
| `DB_DATABASE` | `blush_bloom_salon` |
| `DB_USERNAME`, `DB_PASSWORD` | XAMPP default is `root` with an empty password |
| `SALON_ADMIN_NAME`, `SALON_ADMIN_EMAIL`, `SALON_ADMIN_PASSWORD` | First admin account (used by `db:seed`) |
| `N8N_CHAT_WEBHOOK_URL` | Production URL of n8n Workflow 1 |
| `N8N_APPOINTMENT_WEBHOOK_URL` | Production URL of n8n Workflow 2 |
| `N8N_WEBHOOK_SECRET` | Long random string. Laravel sends it to n8n in the `X-Webhook-Secret` header |
| `N8N_API_TOKEN` | Long random string (16+ characters). n8n sends it to Laravel as `Authorization: Bearer …` |
| `N8N_TIMEOUT`, `N8N_CHAT_TIMEOUT` | HTTP timeouts in seconds (defaults 10 and 45) |
| `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` | "Continue with Google" for customers (see below). Leave empty to hide Google sign-in |
| `SALON_REMOTE_IMAGES` | `true` (default) shows real photos from Unsplash; `false` uses the local files in `public/images` |

Generate random secrets with:
```bash
php -r "echo bin2hex(random_bytes(24)), PHP_EOL;"
```
After editing `.env`, run `php artisan config:clear`.

> Never commit `.env`. It is excluded by `.gitignore`. `.env.example` only contains placeholders.

### Continue with Google setup
1. Open <https://console.cloud.google.com/> → create (or pick) a project.
2. **APIs & Services → OAuth consent screen**: choose *External*, add the app name and your email, and add yourself as a test user.
3. **APIs & Services → Credentials → Create credentials → OAuth client ID** → *Web application*.
4. Under **Authorized redirect URIs** add `http://127.0.0.1:8000/auth/google/callback` (and your real domain later).
5. Copy the Client ID and Client secret into `.env` as `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`, then run `php artisan config:clear`.

Until these are set, the Google button is shown greyed out and customers can still use email sign-in.

### Photo credits
Default service photos are free photos from [Unsplash](https://unsplash.com) (Unsplash License) by Hair Spies, Lindsay Cash, Adam Winger, Lera Kogan, Benyamin Bohlouli, Giorgio Trovato, Rosa Rafael, kimia kazemi, engin akyurt, Toa Heftiba, Taylor Heery, Lola Azizada and Alena Plotnikova. Photos uploaded in the Admin Portal always take priority.

## 8. n8n + Gemini + Gmail setup

Full step-by-step guide: **[docs/N8N-SETUP.md](docs/N8N-SETUP.md)**. Summary:

| Workflow file | Purpose | Trigger |
|---|---|---|
| `n8n-workflows/1-blush-bloom-ai-customer-assistant.json` | Gemini AI agent with Laravel tools: services, business info, availability, create booking, booking status, escalate to a person | Webhook `POST /webhook/blush-bloom-chat` |
| `n8n-workflows/2-blush-bloom-appointment-emails.json` | Emails for booking received (Pending), confirmed, cancelled, rejected | Webhook `POST /webhook/blush-bloom-appointments` |
| `n8n-workflows/3-blush-bloom-appointment-reminders.json` | Reminder emails for upcoming confirmed appointments | Schedule (every hour) |

The AI never touches MySQL directly. It can only call the narrow `/api/n8n/*` endpoints, which are protected by `N8N_API_TOKEN`. Every booking goes through the same Laravel validation as the website form.

**Status of the integrations:** the Laravel side is covered by automated tests, with n8n simulated. The three workflows are provided as importable files. They become operational only after **you** add your own Gemini key, Gmail app password and secrets, and then test them. Until then the website keeps working: bookings save normally, the chatbot uses database answers, and the Automation Logs show `Skipped`.

## 9. API reference

| Method & path | Auth | Purpose |
|---|---|---|
| `GET /api/business/services` | public | Active services (JSON) |
| `GET /api/appointments/availability?service=haircut&date=YYYY-MM-DD` | public | Real time slots |
| `POST /api/appointments` | public (rate-limited) | Create a booking (same rules as the form) |
| `POST /api/chat` | public (rate-limited) | Chat. Laravel forwards to n8n |
| `GET /api/n8n/services` · `GET /api/n8n/business-info` · `GET /api/n8n/availability` | Bearer token | AI tools |
| `POST /api/n8n/appointments` | Bearer token | AI creates a Pending booking |
| `POST /api/n8n/appointments/status` | Bearer token | Status lookup (reference **and** email required) |
| `POST /api/n8n/escalations` | Bearer token | Create an inquiry for a human |
| `POST /api/n8n/reminders/claim` | Bearer token | Claim due reminders (prevents duplicates) |
| `POST /api/n8n/automation-results` | Bearer token | n8n reports whether an email was actually sent |

Laravel → n8n event payload (Workflow 2):
```json
{
  "event_id": "uuid",
  "event": "appointment.created | appointment.status_changed | appointment.rescheduled",
  "appointment": { "reference": "BB-261116-7KQ2", "status": "Pending", "customer_name": "…", "customer_email": "…",
                   "service": "Haircut", "price": "₱250", "date_label": "Monday, November 17, 2026", "time_label": "10:00 AM" }
}
```

## 10. Project structure
```text
app/
  Http/Controllers/        PageController (Home/About/Contact), SalonController, BookingController
  Http/Controllers/Auth/   CustomerAuthController
  Http/Controllers/Admin/  Dashboard, Customers, Appointments, Inquiries, Services, AutomationLogs, Settings, Auth
  Http/Controllers/Api/    PublicApiController, ChatController, N8nToolController
  Http/Middleware/         EnsureAdmin, VerifyN8nToken
  Http/Requests/           BookingRequest (shared booking validation)
  Models/                  Admin, Customer, Service, Appointment, Inquiry, Setting, ChatbotConversation, ChatbotMessage, AutomationLog
  Services/                AvailabilityService, BookingService, AutomationService, ChatFallbackService
  Support/SalonDefaults.php  default services and settings (only used to fill an empty database)
database/migrations/       original tables + additive upgrade migrations
public/css, public/js      one CSS and JS file per page
resources/views/           layouts/app.blade.php, home, about, contact, salon/, booking/, auth/, admin/
routes/web.php, routes/api.php
n8n-workflows/             3 importable workflows
docs/                      setup guides and screenshots
tests/Feature/SalonTest.php
```

## 11. Security notes
- Passwords are hashed with bcrypt. Customer and admin logins use separate guards and are rate-limited.
- CSRF protection on all web forms. API inputs are validated. Eloquent prevents SQL injection, and Blade escapes output (XSS).
- Uploads: images only, at most 3 MB, stored under random file names in `public/uploads/services`.
- n8n credentials, the Gemini key and the SMTP password live only in n8n. Laravel secrets live only in `.env`. Nothing secret is sent to the browser.
- Before going live: set `APP_ENV=production`, `APP_DEBUG=false`, use HTTPS, and change every placeholder secret.

## 12. Deployment
The live site runs on **Vercel** (container with FrankenPHP, see `Dockerfile.vercel`, `Caddyfile`, `vercel.json`) with a **Supabase PostgreSQL** database.
1. Import the GitHub repository in Vercel (Root Directory `./`, preset *Other*).
2. Add the variables from `.env.vercel.example` in Vercel → Settings → Environment Variables (secrets as *Sensitive*).
3. Use the Supabase **Session pooler** connection (port 5432) for `DB_HOST` / `DB_USERNAME`, and `DB_CONNECTION=pgsql`, `DB_SSLMODE=require`.
4. `RUN_MIGRATIONS=true` creates the tables and default data on start-up (safe to repeat; never `migrate:fresh`).
5. In Google Cloud Console, add `https://YOUR-SITE/auth/google/callback` as an authorized redirect URI.
6. In the n8n workflows, use your public site URL for every Laravel API call.

## 13. Repository
https://github.com/smanbiotan/Lara-Proj-AIBJ2-Manbiotan-Orbiso-Gonzalvo
