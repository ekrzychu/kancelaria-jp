# Jaroch-Konwent Pakos

Website for Jaroch-Konwent Pakos Kancelaria Radców Prawnych sp.k. The project is a lightweight PHP 8 application with server-rendered pages, JSON-managed content, and no framework, database, optional PHP extension, or build-step dependency.

## Project scope

- firm profile, team, services, career, publications, contact, and document pages;
- responsive editorial design with accessible navigation and reduced-motion support;
- searchable service directory;
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

The web server must use `public/` as its document root and direct application routes to `public/index.php`.

## Editorial media

- Team entries in `app/data/team.json` can specify an optional `portrait` filename. Entries without a portrait use name and role alone.
- Publication entries in `app/data/articles.json` use `title` and may supply `date` in ISO format (`YYYY-MM-DD`), `category`, `summary`, `image` and `image_alt`. An empty archive displays the publication notice.
- Optional image filenames point to existing AVIF, JPEG, PNG or WebP files in `public/assets/img/`. Use rights-cleared images. About and Contact use the firm's existing building and reception photographs.

## Regression checks

The test baseline uses PHP 8.2 with JSON, sessions, and DOM (including libxml). DOM is required for the content validator and article anchor assertions. The public article template can render without DOM, with generated contents and table enhancements available when DOM is enabled. The application uses mbstring when available and has fallbacks.

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

Capture visual reference images with `node tests/snapshots.mjs <output-directory>`. Choose a directory outside the repository. The command captures the six homepage stages and eight representative pages at desktop and mobile widths, including an isolated Kontakt page. Use the same Chrome version, operating system, and display settings when comparing captures to the audited reference at `c6c7a1804b2ab70b9dbfedfc19239aff13c475cc`. The browser suite separately asserts stage progression, reduced motion, navigation, and interactive states.

GitHub Actions runs the four check categories on pushes and pull requests targeting `main` or `feature/internal-pages`. It uses PHP 8.2, Node 22, and the Ubuntu runner's Chrome, with read-only repository permissions.

## Production notes

Before publication, the firm should approve all service descriptions, team details, contact information, privacy disclosures, formal documents, and third-party profile links. The contact workflow currently stores submissions in `storage/contact-submissions.jsonl`; production delivery can instead be connected to an approved mail service or CRM.

Runtime storage must be writable by PHP and inaccessible from the public web root. HTTPS is required in production.

## License

This is proprietary software. See [LICENSE](LICENSE).
