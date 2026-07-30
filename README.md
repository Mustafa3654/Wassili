# Wassili (وصّلي)

A multi-vendor delivery platform built around a **"Direct Dispatch via WhatsApp"**
model — no native apps, no routing engines, no delivery-driver app to maintain. A
customer builds a cart in the browser, checkout fires a pre-filled WhatsApp message
to the call centre and saves the order, and an operator assigns a driver from a
Filament dashboard with one click that opens WhatsApp again — this time addressed
to the driver with the full order. Everything the customer sees is bilingual
(Arabic/English, RTL/LTR) and priced in both **USD and LBP**.

Designed to run comfortably on shared hosting (Hostinger): no queue daemons and no
websockets are required for the core flow.

## Flow

```
Customer browses the storefront  (ar/en · light/dark · USD + LBP)
        │
        ▼
Builds a client-side cart  ───►  optional free-text "custom request" for unlisted goods
 (items from many vendors + a shared universal catalog)
        │
        ▼
Checkout  ───►  order saved as "pending" with a unique tracking number
        │                 │
        │                 └───►  WhatsApp opens, pre-filled, to the CALL CENTRE
        ▼
Control Center shows it live (15s poll)  ───►  "Assign to Driver"
        │
        ▼
WhatsApp dispatch to the DRIVER  ───►  status: in_progress → delivered
        │
        ▼
Customer follows  /track/{code}   (live status page, no login)
```

## Requirements

| Layer     | Choice                               |
|-----------|--------------------------------------|
| Framework | Laravel 11                           |
| Language  | PHP 8.2+ (tested on 8.5)             |
| Database  | MySQL 5.7+ / 8                       |
| Admin     | Filament v3                          |
| Frontend  | Blade · Tailwind CSS · Alpine.js     |
| Bundler   | Vite                                 |
| Dispatch  | WhatsApp click-to-chat (`wa.me`)     |

PHP extensions: `intl` and `zip` must be enabled (both ship with XAMPP — uncomment
them in `php.ini`). Filament requires them.

## Setup

```bash
# 1. Dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate
```

Set your database + business values in `.env` (see **Configuration** below), then:

```bash
# 3. Database (create it first)
mysql -u root -e "CREATE DATABASE wassili CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed

# 4. Assets + storage
php artisan storage:link
npm run build            # or `npm run dev` while developing

# 5. Serve
php artisan serve
```

- Storefront — <http://localhost:8000>
- Control Center — <http://localhost:8000/admin>
- Order tracking — `http://localhost:8000/track/{tracking_number}`

## Seeded accounts

The admin panel uses **username** login (no email).

| Panel          | Username | Password   |
|----------------|----------|------------|
| Control Center | `admin`  | `password` |

Change it before going live:

```bash
php artisan tinker --execute="\$u=App\Models\User::where('username','admin')->first(); \$u->password=bcrypt('NewStrongPass'); \$u->save();"
```

The seeder also creates sample categories (Restaurants, Supermarket, Cleaning
Supplies), vendors, universal-catalog items and drivers so the storefront is
populated immediately.

## Configuration

All business rules live in `.env` and are read through `config/wassili.php`, so they
can differ per environment without touching code.

```dotenv
# WhatsApp call-centre number — full international format, digits only (no +, no 00)
WASSILI_CALL_CENTER_NUMBER=9611234567

# Delivery fees, in USD (the base currency)
WASSILI_BASE_DELIVERY_FEE=2      # single-vendor order
WASSILI_MULTI_VENDOR_FEE=1       # added per EXTRA distinct pickup point

# Lebanese Pounds per 1 USD — drives the dual USD/LBP price display
WASSILI_LBP_RATE=89000
```

After changing any of these on a cached environment, run `php artisan config:clear`.

### Currency

Prices are stored and entered in **USD**; every displayed amount is rendered next to
its **LBP** equivalent (`$5.00 · 445,000 LL`) using `WASSILI_LBP_RATE`. The rate is
also editable at runtime from **Control Center → Settings**, so you don't have to
redeploy when the market moves — a stored setting overrides the `.env` default. All
formatting goes through a single `App\Support\Money` helper, so the storefront, cart,
tracking page, admin tables and the WhatsApp messages never drift apart.

### Localisation

`app/Http/Middleware/SetLocale.php` resolves the active locale from `?lang=ar|en`,
then the session, then `APP_LOCALE`. Arabic renders `dir="rtl"`, English `dir="ltr"`.
UI strings live in `lang/{ar,en}/wassili.php` (admin) and `lang/ar.json` (storefront);
content models carry `name_ar` columns for bilingual data.

## How it works

### The client-side cart

The cart is pure Alpine.js persisted to `localStorage` (`resources/js/cart.js`) —
there is zero session/database overhead while the customer browses. It supports
items from multiple vendors at once, items from the **universal catalog**, and
free-text **custom requests** for goods that aren't listed. A closed vendor's items
can't be added.

### Delivery fee

The fee is `base + (distinct_pickups − 1) × multi_vendor_fee`. A "pickup" is a
distinct vendor; every universal/custom (vendor-less) item collapses into one shared
"loose" pickup group. The figure is computed client-side for instant feedback **and
recomputed on the server at checkout** — prices are re-read from the database so a
tampered payload can never change what is charged (`App\Http\Controllers\OrderController`).

### Vendor-specific vs. universal catalog

A product with a `vendor_id` belongs to one store (e.g. a restaurant menu item). A
product with `vendor_id = NULL` is a **universal catalog** item (supermarket /
pharmacy staples) that a driver can buy from any nearby store.

### Opening hours

Each vendor stores a weekly `opening_hours` schedule (JSON). `Vendor::is_open` is a
computed accessor that checks the current day and time, so availability flips
automatically without anyone toggling a switch. Closed stores are visibly disabled on
the storefront.

### WhatsApp dispatch engine

Two messages, both human-readable and bilingual:

- **Customer → call centre**, built in the browser at checkout and opened with
  `window.open(wa.me/…)`. The order is saved via AJAX at the same time and the cart
  is cleared.
- **Call centre → driver**, built server-side by `App\Support\WhatsappFormatter`
  when an operator uses *Assign to Driver*. It contains the customer, address,
  itemised order and totals in USD + LBP.

### Order tracking

`/track/{tracking_number}` is a public, no-login page with a visual progress bar
(received → on the way → delivered) that respects the active locale and light/dark
theme. It refreshes itself periodically — no websockets.

## Control Center (admin)

Filament v3 at `/admin`, RTL-aware and with a built-in light/dark toggle.

| Resource / Page | What it does                                                                 |
|-----------------|------------------------------------------------------------------------------|
| Orders          | Live table (15s poll), colour-coded status badges, quick status actions, and **Assign to Driver** → WhatsApp dispatch. Pending count shown as a sidebar badge. |
| Vendors         | CRUD + weekly opening-hours editor + live open/closed badge. Import/Export.   |
| Products        | CRUD, vendor-specific or universal, USD pricing (LBP shown). Import/Export.   |
| Categories      | CRUD with parent/sub-categories, drag-to-reorder. Import/Export.              |
| Drivers         | CRUD, vehicle type + availability status. Import/Export.                      |
| Settings        | Edit the LBP rate, delivery fees and call-centre number without redeploying.  |
| Dashboard       | `StatsOverview` + `OrdersChart` widgets.                                      |

Every resource supports CSV **import and export** via Filament actions.

## Design

- **Bilingual, RTL-first.** Layout direction and fonts switch with the locale; the
  navbar carries a one-tap language switcher.
- **Dark / light.** Toggled with Alpine and persisted in `localStorage`, applied
  before first paint to avoid a flash.
- **Dual currency everywhere.** One formatter, one source of truth for the rate.
- **Lebanon defaults.** Phone fields are prefixed `+961`; the base currency is USD
  with LBP alongside.

## Running publicly (ngrok)

The app must be served at the **domain root**, not a subfolder — Filament/Livewire
load assets from root-absolute paths, so a subfolder like `…/public/admin` breaks the
login (a native POST to `/admin/login` → 405). Serve at the root and it works:

```bash
php artisan serve                 # terminal 1  (keep MySQL running)
ngrok http --url=https://YOUR-DOMAIN.ngrok-free.dev 8000   # terminal 2
```

Set `APP_URL` to the ngrok root (no subfolder) and `php artisan config:clear`.
`bootstrap/app.php` already trusts proxies, so HTTPS is detected via `X-Forwarded-*`.
See `WASSILI_SETUP.md` for the full walkthrough, including the Apache VirtualHost
alternative.

## Deploying to shared hosting (Hostinger)

1. `npm run build` locally and upload `public/build`.
2. Point the domain's document root at `/public`.
3. Production `.env`: real DB credentials, `APP_ENV=production`, `APP_DEBUG=false`,
   real `WASSILI_CALL_CENTER_NUMBER`.
4. `php artisan migrate --force && php artisan config:cache && php artisan route:cache`.

No queue workers or websockets are needed for the core dispatch/tracking flow. (CSV
imports/exports use Laravel's queue; with `QUEUE_CONNECTION=sync` they run inline,
which is fine for shared hosting.)

## Notes

- `intl` and `zip` PHP extensions are required by Filament — enable them in `php.ini`.
- `composer.phar` and `.claude/` are intentionally git-ignored; install Composer
  globally rather than committing the binary.
- The default admin password is `password` — change it before any public exposure.
