# Bandgar Construction — Wallet & Project Management

A small, focused Laravel application for tracking company cash, project wallet
allocations, project costs and manager transfers for Bandgar Construction.

It is deliberately **not** a full accounting/ERP system. Scope is limited to the
agreed contract.

## Stack

- Laravel 12 (PHP 8.2+)
- MySQL/MariaDB (`DECIMAL(14,2)` money columns) — SQLite works for local tests
- Blade + Tailwind CSS 4 (Vite)
- PHPUnit feature tests
- All money arithmetic uses `bcmath` via `App\Support\Money` (no floats)

## The wallet model (important)

- **Business Wallet** (shown as *Company money (total)*) = the company's overall
  actual cash balance.
- **Project Wallet** (shown as *Project money left*) = the portion of company cash
  currently *set aside* for a project. It is a tracking sub-balance, **not extra
  cash**.
- **Owner Personal Wallet** (shown as *Owner's personal money*) = company funds
  moved to the owner. It is **excluded** from company cash. Money can also be moved
  back from the personal wallet into company cash.

Formulas (using the on-screen names):

```
Free company money = Company money (total) - Sum of active project money left
Manager spending (net) = Given to manager - Returned by manager
Total spending = Direct spending + Manager spending (net)
```

A project can be funded either by **receiving a payment for that project** (new
money coming in) or by **setting aside free company money** for it. Setting aside
money and closing a project do **not** change total company money — they only move
money between "free" and "set aside for a project".

## Roles

- **Owner / Admin**: full access — projects, receipts, unallocated income,
  project funding, expenses, manager transfers, manager returns, owner personal
  wallet (transfers in and out), closure, reports.
- **Project Manager**: read-only. Can view assigned projects and the cost
  transfers/returns for those projects. Cannot add expenses, issue transfers, or
  access company/owner wallets.

Permissions are enforced on the backend (`role` middleware, `ProjectPolicy`, and
`WalletService` guards). Hiding UI buttons is not relied upon.

## Core architecture

- `app/Services/WalletService.php` — the single place where all money movements
  and balance changes happen. Controllers only validate, authorise and delegate.
- `app/Support/Money.php` — exact decimal arithmetic.
- `app/Enums/*` — `UserRole`, `ProjectStatus`, `WalletType`, `TransactionType`.
- Every balance change runs inside `DB::transaction` with `lockForUpdate` row
  locks. Failed operations roll back completely.
- `wallet_transactions` is the immutable history: returns are separate records
  linked to their original manager cost via `related_transaction_id`.

### Wallet transaction types

The app avoids technical jargon. Each type has a plain name shown on screen:

| Technical name | What it is called on screen | Business cash | Project assigned | Project cost |
|----------------|-----------------------------|---------------|------------------|--------------|
| `receipt` (project) | Money received | + | + | — |
| `receipt` (unallocated) | Money received | + | — | — |
| `project_expense` | Project expense (paid directly) | − | − | + |
| `manager_transfer` | Money given to manager | − | − | + |
| `manager_return` | Money returned by manager | + | + | − (reduces) |
| `owner_transfer` | Money moved to owner | − | — | — |
| `owner_expense` | Owner personal expense | — | — | — (owner wallet only) |
| `owner_return` | Owner money returned to company | + | — | — (owner wallet → company) |
| `project_allocation` | Money set aside for project | — | + | — (unallocated → project) |
| `closure_release` | Project closed, funds released | — | − | — (reclassification) |

## Plain-language guide (for non-technical users)

The screen wording is deliberately simple. This is what each term means:

| On screen | Simple meaning |
|-----------|----------------|
| Company money (total) | All the money the company actually has right now. |
| Free company money | Company money that is **not** set aside for any project yet. |
| Owner's personal money | Company money moved out to the owner personally. Not counted as company money. |
| Money received | Cash that came in (for a project, or general company income). |
| Project value (agreed) | The agreed price/value of the project with the client. |
| Still to receive | Project value minus money received so far. |
| Project money left | Money set aside for the project minus what has been spent from it. |
| Direct spending | Spent by the company directly on the project. |
| Given to manager | Money handed to the manager for project work. |
| Returned by manager | Money the manager gave back (unused). |
| Manager spending (net) | Given to manager minus returned by manager. |
| Total spending | Direct spending + manager spending (net). |
| Set aside money | Move free company money into a project (no new cash involved). |
| Money movements | The full list of every money in/out entry. |
| What it was for | The category/reason for a money entry. |
| Entered by | The user who recorded the entry. |

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configure DB_* in .env, then:
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Default seeded accounts (change the passwords before going live):

| Role | Email | Password |
|------|-------|----------|
| Owner | owner@bandgar.test | password |
| Manager | ramesh@bandgar.test | password |

## Tests

```bash
php artisan test
```

Covered: the contract workflow from a zero opening balance, phased receipts,
unallocated income, project funding from unallocated funds, owner funds returned
to company, direct expenses, manager transfers and returns, closure release,
insufficient balances, duplicate/over-returns, and role permissions (service and
HTTP level).

## Deployment notes

See **[DEPLOY.md](DEPLOY.md)** for a full step-by-step shared-hosting guide
(no terminal required).

Quick summary:

- Point `APP_URL`/`DB_*` at the production host.
- Build assets with `npm ci && npm run build` on the server or upload `public/build`.
- Set file permissions on `storage/` and `bootstrap/cache/`.
- Run `php artisan migrate --force` (and `route:cache`) **or** use the built-in
  browser installer at `/deploy` (guarded by the `DEPLOY_TOKEN` env var) to run
  migrations, seeders, caches and to create the owner login.
- Remove `DEPLOY_TOKEN` (or rotate the seeded demo users and set strong
  passwords) once the site is live.

## Out of scope (per contract)

No CRM, invoicing, receivables, vendor/supplier billing, inventory, estimation/
scheduling, GST/tax/payroll, formal double-entry accounting, bank integrations,
mobile apps, multi-company/SaaS, third-party integrations, or approval workflows.
