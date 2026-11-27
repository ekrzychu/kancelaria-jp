# Jaroch-Konwent Pakos

Public website and content administration for Jaroch-Konwent Pakos Kancelaria Radców Prawnych sp.k. Laravel 13 serves Blade views with the approved CSS, vanilla JavaScript, photography and fonts. The public site has no frontend build step.

## Architecture and content ownership

- `routes/web.php` and `app/Http/` define public routes, HTTP behavior and the database-aware `/sitemap.xml`.
- `app/Content/SiteContent.php` reads editable content through four Eloquent models and preserves the existing Blade data shapes. `PublicationContent` prepares publication lists, anchor navigation and table wrappers.
- MariaDB is authoritative for articles, specializations, team profiles and job offers. Changes made through the database appear on the corresponding public pages without a deployment. A database failure returns a generic HTTP 503 for affected pages.
- `app/data/site.json` is authoritative for firm identity, navigation, contact details, office hours, the canonical origin, social links and static documents.
- `app/data/{articles,services,team,recruitment}.json` are reviewed migration inputs and regression fixtures. Editors manage current content through Filament.
- `resources/views/` and `public/` contain the approved public templates and assets. `storage/` and `bootstrap/cache/` are private writable runtime directories.

Phase 2 preserves the approved Phase 1 baseline `cca68503e4666e7c268e448c3c2d9d4821844e5c` and its 27 public pages. The sitemap preserves the canonical paths, includes published articles and active specializations, and excludes documents and deleted or hidden content. Laravel serves it directly through a cacheable route.

## Requirements

Use PHP 8.4 or 8.5 with `pdo_mysql` and the standard Laravel extensions, including cURL, DOM, GD with JPEG, PNG and WebP support, libxml, mbstring, OpenSSL and XML. Use Composer 2 and MariaDB 11.4 or 12.3. Phase 2 was verified against PHP 8.4.28 with mysqlnd 8.4.28, Composer 2.11.3, Laravel 13.44.1 and MariaDB 11.4.14. The `mariadb` connection uses native PDO parameter binding, InnoDB, strict SQL, `utf8mb4_unicode_ci` and a UTC session timezone.

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

On macOS or Linux, use the equivalent `composer install`, `composer check-platform-reqs`, `php artisan key:generate` and `php artisan ...` commands with a supported PHP version and a dedicated MariaDB instance. Open `http://127.0.0.1:8743`. Press Ctrl+C to stop a foreground PHP development server.

### Native Arch Linux

Use the Arch packages `php`, `php-gd`, `composer` and `mariadb-lts` with the supported versions above. In `/etc/php/php.ini`, enable `curl`, `gd`, `intl`, `pdo_mysql` and `zip`. Run `composer install` and `composer check-platform-reqs` to verify the locked dependencies against the enabled extensions.

Initialize a private MariaDB data directory with `mariadb-install-db` and run `mariadbd` bound to `127.0.0.1` on a separate port such as `3314`. Use the provisioning SQL above for the application and test accounts. Copy `.env.example` to `.env` if needed, set its `DB_*` values for `kjp_app`, and follow the migration and startup commands above.

For regressions, use Node.js 22 or newer and Chrome or Chromium. Set `CHROME_BIN=/usr/bin/chromium` when using Arch Chromium. On the test PHP CLI, use `upload_max_filesize=10M`, `post_max_size=12M` and `memory_limit=384M` to match CI. Export `TEST_DB_HOST=127.0.0.1`, `TEST_DB_PORT=3314`, `TEST_DB_USERNAME=kjp_test` and `TEST_DB_PASSWORD` from your private test credentials, then run `node tests/run.mjs`.

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

Each run creates random `kancelaria_jp_test_<hex>` schemas using the isolated account, runs migrations and imports fixtures as needed, then drops only schemas it created. The harness rejects missing test credentials, port 3306 and servers outside the MariaDB 11.4 and 12.3 series. It uses disposable files and an explicit test connection. GitHub Actions uses PHP 8.4, pins its own MariaDB 11.4.13 service image and generates a masked ephemeral test password. CI invokes the same runner and groups its output by suite. Set PANEL_SCREENSHOTS, CONTENT_ADMIN_SCREENSHOTS or MEDIA_SCREENSHOTS to an ignored directory to capture panel screenshots.

Run `php tests/content.php`, `node --test tests/database.test.mjs`, `node --test tests/http.test.mjs`, `node --test tests/browser.test.mjs` or `node --test tests/contact.test.mjs` individually when needed.

Use `node tests/snapshots.mjs test-artifacts/phase2-mariadb --details --stable` for visual comparisons. Export `cca68503e4666e7c268e448c3c2d9d4821844e5c` to a temporary directory with `git archive`, install its locked dependencies there, and run the same capture command with a separate output directory. The 33 views use 1440×900 and 390×844. The six homepage stages use regular motion and the internal layouts use reduced motion for stable comparison.

## Production deployment

The dhosting environment still needs staging verification. Use this release checklist. [dhosting hosting capabilities](https://dhosting.pl/elastyczny-hosting)

1. Point both hostnames at Laravel's `public/` directory with HTTPS. Set the exact public and admin origins from the table below. Confirm PHP sees the original host, HTTPS state and client IP. Reject other hostnames and keep Kontakt outside shared caching. Check redirects and the `/sitemap.xml` rewrite.
2. Verify the supported PHP CLI and web extensions, the MariaDB connection and TLS settings, and write access to `storage/`, `bootstrap/cache/` and the [persistent media directory](#article-images-and-team-portraits). Install the locked dependencies and run `composer check-platform-reqs` and `composer audit --locked`.
3. Back up MariaDB and managed media together and verify a restore. Run `php artisan migrate --force` before serving the release. Use `site:import-content` only on first-time provisioning of an empty content schema.
4. Set `APP_ENV=production`, `APP_DEBUG=false`, `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_SCHEME`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` and `CONTACT_MAIL_TO=kontakt@kancelaria-jp.pl`. Authenticate as the dedicated dhosting mailbox with `smtps` on 465 or `smtp` on 587 with required STARTTLS. Verify routing to OVH, SPF/DKIM/DMARC and the existing OVH MX. Verify receipt and visitor Reply-To in Outlook during an authorized release test. Local and CI runs use the in-memory transport. [dhosting SMTP settings](https://dhosting.pl/pomoc/baza-wiedzy/porty-poczty-i-szyfrowanie-smtp-587-i-465-imap-993-oraz-pop3-995/)
5. Run `php artisan config:cache` and `php artisan route:cache`. Schedule `php artisan contact:deliver` every minute using the deployment PHP CLI and application directory. Each run processes up to 20 due messages. Monitor failed and uncertain deliveries in Wiadomości.
6. Approve the [contact privacy notice and retention procedure](#contact-submissions-and-wiadomości). Production invitations, recovery procedures and mandatory MFA remain release work.

## Administration

Laravel serves the public site and the Filament panel from the same application and MariaDB schema. The Composer lockfile uses Laravel 13.44.1, Filament 5.12.0, Livewire 4.4.10 and Symfony HTML Sanitizer 8.1.12. Composer publishes the locked panel assets without a Node build. The panel reuses the approved prototype palette, CSS, navigation icons, account table and profile layout. This application replaces the prototype routing, article storage, preview rendering, account lock and upload controls.

| Environment variable | Local value | Production value |
| --- | --- | --- |
| PUBLIC_HOST | 127.0.0.1 | kancelaria-jp.pl |
| APP_URL | http://127.0.0.1:8743 | https://kancelaria-jp.pl |
| ADMIN_HOST | panel.localhost | panel.kancelaria-jp.pl |
| ADMIN_URL | http://panel.localhost:8743 | https://panel.kancelaria-jp.pl |
| ADMIN_TEST_RECOVERY | false | false |

Add the local settings to the ignored .env and bind the development server to 127.0.0.1 as described above. Chrome resolves panel.localhost on loopback without a hosts-file change. Use the public site at http://127.0.0.1:8743 and the panel at http://panel.localhost:8743. For staging, set separate matching host and origin pairs. HTTPS origins are required outside local and testing environments. Public canonical metadata continues to use the editorial origin in site.json.

Public routes are bound to PUBLIC_HOST. The panel uses ADMIN_HOST at its root and /login for authentication. The hostname boundary rejects other hosts before public redirects and session middleware. Forwarded host, scheme and IP headers are untrusted. The administrative Livewire route is domain-bound. The hostname middleware also covers the package's routes, including the default Livewire endpoint. Generic Livewire uploads, import or export downloads and dynamic storage endpoints are blocked. Purpose-specific image routes are documented below. Private responses use no-store and noindex headers.

The database stores encrypted session payloads. The kjp_panel_session cookie is host-only, HttpOnly and SameSite=Lax, with Secure over HTTPS. Public pages run without session middleware except Kontakt, which uses a session for CSRF and confirmation. Laravel CSRF protection also applies to the panel. When a panel write request supplies an Origin header it must match ADMIN_URL exactly. Login is rate-limited by client IP in a server-side file cache shared across sessions.

### Account setup and management

On an existing Phase 2 installation, run the forward migration using the PHP 8.4 interpreter. It creates users, sessions, password_reset_tokens and one admin_account_lock row. It leaves the imported content tables intact. Then provision the first administrator interactively.

~~~sh
php artisan migrate
php artisan panel:provision-administrator
~~~

The provisioning command prompts for an email and a hidden password with confirmation. The operator needs terminal access to the configured application and database. Administrators use Użytkownicy to create and edit accounts, change roles and deactivate or reactivate users. User records are independent of public team profiles. Employees start with no grants and can receive any combination of Artykuły, Specjalizacje, Zespół, Kariera and Wiadomości. Their first permitted section opens at login. Accounts without section grants land on Mój profil.

An InnoDB row lock serializes account privilege changes and preserves at least one active administrator. Deactivation preserves records, and changes to privileges, email or active status invalidate existing sessions. Mój profil keeps email read-only and verifies the current password before changing the displayed name or password. A password change rotates the current session and invalidates other sessions. Database timestamps are UTC. Login times display in Europe/Warsaw.

For local recovery tests use disposable accounts with ADMIN_TEST_RECOVERY=true and MAIL_MAILER=array. Filament and the Laravel password broker issue signed administrative URLs with expiring tokens. The array transport keeps mail inside the process. Production invitation delivery, recovery procedures and mandatory MFA are release work. Filament provides native authenticator-app and email challenge support. Confirm enrollment, recovery and operator procedures before enabling production login. [Filament authentication documentation](https://filamentphp.com/docs/5.x/users/overview)

### Article administration

Artykuły uses the existing Article model. New records begin as drafts. Publish and withdraw are explicit actions. Changing a published article updates its public version on save and keeps its established address. The trash filter makes soft-deleted records recoverable. Previously published articles restored from trash remain withdrawn until published again. The public homepage, archive, related lists, sitemap and detail routes use published nondeleted articles.

Podgląd renders the public article template on the administrative hostname behind the article policy. Its navigation links return to the configured public origin. Symfony HTML Sanitizer applies an explicit allowlist at the editorial write boundary and on both public and private reads. The allowlist keeps legal editorial formatting, safe link schemes, figures, image attributes, tables and section IDs. It removes executable URLs, script, inline event attributes and embedded documents.

The importer retains the original HTML. In the editor, structures that cannot round-trip through Tiptap, such as figures, captioned tables and existing explicit anchors, appear between editable segments. On save the protected markup is reconstructed from the server-side record. Manage new inline images through the image controls described below.

### Specialization, team and recruitment administration

Each resource uses its existing MariaDB content table and its own section permission. The same permissions apply to navigation, direct routes and Livewire actions.

- **Specjalizacje** starts new records inactive. Activate or deactivate a record from its row. The address is generated from the title on creation and stays fixed after saving. The detailed description and service scope are ordered, plain-text lists. Leave the homepage description empty to use the short description. Restoring a record leaves it inactive.
- **Zespół** requires an explicit public group. Saving a profile makes it public. Wspólnik na stronie głównej nominates a profile for one of two homepage slots, selected by display order. Existing portrait associations can be removed. To offer a portrait for selection, list its approved filename and label in config/site.php under team_portraits. The file must already exist in public/assets/img. The team editor also accepts managed portrait uploads. Restoring a profile makes it public again.
- **Kariera** saves new offers as drafts. Opublikuj and Wznów rekrutację validate and save pending editor changes before making the offer public. Zakończ rekrutację removes it from the public list. New links accept HTTP(S) only. Restored open offers are closed until explicitly reopened.

Use Przesuń wyżej or Przesuń niżej under Więcej to move a record one position at a time, including across page boundaries. For team members, moves stay within the same group, and the team group filter remains available. Clear search, status, category and trash filters to move records. Więcej also contains Przenieś do kosza and Przywróć. Content writes are serialized per schema and reordering runs in a transaction.


### Article images and team portraits

Use Zdjęcia w artykule to upload one or more images and edit their alternative text, caption and placement. Placement refers to the saved article blocks. Save the text first to make new blocks available in the placement list. Images assigned to the same place follow the list order. Removing a block removes its reference after saving. Existing reviewed figures remain in their original markup.

Use Prześlij portret on a team profile to preview a replacement. Saving an uploaded portrait replaces the former association. Clear the uploaded portrait to remove it or to select an approved existing portrait. A profile can be saved without an image.

Both editors accept JPEG, PNG and WebP up to 10 MB, 24 megapixels and 12,000 pixels on either edge. PHP GD checks and decodes the input, applies EXIF orientation and writes WebP at quality 85. Widths are capped at 1920 px for articles and 1200 px for portraits. Smaller images retain their size. The output preserves aspect ratio and transparency, and re-encoding removes metadata. Animated images and other formats are rejected.

The upload and private preview routes run only on the administrative hostname, with an active account, current session and the relevant section permission. Uploads require CSRF protection. Temporary images belong to their uploader and account version until attached, and image IDs cannot be reused across records or purposes. Public delivery checks the saved reference and current article or profile visibility, including soft deletion. Image responses use no-store. Previously downloaded copies remain outside the application's control.

Files are stored outside public/, in storage/app/media by default. Set MEDIA_STORAGE_ROOT to an absolute, writable directory that survives deployments. MariaDB stores the IDs, dimensions, owner and purpose. Keep the database and media files together in backups and restore both from the same point in time. Existing Git-managed assets remain separate.

Unattached uploads expire after 24 hours. Use the command without flags to count expired uploads, and use --apply to remove them. A periodic PHP CLI cron job is sufficient. Bound files are retained after replacement, removal and soft deletion to avoid losing recoverable data. The cleanup command never removes bound files. Any future permanent cleanup must check database references, including soft-deleted records, and verify backups.

~~~sh
php artisan media:clean-temporary
php artisan media:clean-temporary --apply
~~~

Enable PHP GD with JPEG, PNG and WebP support in both the web runtime and CLI. Configure upload_max_filesize to at least 10M, post_max_size to at least 12M, memory_limit to at least 384M and allow PHP to write to its temporary upload directory and the persistent media directory. Use php --ini and php -i to verify the loaded configuration. On the current portable Windows workstation, the loopback server uses an ignored storage/app/phase5-runtime/media.ini override through PHP_INI_SCAN_DIR. Set the same scan directory for local Composer or Artisan commands. Confirm these settings and the storage path on dhosting before deployment. This phase verifies the local and CI environments.

### Contact submissions and Wiadomości

Kontakt validates and stores the visitor's name, email, optional phone and message in MariaDB. The confirmation means the submission was saved. Laravel checks CSRF, rejects a filled honeypot, and allows five submission attempts per minute per client IP. Keep Kontakt outside any shared page cache and make sure PHP sees the client IP on the deployed host.

Administrators and active employees with the Wiadomości grant can search by sender name or email and open the private details. Opening an unread message marks it as read. Staff can mark it unread, archive it, or queue an eligible delivery retry. The panel does not offer edits, deletion or export.

The initial delivery attempt and up to two automatic retries use the same Message-ID. Failures wait five and ten minutes before retrying. Staff can queue another attempt in the panel. Wysłana means the configured transport accepted the email. An interrupted attempt becomes Wymaga sprawdzenia after 30 minutes. Check the recipient inbox before retrying because SMTP may already have accepted it.

Keep submissions, mail copies, backups and SMTP credentials restricted to authorized staff and operators. Keep request bodies, SQL bindings and mail payloads out of logging and tracing. The application emits counts for delivery and does not record transport exception text. Set `CONTACT_RETENTION_DAYS` to the approved period after archiving and run `php artisan contact:retention` to count eligible archived records. Zero disables the report. No command automatically deletes correspondence. Before launch, approve the retention procedure for MariaDB, OVH mail and backups, and confirm the privacy notice reflects the actual processing.

### Phase 7A editorial workflows

The panel uses the public monogram, Source Serif headings and a compact Filament interface. Article writing and illustrations occupy the main editor column. Publication metadata stays in its own section. Podgląd shows the saved article in the public template with computer and phone widths. The embedded preview remains private to the administrative origin. Save a new draft to preview it. The panel warns about unsaved changes when leaving an editor. Crash recovery is deferred pending private, versioned draft storage and conflict handling.

Use Opublikuj to publish on creation or Zapisz szkic to save a private draft. Successful saves and publishes return to the corresponding resource list. Internal article and specialization addresses are generated from the title and use numbered suffixes for collisions. Administrators can choose a custom address on creation and edit an unpublished article draft’s address. Published article addresses and saved specialization addresses stay stable.

Na stronie głównej selects up to eight active specializations in their existing order. Its migration selects the same first eight active, non-deleted entries previously displayed. Mój profil supports a private account photo with local initials as the fallback. Avatar uploads use the existing private media storage and owner checks. Run the forward migrations after backing up MariaDB and managed media. Release validation uses simulated contact email and disposable data.

Design references used were the editorial layout and navigation in [Craft CMS](https://craftcms.com/docs/5.x/system/control-panel.html) and the [Statamic publish screen](https://www.statamic.com/blog/statamic-6-sneak-peek), responsive preview controls from [Contentful](https://www.contentful.com/help/content-preview/), distinct draft and publish actions from [Strapi](https://docs.strapi.io/cms/features/draft-and-publish), and direct recovery copy from the [GOV.UK Design System](https://design-system.service.gov.uk/patterns/service-unavailable-pages/).

## Branches and license

The Laravel migration is on `laravel-migration`. `main` and the two fullscreen animation branches retain their existing histories. This is proprietary software. See [LICENSE](LICENSE). Asset licenses remain alongside their assets.
