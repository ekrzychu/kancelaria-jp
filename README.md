# Jaroch-Konwent Pakos

Website for Jaroch-Konwent Pakos Kancelaria Radców Prawnych sp.k. The application uses server-rendered PHP, JSON-managed content, a single stylesheet, and vanilla JavaScript. It runs without a framework, database, or frontend build step.

## Project scope

- firm profile, team, services, career, publications, contact, and document pages;
- responsive editorial design with accessible navigation and reduced-motion support;
- searchable specialization directory and article archive;
- contact form with validation, CSRF protection, spam throttling, and local server-side storage;
- canonical metadata, social sharing metadata, sitemap, and crawler configuration.

## Architecture

- `public/index.php` — front controller and route definitions
- `public/router.php` — development-server routing for static files and application pages
- `app/bootstrap.php` — shared helpers, data loading, and contact-form handling
- `app/templates/` — shared page shell
- `app/pages/` — page views
- `app/data/` — structured website content
- `public/assets/` — styles, scripts, icons, and photography
- `storage/` — runtime-only form and rate-limit data

## Local development

PHP 8.2 is the tested baseline. The application uses JSON, sessions, and the standard filter functions. DOM with libxml enables generated article section anchors, the table of contents, and scrollable table wrappers. Enable it for development and CI. The application uses mbstring when available and has fallbacks for the text operations it needs.

In XAMPP, use a named Apache virtual host such as `kancelaria-jp.local` with the checkout’s `public/` directory as its document root. Resolve that name to `127.0.0.1` locally. Enable Apache’s rewrite module and allow the directives in `public/.htaccess`, which directs application routes to `public/index.php`. Start Apache and open the named host. Keep `app/`, `.git/`, and runtime files outside the paths served by any local host.

For a standalone local preview, run `php -S 127.0.0.1:8000 -t public public/router.php` from the repository root.

## Editing content

Run the content validator after editing JSON. It checks required fields, optional field types, publication dates, route slugs, supported URL schemes, and exact-case references to local assets.

- **Articles** in `articles.json` use `slug`, `title`, and reviewed `body_html`. Add optional `date` as `YYYY-MM-DD`, plus `category`, `summary`, or `author` as available. Keep published URLs stable at `/<slug>/`. The homepage selects up to three dated articles with a title and summary. Update `public/sitemap.xml` when adding or removing an indexable route.
- **Article images** are intentionally omitted from the archive. Individual articles render figures directly from `body_html`. Reference rights-cleared images under `/assets/img/` and provide suitable alternative text and dimensions. The optional `image` and `image_alt` fields are metadata.
- **Specializations** use `slug`, `title`, `category`, `summary`, `body`, and `scope` in `services.json`. The first eight entries supply the homepage preview, with optional `home_summary` overriding the directory summary there. Detail routes use `/specjalizacje/<slug>/`.
- **Team** entries in `team.json` use `name` and `role`. The team page groups entries by their displayed roles. An optional `portrait` is an existing image filename in `public/assets/img/`, with exact matching case.
- **Recruitment** listings come from `recruitment.json`. Use `status: "open"` and a title to display a vacancy, with an optional description and link. Closed and draft listings stay in the dataset. An empty open list displays the vacancy notice.
- **Office details and documents** are managed in `site.json`. Hours may vary by day. Document entries provide a slug, title, and summary. The current document detail view displays a publication placeholder.

## Regression checks

Use PHP 8.2. DOM with libxml is required by the content validator and the article anchor tests, and CI also enables mbstring.

Install Node.js 22 or newer and Google Chrome for local testing. The suite uses Node's built-in test runner and Chrome DevTools Protocol. It has no npm packages or PHP test framework. Use `PHP_BIN` and `CHROME_BIN` to select executables if needed. For XAMPP in PowerShell, for example, set `$env:PHP_BIN = 'H:\xampp\php\php.exe'`.

From the repository root:

```sh
node tests/run.mjs
node tests/check.mjs
php tests/content.php
node --test tests/http.test.mjs
node --test tests/browser.test.mjs
```

The first command runs the complete suite. The remaining commands run syntax checks, content checks, HTTP and enquiry tests, and browser tests individually. For the direct PHP command, use the same PHP binary as the suite. The content validator accepts optional editorial metadata and checks the `open`, `closed`, and `draft` vacancy statuses.

The browser checks for A11Y-01 and UX-01 reproduce the focused skip-link and specialization search defects against the audited source.

HTTP and browser tests serve a temporary copy of `app/` and `public/` on a loopback-only PHP server. Sessions, synthetic enquiries, and rate-limit state are confined to that copy. Tests stop the server and remove the copy afterwards. The contact coverage describes the current workflow. Array-shaped input, throttle-store failures, crash durability, and deployment session settings remain part of the deferred SEC-03 and SEC-04 hardening work.

Capture visual reference images with `node tests/snapshots.mjs <output-directory>`. Use an external directory or the ignored `test-artifacts/` directory. The command captures the six homepage stages and eight representative pages at 1440×900 and 390×844, including an isolated Kontakt page. Compare captures with matching Chrome, viewport, fonts, device scale, and scroll state. The Stage 3 reference is the approved `eab7edfff97e8ac1f300fb43e7f98938916ca5bf` baseline. The browser suite separately asserts stage progression, reduced motion, navigation, and interactive states.

GitHub Actions runs the four check categories on pushes and pull requests targeting `main` or `feature/internal-pages`. It uses PHP 8.2, Node 22, and the Ubuntu runner's Chrome, with read-only repository permissions.

## Production notes

The contact form appends submissions to `storage/contact-submissions.jsonl` and records throttle state in `storage/contact-rate-limit.json`. The current confirmation refers to a local write. Operational collection, email or CRM delivery, private storage access, retention, and backup and recovery need to be agreed before opening intake. Keep credentials and enquiry files out of Git.

Runtime storage must be writable by PHP and inaccessible from the public web root. A production rollout still requires verification of HTTPS, sessions, proxy behavior, error handling, and storage permissions, plus approval of the privacy notice and other public content. The homepage client-logo permissions and experience figure require owner confirmation.

## Branches

`main` is the default branch. The approved internal pages and current engineering work are on `feature/internal-pages`. The `continue-fullscreen-animation` and `collapse-fullscreen-animation` branches preserve the earlier homepage variants for reference and comparison.

## License

This is proprietary software. See [LICENSE](LICENSE).
