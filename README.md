# CargoFlow — Logistics & Shipment Tracking Platform

A modern, full-featured logistics and shipment-tracking web application with an
original **CargoFlow** brand, inspired by Cargofy-style logistics SaaS dashboards.

Built with **PHP + MySQL + HTML5 + CSS3 + Bootstrap 5 + Tailwind-style utility
patterns + vanilla JavaScript**. No frameworks, no React, no Node backend, no SQLite.

> **Note:** The design leans on Bootstrap 5 as the primary CSS framework, with a
> custom Tailwind-inspired design-token system (CSS variables) layered on top for
> theming and dark mode. This keeps the stack dependency-free while delivering a
> modern SaaS look.

---

## ✨ Features

**Public website**
- Beautiful, fully responsive homepage with hero + inline tracking
- Shipment tracking page (tracking number lookup, real-time timeline + embedded map)
- Get a Quote page (AJAX submission with instant price estimate)
- Services, About and Contact pages
- Dark mode toggle (persisted), reveal-on-scroll animations, animated counters

**Admin dashboard** (`/admin/`)
- Secure login / logout with hashed passwords (bcrypt) & CSRF protection
- Modern dashboard with KPI cards and Chart.js visualizations
- Shipments — full CRUD, automatic tracking-number generation (`CF-XXXXXXXXXX`)
- Tracking status / event management (add events, sync status & location)
- Customers, Drivers & Vehicles management
- Invoices & Payments (auto tax/total calculation, mark-paid, reconciliation)
- Reports & statistics (revenue/volume trends, status breakdown, CSV export)
- Quote requests & contact messages inbox
- Notifications center
- Settings & profile (site config, profile, change password)
- User management (roles: admin / manager / staff)

**Technical**
- PDO with **prepared statements** everywhere (SQL-injection safe)
- `password_hash()` / `password_verify()` auth + CSRF tokens + session hardening
- AJAX/Fetch-based JSON API under `/api/`
- Reusable includes (`includes/`, `admin/includes/`), separated CSS/JS
- cPanel-ready: `.htaccess`, web installer, phpMyAdmin import

---

## 🗂️ Project structure

```
cargoflow/
├── index.php              # Public homepage
├── track.php              # Shipment tracking
├── quote.php              # Get a quote
├── services.php           # Services
├── about.php              # About
├── contact.php            # Contact
├── login.php / logout.php # Admin auth
├── install.php            # Web installer (delete after setup)
│
├── config/
│   ├── config.php         # DB credentials + app settings  ← EDIT THIS
│   └── database.php       # PDO connection + query helpers
│
├── includes/
│   ├── bootstrap.php      # Session, timezone, autoload
│   ├── functions.php      # Helpers, formatting, tracking-number gen
│   ├── auth.php           # Login/logout, roles, guards
│   ├── api.php            # API request helpers
│   ├── header.php         # Public header/nav
│   └── footer.php         # Public footer
│
├── database/
│   └── schema.sql         # Full schema + seed data
│
├── api/                   # JSON endpoints (public + admin CRUD)
│   ├── tracking.php, quotes.php, contact.php
│   ├── shipments.php, customers.php, drivers.php, vehicles.php
│   ├── invoices.php, payments.php, notifications.php
│   ├── settings.php, users.php, stats.php, admin_quotes.php
│   ├── messages.php, shipment_detail.php, health.php
│
├── assets/
│   ├── css/style.css      # Public styles (design tokens + dark mode)
│   ├── js/main.js         # Public JS
│   └── img/favicon.svg
│
└── admin/
    ├── index.php          # Dashboard
    ├── shipments.php, customers.php, drivers.php, vehicles.php
    ├── invoices.php, payments.php, quotes.php, messages.php
    ├── notifications.php, reports.php, settings.php, users.php
    ├── assets/admin.css, admin.js
    └── includes/header.php, footer.php
```

---

## 🚀 Installation (cPanel / shared hosting)

### 1. Upload
Upload the contents of this folder to your web root (e.g. `public_html/`), or
into a sub-folder such as `public_html/cargoflow`.

### 2. Create the database
- In cPanel → **MySQL® Databases**, create a database (e.g. `cargoflow`).
- Create a database user and add it to the database with **ALL PRIVILEGES**.

### 3. Configure
Copy the config template and set your database credentials:

```bash
cp config/config.example.php config/config.php
```

Then edit `config/config.php` and set:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cargoflow');
define('DB_USER', 'cargoflow_user');
define('DB_PASS', 'your_strong_password');
define('BASE_URL', 'https://yourdomain.com');   // optional, auto-detects if empty
```

> **Note:** `config/config.php` contains live credentials and is excluded from
> git (the repo is public). Always create it from `config.example.php` on the
> server — never commit real passwords.

### 4. Import the schema
- Easiest: open `https://yourdomain.com/install.php` in your browser and run the
  importer, **or**
- cPanel → **phpMyAdmin** → select your database → **Import** →
  choose `database/schema.sql`.

### 5. Log in
- **Public site:** `https://yourdomain.com/`
- **Admin:** `https://yourdomain.com/login.php`
- **Default credentials:** username `admin` / password `admin123`
  *(change this immediately in Admin → Settings → Change password.)*

### 6. Cleanup
Delete `install.php` after successful setup.

---

## ⚙️ Requirements
- PHP **7.4+** (8.x recommended) with `pdo_mysql`, `mbstring`, `json`
- MySQL **5.7+** or MariaDB 10.3+
- Apache (`.htaccess` support) — or Nginx with a PHP-FPM equivalent

---

## 🔐 Security notes
- All queries use **PDO prepared statements**.
- Passwords hashed with bcrypt (`password_hash`).
- CSRF tokens verified on all mutating requests.
- Session ID regenerated on login; admin pages guarded by `require_login()`.
- Sensitive files (`config/`, `database/`, `includes/`) are blocked via `.htaccess`.

---

## 🎨 Customization
- **Brand colors / theme tokens:** edit the CSS variables at the top of
  `assets/css/style.css` (public) and `admin/assets/admin.css` (admin).
- **Site text (name, contact, currency, tax):** Admin → Settings, or edit the
  `settings` table rows directly.
- **Tracking number format:** `generate_tracking_number()` in `includes/functions.php`.

---

## 📄 License
Provided as a demo/reference project. You are free to adapt it for your own use.
