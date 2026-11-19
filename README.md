# Markham Infiniti – Homepage + Model Showroom Admin

Responsive conversion of the supplied homepage design. Only the **Model Showroom**
section is database-driven; everything else is static, as requested.

**Stack:** Laravel 13 · MySQL · Bootstrap 5 · jQuery · Laravel Sanctum (API tokens)

## What is included

| Area | Details |
|------|---------|
| Homepage | Header + navigation, hero banner, welcome text, 4 quick-link circles, Model Showroom slider, footer quick links. Desktop, tablet and mobile layouts (collapsing menu, 4 / 3 / 2 / 1 cards per slide). |
| Model Showroom | Read from the `model_showrooms` table – **active** rows only, sorted by **display order**. Slider arrows + touch swipe. |
| Admin panel | Login, model list, add, edit, delete (with confirm dialog), image upload with live preview, active/inactive switch, display order. |
| REST API (optional) | CRUD for model showrooms, token-protected writes. |
| Tests | 16 feature tests (homepage, auth, CRUD, validation, image handling, API). |

## Setup

Requirements: PHP 8.3+, Composer, MySQL 8, `pdo_mysql`, `fileinfo`, `gd` extensions
(all present in Laragon / XAMPP).

```bash
# 1. install dependencies
composer install

# 2. environment
cp .env.example .env
php artisan key:generate
#    then edit .env if your MySQL user/password differ (default: root, no password)

# 3. create an empty database
mysql -uroot -e "CREATE DATABASE vehicle_showroom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 4. tables + sample data (admin user + 6 sample vehicles with images)
php artisan migrate --seed

# 5. make uploaded images publicly reachable (public/storage -> storage/app/public)
php artisan storage:link

# 6. run
php artisan serve        # http://127.0.0.1:8000
```

With Laragon you can also just open `http://vehicle-showroom.test` (set `APP_URL` accordingly).
No `npm install` is needed – Bootstrap, jQuery and Google Fonts load from CDNs, so an internet
connection is required when viewing the site.

### Alternative: import the SQL dump

`database/vehicle_showroom.sql` contains the full schema and the sample data.
If you import it instead of running `migrate --seed`, also copy the four pictures in
`database/seeders/sample-images/` into `storage/app/public/models/` as `seed-q50.png`,
`seed-qx70.png`, `seed-qx50.png`, `seed-qx60.png` (rows 5 and 6 reuse `seed-q50-hybrid.png` /
`seed-qx80.png`) – or simply run the seeder, which does this for you.

## Admin login

| URL | `/admin/login` |
|-----|----------------|
| Email | `admin@markham.test` |
| Password | `Admin@12345` |

Change this password before deploying anywhere public.

## Sample data

Taken from the design:

| Model | Year | Price |
|-------|------|-------|
| Q50  | 2014 | $37,500 |
| QX70 | 2014 | $53,350 |
| QX50 | 2014 | $37,900 |
| QX60 | 2014 | $43,000 |

Two extra rows (**Q50 Hybrid** – active, **QX80** – *inactive*) are added only so the slider
arrows and the active/inactive filter can be demonstrated. Delete them in the admin if not wanted.

### Table `model_showrooms`

| Column | Type | Notes |
|--------|------|-------|
| `id` | bigint, PK | |
| `model_name` | varchar(100) | e.g. `Q50` |
| `year` | smallint unsigned | |
| `price` | decimal(10,2) | shown as `Starting at $37,500` |
| `image` | varchar(255) null | path inside `storage/app/public` |
| `status` | tinyint(1) | 1 = active, 0 = inactive |
| `display_order` | int unsigned | lowest first |
| `created_at`, `updated_at` | timestamps | |

## REST API

Base URL: `/api`. Reads are public; writes need a bearer token.

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/login` | – | `email`, `password` → `{ "token": "..." }` |
| GET | `/api/model-showrooms` | – | Active models, ordered |
| GET | `/api/model-showrooms/{id}` | – | One active model |
| POST | `/api/model-showrooms` | Bearer | Create (multipart: `model_name`, `year`, `price`, `image`, optional `status`, `display_order`) |
| PUT/PATCH | `/api/model-showrooms/{id}` | Bearer | Update – send only the fields to change |
| DELETE | `/api/model-showrooms/{id}` | Bearer | Delete (also removes the image) |
| POST | `/api/logout` | Bearer | Revoke the current token |

Example:

```bash
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" \
  -d "email=admin@markham.test" -d "password=Admin@12345" | php -r 'echo json_decode(stream_get_contents(STDIN))->token;')

# create
curl -X POST http://127.0.0.1:8000/api/model-showrooms \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" \
  -F model_name=QX30 -F year=2016 -F price=39900 -F image=@car.png

# update (PHP cannot read multipart bodies on a real PUT, so use POST + _method)
curl -X POST http://127.0.0.1:8000/api/model-showrooms/7 \
  -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" \
  -F _method=PUT -F price=41000
# (a plain JSON PUT without an image works as well)
```

## Project structure (the parts that matter)

```
app/
  Models/ModelShowroom.php                 scopes (active/ordered), price + image accessors
  Services/ShowroomImage.php               store / replace / delete uploaded pictures
  Http/
    Controllers/HomeController.php         homepage
    Controllers/Admin/AuthController.php   admin login/logout
    Controllers/Admin/ModelShowroomController.php   admin CRUD
    Controllers/Api/…                      REST API
    Requests/ModelShowroomRequest.php      validation shared by admin + API
    Resources/ModelShowroomResource.php    API JSON shape
database/
  migrations/…create_model_showrooms_table.php
  seeders/                                 admin user + sample vehicles (+ sample-images/)
  vehicle_showroom.sql                     ready-made dump
resources/views/
  layouts/app.blade.php, partials/         public layout, header, footer
  home.blade.php                           homepage
  admin/…                                  login, list, create/edit form
public/assets/{css,js,images}              style.css, main.js, admin.css, design assets
tests/Feature/ModelShowroomTest.php
```

## Running the tests

```bash
php artisan test
```

(Uses an in-memory SQLite database, so your MySQL data is not touched.)

## Notes

* Navigation items, quick-link circles, social icons and footer links have no destination
  pages in this task, so they point to `#`.
* The hero slider dots are decorative – only one banner image was supplied.
* "Dealership Hours" content is placeholder text; no hours were given in the design.
* The "powered by e-DEALER.CA" mark is recreated as text because the logo file was not in the assets.
