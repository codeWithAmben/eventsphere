# EventSphere (Clean)

EventSphere is a lightweight PHP-based events management prototype for demos and learning.

## Quick Summary

- Language: PHP (procedural)
- Storage: XML under `data/`
- UI: TailwindCSS + Google Fonts + Lucide icons
- SSO: Google OAuth2 (OpenID Connect)

## Dev quick start

1. Copy `.env.example` → `.env` and set your Google credentials.
2. (Optional) `composer install`.
3. `php -S localhost:8000 -t .` and visit `http://localhost:8000/eventsphere/`.

## SSO paths

- `sso_google.php` — start oauth
- `sso_callback.php` — canonical callback

## Notes

- Keep `.env` out of Git.
- Remove any committed secrets from history and rotate them.

---

This is a minimal canonical README. Replace the main README with this if you prefer a concise version.
