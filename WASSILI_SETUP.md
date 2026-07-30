# Wassili (وصّلي) — Setup Guide

A Multi-Vendor "Direct Dispatch via WhatsApp" delivery app.
**Laravel 11 · MySQL · Blade + Tailwind + Alpine.js · FilamentPHP v3**

> The custom source files (migrations, models, Filament resources, Alpine cart,
> tracking page, controllers, routes, translations) are **already written** into
> this folder at their final Laravel paths. You just need to scaffold the base
> framework around them and merge.

---

## 0. Prerequisites (already verified on this machine)

- PHP **8.5** (XAMPP) — `D:\xampp\php\php.exe`
- Node **v24** + npm
- MySQL running via XAMPP on **port 3306**
- **Composer is NOT installed** — install it first (step 1).

---

## 1. Install Composer (Windows)

Download & run the official installer: <https://getcomposer.org/Composer-Setup.exe>
(point it at `D:\xampp\php\php.exe`). Then reopen the terminal and verify:

```powershell
composer --version
```

---

## 2. Scaffold Laravel 11 into this folder

`composer create-project` needs an empty target, and this folder already holds
our custom files + `.claude`. So scaffold into a temp dir, then copy the
framework skeleton in **without** overwriting our files:

```powershell
# From D:\xampp\htdocs
composer create-project laravel/laravel:^11.0 Wassili_base

# Copy the framework skeleton into Wassili, keeping OUR files on conflict.
robocopy .\Wassili_base .\Wassili /E /XC /XN /XO
#   /XC /XN /XO  => never overwrite existing (our) files
Remove-Item -Recurse -Force .\Wassili_base
```

Our `bootstrap/app.php`, `routes/web.php`, `database/seeders/DatabaseSeeder.php`,
`tailwind.config.js`, `vite.config.js`, `postcss.config.js` intentionally
replace the stock ones — if robocopy kept the stock versions, delete those and
keep ours (they're the files listed in this repo).

---

## 3. Environment

```powershell
Copy-Item .env.example .env      # if not already present
# Merge in the values from .env.wassili.example (DB + APP_NAME + WASSILI_* keys)
php artisan key:generate
```

Create the database (XAMPP MySQL, empty root password):

```powershell
& "D:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE wassili CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Set the important keys in `.env` (see `.env.wassili.example`):

```env
APP_NAME="Wassili"
APP_LOCALE=ar
APP_FALLBACK_LOCALE=en
DB_DATABASE=wassili
DB_USERNAME=root
DB_PASSWORD=
WASSILI_CALL_CENTER_NUMBER=9665XXXXXXXX
WASSILI_BASE_DELIVERY_FEE=15
WASSILI_MULTI_VENDOR_FEE=8
```

---

## 4. Install FilamentPHP v3 + panel

```powershell
composer require filament/filament:"^3.2" -W
php artisan filament:install --panels
```

When asked for the panel ID, use **admin**. This generates
`app/Providers/Filament/AdminPanelProvider.php` — **overwrite it with ours**
(it already wires branding, dark mode, and the SetLocale middleware).

Create your admin user (or rely on the seeder in step 6):

```powershell
php artisan make:filament-user
```

### Arabic + RTL for the Filament panel
Publish community Arabic translations so the panel flips to RTL under `ar`:

```powershell
composer require filament/support
php artisan vendor:publish --tag=filament-panels-translations
# Arabic pack (adds ar/*.php with direction=rtl):
composer require --dev laravellux/filament-arabic-translations 2>$null; # optional helper
```

> Filament auto-derives `dir="rtl"` from the active locale's translation files.
> Our `SetLocale` middleware sets `app()->setLocale('ar')`, so once Arabic
> translations exist the whole panel renders RTL. Dark/Light is built into
> Filament (toggle in the user menu) and enabled via `->darkMode(true)`.

---

## 5. Frontend dependencies (Alpine + Tailwind)

Laravel 11 ships Tailwind + Vite. Add Alpine:

```powershell
npm install
npm install alpinejs axios
npm run build      # one-shot production build — no daemon needed on Hostinger
# (use `npm run dev` only during local development)
```

---

## 6. Migrate + seed

```powershell
php artisan migrate
php artisan db:seed          # sample categories, vendors, universal items, drivers, admin
php artisan storage:link     # so uploaded product/vendor images resolve
```

Seeder admin login → **admin@wassili.test / password**

---

## 7. Run it

```powershell
php artisan serve
```

- Storefront: <http://localhost:8000>
- Control Center: <http://localhost:8000/admin>
- Tracking: `http://localhost:8000/track/{tracking_number}`

Admin login: **username `admin` / password `password`** (change it before going live).

---

## Sharing publicly with ngrok

The app must be served at the **domain root**, not a subfolder. Filament's admin
runs on Livewire, and Livewire loads `/livewire/livewire.js` as a root-absolute
path — under a subfolder like `…/Wassili/public/admin` that JS 404s, the login
form falls back to a native POST, and you get **"The POST method is not supported
for route admin/login" (405)**. Serving at root fixes it.

### Recommended: `php artisan serve` + ngrok

1. **Terminal 1** — start the app at the root (keep it running). Keep XAMPP
   **MySQL** on for the database; Apache is not needed for this.
   ```
   php artisan serve
   ```
2. **Terminal 2** — point ngrok at port **8000** (not 80):
   ```
   ngrok http --url=https://YOUR-DOMAIN.ngrok-free.dev 8000
   ```
   (older ngrok: `ngrok http --domain=YOUR-DOMAIN.ngrok-free.dev 8000`).
   The `Forwarding` line should read `… -> http://localhost:8000`.
3. Set `APP_URL` to the ngrok root (no `/Wassili/public`) and clear config:
   ```
   APP_URL=https://YOUR-DOMAIN.ngrok-free.dev
   ```
   ```
   php artisan config:clear
   ```
4. Open **`https://YOUR-DOMAIN.ngrok-free.dev/admin/login`** — note there is **no
   `/Wassili/public`** in the URL. Hard-refresh (Ctrl+F5) and click through
   ngrok's one-time warning page.

`bootstrap/app.php` already calls `trustProxies(at: '*')`, so Laravel reads
ngrok's `X-Forwarded-Proto: https` and keeps Livewire/asset URLs on HTTPS
(no mixed-content). Don't run `npm run dev` while sharing over ngrok — the Vite
dev server isn't reachable remotely; use the built assets (`npm run build`).

> Free ngrok domains can change on restart. If yours changes, update `APP_URL`
> to the new domain and run `php artisan config:clear` again.

### Alternative: Apache VirtualHost (keep `ngrok http 80`)

If you'd rather keep ngrok pointed at Apache's port 80, serve the app at root via
a name-based VirtualHost so the ngrok domain maps to `public/` while `localhost`
still serves your other XAMPP sites. In
`D:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot "D:/xampp/htdocs"
</VirtualHost>

<VirtualHost *:80>
    ServerName YOUR-DOMAIN.ngrok-free.dev
    DocumentRoot "D:/xampp/htdocs/Wassili/public"
    <Directory "D:/xampp/htdocs/Wassili/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Then restart Apache from the XAMPP Control Panel and open
`https://YOUR-DOMAIN.ngrok-free.dev/admin` (root, no subfolder).

---

## File map (what was generated for you)

| Concern | Path |
|---|---|
| Env template | `.env.wassili.example` |
| Business config | `config/wassili.php` |
| Locale middleware | `app/Http/Middleware/SetLocale.php` |
| Middleware registration | `bootstrap/app.php` |
| Migrations | `database/migrations/2024_01_01_0000*` |
| Models | `app/Models/{Category,Vendor,Product,Driver,Order}.php` |
| Filament panel | `app/Providers/Filament/AdminPanelProvider.php` |
| Filament resources | `app/Filament/Resources/*` |
| WhatsApp formatter (driver) | `app/Support/WhatsappFormatter.php` |
| Alpine cart + WhatsApp (customer) | `resources/js/cart.js` |
| Layout (RTL + dark + switcher) | `resources/views/layouts/app.blade.php` |
| Cart drawer + checkout | `resources/views/partials/cart-drawer.blade.php` |
| Storefront | `resources/views/storefront/index.blade.php` |
| Tracking page | `resources/views/track/show.blade.php` |
| Controllers | `app/Http/Controllers/{Storefront,Order,Tracking}Controller.php` |
| Routes | `routes/web.php` |
| Translations | `lang/{ar,en}/wassili.php`, `lang/ar.json` |
| Seeder | `database/seeders/DatabaseSeeder.php` |

---

## Deploying to Hostinger (shared hosting)

1. Run `npm run build` locally and commit `public/build`.
2. Upload the project; point the domain's document root to `/public`.
3. Set the production `.env` (real DB creds, `APP_ENV=production`, `APP_DEBUG=false`,
   real `WASSILI_CALL_CENTER_NUMBER`).
4. `php artisan migrate --force && php artisan config:cache && php artisan route:cache`.
5. No queue workers / websockets required — dispatch is synchronous WhatsApp
   links and tracking polls via a lightweight page refresh.
