# Bandgar Construction — Project Context Document

> Updated: 28 October 2026
> Location: `C:\Users\Somnath Mali\Desktop\BC`
> Owner: Somnath Mali
> Branch: `main` — production deploys from Git

---

## 1. Overview

The repository holds two surfaces that share one Laravel application:

1. **Public business website** (`bandgarconstruction.com`) — a bilingual
   Marathi/English site for Bandgar Construction & Developers: home, about,
   services, portfolio and contact, plus a private contractor statement link.
2. **Wallet & project management (BAM)** — an internal, owner-only admin app at
   `/admin` for company cash, project wallets, contractor transfers, expenses,
   owner money, project closure and employees (attendance, advances and weekly
   salary).

The public site is the shopfront; the admin app is deliberately **not** a full
accounting/ERP system. Its scope is limited to the agreed wallet model and the
employee module described below.

---

## 2. Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 (PHP 8.2+) |
| Database | MySQL / MariaDB (XAMPP locally) |
| Frontend | Blade + Tailwind CSS 4 (Vite) |
| Typefaces | Self-hosted Archivo (display), Inter (body), Noto Sans Devanagari (Marathi) |
| Money math | `bcmath` via `App\Support\Money` (no floats) |
| Tests | PHPUnit feature tests |
| Local server | `php artisan serve` (port 8000) |
| Production | Shared hosting (cPanel), no terminal, no Node |

**Money columns:** `DECIMAL(14,2)` — never floats. All arithmetic goes through
`App\Support\Money`.

**Compiled assets are committed.** `public/build/` is part of the repository
because production has no Node. A pre-commit hook rebuilds Vite whenever
frontend sources are staged (see §15).

---

## 3. Public Website

### 3.1 Language architecture

- **Marathi is canonical at the root**: `/`, `/about`, `/services`,
  `/projects`, `/contact`.
- **English lives under `/en`**: `/en`, `/en/about`, and so on.
- Old `/mr/...` URLs permanently redirect to the Marathi canonical URLs.
- `SetSiteLocale` middleware sets the locale from the route group; the URL is
  the source of truth, so shared links stay in the language they were written
  in.
- Translation strings live in `lang/en/site.php` and `lang/mr/site.php`. Both
  files must keep identical key sets — currently **319 keys each**.

### 3.2 Content rules (non-negotiable)

These rules came from the owner and are enforced by review, not code:

- **Never invent** projects, locations, budgets, reviews, years of experience,
  awards or certifications.
- **Testimonials stay unpublished** until a customer has agreed in writing.
  `config('site.testimonials')` is empty, and the section stays hidden while it
  is empty.
- **Design renderings are never presented as completed buildings.** Every
  project image is labelled *"3D design visualisation"* (`portfolio.visualisation_note`,
  `footer.visualisation_note`).
- **Contact details come from `config/site.php`** — phones, WhatsApp, email,
  address, map. Do not hard-code them in Blade.
- **No template/placeholder copy.** Any "coming soon", "sample project" or
  "portfolio being prepared" language is a bug. The credits page and the
  sample-project UI were removed on 28 Oct 2026.

### 3.3 Typography

Fonts are self-hosted in `public/fonts/` and declared in `resources/css/app.css`:

| Use | Family | File |
|-----|--------|------|
| Headings (Latin) | Archivo (variable, 400–700) | `archivo-var-latin.woff2` |
| Body (Latin) | Inter (variable, 400–600) | `inter-var-latin.woff2` |
| Marathi | Noto Sans Devanagari (variable) | `noto-sans-devanagari-var.ttf` |

Devanagari must stay **self-hosted with the full font file**: Google's
subsetted webfonts strip Indic conjunct features, so clusters such as "ष्ट्र"
lose their rakar. There is no Google Fonts `<link>` or preconnect in the
layout; Latin faces are preloaded.

> `noto-serif-devanagari-var.ttf` is still on disk but unused since the switch
> to Archivo on 28 Oct 2026.

### 3.4 Pages and routes

| URL (Marathi / English) | Purpose |
|---|---|
| `/` · `/en` | Home: hero, services, selected projects, process, enquiry call-out |
| `/about` · `/en/about` | Company, approach, service area |
| `/services` · `/en/services` | Full service list, each card links to the enquiry form with `?service=<slug>#enquiry-form` |
| `/projects` · `/en/projects` | Published portfolio with category filter |
| `/projects/{slug}` · `/en/projects/{slug}` | Project detail, gallery, related services |
| `/contact` · `/en/contact` | Contact details, Google map, enquiry form |
| `/sitemap.xml` | Bilingual sitemap with `hreflang` alternates |
| `/statement/{token}` | Private contractor statement (random token, `noindex`, `no-store`) |
| `/contractors/{slug}` | Retired guessable statement link — `410 Gone` |

`/credits` and `/en/credits` were removed on 28 Oct 2026 and now return 404.

### 3.5 Enquiry form

`Site\ContactController` validates through `StoreInquiryRequest`, stores an
`Inquiry` row and emails the owner via `InquiryReceived`.

- Fields: name, phone, email (optional), **location**, **budget**, service,
  message, plus a hidden honeypot named `company_website`.
- `location` and `budget` were added on 28 Oct 2026
  (`2026_10_28_000001_add_project_details_to_inquiries_table.php`).
- The service card links preselect the service on the contact page.
- Rate limit: **5 requests per minute per IP** (`site.contact.throttle`).
- Stored metadata: `locale`, IP address, user agent, `status` (default `new`).
- Notification recipient: `SITE_NOTIFY_EMAIL`, falling back to `site.email`.
- Submitting disables the button and shows "Sending…"; a double-submit guard
  prevents duplicate rows.

### 3.6 Map and local SEO

- The Google Maps embed URL is owner-supplied and lives in
  `config('site.address.map_embed_url')`; the iframe is responsive (288 px
  mobile, 320 px tablet, 420 px desktop), lazy-loaded and has a descriptive
  title.
- Confirmed coordinates: **17.3126691, 74.8801869**, used in the
  `HomeAndConstructionBusiness` JSON-LD as `geo`, alongside `hasMap` pointing
  at the business listing.
- `seo.og_image` is the Modern Duplex cover; project pages push their own
  `og:image`.
- `robots.txt` disallows the internal app (`/admin`, `/deploy`, `/login`,
  `/dashboard`, `/transactions`, `/owner`) and the tokenised statement URLs
  (`/statement`, `/contractors`); the statement page also sends `noindex`
  and `no-store` headers.

### 3.7 Portfolio data

`PortfolioProject` is database-driven with per-locale fields
(`title_mr`, `summary_mr`, `description_mr`, `location_mr`), a category, cover
image, gallery JSON, year, `is_published` and `sort_order` scope.

- Current data: **4 published projects, 0 samples**.
- The `is_sample` column remains in the schema but is no longer read by any
  view; the sample badge/notice UI was removed.
- Portfolio content is seeded (`RealPortfolioSeeder`) because there is no admin
  screen for it yet.

---

## 4. The Wallet Model (Core Concept)

Three wallet types exist:

```
┌─────────────────────────────────────────────────────────┐
│  BUSINESS WALLET ("Company money (total)")              │
│  = the company's overall actual cash balance            │
│                                                         │
│  ┌─────────────────────┐  ┌──────────────────────────┐  │
│  │ Free company money  │  │ Project Wallets (each)   │  │
│  │ (not set aside yet) │  │ "Project money left"     │  │
│  │                     │  │ (portion of company cash │  │
│  │                     │  │  set aside for a project)│  │
│  └─────────────────────┘  └──────────────────────────┘  │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  OWNER PERSONAL WALLET ("Owner's personal money")       │
│  = company funds moved to owner. EXCLUDED from          │
│    company cash. Can be moved back to company.          │
└─────────────────────────────────────────────────────────┘
```

### Key formulas

```
Free company money = Company money (total) − Σ(active project money left)
Contractor spending = Σ contractor transfers recorded for the project
Total spending = Direct spending + Contractor spending
```

> Contractor returns are **not implemented** in the current ledger; only
> `contractor_transfer` (money out) exists. If returns are needed, they require
> a new transaction type plus wallet logic, not just a UI screen.

### Invariant

> Unallocated funds = Business balance − sum(active project assigned balances)

Setting aside money and closing a project do **not** change total company cash
— they only reclassify money between "free" and "set aside for a project".

---

## 5. Roles

| Role | Access |
|------|--------|
| **Owner / Admin** | Everything: projects, receipts, unallocated income, funding, expenses, contractor transfers/returns, owner wallet, closure, reports, contractors, employees, attendance, advances, salary |
| **Contractor** | No login. Receives project transfers and can be shown a private statement via a tokenised link |

`UserRole` currently has a single case, `owner`. Contractors are payment
recipients, not application users. Enforcement uses the `role:owner`
middleware and `ProjectPolicy`; hiding buttons is not relied upon.

---

## 6. Core Architecture

### Key files

| File | Purpose |
|------|---------|
| `app/Services/WalletService.php` | Single source of truth for ALL money movements and balance changes (873 lines). Controllers validate, authorise and delegate. |
| `app/Support/Money.php` | Exact decimal arithmetic (bcmath wrapper) |
| `app/Enums/*` | `UserRole`, `ProjectStatus`, `WalletType`, `TransactionType` |
| `app/Exceptions/WalletException.php` | Domain exception for wallet errors |
| `app/Support/Site/SiteContent.php` | Company/services/category data for the public site |
| `app/Support/Site/SiteRoutes.php` | Locale-aware URL helpers |

### Transaction safety

Every balance change runs inside `DB::transaction` with `lockForUpdate` row
locks. Failed operations roll back completely.

### Immutable ledger

`wallet_transactions` is the immutable history. Returns are separate records
linked to their original contractor cost via `related_transaction_id`.

---

## 7. Enums

### UserRole
- `owner` — Owner / Admin

### ProjectStatus
- `active` — Active
- `closure_pending` — Closure Pending
- `closed` — Closed

### WalletType
- `business` — Company wallet
- `project` — Per-project wallet
- `owner` — Owner personal wallet

### TransactionType (9 types)

| Technical name | On-screen label | Company cash | Project wallet | Project cost |
|----------------|-----------------|--------------|----------------|--------------|
| `receipt` | Payment Received | + | + (when allocated to a project) | — |
| `project_expense` | Project Expense (Direct) | − | − | + |
| `contractor_transfer` | Contractor Payment | − | − | + |
| `owner_transfer` | Transfer to Owner | − | — | — |
| `owner_expense` | Personal Expense | — | — | — (owner wallet only) |
| `owner_income` | Personal Income | — | — | — (owner wallet only) |
| `owner_return` | Transfer to Company | + | — | — |
| `project_allocation` | Project Funding | — | + | — (reclassification) |
| `closure_release` | Funds Released (Closure) | — | − | — (reclassification) |

The effects are defined in `TransactionType::businessCashEffect()`,
`ledgerDirection()` and `isProjectCost()` rather than in views.

---

## 8. Models

### User
- Fillable: `name`, `email`, `password`, `role`, `is_active`
- Hidden: `password`, `remember_token`
- Role via `UserRole` enum

### Project
- Fillable: `name`, `location`, `agreed_amount`, `contractor_id`, `status`, `closed_at`
- Casts: `agreed_amount` → `decimal:2`, `status` → `ProjectStatus`
- Relations: `contractor()` (belongsTo `Contractor`)

### Contractor
- Fillable: `name`, `slug`, `phone`, `contractor_type_id`, `notes`, `is_active`
- Relations: `type()`, `projects()`
- Public statement token lives on this table (`statement_token`)

### ContractorType
- Groups contractors by trade/skill for the admin index

### Employee
- Fillable: `employee_id`, `full_name`, `phone_number`, `employee_type_id`, `daily_salary`, `weekly_salary_payment_day`, `joining_date`, `status`
- Casts: `daily_salary` → `decimal:2`, `joining_date` → `date`
- `daily_salary` is legacy and no longer used for pay; new employees leave it null
- Relations: `type()`, `attendances()`, `advances()`, `salaryPayments()`
- `isDatePaid()` tells whether a salary payment covers a date
- Scopes `active()` / `inactive()`; `isActive()` helper
- `generateEmployeeId()` produces `EMP-YYYY-NNNN`, continuing the current year's sequence
- `getWeeklyPeriod($reference)` — last completed salary week: the 7 days ending on the most recent payment day on or before the reference date (used by Pay Salary)
- `getWeekContaining($reference)` — the 7 days ending on the next payment day on or after the reference date (used by the attendance chart, so today is always inside the edited week)
- `totalAdvances()`, `totalRepayments()` and `outstandingAdvance()`; repayments are summed from `employee_salary_payments.advance_repayment`

### EmployeeAttendance
- Fillable: `employee_id`, `attendance_date`, `status`, `payable_days`, `amount`
- Casts: `attendance_date` → `date`, `payable_days` → `decimal:1`, `amount` → `decimal:2`
- Unique `(employee_id, attendance_date)`

### EmployeeType
- Fixed and seeded by migration: Labour, Mistri, Other. It is a label only and sets no pay

### EmployeeAdvance
- Fillable: `employee_id`, `advance_date`, `amount`, `notes`
- Casts: `advance_date` → `date`, `amount` → `decimal:2`

### EmployeeSalaryPayment
- `daily_salary` is `0.00` on new payments; `half_days` stays 0
- Fillable: `employee_id`, `period_start`, `period_end`, `daily_salary`, `gross_salary`, `present_days`, `half_days`, `total_payable_days`, `outstanding_advance_before`, `advance_repayment`, `net_salary_payable`, `amount_paid`, `remaining_salary_due`, `remaining_advance_after`, `status`, `payment_date`, `notes`
- Casts: dates for periods/payment date; money columns → `decimal:2`; day columns → `decimal:1`
- Status: `paid` or `partially_paid`
- Unique `(employee_id, period_start, period_end)` stops the same week being paid twice

### Wallet
- Fillable: `type`, `project_id`, `contractor_id`, `user_id`, `balance`
- Casts: `type` → `WalletType`, `balance` → `decimal:2`

### WalletTransaction
- Fillable: `type`, `amount`, `transaction_date`, `description`, `project_id`, `category_id`, `contractor_id`, `source_wallet_id`, `destination_wallet_id`, `related_transaction_id`, `created_by`
- Casts: `type` → `TransactionType`, `amount` → `decimal:2`, `transaction_date` → `date`

### ExpenseCategory
- Fillable: `name`, `context`, `is_active`
- Scopes: `active()`, `forProject()`

### PortfolioProject
- Fillable: `title`, `title_mr`, `slug`, `category`, `location`, `location_mr`, `summary`, `summary_mr`, `description`, `description_mr`, `cover_image`, `gallery`, `year`, `is_sample`, `is_published`, `sort_order`
- Scopes: `published()`, `ordered()`; helpers `titleFor()`, `summaryFor()`, `coverUrl()`, `galleryUrls()`

### Inquiry
- Fillable: `name`, `phone`, `email`, `location`, `budget`, `service`, `message`, `locale`, `status`, `ip_address`, `user_agent`

---

## 9. Controllers

### Public site (`app/Http/Controllers/Site`)

| Controller | Responsibility |
|------------|----------------|
| `HomeController` | Home page data (services, selected projects, testimonial switch) |
| `PageController` | About, services |
| `PortfolioController` | Portfolio index with category filter, project detail |
| `ContactController` | Contact page, enquiry validation/store, owner notification |
| `ContractorStatementController` | Tokenised public statement, 410 for retired slugs |
| `LegacyMarathiRedirectController` | `/mr/...` → canonical Marathi URLs |
| `SitemapController` | Bilingual XML sitemap |

### Admin (`app/Http/Controllers/Admin`, mounted at `/admin`)

| Controller | Responsibility |
|------------|----------------|
| `AuthController` | Login/logout (rate limited) |
| `DashboardController` | Dashboard overview |
| `ProjectController` | Project CRUD + closure request/finalize |
| `ProjectFinanceController` | Receipts, expenses, transfers, funding per project |
| `ContractorController` | Contractor list/create |
| `EmployeeController` | Employee CRUD; detail page paginates attendance (5/page), advances and salary history (10/page) |
| `EmployeeAttendanceController` | Select an employee and edit their salary week; rejects pre-joining dates and paid weeks and upserts one row per employee/day |
| `EmployeeAdvanceController` | Advance list and create |
| `EmployeeSalaryController` | Weekly salary calculation, payment recording and partial-balance top-up |
| `OwnerWalletController` | Owner wallet show/income/transfer/return/expense |
| `TransactionController` | Transaction ledger index |
| `InstallerController` | Browser-based deploy/migrate/seed (`/deploy`) |

> `ManagerController`, `ManagerReturnController`, `IncomeController` and the
> `StoreManagerRequest` / `TransferToManagerRequest` /
> `RecordManagerReturnRequest` classes are leftovers from before contractors
> replaced managers. They are not routed and should be deleted with the stale
> `ManagerManagementTest` (see §16).

### Employee rules

- **Attendance**: select an employee, then mark each day of their salary week Present or Absent and enter the amount. One row per employee per date. A present day needs an amount; an absent day is ₹0. Days before `joining_date` are never saved, and unmarked days are skipped.
- **Salary**: there is no fixed salary. Gross is the sum of the week's amounts.
- **Paid-week lock**: attendance inside a paid salary week cannot change; the employee row is locked during saves so this cannot race with a payment.
- **Salary week**: Pay Salary uses the last completed week ending on the employee's `weekly_salary_payment_day`. The period is re-derived server-side from `period_end`, so hidden form fields cannot shift or split a week. A week ending after today is refused, and the in-progress week is shown as a read-only summary instead of a payment form.
- **No double pay**: `(employee_id, period_start, period_end)` is unique, and a transaction locks the employee row before the duplicate check. A week with zero gross (no attendance) is refused so a ₹0 payment cannot lock the period.
- **Advances**: outstanding = total advances − repayments. A repayment is stored once, on `employee_salary_payments.advance_repayment`, and cannot exceed the outstanding balance.
- **Salary amounts**: net payable = gross − advance repayment; amount paid cannot exceed net payable. The payment is `paid` when nothing remains and `partially_paid` otherwise. `complete()` tops up the remaining due without creating a second payment or recording the repayment again.
- **Dashboard cards**: active employees, present today, absent today (based on attendance rows, not employee status), outstanding advances, salary paid this calendar week (Mon–Sun by `payment_date`), and the count of `partially_paid` payments.

---

## 10. Routes

### Public business website (`routes/web.php`)

Marathi at the root, English under `/en`:

- `GET /` — home
- `GET /about`, `GET /services`, `GET /contact`
- `POST /contact` — enquiry (throttled 5/min per IP)
- `GET /projects`, `GET /projects/{slug}` — public portfolio
- `GET /en/...` — English versions of the same pages
- `GET /mr/{path?}` — permanent redirects to Marathi canonicals
- `GET /sitemap.xml` — bilingual sitemap
- `GET /statement/{contractor:statement_token}` — public contractor statement
- `GET /contractors/{slug}` — retired guessable URL, answers `410 Gone`

### Internal app (`routes/admin.php`, mounted at `/admin` by `bootstrap/app.php`)

- Login/logout, dashboard
- Projects (index, create, store, show), receipts, expenses, transfers, funding,
  closure request/finalize
- Transactions ledger
- Contractors (index, store)
- Employees (`/admin/employees` resource: index, create, store, show, edit, update, destroy/deactivate)
- Attendance: `GET|POST /admin/attendance`
- Advances: `/admin/advances`, `/admin/advances/create`, `POST /admin/advances`
- Pay Salary: `GET|POST /admin/pay-salary`, `POST /admin/pay-salary/calculate`, `POST /admin/pay-salary/{payment}/complete` (top up a partial payment)
- Owner wallet (show, income, transfer, return, expense)
- All admin pages send `noindex`; sign-in is rate limited to 5 failed attempts
  per minute per account+IP
- Legacy paths (`/login`, `/dashboard`, `/transactions`, `/contractors`,
  `/owner`) 301 to their `/admin` equivalent

### Deployment

- `GET /deploy`, `POST /deploy/run` — protected by `DEPLOY_TOKEN`

---

## 11. Views (Blade)

```
resources/views/
├── admin/
│   ├── advances/{create,index}.blade.php
│   ├── attendance/index.blade.php
│   ├── auth/login.blade.php
│   ├── contractors/index.blade.php
│   ├── dashboard.blade.php
│   ├── employees/{create,edit,index,show}.blade.php
│   ├── managers/index.blade.php          ← leftover, not routed
│   ├── owner/show.blade.php
│   ├── pay-salary/create.blade.php
│   ├── projects/{create,index,show}.blade.php
│   └── transactions/index.blade.php
├── components/
│   ├── icon.blade.php
│   └── site/{button,icon,page-hero,project-card,section-heading,service-card}.blade.php
├── emails/inquiry.blade.php
├── layouts/{admin,site,statement}.blade.php
├── partials/
│   ├── admin/nav-links.blade.php
│   └── site/{footer,nav}.blade.php
├── site/
│   ├── about.blade.php
│   ├── coming-soon.blade.php
│   ├── contact.blade.php
│   ├── home.blade.php
│   ├── portfolio/{index,show}.blade.php
│   ├── services.blade.php
│   ├── sitemap.blade.php
│   ├── statement.blade.php
│   └── statement-unavailable.blade.php
```

`resources/views/welcome.blade.php` was removed on 28 Oct 2026; the root route
is the business home page.

### Employee detail page

`admin/employees/show.blade.php` uses independent pagination for its three
history lists: attendance **5 per page** (`attendance_page`), advances and
salary history **10 per page** (`advance_page`, `salary_page`). Each paginator
keeps the other query strings via `withQueryString()` and only renders a
pagination bar when it has more than one page.

On phones the summary cards become a 2×2 grid, Profile labels stack above their
values, and Salary History switches from the 8-column table to stacked cards
below the `xl` breakpoint. The Profile and Attendance cards use equal-height
layout, with attendance pagination pinned to the bottom so the two cards line up
on desktop.

---

## 12. Database Schema (Migrations)

| Migration | Creates |
|-----------|---------|
| `0001_01_01_000000_create_users_table.php` | users |
| `0001_01_01_000001_create_cache_table.php` | cache |
| `0001_01_01_000002_create_jobs_table.php` | jobs |
| `2026_10_13_000001_add_role_to_users_table.php` | adds `role` to users |
| `2026_10_13_000002_create_expense_categories_table.php` | expense_categories |
| `2026_10_13_000003_create_projects_table.php` | projects |
| `2026_10_13_000004_create_wallets_table.php` | wallets |
| `2026_10_13_000005_create_wallet_transactions_table.php` | wallet_transactions |
| `2026_10_18_000001_create_contractor_types_table.php` | contractor_types |
| `2026_10_18_000002_create_contractors_table.php` | contractors |
| `2026_10_18_000003_add_contractor_id_columns.php` | contractor_id on projects/transactions/wallets |
| `2026_10_18_000004_migrate_managers_to_contractors.php` | copies manager rows to contractors |
| `2026_10_18_000005_drop_manager_columns.php` | drops manager columns |
| `2026_10_18_000006_add_slug_to_contractors_table.php` | adds `slug` |
| `2026_10_25_000001_create_portfolio_projects_table.php` | portfolio_projects |
| `2026_10_25_000002_create_inquiries_table.php` | inquiries |
| `2026_10_25_000003_add_statement_token_to_contractors_table.php` | adds `statement_token` |
| `2026_10_28_000001_add_project_details_to_inquiries_table.php` | adds `location`, `budget` to inquiries |
| `2026_10_28_072204_create_employees_table.php` | employees |
| `2026_10_28_072213_create_employee_attendances_table.php` | employee_attendances (unique employee/date) |
| `2026_10_28_072214_create_employee_advances_table.php` | employee_advances |
| `2026_10_28_072216_create_employee_salary_payments_table.php` | employee_salary_payments (unique employee/period) |
| `2026_10_28_080000_drop_salary_payment_id_from_employee_advances.php` | drops the unused advance → salary-payment link |
| `2026_10_31_000001_create_employee_types_and_pay_rates_tables.php` | employee_types, employee_pay_rates (seeded), employee_pay_rate_overrides |
| `2026_10_31_000002_add_work_pay_columns_to_employee_tables.php` | adds type, day amount, adjustment and breakdown columns; makes `daily_salary` nullable |
| `2026_10_31_000003_backfill_employee_pay_data.php` | moves existing employees to Other and fills attendance amounts |
| `2026_10_31_000004_simplify_employee_pay_to_daily_amounts.php` | removes pay rates, levels and adjustments, keeping each day's amount |

---

## 13. Local Development Environment

### `.env` (local)

```
APP_NAME="Bandgar Account Manager"
APP_ENV=local
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bandgar_wallet
DB_USERNAME=root
DB_PASSWORD=•••• (see .env — never commit)
SITE_EMAIL=…            # public contact address (optional locally)
SITE_NOTIFY_EMAIL=…     # enquiry notifications (falls back to SITE_EMAIL)
DEPLOY_TOKEN=…          # add only while installing; remove after
```

`SITE_EMAIL`, `SITE_NOTIFY_EMAIL` and `DEPLOY_TOKEN` are not set in the local
`.env` today; the site works without them, but enquiry emails are skipped until
a recipient is configured.

### Database: `bandgar_wallet`

Host: XAMPP MariaDB 10.4.32 at `C:\xampp\mysql\`.

### Running it on this machine

- PHP has no WSL install; use `/mnt/c/xampp/php/php.exe` from WSL (or
  `C:\xampp\php\php.exe` on Windows).
- Node/npm live in WSL (`~/.nvm/...`); `node_modules` was installed there.
- Start the site with `php artisan serve` (port 8000) from Windows so Chrome
  can reach `127.0.0.1:8000`.
- Tests: `php artisan test`.

### Seeded account (password: `password`)

| Email | Role |
|-------|------|
| `owner@bandgar.test` | Owner / Admin |

`DatabaseSeeder` also creates expense categories and calls
`RealPortfolioSeeder` so a fresh deploy has the published projects.

### Demo employee records (local review)

`DemoEmployeeSeeder` is **local-only** (it throws outside the `local`
environment) and never touches the real employee `EMP-2026-0001`. Each run
resets only the two demo employees' histories, so it is safe to repeat while
reviewing:

```bash
php artisan db:seed --class=DemoEmployeeSeeder
```

| Employee | Salary | Pay day | Joining | Demo data |
|----------|--------|---------|---------|-----------|
| Ramesh Patil `EMP-2026-0002` | ₹700/day | Saturday | 15 Sep 2026 | ₹5,000 advance, 21 attendance rows, 18–24 Oct week paid in full |
| Suresh Jadhav `EMP-2026-0003` | ₹650/day | Wednesday | 1 Oct 2026 | ₹3,000 advance, 21 attendance rows, 22–28 Oct week partially paid (₹770 due) |

That produces 2 present / 1 absent today, ₹5,000 outstanding advances and one
pending (partially paid) salary payment on the dashboard. To remove the demo
data:

```bash
php artisan tinker --execute="App\Models\Employee::whereIn('employee_id', ['EMP-2026-0002','EMP-2026-0003'])->delete();"
```

The foreign keys cascade, so attendance, advances and salary payments for those
two employees go with them.

### Registered accounts (passwords unknown — bcrypt hashed)

| Email | Notes |
|-------|-------|
| `somnath.malim46@gmail.com` | Registered via app |
| `system@myvivah.local` | Registered via app |

> Data counts in an earlier version of this document (users, transactions,
> projects) are historical. Query the database or run `php artisan tinker`
> before quoting numbers.

---

## 14. MySQL Recovery Incident (18 Oct 2026)

Kept for history; the server has been stable since.

### What happened
- XAMPP MariaDB 10.4.32 crashed with `APPCRASH` exception `0x80000003` at 11:37 AM
- Root cause: InnoDB corruption from a bad recovery on **12 Oct** where
  `ib_logfile0/1` were wiped while `ibdata1` retained higher LSNs
- Additional: `mysql.proxies_priv` (MyISAM) was crashed, blocking startup
- `gtid_slave_pos.ibd` had a tablespace ID mismatch

### Recovery actions taken
1. Enabled `innodb_force_recovery` in `my.ini`
2. Started with `--skip-grant-tables` to bypass crashed privilege tables
3. Dumped all databases to `Desktop\BAM\mysql_recovery_dumps\`:
   - `all_databases.sql` (89 KB) — full data + schema
   - `mysql_system.sql` (15 KB) — users/grants
4. Moved corrupt `gtid_slave_pos.ibd` → `.bad`

### Pending (Phase 2 — not yet executed)
- [ ] Shutdown MySQL
- [ ] Move `ibdata1`, `ib_logfile0/1`, `ibtmp1` aside
- [ ] Reset `innodb_force_recovery=0`
- [ ] Start clean, restore dumps

---

## 15. Deployment

Designed for **shared hosting (cPanel)** without terminal access. Browser
installer at `/deploy`, protected by `DEPLOY_TOKEN`. See `DEPLOY.md` for the
full walkthrough.

### Deploy steps
1. Create the MySQL database in cPanel
2. Upload the zip — it includes `vendor/` and the committed `public/build/`
3. Two layout options:
   - **Option A (secure):** app outside `public_html`, only `public/` inside
   - **Option B (easy):** everything in `public_html` with `.htaccess` rewrite
4. Create `.env` with DB credentials, `SITE_EMAIL`, `SITE_NOTIFY_EMAIL` and
   `DEPLOY_TOKEN`
5. Permissions: `storage/` and `bootstrap/cache/` → 775, `.env` → 600
6. Visit `/deploy`: generate key → migrate+seed → create owner login → optimize
7. **Remove `DEPLOY_TOKEN`** after setup, clear caches, confirm `/deploy`
   returns 404

> The employee module adds five migrations. After pulling it, run the installer's
> **Run migrations** action (or `php artisan migrate --force` with terminal
> access) before opening `/admin/employees`. Never use **Reset database** on
> production.

### Assets and the pre-commit hook

Production has no Node, so `public/build/` must be committed. The repository
hook (`.git/hooks/pre-commit`, not versioned) rebuilds Vite when staged files
match `resources/(css|js)/`, `resources/views/`, `vite.config.*`,
`tailwind.config.*` or `package*.json`, then stages `public/build`.

The checkout is used from both WSL and Windows. Windows Git has no
`node_modules/.bin/vite.cmd` because `node_modules` was installed under WSL, so
the hook falls back to `wsl.exe ... npm run build` instead of failing. Bypass
once with `SKIP_BUILD=1 git commit ...` only when assets cannot change.

### Production checklist
- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Unique `APP_KEY` generated
- [ ] Strong owner password (not the seeded `password`)
- [ ] `DEPLOY_TOKEN` removed
- [ ] `.env` not web-accessible
- [ ] Demo/seeded accounts removed or passwords changed
- [ ] Enquiry notification address tested

---

## 16. Testing and Known Issues

`php artisan test` currently runs **99 tests** (462 assertions):

- **91 pass**, including all public-site tests (`PublicSiteTest`,
  `SiteSurfaceTest`), the wallet/contractor suites and `EmployeeManagementTest`.
- **8 fail** — all in `ManagerManagementTest`. The `/managers` routes were
  replaced by `/contractors`, so that test file is stale. Fixing it means
  deleting the test plus the leftover `ManagerController`,
  `ManagerReturnController`, `StoreManagerRequest`, `TransferToManagerRequest`,
  `RecordManagerReturnRequest` and `admin/managers/index.blade.php`.

Public-site tests cover: locale routing, rendered copy, portfolio visibility,
filters, enquiry storage/mail/honeypot, sitemap, robots, footer links, and the
removal of the credits page.

`EmployeeManagementTest` has **16 tests / 82 assertions**: generated employee
IDs, dashboard cards, weekly-chart period selection, completed vs current salary
weeks, zero-attendance pay guard, duplicate-period guard, pre-joining attendance
locks, advance-repayment limits, amount-paid limits, partial top-up without a
second repayment, and detail-page pagination.

### Known debt / unused files

- Leftover manager classes and view listed above, plus the unrouted
  `IncomeController`.
- `portfolio_projects.is_sample` column and model attribute are kept but no
  longer read by any view.
- `public/fonts/noto-serif-devanagari-var.ttf` (758 KB) is unused.
- `public/images/site/hero-advertisement*.png` (5 files, ~11 MB) are the
  owner's original posters, no longer referenced by the site.

---

## 17. File Tree (app code only)

```
BC/
├── app/
│   ├── Enums/
│   │   ├── ProjectStatus.php
│   │   ├── TransactionType.php
│   │   ├── UserRole.php
│   │   └── WalletType.php
│   ├── Exceptions/WalletException.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/{Auth,Contractor,Dashboard,Employee,EmployeeAdvance,
│   │   │   │          EmployeeAttendance,EmployeeSalary,Income,Installer,
│   │   │   │          Manager,ManagerReturn,OwnerWallet,Project,
│   │   │   │          ProjectFinance,Transaction}Controller.php
│   │   │   └── Site/{Contact,ContractorStatement,Home,LegacyMarathiRedirect,
│   │   │              Page,Portfolio,Sitemap}Controller.php
│   │   ├── Middleware/{EnsureUserHasRole,SetSiteLocale}.php
│   │   └── Requests/
│   │       ├── Admin/…                ← wallet/project/contractor/employee forms
│   │       └── Site/StoreInquiryRequest.php
│   ├── Models/
│   │   ├── Contractor.php
│   │   ├── ContractorType.php
│   │   ├── Employee.php
│   │   ├── EmployeeAdvance.php
│   │   ├── EmployeeAttendance.php
│   │   ├── EmployeeSalaryPayment.php
│   │   ├── ExpenseCategory.php
│   │   ├── Inquiry.php
│   │   ├── PortfolioProject.php
│   │   ├── Project.php
│   │   ├── User.php
│   │   ├── Wallet.php
│   │   └── WalletTransaction.php
│   ├── Policies/ProjectPolicy.php
│   ├── Providers/AppServiceProvider.php
│   ├── Services/WalletService.php      ← core money logic (873 lines)
│   └── Support/
│       ├── Money.php                   ← bcmath decimal arithmetic
│       └── Site/{SiteContent,SiteRoutes}.php
├── config/site.php                     ← company, address, map, services, SEO
├── database/
│   ├── factories/
│   ├── migrations/                     ← 23 migrations
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── DemoEmployeeSeeder.php      ← local-only review data
│       └── RealPortfolioSeeder.php
├── lang/{en,mr}/site.php               ← public copy, 319 keys each
├── public/
│   ├── build/                          ← committed Vite assets
│   ├── fonts/                          ← self-hosted typefaces
│   └── images/site/                    ← brand, portfolio imagery
├── resources/
│   ├── css/app.css                     ← fonts, theme, components
│   ├── js/app.js                       ← nav, reveal, enquiry submit
│   └── views/                          ← see §11
├── routes/{web,admin}.php
├── storage/
├── tests/Feature/                      ← 13 test files
├── .env                                ← local config (never commit)
├── composer.json                       ← Laravel 12, PHP 8.2+
├── DEPLOY.md                           ← shared hosting guide
├── package.json
├── vite.config.js
└── mysql_recovery_dumps/               ← 18 Oct recovery dumps
```

---

## 18. Related Projects (same machine)

Also present in XAMPP MySQL:

| Database | Description |
|----------|-------------|
| `myvivah` | Marriage/social platform (schema present, mostly empty) |
| `myvivahai_chat` | Chat module for myvivah (has sample data) |

These are **separate projects** sharing the same MySQL instance.

---

*End of project context document.*
