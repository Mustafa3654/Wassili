# Deploying Wassili to Hostinger (wassilli.com)

Written for a **Hostinger shared plan** with hPanel. No Node, no queue worker
and no root access are assumed — everything below runs with what a shared plan
gives you.

---

## 0. Before you start

Check in hPanel that the plan gives you:

| Requirement | Where | Needed value |
|---|---|---|
| PHP version | hPanel → Advanced → PHP Configuration | **8.4 or newer** |
| PHP extensions | same screen, "PHP extensions" tab | `intl`, `zip`, `mbstring`, `pdo_mysql`, `fileinfo`, `gd`, `openssl` |
| SSH access | hPanel → Advanced → SSH Access | Optional but makes this far easier |

`intl` and `zip` are the two people usually have to switch on — Filament will
not boot without them.

Two version gotchas on Hostinger:

- `composer.json` allows PHP 8.2, but the committed `composer.lock` resolves to
  Symfony 8 packages that require **8.4+**. Installing from the lock on anything
  older fails. Either run 8.4/8.5, or regenerate the lock with
  `config.platform.php` pinned to your target version.
- **SSH has its own PHP default, separate from the domain's.** A shell that
  reports 8.1 while the site runs 8.5 is normal. Point your shell at the right
  binary before running composer or artisan:

  ```bash
  echo "alias php='/opt/alt/php85/usr/bin/php'" >> ~/.bashrc && source ~/.bashrc
  php -v          # confirm before continuing
  ```

---

## 1. Create the database

hPanel → **Databases → MySQL Databases**. Create a database and a user, and
grant the user all privileges on it.

Hostinger prefixes both with your account id, so you end up with something like
`u123456789_wassili` / `u123456789_admin`. Write the three values down — you'll
paste them into `.env` in step 3. **Keep the password to yourself; it only ever
gets typed into the server's `.env`.**

---

## 2. Get the code onto the server

### With SSH (preferred)

```bash
cd ~/domains/wassilli.com
rm -rf public_html                      # only if it's still the default parking page
git clone https://github.com/Mustafa3654/Wassili.git app
cd app
composer install --no-dev --optimize-autoloader
```

If Composer isn't on PATH:

```bash
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

### Without SSH (File Manager / FTP)

1. Run `composer install --no-dev --optimize-autoloader` **locally**, so the
   `vendor/` folder is complete.
2. Zip the whole project **including `vendor/` and `public/build/`**, but
   **excluding** `node_modules/`, `.git/`, `.env` and `storage/logs/*`.
3. Upload the zip to `~/domains/wassilli.com/` and extract it into `app/`.

`public/build/` is committed to the repo precisely because shared hosting has no
Node to compile assets. Without it the site loads with no styling at all.

---

## 3. Configure the environment

```bash
cp .env.production.example .env
php artisan key:generate
```

Then edit `.env` and fill in:

- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` from step 1
- `WASSILI_CALL_CENTER_NUMBER` — your real WhatsApp number, digits only
- confirm `APP_URL=https://wassilli.com`, `APP_ENV=production`, `APP_DEBUG=false`

Without SSH, use hPanel's File Manager to create and edit `.env` directly.

---

## 4. Point the domain at `public/`

Laravel must serve from `public/`, never the project root — otherwise `.env`,
`vendor/` and the whole codebase are downloadable over the web.

**Preferred:** hPanel → **Websites → Domains → wassilli.com**, set the document
root to `domains/wassilli.com/app/public`.

**If your plan won't let you change the document root:** put the contents of
`app/public/` into `public_html/`, then edit `public_html/index.php` so the two
require paths point one level up at the app folder:

```php
require __DIR__.'/../app/vendor/autoload.php';
$app = require_once __DIR__.'/../app/bootstrap/app.php';
```

---

## 5. Migrate, seed and cache

```bash
php artisan migrate --force
php artisan db:seed --force        # sample data — SKIP if you'll enter real stores
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`storage:link` sometimes fails on shared hosting. If it does, create the symlink
by hand, or in File Manager copy `storage/app/public` to `public/storage`.

Re-run the three `:cache` commands after **any** future `.env` change — cached
config ignores edits until you do.

---

## 6. Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

If uploads or logs fail with "permission denied", this is almost always why.

---

## 7. Enable HTTPS

hPanel → **Security → SSL** and install the free Let's Encrypt certificate for
wassilli.com, then turn on **Force HTTPS**. `SESSION_SECURE_COOKIE=true` in the
production env assumes HTTPS is live — set it up before you log in to `/admin`.

---

## 8. First-run checklist

Visit `https://wassilli.com/admin` and sign in with **admin / password**, then
immediately:

1. **Change the password** — user menu → Profile. The seeded one is public
   knowledge; leaving it is the single biggest risk on a live site.
2. **Settings → call-centre number** — confirm it's your real WhatsApp number.
   Every customer order is sent there; a wrong number means orders vanish.
3. **Settings → LBP rate** — set today's rate.
4. **Vendors → Opening Hours** — no vendor has a schedule yet, so every store
   currently shows as open 24/7.
5. If you seeded the sample data, delete the demo stores and products before
   going public.

Then place one real test order end-to-end and confirm the WhatsApp message
arrives on the call-centre phone.

---

## Updating later

```bash
cd ~/domains/wassilli.com/app
git pull origin master
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Because `public/build/` ships in the repo, styling updates arrive with the pull —
nothing needs compiling on the server.

---

## Troubleshooting

| Symptom | Cause |
|---|---|
| Blank white page | `APP_DEBUG=false` hiding an error — read `storage/logs/laravel.log` |
| "500 Server Error" right after deploy | Usually missing `intl`/`zip`, or `storage/` not writable |
| Site loads but looks unstyled | `public/build/` missing, or `APP_URL` doesn't match the real domain |
| Admin login returns 405 | App is being served from a subfolder instead of the domain root |
| `.env` changes have no effect | Config is cached — re-run `php artisan config:cache` |
| Images upload but never display | `storage:link` didn't run |
