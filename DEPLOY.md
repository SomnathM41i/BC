# Deploying to shared hosting

This guide takes you from the zip file to a working site on a typical cPanel /
shared hosting account **without needing a terminal**. Everything that normally
needs the command line (migrations, seeders, caches, app key) is available from a
protected page at `/deploy`.

---

## 1. What you need from your hosting account

- A **MySQL/MariaDB database**: name, username, password and host (usually
  `localhost`).
- The **document root** of your domain. On cPanel this is normally
  `public_html`.
- **PHP 8.2 or newer** with these extensions enabled: `bcmath`, `pdo_mysql`,
  `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`.
  You can confirm this on the `/deploy` page once uploaded. If `bcmath` is
  missing, ask your host to enable it — the money calculations require it.

## 2. Create the database

1. In cPanel open **MySQL Databases**.
2. Create a database (e.g. `myuser_bandgar`) and a database user.
3. Add the user to the database with **ALL PRIVILEGES**.
4. Note the full database name, username and password.

## 3. Upload the files

The zip contains the complete application **including `vendor/` and the built
`public/build/` assets**, so you do not need Composer or Node on the server.

You have two layout options. **Option A is the most secure; Option B is the
easiest.**

### Option A (recommended) — application outside the web root

1. Upload and extract the zip to a folder **outside** `public_html`, for example
   `/home/youruser/bandgar`.
2. Copy **the contents of** `bandgar/public/` into `public_html/`
   (i.e. `public_html/index.php`, `public_html/.htaccess`, and the
   `public_html/build/` folder).
3. Open `public_html/index.php` and change the two `require` lines so they point
   at the application folder you created:

   ```php
   require __DIR__.'/../bandgar/vendor/autoload.php';
   // ...
   $app = require_once __DIR__.'/../bandgar/bootstrap/app.php';
   ```

   (Replace `bandgar` with your actual folder name.)

### Option B (easiest) — everything inside `public_html`

1. Upload and extract the whole zip directly into `public_html`.
2. That's it — the `.htaccess` included in the zip automatically sends all
   requests through the app's `public/` folder. Visitors never see your `.env`,
   `vendor/` or other internals.

## 4. Create the `.env` file

1. Copy `.env.example` to `.env` (in the application folder).
2. Edit `.env` and set at least:

   ```
   APP_URL=https://your-domain.com
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   DB_HOST=localhost

   # Choose a long random secret for the installer:
   DEPLOY_TOKEN=some-long-random-secret
   ```

   `APP_KEY` can be left empty — step 6 generates it for you. If you prefer,
   paste any `base64:` key yourself.

   > Keep `APP_ENV=production` and `APP_DEBUG=false` that are already in the
   > example file.

## 5. Set folder permissions

Using the cPanel **File Manager**, set permissions (right-click → *Change
Permissions*):

- `storage/` and everything inside it → **775** (and ensure "recurse").
- `bootstrap/cache/` → **775**.
- `.env` → **600** (or at least 644).

## 6. Run the installer

1. Visit `https://your-domain.com/deploy`.
2. You will see an environment check. Fix anything marked **FAIL** first.
3. Enter your `DEPLOY_TOKEN` and run, in this order:

   1. **Generate application key**
   2. **Install (migrate + seed)** — creates the tables and starter data
   3. **Create / reset owner login** — enter your name, email and a strong
      password. This is the account you will sign in with.
   4. **Optimize for production**

4. Open `https://your-domain.com` and sign in.

## 7. Disable the installer (important)

Once you are logged in and everything works:

1. Open `.env` and remove the value of `DEPLOY_TOKEN` (leave the line, e.g.
   `DEPLOY_TOKEN=`).
2. Go back to `/deploy` and click **Clear all caches** so the cached config no
   longer holds the old token.
3. Confirm that `https://your-domain.com/deploy` now returns a **404**.

---

## Everyday tasks after deployment

Use the same `/deploy` page (re-enable it temporarily by setting `DEPLOY_TOKEN`
again) for maintenance:

| Task | Button |
|------|--------|
| Apply new database changes after an update | Run migrations |
| Recreate default categories/accounts | Run seeders |
| Site behaving oddly after a config change | Clear all caches |
| Speed things up after changes | Optimize for production |

---

## Troubleshooting

**Blank page or "500 Server Error"**
- Set `APP_DEBUG=true` in `.env` temporarily to see the real error, then set it
  back to `false`.
- Check `storage/logs/laravel.log` in the File Manager.
- Make sure `storage/` and `bootstrap/cache/` are writable (775) — this is the
  most common cause.

**"No application encryption key has been specified"**
- Run **Generate application key** on the `/deploy` page.

**"SQLSTATE: could not find driver" or database errors**
- The `pdo_mysql` extension is missing or the `.env` database details are wrong.
- Re-check step 4 and the **Database connection** check on `/deploy`.

**CSS looks broken / no styling**
- Confirm the `public/build/` folder was uploaded. If you use Option A, make
  sure it was copied into `public_html`.

**"The deployment token you entered is incorrect"**
- The value must exactly match `DEPLOY_TOKEN` in `.env`. After changing `.env`,
  click **Clear all caches** (or wait, as cached config is refreshed).

**Changing `.env` seems to have no effect**
- Click **Clear all caches** on `/deploy`. When config is cached, Laravel reads
  the cached values, not `.env`.

**HTTPS / redirect loops**
- Set `APP_URL` to the exact `https://` address. Most hosts issue a free SSL
  certificate (Let's Encrypt) from cPanel; also enable "Force HTTPS Redirect".

---

## Security checklist before handing over

- [ ] `APP_ENV=production` and `APP_DEBUG=false`.
- [ ] A unique `APP_KEY` generated (step 6.1).
- [ ] Owner login uses a strong, private password (step 6.3).
- [ ] `DEPLOY_TOKEN` removed and `/deploy` returns 404 (step 7).
- [ ] `.env` is not web-accessible (it isn't with either Option A or B above).
- [ ] Seeded demo accounts (`owner@bandgar.test`, `ramesh@bandgar.test`) either
      removed or given strong passwords. The owner account is replaced when you
      use **Create / reset owner login**; create managers from the **Managers**
      page instead of leaving the demo manager in place. To remove the demo
      manager, ask your host for a one-off database query or use the reset
      option on `/deploy` (this wipes all data).
