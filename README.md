# Jaroch-Konwent Pakos

Public website and content administration for Jaroch-Konwent Pakos Kancelaria Radców Prawnych sp.k. Laravel 13 serves Blade views with the approved CSS, vanilla JavaScript, photography and fonts. The public site has no frontend build step.

## Architecture and content ownership

- `routes/web.php` and `app/Http/` define public routes, HTTP behavior and the database-aware `/sitemap.xml`.
- `app/Content/SiteContent.php` reads editable content through four Eloquent models and preserves the existing Blade data shapes. `PublicationContent` prepares publication lists, anchor navigation and table wrappers.
- MariaDB is authoritative for articles, specializations, team profiles and job offers. Changes made through the database appear on the corresponding public pages without a deployment. A database failure returns a generic HTTP 503 for affected pages.
- `app/data/site.json` is authoritative for firm identity, navigation, contact details, office hours, the canonical origin, social links and static documents.
- `app/data/{articles,services,team,recruitment}.json` are reviewed migration inputs and regression fixtures. Edit current public database content in MariaDB.
- `resources/views/` and `public/` contain the approved public templates and assets. `storage/` and `bootstrap/cache/` are private writable runtime directories.

Phase 2 preserves the approved Phase 1 baseline `cca68503e4666e7c268e448c3c2d9d4821844e5c` and its 27 public pages. The sitemap preserves the canonical paths, includes published articles and active specializations, and excludes documents and deleted or hidden content. Laravel serves it directly through a cacheable route.

## Requirements

Use PHP 8.4 with `pdo_mysql` and the standard Laravel extensions, including cURL, DOM, libxml, mbstring, OpenSSL and XML. Use Composer 2 and MariaDB 11.4. Phase 2 was verified against PHP 8.4.28 with mysqlnd 8.4.28, Composer 2.11.3, Laravel 13.44.1 and MariaDB 11.4.14. The `mariadb` connection uses native PDO parameter binding, InnoDB, strict SQL, `utf8mb4_unicode_ci` and a UTC session timezone.

## Local MariaDB

The Windows workstation uses the portable toolchain at `C:\Users\krzyc\Projects\kancelaria-jp-toolchain` and MariaDB listening on `127.0.0.1:3314`. Its database files and private credential file are in `mariadb-kancelaria-jp` under that directory. The initialized application schema is `kancelaria_jp` and its dedicated user is `kjp_app`. The `kjp_test` account can create and drop only the test-schema namespace.

On a fresh workstation, extract the official MariaDB 11.4 Windows ZIP outside the repository and initialize an empty, private data directory with the ZIP's `mariadb-install-db.exe --datadir=<path> --port=3314 --password=<new-local-root-password>`. Use a unique root password and omit the `--service` option to run it as an ordinary process. Verify the archive checksum before extracting. [Portable MariaDB ZIP installation](https://mariadb.com/docs/server/server-management/install-and-upgrade-mariadb/installing-mariadb/binary-packages/installing-mariadb-windows-zip-packages)

Start the existing workstation instance in a separate foreground PowerShell window with:

```powershell
$toolchain = 'C:\Users\krzyc\Projects\kancelaria-jp-toolchain'
$bundle = Join-Path $toolchain 'mariadb-11.4.14-winx64'
$instance = Join-Path $toolchain 'mariadb-kancelaria-jp'
& (Join-Path $bundle 'bin\mariadbd.exe') --no-defaults --console "--basedir=$bundle" `
    "--datadir=$(Join-Path $instance 'data')" --bind-address=127.0.0.1 --port=3314 `
    --character-set-server=utf8mb4 --collation-server=utf8mb4_unicode_ci
```

To stop the existing workstation instance cleanly, run the following with the same variables and private root option file:

```powershell
& (Join-Path $bundle 'bin\mariadb-admin.exe') "--defaults-file=$(Join-Path $instance 'root-client.ini')" `
    --protocol=tcp --host=127.0.0.1 --port=3314 shutdown
```

Provision a new instance using an administrator connection to that isolated server. Replace both password placeholders with independently generated secrets:

```sql
CREATE DATABASE kancelaria_jp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'kjp_app'@'127.0.0.1' IDENTIFIED BY '<application-password>';
CREATE USER 'kjp_test'@'127.0.0.1' IDENTIFIED BY '<test-password>';
GRANT ALL PRIVILEGES ON `kancelaria\_jp`.* TO 'kjp_app'@'127.0.0.1';
GRANT ALL PRIVILEGES ON `kancelaria\_jp\_test\_%`.* TO 'kjp_test'@'127.0.0.1';
```

## Setup, migration and startup

From the repository root, select PHP 8.4 and install the Composer lockfile. On Windows:

```powershell
$toolchain = 'C:\Users\krzyc\Projects\kancelaria-jp-toolchain'
$env:PHPRC = Join-Path $toolchain 'php84'
$env:PHP_BIN = Join-Path $env:PHPRC 'php.exe'
if (!(Test-Path -LiteralPath '.env')) {
    Copy-Item -LiteralPath '.env.example' -Destination '.env'
}
& $env:PHP_BIN (Join-Path $toolchain 'composer.phar') install --prefer-dist --no-interaction
& $env:PHP_BIN (Join-Path $toolchain 'composer.phar') check-platform-reqs
if (Select-String -LiteralPath '.env' -Pattern '^APP_KEY=$' -Quiet) {
    & $env:PHP_BIN artisan key:generate
}
```

Set the `DB_*` values in the ignored `.env` to the dedicated application schema and account. Keep `APP_DEBUG=false` whenever others can reach the preview. With MariaDB running, use the following on a new database:

```powershell
& $env:PHP_BIN artisan migrate
& $env:PHP_BIN artisan site:import-content --dry-run
& $env:PHP_BIN artisan site:import-content
& $env:PHP_BIN artisan serve --host=127.0.0.1 --port=8743
```

On macOS or Linux, use the equivalent `composer install`, `composer check-platform-reqs`, `php artisan key:generate` and `php artisan ...` commands with your installed PHP 8.4 and a dedicated MariaDB instance. Open `http://127.0.0.1:8743`. Press Ctrl+C to stop a foreground PHP development server.

## Import and schema

`site:import-content --dry-run` validates all five JSON files and reports planned counts without connecting to MariaDB. Run `site:import-content` after migrations against an empty schema. The importer locks the schema for concurrent imports and writes the four datasets in one transaction. It refuses a populated table, including soft-deleted records, and rolls back partial writes on error. The reviewed source contains 3 articles, 13 specializations, 23 profiles and 0 offers.

| Table and model | Content columns |
| --- | --- |
| `articles` / `Article` | Unique ASCII slug `VARCHAR(191)`, title, category, author and image metadata `VARCHAR(255)`, summary `TEXT`, body `LONGTEXT`, nullable date-only `DATE`, status enum. |
| `specializations` / `Specialization` | Unique ASCII slug `VARCHAR(191)`, title and category `VARCHAR(255)`, summary and optional homepage summary `TEXT`, body and scope `JSON`, active flag, unsigned display order. |
| `team_members` / `TeamMember` | Name, displayed role and optional portrait `VARCHAR(255)`, explicit group enum, homepage partner flag, unsigned display order. |
| `job_offers` / `JobOffer` | Nullable title `VARCHAR(255)`, optional description and URL `TEXT`, status enum, unsigned display order. Open offers require a title. |

Each table has a bigint ID, `created_at` and `updated_at` UTC timestamps, and a nullable `deleted_at` timestamp for recoverable deletion. The JSON values in specializations are ordered lists. Team group and homepage partner eligibility are stored independently. Ordered lists use display order then ID. Publication lists sort newest first, undated items follow dated ones, and equal dates retain insertion order by ascending ID.

`draft` means an article has never been public, `published` is public, and `unpublished` is withdrawn and can be republished. The article model locks the slug once a record is published or withdrawn. Reserved application paths are rejected by the importer and model. A soft-deleted slug stays reserved by the unique index. Job offers are public only while `open`. Offers marked `closed` or `draft` remain private. Soft-deleted articles, specializations, team profiles and offers are excluded from public queries.

## Editorial HTML boundary

The importer preserves reviewed `body_html` byte for byte. Current publications use headings, paragraphs, line breaks, lists, blockquotes, emphasis, links, figures and images. Existing links use `href`. Images use `src`, `width`, `height`, `alt` and `loading`. Generated article navigation uses anchors and heading IDs. Tables, captions and their scroll wrappers are supported and regression-tested.

See Article administration below for the Phase 3 write and render controls.

## Regression checks

Install Node.js 22 or newer and Chrome. Set `PHP_BIN` and `CHROME_BIN` as needed. Set these variables explicitly before running database-backed tests:

```powershell
$instance = Join-Path $toolchain 'mariadb-kancelaria-jp'
$credentials = Get-Content -Raw -LiteralPath (Join-Path $instance 'local-credentials.json') | ConvertFrom-Json
$env:TEST_DB_HOST = '127.0.0.1'
$env:TEST_DB_PORT = '3314'
$env:TEST_DB_USERNAME = 'kjp_test'
$env:TEST_DB_PASSWORD = $credentials.test
node tests/run.mjs
```

Each run creates random `kancelaria_jp_test_<hex>` schemas using the isolated account, runs migrations and imports fixtures as needed, then drops only schemas it created. The harness rejects missing test credentials, port 3306 and servers outside the MariaDB 11.4 series. It uses disposable files and an explicit test connection. GitHub Actions pins its own MariaDB 11.4.13 service image and generates a masked ephemeral test password. Tests cover real MariaDB operations, route/config caching, public visibility, search, filtering, browser interactions and disabled contact submissions.

Run `php tests/content.php`, `node --test tests/database.test.mjs`, `node --test tests/http.test.mjs`, and `node --test tests/browser.test.mjs` individually when needed.

Use `node tests/snapshots.mjs test-artifacts/phase2-mariadb --details --stable` for visual comparisons. Export `cca68503e4666e7c268e448c3c2d9d4821844e5c` to a temporary directory with `git archive`, install its locked dependencies there, and run the same capture command with a separate output directory. The 33 views use 1440×900 and 390×844. The six homepage stages use regular motion and the internal layouts use reduced motion for stable comparison.

## Hosting and deferred work

Serve only Laravel's `public/` directory. Verify dhosting's PHP CLI and web runtime, `pdo_mysql`, MariaDB 11.4 credentials and TLS settings, write access to `storage/` and `bootstrap/cache/`, HTTPS, rewrites for `/sitemap.xml`, route/config caching, and backup/restore in staging. The hosting account has yet to be verified. [dhosting hosting capabilities](https://dhosting.pl/elastyczny-hosting) · [Laravel database configuration](https://laravel.com/docs/13.x/database)

Back up the persistent MariaDB schema before deployments that run migrations. Include the database in restore rehearsals because Git contains the original import, while live edits exist in MariaDB. Future deployments run forward migrations and use the existing content. Reserve `site:import-content` for first-time provisioning of empty schemas and avoid reset or reimport operations on a live schema.

Kontakt retains its disabled form, telephone and email links. Direct POSTs return HTTP 405. Enquiry storage, email delivery, production recovery, mandatory MFA and deployment are deferred.


## Phase 3 administration

Laravel serves the public site and the Filament panel from the same application and MariaDB schema. The Composer lockfile uses Laravel 13.44.1, Filament 5.12.0, Livewire 4.4.10 and Symfony HTML Sanitizer 8.1.12. Composer publishes the locked panel assets without a Node build. The panel reuses the approved prototype palette, CSS, navigation icons, account table and profile layout. This application replaces the prototype routing, article storage, preview rendering, account lock and upload controls.

| Environment variable | Local value | Production value |
| --- | --- | --- |
| PUBLIC_HOST | 127.0.0.1 | kancelaria-jp.pl |
| APP_URL | http://127.0.0.1:8743 | https://kancelaria-jp.pl |
| ADMIN_HOST | panel.localhost | panel.kancelaria-jp.pl |
| ADMIN_URL | http://panel.localhost:8743 | https://panel.kancelaria-jp.pl |
| ADMIN_TEST_RECOVERY | false | false |

Add the local settings to the ignored .env and bind the development server to 127.0.0.1 as described above. Chrome resolves panel.localhost on loopback without a hosts-file change. Use the public site at http://127.0.0.1:8743 and the panel at http://panel.localhost:8743. For staging, set separate matching host and origin pairs. HTTPS origins are required outside local and testing environments. Public canonical metadata continues to use the editorial origin in site.json.

Public routes are bound to PUBLIC_HOST. The panel uses ADMIN_HOST at its root and /login for authentication. The hostname boundary rejects other hosts before public redirects and session middleware. Forwarded host, scheme and IP headers are untrusted. The administrative Livewire route is domain-bound. The hostname middleware also covers the package's routes, including the default Livewire endpoint. Temporary uploads, generic import or export downloads and dynamic storage endpoints are unavailable. Private responses use no-store and noindex headers.

The database stores encrypted session payloads. The kjp_panel_session cookie is host-only, HttpOnly and SameSite=Lax, with Secure over HTTPS. Public pages run without session middleware. Laravel CSRF protection applies to the panel. When a write request supplies an Origin header it must match ADMIN_URL exactly. Login is rate-limited by client IP in a server-side file cache shared across sessions.

### Account setup and management

On an existing Phase 2 installation, run the forward migration using the PHP 8.4 interpreter. It creates users, sessions, password_reset_tokens and one admin_account_lock row. It leaves the imported content tables intact. Then provision the first administrator interactively.

~~~sh
php artisan migrate
php artisan panel:provision-administrator
~~~

The provisioning command prompts for an email and a hidden password with confirmation. The operator needs terminal access to the configured application and database. Administrators use Użytkownicy to create and edit accounts, change roles and deactivate or reactivate users. User records are independent of public team profiles. Employees start with no grants and can receive any combination of Artykuły, Specjalizacje, Zespół, Kariera and Wiadomości. Their first permitted section opens at login. Accounts without section grants land on Mój profil. The four non-article content sections are permission-checked placeholders.

An InnoDB row lock serializes account privilege changes and preserves at least one active administrator. Deactivation preserves records, and changes to privileges, email or active status invalidate existing sessions. Mój profil keeps email read-only and verifies the current password before changing the displayed name or password. A password change rotates the current session and invalidates other sessions. Database timestamps are UTC. Login times display in Europe/Warsaw.

For local recovery tests use disposable accounts with ADMIN_TEST_RECOVERY=true and MAIL_MAILER=array. Filament and the Laravel password broker issue signed administrative URLs with expiring tokens. The array transport keeps mail inside the process. Production invitation delivery, recovery procedures and mandatory MFA are release work. Filament provides native authenticator-app and email challenge support. Confirm enrollment, recovery and operator procedures before enabling production login. [Filament authentication documentation](https://filamentphp.com/docs/5.x/users/overview)

### Article administration

Artykuły uses the existing Article model. New records begin as drafts. Publish and withdraw are explicit actions. Changing a published article updates its public version on save and keeps its established address. The trash filter makes soft-deleted records recoverable. Previously published articles restored from trash remain withdrawn until published again. The public homepage, archive, related lists, sitemap and detail routes use published nondeleted articles.

Podgląd renders the public article template on the administrative hostname behind the article policy. Its navigation links return to the configured public origin. Symfony HTML Sanitizer applies an explicit allowlist at the editorial write boundary and on both public and private reads. The allowlist keeps legal editorial formatting, safe link schemes, figures, image attributes, tables and section IDs. It removes executable URLs, script, inline event attributes and embedded documents.

The importer retains the original HTML. In the editor, structures that cannot round-trip through Tiptap, such as figures, captioned tables and existing explicit anchors, appear between editable segments. On save the protected markup is reconstructed from the server-side record. Media uploading is deferred.

### Testing and hosting

Run node tests/run.mjs with the existing isolated MariaDB test account. The complete suite includes tests/panel.test.mjs. Set PANEL_SCREENSHOTS to an ignored local directory for administrative screenshots. Run composer check-platform-reqs and composer audit --locked. PHP Intl and ZIP are also required by the panel dependencies.

Point both DNS names at the hosting service and obtain HTTPS certificates for both. Configure both names to serve the same Laravel public/ directory with the same application and database. Set the exact configured origins, then run php artisan config:cache and php artisan route:cache. Confirm with dhosting that both domains can share a document root, that PHP observes the original Host and HTTPS state, and that unapproved technical or origin hostnames are rejected. Production hostname isolation must be verified on that hosting environment before release. [dhosting hosting capabilities](https://dhosting.pl/elastyczny-hosting)

## Branches and license

The Laravel migration is on `feature/laravel-migration`. `main` and the two fullscreen animation branches retain their existing histories. This is proprietary software. See [LICENSE](LICENSE). Asset licenses remain alongside their assets.
