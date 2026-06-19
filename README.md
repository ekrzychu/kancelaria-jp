# Jaroch-Konwent Pakos — PHP website concept

A full PHP website concept for a modern, formal, minimal law firm website.

## Run locally

```bash
php -S localhost:8000 -t public
```

Open `http://localhost:8000`.

## Structure

- `public/index.php` — front controller and router
- `app/templates/` — shared header/footer
- `app/pages/` — page templates
- `app/data/` — content stored as JSON
- `public/assets/` — CSS, JS, images and minimal utility icons
- `storage/contact-submissions.jsonl` — created when the contact form is submitted

## Design notes

- Main accent: `#1d0613`
- Secondary accent used sparingly: `#a38e76`
- Formal type system: Source Serif 4 for main reading/display, IBM Plex Sans for interface labels
- Minimal icons only in contact/footer/search contexts
- User-provided photographs are used as site imagery
- Article page is included but intentionally empty for now
