# BAM — Project Context Document

> Generated: 18 October 2026
> Location: `C:\Users\Somnath Mali\Desktop\BAM`
> Owner: Somnath Mali

---

## 1. Overview

**Bandgar Construction — Wallet & Project Management (BAM)** is a focused Laravel application for tracking:

- Company cash (business wallet)
- Project wallet allocations
- Project costs (direct + manager-mediated)
- Manager transfers and returns
- Owner personal wallet
- Project closure with fund release

It is deliberately **not** a full accounting/ERP system. Scope is limited to the agreed contract for Bandgar Construction.

---

## 2. Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 (PHP 8.2+) |
| Database | MySQL / MariaDB (XAMPP local) |
| Frontend | Blade + Tailwind CSS 4 (Vite) |
| Money math | `bcmath` via `App\Support\Money` (no floats) |
| Tests | PHPUnit feature tests |
| Local server | `php artisan serve` (port 8000) |

**Money columns:** `DECIMAL(14,2)` — never floats. All arithmetic goes through `App\Support\Money`.

---

## 3. The Wallet Model (Core Concept)

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
Manager spending (net) = Given to manager − Returned by manager
Total spending = Direct spending + Manager spending (net)
```

### Invariant

> Unallocated funds = Business balance − sum(active project assigned balances)

Setting aside money and closing a project do **not** change total company cash — they only reclassify money between "free" and "set aside for a project".

---

## 4. Roles

| Role | Access |
|------|--------|
| **Owner / Admin** | Full: projects, receipts, unallocated income, funding, expenses, manager transfers/returns, owner wallet, closure, reports |
| **Project Manager** | Read-only. Views assigned projects and their cost transfers/returns. Cannot add expenses, issue transfers, or access company/owner wallets |

Enforcement: `role` middleware, `ProjectPolicy`, `WalletService` guards. Hiding UI buttons is **not** relied upon.

---

## 5. Core Architecture

### Key files

| File | Purpose |
|------|---------|
| `app/Services/WalletService.php` | Single source of truth for ALL money movements and balance changes (782 lines). Controllers only validate, authorise, delegate. |
| `app/Support/Money.php` | Exact decimal arithmetic (bcmath wrapper) |
| `app/Enums/*` | `UserRole`, `ProjectStatus`, `WalletType`, `TransactionType` |
| `app/Exceptions/WalletException.php` | Domain exception for wallet errors |

### Transaction safety

Every balance change runs inside `DB::transaction` with `lockForUpdate` row locks. Failed operations roll back completely.

### Immutable ledger

`wallet_transactions` is the immutable history. Returns are separate records linked to their original manager cost via `related_transaction_id`.

---

## 6. Enums

### UserRole
- `owner` — Owner / Admin
- `manager` — Project Manager

### ProjectStatus
- `active` — Active
- `closure_pending` — Closure Pending
- `closed` — Closed

### WalletType
- `business` — Company wallet
- `project` — Per-project wallet
- `owner` — Owner personal wallet

### TransactionType (9 types)

| Technical name | On-screen label | Business cash | Project assigned | Project cost |
|----------------|-----------------|---------------|------------------|--------------|
| `receipt` (project) | Money received | + | + | — |
| `receipt` (unallocated) | Money received | + | — | — |
| `project_expense` | Project expense (paid directly) | − | − | + |
| `manager_transfer` | Money given to manager | − | − | + |
| `manager_return` | Money returned by manager | + | + | − (reduces) |
| `owner_transfer` | Money moved to owner | − | — | — |
| `owner_expense` | Owner personal expense | — | — | — (owner wallet only) |
| `owner_return` | Owner money returned to company | + | — | — (owner → company) |
| `project_allocation` | Money set aside for project | — | + | — (unallocated → project) |
| `closure_release` | Project closed, funds released | — | − | — (reclassification) |

---

## 7. Models

### User
- Fillable: `name`, `email`, `password`, `role`, `is_active`
- Hidden: `password`, `remember_token`
- Roles via `UserRole` enum

### Project
- Fillable: `name`, `location`, `agreed_amount`, `manager_id`, `status`, `closed_at`
- Casts: `agreed_amount` → `decimal:2`, `status` → `ProjectStatus`
- Relations: `manager()` (belongsTo User)

### Wallet
- Fillable: `type`, `project_id`, `user_id`, `balance`
- Casts: `type` → `WalletType`, `balance` → `decimal:2`
- Relations: `project()`, `user()`

### WalletTransaction
- Fillable: `type`, `amount`, `transaction_date`, `description`, `project_id`, `category_id`, `manager_id`, `source_wallet_id`, `destination_wallet_id`, `related_transaction_id`, `created_by`
- Casts: `type` → `TransactionType`, `amount` → `decimal:2`, `transaction_date` → `date`

### ExpenseCategory
- Fillable: `name`, `context`, `is_active`
- Scopes: `active()`, `forProject()`

---

## 8. Controllers

| Controller | Responsibility |
|------------|----------------|
| `AuthController` | Login/logout |
| `DashboardController` | Dashboard overview |
| `HomeController` | Public landing page |
| `IncomeController` | Record unallocated income |
| `InstallerController` | Browser-based deploy/migrate/seed (`/deploy`) |
| `ManagerController` | CRUD managers |
| `ManagerReturnController` | Record manager returns |
| `OwnerWalletController` | Owner wallet show/transfer/return/expense |
| `ProjectController` | Project CRUD + closure request/finalize |
| `ProjectFinanceController` | Receipts, expenses, transfers, funding per project |
| `TransactionController` | Transaction ledger index |

---

## 9. Routes

> **Current map after the restructure (Phases 1–4).** The pre-restructure route list below it is kept for history only.

### Public business website (`routes/web.php`, English at the root, Marathi under `/mr`)
- `GET /` — home
- `GET /about`, `GET /services`, `GET /contact`, `GET /credits`
- `GET /projects`, `GET /projects/{slug}` — public portfolio
- `GET /sitemap.xml` — bilingual sitemap
- `GET /contractors/{contractor}` — public contractor statement (unchanged until Phase 5)

### Internal app (`routes/admin.php`, mounted at `/admin` by `bootstrap/app.php`)
- Login, dashboard, projects, transactions, owner wallet, contractors — all under `/admin`
- Legacy paths (`/login`, `/dashboard`, `/owner`, `/transactions`, `/contractors`) 301 to their `/admin` equivalent

### Deployment
- `GET /deploy`, `POST /deploy/run` — protected by `DEPLOY_TOKEN`

### Historical route list (pre-restructure)

### Public
- `GET /` — welcome page
- `GET /login`, `POST /login` — auth (guest only)
- `POST /logout` — auth required

### Auth required
- `GET /dashboard`
- `GET /projects`, `GET /projects/{id}`
- `GET /transactions`

### Owner only (`role:owner` middleware)
- `GET /projects/create`, `POST /projects`
- `POST /projects/{id}/receipts`
- `POST /projects/{id}/expenses`
- `POST /projects/{id}/transfers`
- `POST /projects/{id}/fund`
- `POST /transfers/{id}/returns`
- `POST /projects/{id}/request-closure`
- `POST /projects/{id}/finalize-closure`
- `POST /income`
- `GET /managers`, `POST /managers`
- `GET /owner`, `POST /owner/transfer`, `POST /owner/return`, `POST /owner/expense`

### Deploy (protected by DEPLOY_TOKEN)
- `GET /deploy`, `POST /deploy/run`

---

## 10. Views (Blade)

```
resources/views/
├── auth/login.blade.php
├── components/icon.blade.php
├── dashboard.blade.php
├── layouts/app.blade.php
├── managers/index.blade.php
├── owner/show.blade.php
├── partials/nav-links.blade.php
├── projects/create.blade.php
├── projects/index.blade.php
├── projects/show.blade.php
├── transactions/index.blade.php
└── welcome.blade.php
```

---

## 11. Database Schema (Migrations)

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

---

## 12. Local Development Environment

### `.env` (local)
```
APP_NAME="Bandgar Account Manager"
APP_ENV=production (or local)
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bandgar_wallet
DB_USERNAME=root
DB_PASSWORD=•••• (see .env — never commit)
```

### Database: `bandgar_wallet`
Host: XAMPP MariaDB 10.4.32 at `C:\xampp\mysql\`

### Seeded accounts (password: `password`)
| Email | Role |
|-------|------|
| `owner@bandgar.test` | Owner / Admin |
| `ramesh@bandgar.test` | Project Manager |

### Registered accounts (passwords unknown — bcrypt hashed)
| Email | Notes |
|-------|-------|
| `somnath.malim46@gmail.com` | Registered via app |
| `system@myvivah.local` | Registered via app |

### Current data (as of 18 Oct 2026 recovery)
| Database | Contents |
|----------|----------|
| `bandgar_wallet` | 4 users, 4 wallets, 10 transactions, 2 projects, 10 expense categories, 7 sessions |
| `myvivah` | 20 table structures (mostly empty — schema-only) |
| `myvivahai_chat` | 5 users, 3 conversations, 9 messages, 10 receipts |

---

## 13. MySQL Recovery Incident (18 Oct 2026)

### What happened
- XAMPP MariaDB 10.4.32 crashed with `APPCRASH` exception `0x80000003` at 11:37 AM
- Root cause: InnoDB corruption from a bad recovery on **12 Oct** where `ib_logfile0/1` were wiped while `ibdata1` retained higher LSNs
- Additional: `mysql.proxies_priv` (MyISAM) was crashed, blocking startup
- `gtid_slave_pos.ibd` had tablespace ID mismatch

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

## 14. Deployment

Designed for **shared hosting (cPanel)** without terminal access. Browser installer at `/deploy` (protected by `DEPLOY_TOKEN` in `.env`).

### Deploy steps
1. Create MySQL database in cPanel
2. Upload zip (includes `vendor/` and `public/build/`)
3. Two layout options:
   - **Option A (secure):** App outside `public_html`, only `public/` contents inside
   - **Option B (easy):** Everything in `public_html` with `.htaccess` rewrite
4. Create `.env` with DB credentials + `DEPLOY_TOKEN`
5. Set permissions: `storage/` and `bootstrap/cache/` → 775, `.env` → 600
6. Visit `/deploy`, run: generate key → migrate+seed → create owner login → optimize
7. **Remove `DEPLOY_TOKEN`** after setup, clear caches, confirm `/deploy` returns 404

### Production checklist
- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Unique `APP_KEY` generated
- [ ] Strong owner password (not seeded `password`)
- [ ] `DEPLOY_TOKEN` removed
- [ ] `.env` not web-accessible
- [ ] Demo accounts removed or passwords changed

---

## 15. File Tree (app code only)

```
BAM/
├── app/
│   ├── Enums/
│   │   ├── ProjectStatus.php
│   │   ├── TransactionType.php
│   │   ├── UserRole.php
│   │   └── WalletType.php
│   ├── Exceptions/
│   │   └── WalletException.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── Controller.php
│   │   │   ├── DashboardController.php
│   │   │   ├── HomeController.php
│   │   │   ├── IncomeController.php
│   │   │   ├── InstallerController.php
│   │   │   ├── ManagerController.php
│   │   │   ├── ManagerReturnController.php
│   │   │   ├── OwnerWalletController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── ProjectFinanceController.php
│   │   │   └── TransactionController.php
│   │   ├── Middleware/
│   │   │   └── EnsureUserHasRole.php
│   │   └── Requests/
│   │       ├── FundProjectRequest.php
│   │       ├── ReceiveProjectPaymentRequest.php
│   │       ├── ReceiveUnallocatedIncomeRequest.php
│   │       ├── RecordManagerReturnRequest.php
│   │       ├── RecordOwnerExpenseRequest.php
│   │       ├── RecordProjectExpenseRequest.php
│   │       ├── ReturnOwnerFundsRequest.php
│   │       ├── StoreManagerRequest.php
│   │       ├── StoreProjectRequest.php
│   │       ├── TransferToManagerRequest.php
│   │       └── TransferToOwnerRequest.php
│   ├── Models/
│   │   ├── ExpenseCategory.php
│   │   ├── Project.php
│   │   ├── User.php
│   │   ├── Wallet.php
│   │   └── WalletTransaction.php
│   ├── Policies/
│   │   └── ProjectPolicy.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   ├── Services/
│   │   └── WalletService.php          ← core money logic (782 lines)
│   └── Support/
│       └── Money.php                  ← bcmath decimal arithmetic
├── config/
├── database/
│   ├── factories/
│   ├── migrations/                    ← 8 migrations
│   └── seeders/
│       └── DatabaseSeeder.php
├── public/
├── resources/views/                   ← 12 Blade templates
├── routes/
│   ├── console.php
│   └── web.php                        ← all routes
├── storage/
├── tests/
├── .env                               ← local config (never commit)
├── .env.example
├── composer.json                      ← Laravel 12, PHP 8.2+
├── DEPLOY.md                          ← shared hosting guide
├── README.md
├── vite.config.js
└── mysql_recovery_dumps/              ← 18 Oct recovery dumps
    ├── all_databases.sql
    └── mysql_system.sql
```

---

## 16. Related Projects (same machine)

Also present in XAMPP MySQL:

| Database | Description |
|----------|-------------|
| `myvivah` | Marriage/social platform (schema present, mostly empty) |
| `myvivahai_chat` | Chat module for myvivah (has sample data) |

These are **separate projects** sharing the same MySQL instance.

---

*End of project context document.*
