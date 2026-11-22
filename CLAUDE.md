# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Casa Blanca is a restaurant management app on Laravel 12 (PHP 8.2) with Breeze auth. Domain names, routes, UI text and flash messages are in Spanish; keep new ones in Spanish. Staff roles are `admin`, `mozo` (waiter) and `cajera` (cashier). It is a restaurant sales system: the admin registers supplies (insumos) and products, the mozo or the customer creates an order identified by DNI, and the cajera charges it and issues a receipt.

## Commands

```bash
composer setup                 # first install: deps, .env, key, migrate, npm build (does not seed)
composer dev                   # server, queue listener, pail logs and vite together
php artisan serve              # server only

composer test                  # clears config cache, then runs all tests
php artisan test tests/Feature/ProfileTest.php
php artisan test --filter=test_profile_page_is_displayed

php vendor/bin/pint --dirty    # format changed PHP files (default Laravel preset)

php artisan storage:link       # required for product images (public/storage is gitignored)
```

Seeding must name each class, in this order, because `DatabaseSeeder` calls neither and creates only a role-less test user:

```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserSeeder   # admin@, juan@ (mozo), maria@ (cajera) @casablanca.com, password 12345678 (fresh databases only; the local admin password was changed)
php artisan db:seed --class=ContenidoWebSeeder   # default facilities for the home page
php artisan db:seed --class=RecetasSeeder   # insumos and estimated recipes for the menu; keeps existing stock and recipes
```

## Environment

- The local `.env` uses MySQL (`bdcasa_blanca`), while `.env.example` defaults to SQLite.
- Tests run on in-memory SQLite (`phpunit.xml`), so migrations must work on both engines.
- The app timezone is `America/Lima`.
- Tests never seed roles, so tests for role-protected routes must create the `Role` and assign it to the user, as `tests/Feature/VentasTest.php` does.

## Architecture

### Role-based access

- `users.role_id` points to `roles`, and the role is checked by its `nombre`, not its id. `User::hasRole()` and the `isAdmin()`, `isMozo()`, `isCajera()` helpers do the comparison.
- The `checkrole:<nombre>` middleware alias is registered in `bootstrap/app.php`. It implements `App\Http\Middleware\CheckRole` and returns 403 on a mismatch.
- `routes/web.php` has one group per role, each with a matching URL prefix and route name prefix (`admin.`, `mozo.`, `cajera.`). Controllers live in matching namespaces (`App\Http\Controllers\Admin`, etc.).
- `/dashboard` is only a dispatcher: login lands there and it redirects to the role's dashboard. A new role needs a branch added there.
- Public registration (`RegisteredUserController`, linked from the login page) creates users with no role. `/dashboard` sends them to `login`, and the `guest` middleware sends them back to `/dashboard`, so they get a redirect loop.

### Sales flow

- Insumos have a decimal `stock`. Products have a `tipo`: `directo` sells from `productos.stock`; `plato` has a recipe (`producto_insumo` pivot) and its `stock` column stays 0. `Producto::disponible` gives the sellable units either way, so load `insumos` first to avoid N+1.
- An order (`pedidos`) starts as `pendiente` and comes from the mozo or from the public, unauthenticated `/carta` page (throttled). `pedido_items` copies the product name and price, so receipts stay stable.
- Creating an order only checks availability. **Stock is discounted when the order is charged**, in `App\Services\VentaService::cobrar`, inside a transaction with row locks. All order, charge and cancel logic belongs in that service.
- A charge takes 1 or 2 payments (`pagos`) that must add up to the order total. `numero_operacion` is required except for `efectivo`.
- `PD-` and `RC-` numbers are accessors built from the id, not columns.

### Receipt printer (ticketera)

- After a charge, `CobroController::cobrar` sends the voucher straight to the thermal printer through `App\Services\TicketeraService`, which builds ESC/POS bytes (code page 850, 48 columns for 80 mm or 32 for 58 mm) and runs `resources/ticketera/imprimir-raw.ps1` to write them RAW to the Windows printer. A failed print never undoes the charge; it only flashes an `error` with a reprint button (`recibos.imprimir`).
- The printer name comes from `TICKETERA_IMPRESORA` in `.env` (`config('services.ticketera.impresora')`). Empty means no direct printing, and `recibos/show` falls back to the browser's print dialog. `phpunit.xml` forces it empty so tests never print; use `Process::fake()` to test it.
- `php artisan serve` strips Windows environment variables, so the service passes `SystemRoot` to PowerShell explicitly (without it PowerShell fails with error 8009001d).
- The business data on the voucher (name, RUC, place, message, paper width) are `ticket_*` fields in `Contenido::CAMPOS`, edited from the admin content screen.

### Prices

- Sales never read the current price: `pedido_items` stores `precio_unitario` and `subtotal` when the order is created, and `recibos.total` when it is charged. Reports must use those columns, never `productos.precio_venta`.
- `Producto`'s `updated` hook writes every `precio_venta` change to `producto_precios` with the user, whichever screen made it.

### Website content

- `welcome.blade.php` is rendered by `InicioController` from `Contenido` (key/value texts, defined in `Contenido::CAMPOS`) and `SitioItem` (attractions, activities, events, gallery, testimonials). A section with no published items is hidden. The admin editor at `admin.contenido.index` is generated from those two constants, so a new field or item type needs no new screen.
- The `instalacion` item type is shown as "Atractivos". Its icon is picked from words in the title (`SitioItem::ICONOS`). Items with a photo render as photo cards and items without one as icon cards.
- In `Contenido` titles, text between `*asterisks*` renders in italics (`Contenido::titulo()`, which escapes first). An empty image field falls back to `hero_imagen`.
- The menu PDF is served from `public/docs/carta-casa-blanca.pdf`. Do not create a `public/carta/` folder: it shadows the `/carta` route.
- Uploaded site images are on the `public` disk under `web/`. Product images are under `productos/`. Both are gitignored, so deploying means copying `storage/app/public` and exporting the database.
- `public/creditos-fotos.txt` holds the attribution the stock photos' licenses require.

### Product modal

- `partials/visor-producto` is the click-to-open modal (photo, price, "Qué trae"), used by the menu, the mozo order form, the home page and the product list. Any element with `data-visor` opens it.
- "Qué trae" combines `Producto::incluye`, built from the recipe, with `productos.descripcion`. Customers see counts for `presa`, `porción` and `unidad` insumos and only the name for weights and volumes. `producto_insumo.visible_cliente` hides items like oil while still discounting their stock.
- Do not name a pivot column `visible`: inside a model, `$pivot->visible` resolves to Eloquent's protected `$visible` property instead of the attribute.

### Admin controllers

- Routes are declared explicitly rather than with `Route::resource`.
- Controllers take a raw `$id` and call `findOrFail`, validate inline with `$request->validate()`, and redirect with `success` or `error` flash messages that the views render.
- `AlmacenController` handles products, categories and subcategories. **`SubcategoriaController` is dead code**: no route points to it, so change the `subcategorias*` methods in `AlmacenController` instead.
- Filtering subcategories by category on the product forms happens client-side through `data-categoria` attributes. The JSON route `admin.subcategorias.por-categoria` has no caller in the views.

### Product images

- All uploads go through `App\Services\ImagenService`: WebP, at most 1200 px wide, plus a 240 px thumbnail in `<folder>/mini/`. Lists use `Producto::imagen_mini_url`; the modal uses the full image. If GD has no WebP support it stores the original and logs a warning.
- `php artisan imagenes:convertir` converts stored images and creates missing thumbnails. It is safe to run repeatedly.
- Pass the `public` disk explicitly, because the default `FILESYSTEM_DISK` is `local`.
- Read images through the `Producto::imagen_url` accessor. Its fallback, `public/img/sin-imagen.png`, is not currently in the repo.
- The model's `deleting` hook removes the file when a product is deleted.

### Views have several separate styling setups

No view uses `@vite`, so the compiled `resources/css/app.css` and `resources/js/app.js` (including Alpine) are not loaded on any page.

| Views | Setup |
| --- | --- |
| `admin/insumos`, `admin/ventas`, `mozo/*`, `cajera/*` | `@extends('layouts.panel', ['nav' => ...])`: shared white and orange layout with Tailwind and Font Awesome from CDNs, flash messages and a validation error summary. Use this for new panel pages. |
| `admin/almacen`, `admin/subcategorias`, `admin/usuarios`, `admin/dashboard` | Older standalone HTML documents with the same CDNs and `@include('admin.nav')`. All are white and orange except `admin/dashboard`, which is still dark. |
| `welcome`, `carta/index`, `recibos/show` | Standalone public home page (own CSS, white theme, `img/logo-claro.png`), public menu and printable receipt. |
| `auth/login` | Standalone page with hand-written CSS only. |
| `profile/*`, other `auth/*` | Stock Breeze `<x-app-layout>` / `<x-guest-layout>`. With no compiled CSS or JS loaded, Tailwind classes and Alpine behaviour do not apply here. |

New admin sidebar links go in `admin/nav.blade.php`, using `request()->routeIs()` for the active state. Mozo and cajera links are arrays in `mozo/nav.blade.php` and `cajera/nav.blade.php`, rendered by `layouts/staff-nav`.
