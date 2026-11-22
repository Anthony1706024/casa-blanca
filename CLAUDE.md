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

Production (Railway) data changes ship as data migrations, because Railway runs `migrate` on deploy and there is no direct database access. They must be idempotent and do nothing on an empty database, so tests and fresh installs are unaffected. Examples: `2026_11_22_000003_actualizar_mensaje_final_del_ticket` and `2026_11_22_000006_agregar_jarras_y_cocteles`; the latter adds products by name only when missing, as `directo` with 60 units each, and attaches recipes with `RecetasSeeder` (limited through its `$solo` property) only so the menu can show "Qué trae"; `directo` products never discount insumos.

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
- An order (`pedidos`) starts as `pendiente` and comes from the mozo, from caja (`origen` `caja`, then it redirects straight to the charge screen) or from the public, unauthenticated `/carta` page (throttled). `mozo_id` holds whichever staff user registered it. `pedido_items` copies the product name and price, so receipts stay stable.
- Only the table (`mesa`) is required for an order: `dni` (nullable column) and `cliente_nombre` are optional for staff and `/carta`, and tickets skip them when empty. Caja searches pending orders by DNI or table. Caja's "Nuevo pedido" only saves (no charge).
- Charging is a separate step after the customer pays. Before that, caja prints the **cuenta** (`Cajera\CuentaController`, `TicketeraService::cuenta`, queue type `cuenta`, `recibos/show` with `$recibo = null`): same layout as the voucher, "PENDIENTE DE PAGO", and the order stays `pendiente`. The cuenta and kitchen tickets open in a separate window; after `cobrar` caja goes back to "Pedidos por cobrar", where the voucher goes to the printer or opens in a separate window.
- Printer text goes through `TicketeraService::sinTildes` (accents, ñ, ¡ and º dropped): the thermal printer did not print them.
- A mozo only sees, adds to, removes items from and cancels his own orders (`Mozo\PedidoController::miPedido` scopes by `mozo_id`; another mozo's order is a 404). Caja sees all of them, including the `/carta` prepedidos. Both use the shared views `pedidos/_items` and `pedidos/adicionales`, with `$prefijo` (`mozo` or `cajera`) picking the route names.
- `pedido_items.tipo` is `pedido` (the original order) or `adicional` (added later via `VentaService::agregarItems`). An add-on never merges into an original line, only into an add-on line not yet sent to the kitchen, so the kitchen tickets print just the new items. `ComandaService::adicionalesPorImprimir` feeds caja's alert for them.
- A table can have only one pending order: `VentaService::crearPedido` calls `verificarMesaLibre` (tables compared with `normalizarMesa`: case, spaces and leading zeros ignored). New items go into that order as add-ons.
- `partials/auto-actualizar` refreshes panel pages in place: every few seconds it refetches the page and swaps the `data-auto="…"` blocks. With `avisar`, new ids in `[data-aviso-id]` beep, show a toast, prefix the tab title and highlight the `[data-pedido-id]` rows of `data-aviso-pedido`. Caja's ids come from `cajera/_avisos`: `p{id}` per pending order and `a{item}` per add-on to print (`data-aviso-tipo="adicional"`). Used by caja's dashboard and "Pedidos por cobrar" and by the mozo's list and dashboard.
- `/carta` is the customer menu, laid out like the printed PDF menu: `App\Services\CartaService::SECCIONES` maps PDF sections (titles, order) to category/subcategory names, pairs "Fuente de X" with "X" in one Personal/Fuente row, and appends anything unmatched by category so new products still show. It keeps a cart in `localStorage` (`casablanca.carrito`) and a cart modal where the customer enters DNI and table. `mesa` is required there but optional for staff, whose rules live in `PedidoPersonalRequest`. Staff screens use the `pedidos._selector` quantity grid instead.
- Creating an order only checks availability. **Stock is discounted when the order is charged**, in `App\Services\VentaService::cobrar`, inside a transaction with row locks. All order, charge and cancel logic belongs in that service.
- A charge takes 1 or 2 payments (`pagos`) that must add up to the order total. `numero_operacion` is optional for every method; the voucher prints it only when present.
- `PD-` and `RC-` numbers are accessors built from the id, not columns.

### Kitchen tickets (comandas) and add-ons

- `productos.cocina` (`Producto::COCINAS`: `cocina_1`, `bebidas`, `cocina_2`, default `cocina_2`) says which kitchen ticket a product prints on. The admin edits it in the product form (`almacen/_tipo`) and in bulk in `admin.precios.index`.
- From the charge screen, caja can add add-ons to a pending order (`VentaService::agregarItems`, merged into the existing line when the price is the same), remove one unit or a whole line (`VentaService::quitarItem`, which refuses to empty the order and warns when the units were already sent to the kitchen) and print kitchen tickets (`Cajera\ComandaController`). `ComandaService::lote` builds one ticket per kitchen in `COCINAS` order and only prints what `pedido_items.cantidad_enviada` says has not been sent yet; `todo=1` reprints everything.
- With a printer configured, `TicketeraService::comanda` sends all tickets in one job, each ending with a paper cut, and items are marked sent only if printing succeeds. Without one, they are marked sent and `cajera/pedidos/comanda` prints them from the browser, one page per ticket.
- The ticket buttons on `cajera/pedidos/show` submit into a separate popup window (`ticket-pedido-{id}`) so the order tab never reloads; every `ComandaController::imprimir` response redirects to `cajera.pedidos.comanda.ver`, which shows the tickets and offers print or reprint. Reprinting always asks for confirmation in a modal.
- Kitchen tickets carry no logo. The payment voucher (`TicketeraService::voucher`, and `recibos/show` in the browser) starts with a 1-bit `GS v 0` raster from `resources/ticketera/logo.bin`: the shrimp only (`resources/ticketera/camaron.png`, cut out of `logo-claro.png`), dithered, with "Restaurante Campestre" (Monotype Corsiva) and "Casa Blanca" (Brush Script MT) drawn under it, so the voucher does not print the name again as text. Regenerate it with `resources/ticketera/generar-logo.ps1` (System.Drawing, Windows fonts); it also writes `logo-vista.png` to preview the result. `recibos/show` uses `public/img/camaron.webp` and the same fonts in CSS. The voucher no longer says "No válido como comprobante de pago".

### Receipt printer (ticketera)

- `App\Services\TicketeraService` builds the ESC/POS bytes (code page 850, 48 columns for 80 mm or 32 for 58 mm): `voucher()` after a charge (`CobroController::cobrar`, reprint `recibos.imprimir`) and `comanda()` for kitchen tickets. A failed print never undoes the charge; it only flashes an `error`.
- `TICKETERA_MODO` (`config('services.ticketera.modo')`) picks how they reach the printer:
  - `cola` (default, used locally and on Railway): `ColaImpresionService::encolar` stores the bytes base64 in the `impresiones` table (`Impresion`: `pendiente` → `enviando` → `impresa`, or `error` after 3 failures). Needs `TICKETERA_CLAVE`; without it the queue counts as not configured.
  - `directo`: runs `resources/ticketera/imprimir-raw.ps1` on this same Windows PC with the printer named in `TICKETERA_IMPRESORA`. `php artisan serve` strips Windows environment variables, so the service passes `SystemRoot` to PowerShell explicitly (without it PowerShell fails with error 8009001d).
- `TicketeraService::configurada()` is false when the active mode lacks its key or printer name; then `recibos/show` and `cajera/pedidos/comanda` fall back to the browser's print dialog. When it is true, they never open that dialog.
- The agent (`resources/ticketera/agente/`, PowerShell) runs on the PC with the printer: it polls `GET /ticketera/pendientes`, prints each job in order with `imprimir-raw.ps1` (copied next to it) and reports `POST /ticketera/{id}/impresa` or `/error`. Its `config.json` holds the url, key and printer name and is gitignored. `LEEME.txt` explains setup; `instalar-tarea.ps1` registers the scheduled task "Casa Blanca - Ticketera" (at logon, hidden, restarts on failure).
- The `/ticketera/*` routes authenticate with `Authorization: Bearer <TICKETERA_CLAVE>` (`ClaveTicketera` middleware, `hash_equals`; 403 when no key is set, 401 when it is wrong). They skip sessions and CSRF (`withoutMiddleware` in `routes/web.php`, plus the CSRF exception in `bootstrap/app.php`), because the agent polls every few seconds and `SESSION_DRIVER=database` would otherwise create a row per poll. Jobs left in `enviando` for 2 minutes are offered again; the agent remembers the ids it already printed so a lost acknowledgement does not print twice.
- `impresiones.index` (cajera and admin) lists the last 50 jobs with a "Reintentar" button for `error` and warns when jobs have been waiting more than a minute.
- `phpunit.xml` forces `TICKETERA_MODO=directo` with an empty key and printer, so tests neither print nor queue unless they set the config themselves; use `Process::fake()` as `ColaImpresionTest`, `TicketeraTest` and `ComandaTest` do.
- The business data on the voucher (name, RUC, place, message, paper width) are `ticket_*` fields in `Contenido::CAMPOS`, edited from the admin content screen.

### Prices

- Sales never read the current price: `pedido_items` stores `precio_unitario` and `subtotal` when the order is created, and `recibos.total` when it is charged. Reports must use those columns, never `productos.precio_venta`.
- `Producto`'s `updated` hook writes every `precio_venta` change to `producto_precios` with the user, whichever screen made it.

### Finances (admin)

- `admin.ingresos.index` ("Ingresos diarios") and `admin.egresos.index` share `App\Support\Periodo` (`?periodo=dia|semana|mes|anio|rango`, with `fecha` or `desde`/`hasta`; weeks run Monday to Sunday; a year or a range over 62 days is grouped by month) and `App\Services\FinanzasService`. Income comes from `recibos.total` and `pagos.monto` by `recibos.created_at`; the balance is income minus egresos of the same period.
- Egresos (`Egreso`, `fecha` stored as a plain `Y-m-d` by its attribute mutator so SQLite and MySQL filter alike) belong to a `CategoriaEgreso` (`categorias_egreso`); the migration seeds the default types and the admin adds more from the egresos screen. A type with egresos cannot be deleted.
- The opening cash balance (`SaldoInicial`, `saldos_iniciales`, one per `fecha`; saving a date again replaces it) is set on the income screen. A period's opening balance is the one on its earliest day that has one, and the closing balance is opening + income − egresos (just the balance when there is none).
- Both screens export CSV for Excel (`;`, UTF-8 BOM) and print with `window.print()` (the `.no-print` class hides the filters and the sidebar).

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
| `welcome`, `carta/index`, `recibos/show` | Standalone public home page (own CSS, white theme, `img/logo-claro.png`), public menu with cart (Tailwind CDN; it forces `[hidden]` to win over Tailwind display classes) and printable receipt. |
| `auth/login` | Standalone page with hand-written CSS only. |
| `profile/*`, other `auth/*` | Stock Breeze `<x-app-layout>` / `<x-guest-layout>`. With no compiled CSS or JS loaded, Tailwind classes and Alpine behaviour do not apply here. |

New admin sidebar links go in `admin/nav.blade.php`, using `request()->routeIs()` for the active state. Mozo and cajera links are arrays in `mozo/nav.blade.php` and `cajera/nav.blade.php`, rendered by `layouts/staff-nav`.
