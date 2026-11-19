# Build It Yourself – Step-by-Step Guide

Goal: you type every file yourself, understand why it exists, and can explain it in the interview.

## How to use this guide

1. Create a **fresh** project and build there. Keep this finished project open as a *reference to compare against* (not to copy-paste).
   ```bash
   composer create-project laravel/laravel showroom-mine
   cd showroom-mine
   ```
2. Work in the order below. Each phase ends with **Test it** – do not move on until it works.
3. After each phase, explain it out loud in your own words (that is interview practice).
4. Commit after each phase: `git init`, then `git add . && git commit -m "phase 1: database"`.

Laragon note: PHP is not on PATH by default. Use *Laragon → Terminal*, or run
`$env:Path="D:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64;"+$env:Path` in PowerShell.

---------------------------------------------------------------------------

## Phase 0 – Project setup

**Edit `.env`** (Laravel reads settings from here):
```
APP_NAME="Markham Infiniti"
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vehicle_showroom
DB_USERNAME=root
DB_PASSWORD=
```
**Why:** the app must talk to MySQL, not the default SQLite.

**Create the database** (start MySQL in Laragon first):
```bash
mysql -uroot -e "CREATE DATABASE vehicle_showroom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
php artisan migrate        # creates users/cache/jobs tables
php artisan serve          # http://127.0.0.1:8000 shows the Laravel welcome page
```

**Copy the design images:** put the ZIP's images into `public/assets/images/` (lowercase file names).
**Why `public/`:** files there are served directly by the web server, no Laravel code needed.

**Test it:** the welcome page opens; `php artisan migrate:status` shows "Ran".

**If it fails:**
| Error | Cause / fix |
|---|---|
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL is not running. Start it in Laragon. |
| `Unknown database` | You skipped `CREATE DATABASE`. |
| `php is not recognized` | PHP not on PATH – use the Laragon terminal. |

---------------------------------------------------------------------------

## Phase 1 – Database: migration, model, seeder

### 1.1 Migration
```bash
php artisan make:migration create_model_showrooms_table
```
**Edit** `database/migrations/xxxx_create_model_showrooms_table.php`, inside `up()`:
```php
Schema::create('model_showrooms', function (Blueprint $table) {
    $table->id();
    $table->string('model_name', 100);
    $table->unsignedSmallInteger('year');
    $table->decimal('price', 10, 2);
    $table->string('image')->nullable();
    $table->boolean('status')->default(true);        // 1 active, 0 inactive
    $table->unsignedInteger('display_order')->default(0);
    $table->timestamps();                            // created_at / updated_at

    $table->index(['status', 'display_order']);
});
```
**Why each choice**
- `decimal` for money – `float` gives rounding errors.
- `image` stores only the *path*; the file lives on disk. Storing images inside the DB makes it slow.
- `boolean` status is simpler than a string like "active".
- `timestamps()` fills *Created Date / Updated Date* automatically.
- `index` – the homepage always filters by `status` and sorts by `display_order`.

Run: `php artisan migrate`

### 1.2 Model
```bash
php artisan make:model ModelShowroom
```
**Edit** `app/Models/ModelShowroom.php`:
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModelShowroom extends Model
{
    // Mass-assignment whitelist: only these fields can be filled via create()/update()
    protected $fillable = ['model_name', 'year', 'price', 'image', 'status', 'display_order'];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'decimal:2',
            'status' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    // Query scope: ModelShowroom::active()
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    // ModelShowroom::ordered()
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('id');
    }

    // Accessor: $model->title  -> "2014 Q50"
    public function getTitleAttribute(): string
    {
        return "{$this->year} {$this->model_name}";
    }

    // $model->formatted_price -> "$37,500"
    public function getFormattedPriceAttribute(): string
    {
        return '$'.number_format((float) $this->price, 0);
    }

    // $model->image_url -> http://host/storage/models/abc.png
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }
}
```
**Why**
- `$fillable` protects against *mass assignment* attacks (a user sending `is_admin=1`).
- **Scopes** keep query rules in one place, so the homepage and the API both use `active()->ordered()` and can never disagree.
- **Accessors** keep formatting out of the Blade views.
- `asset()` builds the URL from the current host, so it works on `localhost:8000` and on `vehicle-showroom.test`.

### 1.3 Seeder (sample data)
```bash
php artisan make:seeder ModelShowroomSeeder
```
Put 4 car images (from the ZIP: `car_01..car_04`) in `database/seeders/sample-images/` as `q50.png, qx70.png, qx50.png, qx60.png`.

**Edit** `database/seeders/ModelShowroomSeeder.php`:
```php
public function run(): void
{
    $models = [
        ['model_name' => 'Q50',  'year' => 2014, 'price' => 37500, 'file' => 'q50.png',  'display_order' => 1],
        ['model_name' => 'QX70', 'year' => 2014, 'price' => 53350, 'file' => 'qx70.png', 'display_order' => 2],
        ['model_name' => 'QX50', 'year' => 2014, 'price' => 37900, 'file' => 'qx50.png', 'display_order' => 3],
        ['model_name' => 'QX60', 'year' => 2014, 'price' => 43000, 'file' => 'qx60.png', 'display_order' => 4],
    ];

    foreach ($models as $row) {
        // copy the sample picture into storage/app/public/models
        $path = 'models/seed-'.strtolower($row['model_name']).'.png';
        Storage::disk('public')->put($path, File::get(database_path('seeders/sample-images/'.$row['file'])));

        ModelShowroom::updateOrCreate(
            ['model_name' => $row['model_name'], 'year' => $row['year']],   // find by this
            [
                'price' => $row['price'],
                'image' => $path,
                'status' => true,
                'display_order' => $row['display_order'],
            ]                                                                // else create/update with this
        );
    }
}
```
Imports at the top: `App\Models\ModelShowroom`, `Illuminate\Support\Facades\File`, `Illuminate\Support\Facades\Storage`.

**Why `updateOrCreate`:** you can run the seeder many times without duplicates.

Register it in `database/seeders/DatabaseSeeder.php`:
```php
public function run(): void
{
    $this->call([AdminUserSeeder::class, ModelShowroomSeeder::class]);
}
```
Create the admin seeder:
```bash
php artisan make:seeder AdminUserSeeder
```
```php
public function run(): void
{
    // The User model's "hashed" cast hashes the password for us.
    User::updateOrCreate(
        ['email' => 'admin@markham.test'],
        ['name' => 'Administrator', 'password' => 'Admin@12345']
    );
}
```
Then:
```bash
php artisan storage:link        # public/storage -> storage/app/public
php artisan migrate --seed
```
**Why `storage:link`:** uploads are saved in `storage/` (private by default). The symlink makes them reachable at `/storage/...`.

**Test it** – use Tinker (a live PHP console for your app):
```bash
php artisan tinker
>>> App\Models\ModelShowroom::active()->ordered()->pluck('model_name')
>>> App\Models\ModelShowroom::first()->title
>>> App\Models\ModelShowroom::first()->formatted_price
```
Expect `Q50, QX70, QX50, QX60`, `2014 Q50`, `$37,500`.

**If it fails**
- `Class "...\Storage" not found` → missing `use` import at the top.
- `Add [x] to fillable property` → field missing from `$fillable`.
- `Base table or view already exists` → run `php artisan migrate:fresh --seed` (drops everything, rebuilds).

---------------------------------------------------------------------------

## Phase 2 – Homepage wiring (data first, pretty later)

### 2.1 Controller
```bash
php artisan make:controller HomeController
```
```php
class HomeController extends Controller
{
    public function index()
    {
        $showroomModels = ModelShowroom::active()->ordered()->get();

        return view('home', compact('showroomModels'));
    }
}
```
### 2.2 Route – `routes/web.php`
```php
Route::get('/', [HomeController::class, 'index'])->name('home');
```
### 2.3 Temporary view – `resources/views/home.blade.php`
```blade
@foreach ($showroomModels as $model)
    <p>{{ $model->title }} – Starting at {{ $model->formatted_price }}</p>
@endforeach
```
**Why start this ugly:** you prove *database → controller → view* works before spending hours on CSS. This is how real developers work: logic first, styling after.

**Test it:** open `/` – you see 4 lines. In phpMyAdmin/HeidiSQL set one row `status = 0`, refresh – it disappears.

**Debug tools**
- `dd($showroomModels);` in the controller – dumps and stops.
- `storage/logs/laravel.log` – the real error message.
- `php artisan route:list` – is the route registered?
- `php artisan view:clear` / `optimize:clear` – clears stale caches when changes do not appear.

---------------------------------------------------------------------------

## Phase 3 – Page layout (Blade) + header and footer

Blade layouts avoid repeating `<head>`, header and footer on every page.

### 3.1 `resources/views/layouts/app.blade.php`
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">   {{-- required for responsive --}}
    <title>@yield('title', 'Markham Infiniti – Inspired Performance')</title>

    <link href="https://fonts.googleapis.com/css2?family=Russo+One&family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
</head>
<body>
    @include('partials.header')
    <main>@yield('content')</main>
    @include('partials.footer')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
</body>
</html>
```
**Why:** CSS in `<head>`, scripts at the end of `<body>` so the page paints first. jQuery must load before `main.js` (which uses `$`); Bootstrap's bundle includes Popper for dropdowns.

Make `home.blade.php` use it:
```blade
@extends('layouts.app')

@section('content')
    ...page sections...
@endsection
```

### 3.2 Header – `resources/views/partials/header.blade.php`
Structure (look at the design, then write it block by block):
```
header.site-header
 └ .container.header-inner
    ├ a.brand > img logo
    ├ button.menu-toggle   (visible < 992px, opens the collapse)
    └ #headerMenu.collapse.d-lg-block
       ├ .topbar  (address, phones, hours dropdown, social icons)
       └ nav.mainnav > ul > li > a
```
Key points to type:
```blade
<button class="menu-toggle d-lg-none" type="button"
        data-bs-toggle="collapse" data-bs-target="#headerMenu"
        aria-controls="headerMenu" aria-expanded="false" aria-label="Toggle navigation">&#9776;</button>

<div class="header-menu collapse d-lg-block" id="headerMenu"> ... </div>
```
- `collapse` hides the menu on mobile, `d-lg-block` forces it visible on desktop (≥992px). No custom JS needed – Bootstrap handles the toggle through the `data-bs-*` attributes.
- Dealership Hours uses Bootstrap's `dropdown` (`data-bs-toggle="dropdown"`).
- Use `{{ route('home') }}` and `{{ asset('assets/images/logo.png') }}` – never hard-code URLs.

### 3.3 Footer – `partials/footer.blade.php`
A "Quick Links" label + `<ul>` of links, then a copyright row. Use `{{ date('Y') }}` for the year so it never goes stale.

**Test it:** page shows the header/footer; resize the browser to 500px – the hamburger button appears and toggles the menu. Press F12 → toggle device toolbar to test phone sizes.

---------------------------------------------------------------------------

## Phase 4 – Homepage sections (HTML + CSS)

Build **one section at a time** and compare with the design after each. File: `public/assets/css/style.css`.

### 4.1 CSS foundation (top of file)
```css
:root {
    --purple: #563880;       /* sampled from the design */
    --gray: #777978;
    --font-head: 'Russo One', sans-serif;
    --font-body: 'Ubuntu', sans-serif;
}
body { font-family: var(--font-body); font-weight: 300; color: #6d6d6d; overflow-x: hidden; }
a { text-decoration: none; }
@media (min-width: 1200px) { .container { max-width: 1200px; } }   /* design content is 1172px + gutters */
```
**Why variables:** change the purple in one place. **Why sample colours:** open the design in any image viewer/colour picker instead of guessing.

### 4.2 Hero
```blade
<section class="hero">
    <img src="{{ asset('assets/images/banner.png') }}" alt="2015 Infiniti QX70 – starting at $53,500">
</section>
```
```css
.hero > img { display: block; width: 100%; height: auto; max-height: 760px; object-fit: cover; }
```
The supplied banner already contains the diamond "2015 QX70" badge, so it is just an image. `width:100%; height:auto` makes it responsive.

### 4.3 Header over the hero (desktop only)
**Mobile first:** write the mobile style normally, then override for big screens inside a media query.
```css
@media (min-width: 992px) {
    .site-header { position: absolute; top: 0; left: 0; right: 0; background: transparent; }
    .brand { position: absolute; left: 12px; top: -3px; }
    .header-menu { margin-left: 195px; }          /* leave room for the logo badge */
}
```
`position:absolute` lifts the header out of the page flow so it floats over the banner, exactly like the design.

### 4.4 Section title (reused twice)
```css
.section-title {
    display: inline-block;
    padding: 0 28px 7px 4px;
    font: 400 24px/1.2 var(--font-head);
    text-transform: uppercase;
    color: var(--purple);
    border-bottom: 1px solid #c9c9c9;
}
```
Same class for "Welcome…" and "Model Showroom" = no duplicate CSS.

### 4.5 Welcome
A section with the `welocme_bg.png` background:
```css
.welcome { padding: 26px 0; background: #fafafa url('../images/welocme_bg.png') no-repeat center top / cover; }
```
Note: in CSS files, image paths are relative to the *CSS file* (`../images/` from `assets/css/`).

### 4.6 Quick links (4 circles)
HTML for one item (repeat 4×, changing icon/label):
```blade
<a href="#" class="quick-item">
    <span class="q-circle">
        <span class="q-icon">
            <img class="off" src="{{ asset('assets/images/cta_01.png') }}" alt="">        {{-- grey --}}
            <img class="on"  src="{{ asset('assets/images/new_white.png') }}" alt="">     {{-- white, shown on hover --}}
        </span>
        <span class="q-label">Shop New<br>Vehicles</span>
    </span>
</a>
```
CSS essentials:
```css
.q-circle {
    position: relative; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    width: 196px; height: 196px;
    border: 5px solid var(--gray); border-radius: 50%; background: #fff;
    transition: background-color .25s, border-color .25s;
}
.quick-item:hover .q-circle { background: var(--purple); border-color: #786099; }
.q-icon .on  { opacity: 0; position: absolute; }
.quick-item:hover .q-icon .on  { opacity: 1; }
.quick-item:hover .q-icon .off { opacity: 0; }
```
**Why two images + opacity:** swapping `src` with JS flickers; stacking both and fading with CSS is smooth and needs no JavaScript.
`border-radius: 50%` on a square box = circle.

### 4.7 Model Showroom section (the dynamic one)
```blade
<div class="showroom-band">
  @if ($showroomModels->isEmpty())
      <p class="showroom-empty">New models are coming soon.</p>
  @else
    <div class="container">
      <div class="showroom-frame">
        <button type="button" class="showroom-nav prev" id="showroomPrev" aria-label="Previous models"></button>
        <div class="showroom-viewport">
          <div class="showroom-track" id="showroomTrack">
            @foreach ($showroomModels as $model)
              <article class="model-card">
                <h3 class="model-title">{{ $model->title }}</h3>
                <div class="model-img-wrap">
                    <img src="{{ $model->image_url }}" alt="{{ $model->title }}" loading="lazy">
                </div>
                <p class="model-price">Starting at {{ $model->formatted_price }}</p>
              </article>
            @endforeach
          </div>
        </div>
        <button type="button" class="showroom-nav next" id="showroomNext" aria-label="Next models"></button>
      </div>
    </div>
  @endif
</div>
```
**Why `@if isEmpty`:** the page must not look broken if admin deactivates every model.
**Why `{{ }}` not `{!! !!}`:** `{{ }}` escapes HTML → protects against XSS. Never output admin text raw.

Slider CSS idea: a *viewport* that hides overflow, and a *track* (flex row) that we slide with `transform`.
```css
.showroom-viewport { overflow: hidden; }
.showroom-track    { display: flex; transition: transform .45s ease; }
.model-card        { flex: 0 0 calc(100% / var(--per-view, 1)); }   /* var set by JS */
```

### 4.8 Slider JavaScript – `public/assets/js/main.js`
```js
$(function () {
    var $track = $('#showroomTrack');
    var $cards = $track.children('.model-card');
    var $prev  = $('#showroomPrev');
    var $next  = $('#showroomNext');
    var index = 0;       // first visible card
    var perView = 4;     // cards visible at once

    // how many cards fit at the current width (matches Bootstrap breakpoints)
    function getPerView() {
        var w = window.innerWidth;
        if (w >= 1200) return 4;
        if (w >= 768)  return 3;
        if (w >= 576)  return 2;
        return 1;
    }

    function maxIndex() { return Math.max(0, $cards.length - perView); }

    function render() {
        index = Math.min(Math.max(index, 0), maxIndex());               // clamp 0..max
        $track.css('transform', 'translateX(' + (-index * (100 / perView)) + '%)');
        $prev.prop('disabled', index === 0);
        $next.prop('disabled', index >= maxIndex());
    }

    function layout() {
        perView = getPerView();
        $track.css('--per-view', perView);     // CSS reads this variable
        render();
    }

    $prev.on('click', function () { index--; render(); });
    $next.on('click', function () { index++; render(); });
    $(window).on('resize', layout);

    layout();
});
```
**Explain it like this:** "The track is as wide as all cards. Moving one card = translating by `100 / perView` percent of the track. I clamp the index so arrows can't scroll past the ends, and recalculate on resize."

**Test it:** add a 5th model → arrows enable; resize → card count changes; deactivate models → disappear.

**If CSS/JS changes do not show:** hard refresh `Ctrl+F5`. F12 → Console tab shows JS errors (`$ is not defined` = jQuery not loaded before main.js; a 404 in the Network tab = wrong file path).

### 4.9 Responsive checklist
Test at **1300px, 820px (tablet), 390px (phone)** with F12 device mode. Look for: horizontal scrollbar (something too wide), overlapping text, tiny tap targets.

---------------------------------------------------------------------------

## Phase 5 – Admin authentication

You can use a starter kit, but here you build it by hand to understand it.

### 5.1 Controller
```bash
php artisan make:controller Admin/AuthController
```
```php
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();   // prevents session fixation attacks

        return redirect()->intended(route('admin.models.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
```
**Why**
- `Auth::attempt` finds the user by email and checks the password hash for you.
- A *generic* error message ("credentials do not match") – never reveal whether the email exists.
- `intended()` sends the user back to the page they originally wanted.

### 5.2 Routes – add to `routes/web.php`
```php
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', fn () => redirect()->route('admin.models.index'))->name('dashboard');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        Route::resource('models', ModelShowroomController::class)
            ->except('show')
            ->parameters(['models' => 'showroom']);
    });
});
```
**Why**
- `prefix('admin')` → URLs start with `/admin`; `name('admin.')` → route names like `admin.login`.
- `guest` = only for logged-out users; `auth` = only for logged-in users.
- `throttle:10,1` = max 10 login attempts per minute (brute-force protection).
- `Route::resource` creates index/create/store/edit/update/destroy in one line.
- `parameters(['models' => 'showroom'])` names the URL parameter `{showroom}`, so the controller can type-hint `ModelShowroom $showroom` (route-model binding).

### 5.3 Where to send guests – `bootstrap/app.php`
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->redirectGuestsTo(fn () => route('admin.login'));
    $middleware->redirectUsersTo(fn () => route('admin.models.index'));
})
```
**Why:** by default Laravel redirects guests to a route named `login`, which we don't have (we named it `admin.login`) – that is a classic error: *Route [login] not defined*.

### 5.4 Login view – `resources/views/admin/login.blade.php`
A Bootstrap card with email + password inputs. The important parts:
```blade
<form method="POST" action="{{ route('admin.login.attempt') }}">
    @csrf                                         {{-- REQUIRED, otherwise 419 Page Expired --}}
    <input type="email" name="email" value="{{ old('email') }}"
           class="form-control @error('email') is-invalid @enderror">
    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <input type="password" name="password" class="form-control">
    <button type="submit" class="btn btn-purple w-100">Sign in</button>
</form>
```
- `@csrf` adds a hidden token that blocks cross-site request forgery.
- `old('email')` keeps what the user typed after a failed attempt.
- `@error` shows the validation message for that field.

Create `public/assets/css/admin.css` with a small stylesheet (purple navbar, `.btn-purple`, `.thumb`, `.preview-box`).

### 5.5 Test it
`/admin/models` while logged out → redirected to login. Wrong password → error. Right password → redirected (404/500 until Phase 6, that is expected).

**If it fails**
- **419 Page Expired** → forgot `@csrf`.
- **Route [login] not defined** → missing step 5.3.
- **Always "credentials do not match"** → seeder did not run (`php artisan db:seed`), or the password was stored unhashed.

---------------------------------------------------------------------------

## Phase 6 – Admin CRUD (list, add, edit, delete, image upload)

### 6.1 Form Request – validation in its own class
```bash
php artisan make:request ModelShowroomRequest
```
```php
class ModelShowroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // access is already protected by the "auth" middleware
    }

    public function rules(): array
    {
        $creating = $this->isMethod('POST');
        $presence = $creating ? ['required'] : ['sometimes', 'required'];

        return [
            'model_name'    => [...$presence, 'string', 'max:100'],
            'year'          => [...$presence, 'integer', 'between:1990,'.(date('Y') + 2)],
            'price'         => [...$presence, 'numeric', 'min:0', 'max:99999999.99'],
            'image'         => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status'        => ['sometimes', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Please upload a picture of the vehicle.',
            'image.max'      => 'The picture may not be larger than 2 MB.',
        ];
    }
}
```
**Why**
- One class = one set of rules for both *create* and *update* (and later the API).
- On create the image is required; on update it is optional (keep the old one).
- `max:2048` is in KB (2 MB). `mimes` + `image` stops someone uploading a `.php` file renamed to `.png`.
- The spread `...$presence` matters: writing `'sometimes|required'` *inside* an array does **not** work (a bug we hit in testing).

### 6.2 Image service
```bash
mkdir app/Services
```
`app/Services/ShowroomImage.php`:
```php
class ShowroomImage
{
    private const DISK = 'public';
    private const FOLDER = 'models';

    public function store(UploadedFile $file): string
    {
        return $file->store(self::FOLDER, self::DISK);   // returns "models/RandomName.png"
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public function replace(?string $old, UploadedFile $file): string
    {
        $new = $this->store($file);
        $this->delete($old);          // don't leave orphan files on disk

        return $new;
    }
}
```
**Why a service:** the admin controller and the API both need upload/delete logic. Writing it once = no duplication, easier tests. `store()` generates a random file name, so two uploads called `car.png` never overwrite each other.

### 6.3 Controller
```bash
php artisan make:controller Admin/ModelShowroomController
```
```php
class ModelShowroomController extends Controller
{
    public function __construct(private ShowroomImage $images) {}   // injected by Laravel

    public function index()
    {
        $models = ModelShowroom::ordered()->paginate(10);
        return view('admin.models.index', compact('models'));
    }

    public function create()
    {
        return view('admin.models.create', [
            'showroom' => new ModelShowroom(['status' => true, 'display_order' => $this->nextOrder()]),
        ]);
    }

    public function store(ModelShowroomRequest $request): RedirectResponse
    {
        $data = $request->validated();                    // only validated fields
        $data['status'] = $request->boolean('status');
        $data['display_order'] = $data['display_order'] ?? $this->nextOrder();
        $data['image'] = $this->images->store($request->file('image'));

        ModelShowroom::create($data);

        return redirect()->route('admin.models.index')->with('success', 'Model added successfully.');
    }

    public function edit(ModelShowroom $showroom)
    {
        return view('admin.models.edit', compact('showroom'));
    }

    public function update(ModelShowroomRequest $request, ModelShowroom $showroom): RedirectResponse
    {
        $data = $request->validated();
        $data['status'] = $request->boolean('status');
        $data['display_order'] = $data['display_order'] ?? $showroom->display_order;

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->replace($showroom->image, $request->file('image'));
        } else {
            unset($data['image']);          // no new file -> keep the existing one
        }

        $showroom->update($data);

        return redirect()->route('admin.models.index')->with('success', 'Model updated successfully.');
    }

    public function destroy(ModelShowroom $showroom): RedirectResponse
    {
        $this->images->delete($showroom->image);
        $showroom->delete();

        return redirect()->route('admin.models.index')->with('success', 'Model deleted.');
    }

    private function nextOrder(): int
    {
        return (int) ModelShowroom::max('display_order') + 1;
    }
}
```
**Why**
- Type-hinting `ModelShowroomRequest` runs validation *before* the method body; if it fails Laravel redirects back with errors automatically.
- **Post/Redirect/Get:** after saving we `redirect()` so refreshing the page can't resubmit the form.
- `->with('success', …)` is a one-time *flash* message.
- `$request->boolean('status')` – an unticked checkbox sends nothing, so we convert explicitly.
- Delete the image file first, then the row – otherwise files pile up on disk.

### 6.4 Views (in `resources/views/admin/`)
1. **`layout.blade.php`** – navbar (brand, "View website", Logout form with `@csrf`), flash alert (`@if (session('success'))`), `@yield('content')`, and `@stack('scripts')` before `</body>`.
2. **`models/index.blade.php`** – table with `@forelse ($models as $model) … @empty … @endforelse`, badge for Active/Inactive, Edit link, Delete button, and `{{ $models->links() }}` for pagination.
3. **`models/_form.blade.php`** – one form shared by create and edit. Key parts:
   ```blade
   <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
       @csrf
       @if ($method !== 'POST') @method($method) @endif       {{-- HTML forms only do GET/POST; this fakes PUT --}}

       <input type="text" name="model_name" value="{{ old('model_name', $showroom->model_name) }}">
       ...
       <input type="hidden" name="status" value="0">          {{-- so an unticked box still sends 0 --}}
       <input type="checkbox" name="status" value="1" @checked(old('status', $showroom->status))>

       <input type="file" name="image" accept="image/png,image/jpeg,image/webp">
   </form>
   ```
   - **`enctype="multipart/form-data"` is mandatory for file uploads** – without it `$request->file('image')` is `null`.
   - `old('x', $showroom->x)` = "what the user just typed, else the saved value" – the same form works for create (empty model) and edit.
4. **`models/create.blade.php`** and **`edit.blade.php`** – extend the layout and `@include('admin.models._form', [...])` with different `action`, `method` and button label.
5. **Image preview (jQuery)** inside `@push('scripts')`:
   ```js
   $('#image').on('change', function () {
       var file = this.files && this.files[0];
       if (!file) return;
       var reader = new FileReader();
       reader.onload = function (e) { $('#imagePreview').attr('src', e.target.result).prop('hidden', false); };
       reader.readAsDataURL(file);
   });
   ```
6. **Delete confirmation** – one Bootstrap modal; a button's `data-action` holds the delete URL, jQuery puts it in the modal form's `action`. Deleting must be a `DELETE` request (`@method('DELETE')`) in a form, **never a GET link**, because crawlers/prefetch could trigger it.

### 6.5 Pagination style – `app/Providers/AppServiceProvider.php`
```php
public function boot(): void
{
    Paginator::useBootstrapFive();   // import Illuminate\Pagination\Paginator
}
```
**Why:** Laravel's default pagination markup uses Tailwind classes; we use Bootstrap.

### 6.6 Test it (checklist)
1. Add a model with an image → appears in the list and on the homepage.
2. Submit empty form → error messages under each field; typed values are kept.
3. Upload a `.pdf` → image error.
4. Edit without choosing an image → old image stays. Edit with a new one → old file removed from `storage/app/public/models`.
5. Toggle Inactive → disappears from the homepage.
6. Change Display order → homepage order changes.
7. Delete → row and file gone.

**If it fails**
- **Image not showing** → `php artisan storage:link` not run, or you opened the site on a different host than `APP_URL` (we use `asset()` to avoid this).
- **`The image failed to upload`** → file larger than PHP's `upload_max_filesize`/`post_max_size` in `php.ini`.
- **`$request->file('image')` is null** → missing `enctype`.
- **`MethodNotAllowed` on update/delete** → missing `@method('PUT')` / `@method('DELETE')`.
- **`Missing required parameter for [Route: admin.models.edit]`** → you passed the wrong thing to `route()`; pass the model: `route('admin.models.edit', $model)`.
- **Validation passes but data not saved** → field missing in `$fillable`.

---------------------------------------------------------------------------

## Phase 7 – REST API (optional bonus)

### 7.1 Install Sanctum (token authentication)
```bash
php artisan install:api
```
Add the trait in `app/Models/User.php`:
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
```
**Why Sanctum:** the API has no browser session, so clients send `Authorization: Bearer <token>`. `install:api` creates `routes/api.php` (URLs get the `/api` prefix automatically) and the tokens table.

### 7.2 API Resource (controls the JSON shape)
```bash
php artisan make:resource ModelShowroomResource
```
```php
public function toArray(Request $request): array
{
    return [
        'id'            => $this->id,
        'model_name'    => $this->model_name,
        'year'          => $this->year,
        'price'         => (float) $this->price,
        'image_url'     => $this->image_url,
        'status'        => $this->status,
        'display_order' => $this->display_order,
        'created_at'    => $this->created_at,
        'updated_at'    => $this->updated_at,
    ];
}
```
**Why:** the API's output is a stable contract; renaming a DB column should not silently break API clients.

### 7.3 Controllers – `app/Http/Controllers/Api/`
`AuthController` (login returns a token):
```php
public function login(Request $request): JsonResponse
{
    $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

    $user = User::where('email', $data['email'])->first();

    if (! $user || ! Hash::check($data['password'], $user->password)) {
        return response()->json(['message' => 'Invalid credentials.'], 401);
    }

    return response()->json(['token' => $user->createToken('api')->plainTextToken, 'token_type' => 'Bearer']);
}
```
`ModelShowroomController` – reuse the same Form Request and image service:
```php
public function index(): AnonymousResourceCollection
{
    return ModelShowroomResource::collection(ModelShowroom::active()->ordered()->get());
}

public function store(ModelShowroomRequest $request): JsonResponse
{
    $data = $request->validated();
    $data['status'] = $request->boolean('status', true);
    $data['display_order'] = $data['display_order'] ?? ((int) ModelShowroom::max('display_order') + 1);
    $data['image'] = $this->images->store($request->file('image'));

    return (new ModelShowroomResource(ModelShowroom::create($data)))->response()->setStatusCode(201);
}

public function update(ModelShowroomRequest $request, int $id): ModelShowroomResource { ... }   // like admin update
public function destroy(int $id): JsonResponse { ... }                                            // delete image + row
```
(See `app/Http/Controllers/Api/ModelShowroomController.php` in the finished project for the full `show/update/destroy`.)

**Why status codes matter:** `201 Created` for POST, `401` not logged in, `404` not found, `422` validation failed. Interviewers like to hear this.

### 7.4 Routes – `routes/api.php`
```php
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::get('model-showrooms', [ModelShowroomController::class, 'index']);
Route::get('model-showrooms/{id}', [ModelShowroomController::class, 'show'])->whereNumber('id');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('model-showrooms', [ModelShowroomController::class, 'store']);
    Route::match(['put', 'patch'], 'model-showrooms/{id}', [ModelShowroomController::class, 'update'])->whereNumber('id');
    Route::delete('model-showrooms/{id}', [ModelShowroomController::class, 'destroy'])->whereNumber('id');
});
```
**Why public GET:** anyone may read what the homepage already shows publicly; only writes are protected.

### 7.5 Test it with curl / Postman
```bash
curl http://127.0.0.1:8000/api/model-showrooms -H "Accept: application/json"
curl -X POST http://127.0.0.1:8000/api/login -H "Accept: application/json" -d "email=admin@markham.test" -d "password=Admin@12345"
curl -X POST http://127.0.0.1:8000/api/model-showrooms -H "Accept: application/json" -H "Authorization: Bearer YOUR_TOKEN" -F model_name=QX30 -F year=2016 -F price=39900 -F image=@car.png
```
**Always send `Accept: application/json`** – otherwise Laravel may answer with an HTML redirect instead of a JSON error.
Real `PUT` with a file does not work in PHP; use `POST` + `-F _method=PUT`.

---------------------------------------------------------------------------

## Phase 8 – Automated tests

```bash
php artisan make:test ModelShowroomTest
php artisan test
```
Skeleton:
```php
class ModelShowroomTest extends TestCase
{
    use RefreshDatabase;                       // empty DB for every test

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');               // uploads go to a throw-away disk
    }

    public function test_homepage_lists_only_active_models_in_display_order(): void
    {
        ModelShowroom::factory()->create(['model_name' => 'Second', 'display_order' => 2]);
        ModelShowroom::factory()->create(['model_name' => 'First',  'display_order' => 1]);
        ModelShowroom::factory()->inactive()->create(['model_name' => 'Hidden']);

        $this->get('/')->assertOk()->assertSeeInOrder(['2014 First', '2014 Second'])->assertDontSee('Hidden');
    }

    public function test_guests_are_redirected_to_admin_login(): void
    {
        $this->get('/admin/models')->assertRedirect(route('admin.login'));
    }
}
```
Needs a factory: `php artisan make:factory ModelShowroomFactory` and `use HasFactory;` in the model.
Pattern for every test: **Arrange** (create data) → **Act** (make request) → **Assert**.

Run one test: `php artisan test --filter=test_guests_are_redirected_to_admin_login`.
Read failures from the *top*: the first message is the real cause.

---------------------------------------------------------------------------

## Debugging toolbox (memorise this)

| Problem | First thing to try |
|---|---|
| White page / 500 error | Open `storage/logs/laravel.log`, read the **first** exception line. Set `APP_DEBUG=true` locally. |
| Change in `.env` ignored | `php artisan config:clear` |
| Blade change not showing | `php artisan view:clear` (or `optimize:clear` to clear all) |
| Route 404 | `php artisan route:list` – does it exist, which method/name? |
| "What is in this variable?" | `dd($x)` (dump and die) or `dump($x)` |
| Check DB data quickly | `php artisan tinker` |
| Inspect SQL | `ModelShowroom::active()->ordered()->toSql()` in tinker |
| Start clean | `php artisan migrate:fresh --seed` (**destroys data**) |
| CSS/JS not applying | Ctrl+F5; F12 → Network tab (404?) and Console tab (errors) |
| Layout broken at a size | F12 → device toolbar; right-click the element → Inspect → see which CSS wins |
| Form posts but nothing saves | Check `$fillable`, `@csrf`, input `name=` attributes, and `$errors` output |
| Class not found | Missing `use` import, or run `composer dump-autoload` |

**Habit:** when something breaks, change one thing at a time and re-test. Never debug three things at once.

---------------------------------------------------------------------------

## Interview questions you should be able to answer

1. **What is MVC and where is it in this project?** Model = `ModelShowroom`; Views = Blade files; Controllers = `HomeController`, admin/API controllers.
2. **Why use Eloquent scopes?** Reusable, named query rules – homepage and API share `active()->ordered()`.
3. **What is mass assignment and `$fillable`?** Whitelist of fields that `create()/update()` may set; blocks injection of unexpected fields.
4. **Why a Form Request instead of validating in the controller?** Keeps controllers thin, rules reusable (admin + API), automatic redirect/422 handling.
5. **How does image upload work?** `multipart/form-data` → `$request->file()` → `store()` on the `public` disk → path saved in DB → `storage:link` exposes it → `asset('storage/...')` builds the URL.
6. **Why store only the path in the database?** Smaller DB, faster queries, files can be served by the web server/CDN.
7. **What does `@csrf` do?** Adds a token so other websites can't submit forms on behalf of a logged-in admin.
8. **Why `redirect()` after POST?** Post/Redirect/Get – prevents duplicate submissions on refresh.
9. **Route-model binding?** `{showroom}` in the URL is automatically turned into a `ModelShowroom` (404 if missing).
10. **Session login vs. API tokens?** Browser admin uses cookie sessions; API clients use Sanctum bearer tokens.
11. **How is the page responsive?** Viewport meta tag, Bootstrap grid/collapse, mobile-first CSS with `min-width` media queries, `max-width:100%` images, and a JS slider that changes cards-per-view with the screen width.
12. **How would you improve it?** Caching the homepage query, image resizing/thumbnails, roles/permissions, drag-and-drop ordering, soft deletes, a detail page per model.

---------------------------------------------------------------------------

## Suggested schedule (2–3 days)

- **Day 1:** Phases 0–3, then the static sections in 4.1–4.6.
- **Day 2:** 4.7–4.9 (dynamic slider + responsive), Phase 5, Phase 6.
- **Day 3:** Phase 7 (API), Phase 8 (tests), polish, practise the interview questions.

When you get stuck, compare **one file** with the finished project, find the difference, and make sure you can say *why* it is different.
