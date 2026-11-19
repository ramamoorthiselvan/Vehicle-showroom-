# Markham Infiniti – Vehicle Showroom

Responsive homepage built from the supplied design. Only the Model Showroom section comes from the database.

**Tech:** Laravel, MySQL, Bootstrap 5, jQuery

## Features

- Responsive homepage (header, banner, quick links, model showroom, footer)
- Model Showroom loaded from MySQL (active models only, sorted by display order)
- Admin panel: login, list, add, edit, delete, image upload, active/inactive, display order
- REST API for model showrooms (optional)

## Setup

1. Install packages: `composer install`
2. Copy `.env.example` to `.env`, then run `php artisan key:generate`
3. Create a MySQL database named `vehicle_showroom` and check the DB settings in `.env`
4. Create tables and sample data: `php artisan migrate --seed`
5. Link storage for uploaded images: `php artisan storage:link`
6. Start the site: `php artisan serve` and open http://127.0.0.1:8000


## Admin login

- URL: `/admin/login`
- Email: `admin@markham.test`
- Password: `Admin@12345`

## API

| Method | URL | Login needed |
|--------|-----|--------------|
| POST | `/api/login` | No |
| GET | `/api/model-showrooms` | No |
| GET | `/api/model-showrooms/{id}` | No |
| POST | `/api/model-showrooms` | Yes |
| PUT | `/api/model-showrooms/{id}` | Yes |
| DELETE | `/api/model-showrooms/{id}` | Yes |

Login returns a token. Send it as `Authorization: Bearer <token>` and `Accept: application/json`.
To update with an image, send a POST with `_method=PUT`.


## Notes

- Sample vehicle images are in `public/assets/images/sample-vehicles/`.
- Q50 Hybrid and QX80 (inactive) are extra sample rows; delete them if not needed.
- Menu links point to `#` because no other pages were requested.
- Dealership hours are placeholder text.
