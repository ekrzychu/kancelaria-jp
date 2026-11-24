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

## Production notes

Before publication, the firm should approve all service descriptions, team details, contact information, privacy disclosures, formal documents, and third-party profile links. The contact workflow currently stores submissions in `storage/contact-submissions.jsonl`; production delivery can instead be connected to an approved mail service or CRM.

Runtime storage must be writable by PHP and inaccessible from the public web root. HTTPS is required in production.

## License

This is proprietary software. See [LICENSE](LICENSE).
