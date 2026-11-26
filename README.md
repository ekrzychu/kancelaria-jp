# Jaroch-Konwent Pakos

Public website for Jaroch-Konwent Pakos Kancelaria Radców Prawnych sp.k. Laravel 13 handles HTTP routing and renders Blade views. The approved CSS, vanilla JavaScript, assets and JSON content are served without a frontend build.

## Architecture

- `routes/web.php` defines the public routes, legacy archive redirect and 404 fallback.
- `app/Http/` contains the controller and middleware for trailing slashes and public response headers.
- `app/Content/` reads the private JSON datasets and prepares publication lists and reviewed article HTML.
- `resources/views/` contains the Blade page templates, shared layout and partials.
- `public/` contains the web entry point, assets, `robots.txt`, and `sitemap.xml`.
- `storage/` and `bootstrap/cache/` are private, writable runtime directories.

The approved baseline is `2cdb87b1b770cf3636e5aa985e536597beadbbc3`. It contains the homepage, six top-level landing pages, the documents index, 13 specialization details, three root-level articles and three document details. The Laravel implementation preserves these 27 pages, the archive redirect and the noindex 404. Article URLs remain at `/<slug>/`, and the canonical domain comes from `site.json`. Shared Blade partials preserve the header, navigation, floating Contact link and footer. Framework paths such as `/admin`, `/api` and `/livewire` are reserved.

## Local development

Use PHP 8.4 and Composer 2. Phase 1 was verified with PHP 8.4.28, Composer 2.11.3 and Laravel 13.44.1. Enable the Laravel-required extensions and the article parser extensions, including cURL, DOM, libxml, mbstring, OpenSSL and XML. `composer check-platform-reqs` verifies the installed package requirements.

From the repository root on Linux or macOS:

```sh
composer install --no-interaction --prefer-dist
composer check-platform-reqs
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi
php artisan serve --host=127.0.0.1 --port=8743
```

Generate an application key only during initial setup of an environment. Keep production keys and credentials private. Local `.env.example` defaults keep cache and sessions on disk and mail in memory. The public routes use no database, sessions or outbound mail.

On this Windows machine, the portable toolchain is installed in `C:\Users\krzyc\Projects\kancelaria-jp-toolchain`. From the repository root in PowerShell:

```powershell
$toolchain = 'C:\Users\krzyc\Projects\kancelaria-jp-toolchain'
$env:PHPRC = Join-Path $toolchain 'php84'
$env:PHP_BIN = Join-Path $env:PHPRC 'php.exe'
& $env:PHP_BIN (Join-Path $toolchain 'composer.phar') install --no-interaction --prefer-dist
& $env:PHP_BIN (Join-Path $toolchain 'composer.phar') check-platform-reqs
if (!(Test-Path -LiteralPath '.env')) {
    Copy-Item -LiteralPath '.env.example' -Destination '.env'
    & $env:PHP_BIN artisan key:generate
}
& $env:PHP_BIN artisan serve --host=127.0.0.1 --port=8743
```

Open `http://127.0.0.1:8743`. Press Ctrl+C to stop the foreground server. Adjust the executable path if you install PHP elsewhere. Keep Apache's document root pointed at `public/` when testing Apache separately.

## Editing content

All five reviewed datasets live under `app/data/`. Run `php tests/content.php` after editing them. The validator checks required fields, optional field types, publication dates, route slugs, URL schemes and exact-case local asset references.

- **Articles** use `slug`, `title`, and reviewed `body_html` in `articles.json`. Add date as `YYYY-MM-DD`, category, summary, or author where available. The homepage picks up to three dated articles with a title and summary. Article detail shows up to two newest other publications. Existing entries without a status are published, and `status: "draft"` is excluded everywhere. Update `public/sitemap.xml` when adding or removing indexable routes.
- **Article images** are in the reviewed `body_html`. Use rights-cleared `/assets/img/` links and provide alternative text and dimensions. Optional `image` and `image_alt` are metadata.
- **Specializations** use `slug`, `title`, `category`, `summary`, `body` and `scope` in `services.json`. The first eight entries appear on the homepage. `home_summary` can override their preview text.
- **Team** entries use `name` and `role` in `team.json`. Optional portraits are exact-case filenames in `public/assets/img/`. The page groups entries by displayed role.
- **Recruitment** uses `recruitment.json`. Vacancies appear when `status` is `open`. An empty list shows the usual vacancy notice.
- **Office details and documents** come from `site.json`. Office hours may vary by day. Document details display the existing publication placeholder.

## Contact during Phase 1

The contact page retains the form layout, phone links and email links. The form fields and submit button are disabled, and the page directs visitors to phone or email. Direct POST requests receive HTTP 405. The application stores no enquiries or rate-limit records and generates no success messages.

The historical validation, CSRF session, honeypot, JSONL persistence, rate limiter and storage-error tests are preserved in `git show 2cdb87b1b770cf3636e5aa985e536597beadbbc3:tests/http.test.mjs`. The Laravel suite replaces those cases with assertions for disabled controls, unhandled POSTs, no success redirect or cookie, and no legacy files written. Full enquiry tests return when intake is implemented.

## Regression checks and screenshot comparison

Install Node.js 22 or newer and Chrome. The tests use Node's built-in runner and Chrome DevTools Protocol. Set `PHP_BIN` and `CHROME_BIN` if the tools are not on their default paths. Run from the repository root:

```sh
node tests/run.mjs
node tests/check.mjs
php tests/content.php
node --test tests/http.test.mjs
node --test tests/browser.test.mjs
node tests/snapshots.mjs test-artifacts/laravel-after --details --stable
```

`tests/run.mjs` runs syntax, content, HTTP and browser checks. The server harness starts Laravel from a disposable copy of the source and JSON datasets, reuses installed dependencies, and puts configuration caches and compiled views in temporary storage. It binds to loopback and removes its temporary copy after each run.

Export and unpack the baseline into a temporary directory with `git archive --format=zip --output=<path> 2cdb87b1b770cf3636e5aa985e536597beadbbc3`. Copy the current `tests/snapshots.mjs` into that temporary export to apply the same capture conditions to both implementations. Run the command from each directory with different output paths. Compare matching files at 1440×900 and 390×844 using the same Chrome version, fonts and device scale. The standard capture includes the six homepage stages and eight representative pages at each viewport. `--details` adds the contact form, footer and open mobile menu. `--stable` captures the internal pages with reduced motion so scroll-reveal timing cannot skew pixel comparison. Homepage stages still use normal motion. The temporary Kontakt messaging and disabled fields are intentional differences.

The [Regression checks workflow](.github/workflows/regression.yml) installs locked Composer dependencies, verifies platform requirements, and runs the same checks on Ubuntu with PHP 8.4, Node 22 and Chrome. Its repository permissions are read-only.

## Hosting and next phases

dhosting must run PHP 8.4 for both web and CLI, serve only `public/`, and allow PHP to write `storage/` and `bootstrap/cache/`. Install with `composer install --no-dev --prefer-dist --optimize-autoloader`, set the production `APP_URL`, configure a private `APP_KEY`, set `APP_DEBUG=false`, and run `composer check-platform-reqs --no-dev`. Verify rewrites, static files, permissions, HTTPS and cache commands on staging. The actual hosting account and MariaDB schema have yet to be verified. [Laravel deployment](https://laravel.com/docs/13.x/deployment) · [dhosting Laravel setup](https://dhosting.pl/pomoc/baza-wiedzy/jak-zainstalowac-laravel-na-serwerze/)

MariaDB and Filament will be added to this application in subsequent phases. Enquiry storage, mail delivery, deployment and the broader SEO migration are deferred. Obtain owner confirmation for the homepage client logos, experience figure and final privacy notice before production rollout.

## Branches and license

`main` is the default branch. The Laravel migration is on `feature/internal-pages`. The `continue-fullscreen-animation` and `collapse-fullscreen-animation` branches retain the earlier homepage variants.

This is proprietary software. See [LICENSE](LICENSE). Asset license notices remain alongside their assets.
