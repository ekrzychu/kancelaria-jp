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

## Production notes

Before publication, the firm should approve all service descriptions, team details, contact information, privacy disclosures, formal documents, and third-party profile links. The contact workflow currently stores submissions in `storage/contact-submissions.jsonl`; production delivery can instead be connected to an approved mail service or CRM.

Runtime storage must be writable by PHP and inaccessible from the public web root. HTTPS is required in production.

## License

This is proprietary software. See [LICENSE](LICENSE).
