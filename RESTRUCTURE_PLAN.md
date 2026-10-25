# Bandgar — Public Website + Admin Route Restructure Plan

> Status: **Phases 1–4 complete** (branch `restructure/public-site-admin`). Phase 5 not started.
> Scope: move the existing wallet/project app under `/admin`, free up `/` for a public business website.
> Stack stays the same: Laravel 12 · Blade · Tailwind 4 · Vite · MySQL. No framework change.

---

## 1. Verified current state

| Area | Today |
|------|-------|
| App type | Single Laravel 12 app, Blade + Tailwind 4 (Vite), MySQL |
| Internal routes | All at root: `/dashboard`, `/projects`, `/transactions`, `/owner`, `/managers`, `/contractors` |
| Access control | `auth` + `role:owner` middleware group; `ProjectPolicy`; `WalletService` guards |
| Landing | `GET /` → `HomeController` → redirect to dashboard or login |
| Public pages | Only `GET /contractors/{contractor:slug}` (contractor statement, no login) |
| Deploy | `GET/POST /deploy`, protected by `DEPLOY_TOKEN` |
| Route name refs | 24 unique names, ~50 call sites across `app/`, `resources/views/`, `tests/` |
| Tests | 9 feature test files — several call route names, all must stay green |

**Important:** the contractor statement is already public and URL-enumerable (`/contractors/ramesh-jadhav`). Restructuring is the right moment to fix that (§7).

---

## 2. Target architecture

```
bandgar.test/                 → public business website   (marketing, portfolio, contact)
bandgar.test/admin/           → internal app              (wallet, projects, owner, contractors)
bandgar.test/statement/{token}→ contractor statement      (unguessable, noindex)
bandgar.test/deploy           → installer                 (token-protected, removed after setup)
```

### Decision: one app, two route surfaces

| Option | Verdict |
|--------|---------|
| **A. One Laravel app, route-level split** (`/admin` prefix) | ✅ **Recommended.** One deploy, one DB, one auth, shared domain code. Matches current shared-hosting setup. |
| B. Two Laravel apps (site + admin) | ❌ Duplicate auth, assets, deploy pipeline, two `.env`s. No benefit at this size. |
| C. Separate frontend (Next.js) + Laravel API | ❌ Large rewrite. Only justified if the site needs heavy interactivity/CMS. Not now. |
| D. Subdomain `admin.bandgar.com` | 🟡 Later. Same code, add `Route::domain()` behind a config flag when DNS + SSL are ready. |

**Rule:** presentation splits (`Site` vs `Admin`). Domain logic does **not** split.
`app/Models`, `app/Services`, `app/Enums`, `app/Support`, `app/Policies` stay where they are — they are shared by both surfaces and moving them is churn with zero benefit.

---

## 3. Target folder tree

```
app/
├── Enums/                        # unchanged (shared)
├── Exceptions/                   # unchanged
├── Models/                       # unchanged (shared)
├── Policies/                     # unchanged
├── Providers/
├── Services/                     # unchanged — WalletService stays the money source of truth
├── Support/
└── Http/
    ├── Controllers/
    │   ├── Controller.php
    │   ├── Admin/
    │   │   ├── AuthController.php
    │   │   ├── ContractorController.php
    │   │   ├── DashboardController.php
    │   │   ├── IncomeController.php
    │   │   ├── InstallerController.php      # stays mounted at /deploy, not /admin
    │   │   ├── ManagerController.php
    │   │   ├── ManagerReturnController.php
    │   │   ├── OwnerWalletController.php
    │   │   ├── ProjectController.php
    │   │   ├── ProjectFinanceController.php
    │   │   └── TransactionController.php
    │   └── Site/
    │       ├── HomeController.php
    │       ├── PageController.php            # about, services, contact
    │       ├── PortfolioController.php
    │       ├── ContactController.php
    │       └── ContractorStatementController.php
    ├── Middleware/                # unchanged (EnsureUserHasRole)
    └── Requests/
        ├── Admin/                 # moved 1:1, namespace App\Http\Requests\Admin
        └── Site/
            └── StoreInquiryRequest.php

routes/
├── web.php          # public site only
├── admin.php        # NEW — all internal routes, mounted with prefix + name prefix
├── console.php
└── (optional later) site.php

resources/
├── css/  app.css (shared)  ·  site.css (optional, later)
├── js/   app.js (shared)   ·  site.js (optional, later)
└── views/
    ├── layouts/
    │   ├── site.blade.php     # NEW — public header/footer, SEO meta
    │   └── admin.blade.php    # from current layouts/app.blade.php
    ├── site/
    │   ├── home.blade.php
    │   ├── about.blade.php
    │   ├── services.blade.php
    │   ├── portfolio/index.blade.php
    │   ├── portfolio/show.blade.php
    │   ├── contact.blade.php
    │   └── statement.blade.php
    ├── admin/
    │   ├── dashboard.blade.php
    │   ├── projects/...
    │   ├── transactions/...
    │   ├── owners/...
    │   ├── contractors/...
    │   └── auth/login.blade.php
    ├── components/            # shared icons
    └── partials/
        ├── admin/nav-links.blade.php
        └── site/...
```

---

## 4. Route map (before → after)

| Before | After | Name |
|--------|-------|------|
| `GET /` (redirect) | `GET /` → website home | `home` |
| `GET /login` | `GET /admin/login` | `admin.login` |
| `POST /login` | `POST /admin/login` | — |
| `POST /logout` | `POST /admin/logout` | `admin.logout` |
| `GET /dashboard` | `GET /admin/dashboard` | `admin.dashboard` |
| `GET /projects` | `GET /admin/projects` | `admin.projects.index` |
| `GET /projects/{id}` | `GET /admin/projects/{id}` | `admin.projects.show` |
| `GET /projects/create` | `GET /admin/projects/create` | `admin.projects.create` |
| `POST /projects` | `POST /admin/projects` | `admin.projects.store` |
| `POST /projects/{id}/receipts` | `POST /admin/projects/{id}/receipts` | `admin.projects.receipts.store` |
| `POST /projects/{id}/expenses` | `POST /admin/projects/{id}/expenses` | `admin.projects.expenses.store` |
| `POST /projects/{id}/transfers` | `POST /admin/projects/{id}/transfers` | `admin.projects.transfers.store` |
| `POST /projects/{id}/fund` | `POST /admin/projects/{id}/fund` | `admin.projects.fund` |
| `POST /projects/{id}/request-closure` | `POST /admin/projects/{id}/request-closure` | `admin.projects.request-closure` |
| `POST /projects/{id}/finalize-closure` | `POST /admin/projects/{id}/finalize-closure` | `admin.projects.finalize-closure` |
| `GET /transactions` | `GET /admin/transactions` | `admin.transactions.index` |
| `GET /contractors` | `GET /admin/contractors` | `admin.contractors.index` |
| `POST /contractors` | `POST /admin/contractors` | `admin.contractors.store` |
| `GET /owner` | `GET /admin/owner` | `admin.owner.show` |
| `POST /owner/income` | `POST /admin/owner/income` | `admin.owner.income` |
| `POST /owner/transfer` | `POST /admin/owner/transfer` | `admin.owner.transfer` |
| `POST /owner/return` | `POST /admin/owner/return` | `admin.owner.return` |
| `POST /owner/expense` | `POST /admin/owner/expense` | `admin.owner.expense` |
| `GET /contractors/{slug}` | `GET /statement/{token}` | `statements.show` |
| `GET/POST /deploy` | unchanged (stays at root, token-protected) | `deploy.*` |

**New public routes:** `home`, `about`, `services`, `portfolio.index`, `portfolio.show`, `contact`, `contact.store`, `sitemap`.

---

## 5. Wiring changes (concrete)

### 5.1 `bootstrap/app.php`

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
        Route::middleware('web')
            ->prefix('admin')
            ->name('admin.')
            ->group(base_path('routes/admin.php'));
    },
)
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias(['role' => EnsureUserHasRole::class]);

    // One guard, one login screen — everything internal lives under /admin.
    $middleware->redirectGuestsTo(fn () => route('admin.login'));
    $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
})
```

### 5.2 `routes/admin.php` (new)

```php
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // ... all existing internal routes, name prefix already applied by the mount
});
```

### 5.3 Every `route()` call

All 50 call sites switch to the `admin.` prefix — `route('dashboard')` → `route('admin.dashboard')`, `routeIs('projects.*')` → `routeIs('admin.projects.*')`, etc. Views move to `admin.*` view names (`view('admin.projects.index')`).

### 5.4 Transition redirects (cheap insurance)

Old internal bookmarks keep working for one release:

```php
Route::redirect('/dashboard', '/admin/dashboard');
Route::redirect('/login', '/admin/login');
Route::redirect('/projects/{any}', '/admin/projects/{any}')->where('any', '.*');
// ... etc, delete after 30 days
```

---

## 6. Public website — scope

### Pages (v1)

| Page | Purpose | Content source |
|------|---------|----------------|
| Home | Hero, credibility, services, featured projects, CTA | `config/site.php` |
| About | Company story, team, years of experience | `config/site.php` |
| Services | Construction / renovation / contracting offer | `config/site.php` |
| Portfolio | Completed works grid | `portfolio_projects` table |
| Portfolio detail | One project, photos, scope | `portfolio_projects` table |
| Contact | Form + phone/WhatsApp/address/map | `inquiries` table |
| Legal | Privacy, terms (optional) | static |

### Content strategy — recommendation

**Phase A (v1):** `config/site.php` for copy + `portfolio_projects` table for the work grid + `inquiries` table for leads. Fast, no CMS to build, owner edits copy via one file or asks you.

**Phase B (later, only if needed):** admin screen to edit site content. Do **not** build this up front — it's the most expensive part and the value is unproven.

### Critical rule: never expose the internal `projects` table

The internal `Project` holds `agreed_amount`, `manager_id`, wallet balances. The public portfolio must be a **separate table** (`portfolio_projects`) with only public-safe fields: title, slug, location, category, year, summary, description, cover image, gallery, `is_published`. Financial data never crosses over.

### Contact form

- `inquiries`: name, phone, email, service, message, source, ip, `created_at`, status.
- Honeypot field + Laravel rate limiter (`throttle:5,1`) + optional reCAPTCHA if spam appears.
- Store in DB first, notify owner by mail second (never let mail failure lose a lead).
- Admin inbox can come later; `php artisan tinker` is fine until then.

### SEO / trust basics

- Unique `<title>`, meta description, canonical per page.
- Open Graph + Twitter card, `LocalBusiness` JSON-LD.
- `/sitemap.xml` route, `robots.txt` with `Disallow: /admin` and `Disallow: /statement`.
- WhatsApp/click-to-call on mobile, Google Maps embed on contact.
- Real photos > stock. Warm, natural, site-work imagery.

---

## 7. Contractor statement — fix while restructuring

Current: `/contractors/{slug}` — anyone who guesses a name reads that contractor's full financial statement. That's a privacy problem.

**Recommended:** `/statement/{token}` where `token` is a 40-char random string stored on `contractors.statement_token` (unique, indexed). Benefits over `URL::signedRoute()`: survives `APP_KEY` rotation, can be revoked/regenerated, stable link the owner can re-send.

Also:
- `<meta name="robots" content="noindex,nofollow">` on the statement layout.
- `Cache-Control: private, no-store`.
- Rate-limit repeated 404s on `/statement/{token}`.
- Keep a permanent 301 from the old slug URL during transition **only if** it was already shared — otherwise drop it.

---

## 8. Phased execution plan

One phase at a time. Each phase is a separate commit and must end green before the next starts.

### Phase 0 — Baseline & safety
- `git checkout -b restructure/public-site-admin`
- Run `php artisan test` — record the green baseline.
- `php artisan route:list > /tmp/routes-before.txt`
- Back up DB (`mysqldump bandgar_wallet`).
- **Accept:** baseline captured, tests green, rollback point exists.

### Phase 1 — Routes only, zero visual change
- Create `routes/admin.php`, mount with `prefix('admin')->name('admin.')`.
- Move internal routes out of `web.php`.
- Update guest/user redirects in `bootstrap/app.php`.
- Update all `route()` and `routeIs()` calls (app + views + tests).
- Add transition redirects.
- **Accept:** `/admin/dashboard` works, `/` returns a placeholder "coming soon", every test green, `route:list` shows no duplicate names.

### Phase 2 — Split HTTP layer
- Move controllers to `App\Http\Controllers\Admin` and requests to `App\Http\Requests\Admin`; update namespaces + imports.
- Move views to `resources/views/admin/`; update `view()` calls.
- **Accept:** no behaviour change, all tests green, `php artisan view:clear && route:clear` clean.

### Phase 3 — Layouts & assets
- Rename `layouts/app.blade.php` → `layouts/admin.blade.php`.
- Split `partials/nav-links.blade.php` → `partials/admin/nav-links.blade.php`.
- Create `layouts/site.blade.php` (public header/footer, SEO slots).
- Keep a single Vite entry for now; split `site.css`/`site.js` only if the site design diverges enough to justify it.
- **Accept:** admin looks and behaves exactly as before.

### Phase 4 — Public website v1
- `config/site.php` (brand, phone, address, services, socials).
- `Site\HomeController`, `PageController`, `ContactController`.
- `portfolio_projects` + `portfolio_images` migrations, model, seeder with 6–9 placeholder works.
- `inquiries` migration + `StoreInquiryRequest` + mail notification.
- Views: home, about, services, portfolio index/show, contact.
- SEO: meta partial, sitemap, robots, JSON-LD.
- **Accept:** `/`, `/about`, `/services`, `/portfolio`, `/contact` render on mobile + desktop; contact form stores a row and sends mail; no route leaks financial data.

#### Phase 4 — as built (2026-10-25)
- English at the root, Marathi under `/mr`; URL is the language source of truth, with `hreflang` + `x-default` on every page.
- Portfolio lives at `/projects` (not `/portfolio`). The legacy `projects` → `/admin/projects` redirect was **removed**; `/projects` now belongs to the public site.
- Separate `portfolio_projects` table (never the internal `Project` model) plus an `inquiries` table for enquiries. Gallery images are a JSON column, so no separate `portfolio_images` table was needed.
- Portfolio entries carry `is_sample` and are labelled "Illustrative sample" in the UI and on the detail page. Replace before launch.
- Placeholder photography comes from Wikimedia Commons with visible credits at `/credits`; replace with real project photographs before launch.
- Contact form: honeypot, validation, throttling (`5,1`), stores the row, emails the owner when `SITE_NOTIFY_EMAIL` (or `SITE_EMAIL`) is set.
- No email address, social profile, map embed or founding date is published — unverified business facts stay empty.
- `public/robots.txt` disallows `/admin`, `/deploy`, `/contractors`, and the legacy internal paths; `sitemap.xml` lists both languages.
- Tests: `PublicSiteTest` (14 tests) covers rendering, locale switching, portfolio visibility and filters, enquiry storage/mail/honeypot, sitemap, robots and credits. Suite: **67 passed, 8 failed** — the same 8 pre-existing `ManagerManagementTest` failures from the phase-0 baseline.

### Phase 5 — Statement security
- Add `statement_token` to `contractors` (nullable, unique), backfill existing rows.
- New `Site\ContractorStatementController@show` at `/statement/{token}`.
- Old slug URL → 410 (or 301 if already shared).
- noindex + no-store headers.
- **Accept:** valid token renders the same statement; invalid token 404s; old slug no longer exposes data.

### Phase 6 — Hardening & deploy
- Throttle admin login (`throttle:5,1` + `RateLimiter`).
- `noindex` on admin layout; `robots.txt` disallow.
- HTTPS-only cookies, `SESSION_SECURE_COOKIE=true` in production.
- Update `DEPLOY.md` and `PROJECT_CONTEXT.md` with the new URL map.
- Confirm `/deploy` still works and is removed after setup.
- Final `php artisan optimize` on production, smoke test both surfaces.
- **Accept:** production checklist complete, both surfaces verified.

**Estimated effort:** Phase 1–3 ≈ 1 working day (mechanical, test-guarded). Phase 4 ≈ 2–4 days depending on design/content. Phase 5 ≈ half a day. Phase 6 ≈ half a day.

---

## 9. Risks & gotchas

| Risk | Mitigation |
|------|------------|
| Route-name collisions / missed `route()` calls | Grep the 24 known names; tests catch the rest. Do Phase 1 alone. |
| Guest redirect loops (`/admin/login` ↔ `/admin`) | Set both `redirectGuestsTo` and `redirectUsersTo`; test guest + authed hitting `/admin/login`. |
| Old shared contractor links break | Decide 301 vs 410 per §7 before deploying Phase 5. |
| Public site accidentally queries internal `Project` | Separate `portfolio_projects` table; code review rule. |
| `config:cache` hides new `config/site.php` edits | Document "run `php artisan config:clear` after copy changes". |
| Tailwind 4 `@source` picking up stale classes | `@source '../**/*.blade.php'` already covers new folders; rebuild after moves. |
| Money logic touched during refactor | Forbidden. `WalletService`, `Money`, enums, models are read-only in Phases 1–3. |
| Installer route locked behind new auth | Keep `/deploy` outside the `admin` group. |
| Two Vite builds on shared hosting | Single entry point until the site justifies its own bundle. |

---

## 10. What we are explicitly NOT doing

- Rewriting `WalletService` or any money logic.
- Changing DB schema for existing tables (except adding `contractors.statement_token`).
- Introducing a CMS, admin-editable pages, or a blog in v1.
- Adding API routes, Sanctum, or a separate frontend framework.
- Renaming domain models/services for aesthetics.

---

## 11. Open questions

**Answered before Phase 4:**

1. Brand: Bandgar Construction & Developers; phones, WhatsApp and Kargani/Atpadi address confirmed. No email, socials or map link yet.
2. Services: 13 services, architecture → construction → interiors, ordered in `config/site.php`.
3. Language: English + Marathi (`/mr`).
4. Portfolio: no real photographs available yet; v1 ships labelled illustrative samples and CC placeholder images.
5. Statement links: still open (decides 301 vs 410 in Phase 5).
6. Domain: unchanged for now — root site with `/admin`.
7. Extras: deferred; no blog/testimonials in v1.
8. Content editing: copy lives in `lang/` + `config/site.php` for now; no CMS.

**Still open:**

- Real project photographs, names and permission to publish.
- Confirmed email address, Google Maps link and social profiles.
- Whether any `/contractors/{slug}` links were shared (Phase 5).
- Final domain and whether admin moves to a subdomain later.

1. **Brand name & contact details** — is the public brand "Bandgar Construction"? Phone, WhatsApp, address, email, Google Maps link?
2. **Services list** — which services do we advertise, and in what order?
3. **Language** — English only, or English + Marathi/Hindi versions?
4. **Portfolio content** — do we have real project photos, client names, and permission to publish them, or should v1 use placeholders?
5. **Statement links** — were any `/contractors/{slug}` links already shared with contractors? (decides 301 vs 410)
6. **Domain** — root domain with `/admin`, or eventually `admin.` subdomain?
7. **Extras** — careers page, testimonials, blog, WhatsApp floating button? (v1 or later)
8. **Content editing** — must the owner edit website copy himself, or is "ask Ravonna/Somnath" acceptable for v1?

---

*End of plan. Phase 5 awaits the statement-link decision.*
