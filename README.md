# EventSphere

EventSphere is a lightweight PHP-based events management prototype built for demos, learning, and small-scale event management. It uses simple XML storage (under /data) and includes features such as web UI, basic ticketing, PDF export (optional), and Google SSO for authentication. (THIS IS IN DEVELOPMENT)

---

## 🔎 Project Overview
- Language: PHP (procedural), HTML and minimal JS
- Storage: XML files in `data/` (e.g., `users.xml`, `events.xml`, `tickets.xml`)
- Frontend: TailwindCSS (CDN), Google Fonts, Lucide icons
- OAuth SSO: Google OAuth2 (OpenID Connect) integration

---

## 🚀 Quick Start (Local Development)
### Requirements
- PHP 8+ with cURL and OpenSSL extensions enabled
- Composer (optional, recommended for PHPMailer/TCPDF)
- Web server (XAMPP/Apache) or PHP built-in server

### Install Dependencies
If you plan to use email (PHPMailer) or PDF export (TCPDF), install composer packages:

Windows PowerShell (from project root):

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"; php composer-setup.php; php -r "unlink('composer-setup.php');"; php composer.phar install

Or using composer directly if installed:

composer install

### Run Locally (Recommended: PHP Built-in Server)
Open PowerShell and from the project root run:

php -S localhost:8000 -t .

Open in your browser: http://localhost:8000/eventsphere/

> Note: If you run on a different port or host, ensure the callback/redirect URI registered in Google Cloud Console matches exactly (including scheme, host, port, and path).

---

## ⚙️ Configuration (.env)
Create a `.env` file in the project root (NOT committed to Git). Example env variables:

```
APP_ENV=development
APP_DEBUG=1

GOOGLE_CLIENT_ID=your-google-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost/eventsphere/sso_callback.php
```

Important:
- Keep `.env` out of your repository and never commit secrets.
- If you accidentally committed secrets, rotate them immediately and remove from git history using tools like the BFG Repo-Cleaner or git filter-branch.

---

## 🔐 Google OAuth (SSO) Setup
1. Go to Google Cloud Console -> APIs & Services -> Credentials -> OAuth 2.0 Client IDs.
2. Create a new OAuth Client (Web application) and set the Authorized redirect URI to the callback file used by the app (default is `http://localhost/eventsphere/sso_callback.php`).
3. Copy the Client ID and Client Secret to your `.env` as `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`.

Important: The redirect URI must match exactly. If you run the dev server on port 8000 and the URI is `http://127.0.0.1:8000/eventsphere/sso_callback.php`, register that exact URI in Google Console.

---

## 🧭 How SSO Works in this App
- `sso_google.php`: Entry point that triggers the SSO flow. In development mode (`APP_DEBUG=1`) you can view the full built Google authorization URL at `/eventsphere/sso_google.php?debug=1`.
- `core/auth.php::ssoLogin`: Centralized SSO logic — builds the auth URL, handles the OAuth callback, exchanges code for tokens, fetches userinfo, and creates/links a local user record in `data/users.xml`.
- `sso_callback.php`: A simple wrapper that calls `$auth->ssoLogin('google', $envRedirect)` and redirects the user to `dashboard.php` or `admin.php` on success.
- `callback.php`: Legacy or alternative callback handler with hardcoded fallback values. We recommend using `sso_callback.php` and removing `callback.php` (or merging logic) to avoid duplicated secrets or outdated code.

---

## 🧪 Debugging and Troubleshooting (SSO)
- Debug the built OAuth URL: Navigate to `/eventsphere/sso_google.php?debug=1` (requires `APP_DEBUG=1`). Inspect the `redirect_uri` parameter there and ensure it matches the value registered in Google Console.
- Validate Redirect Reachability: If you are using a different host/port than `localhost`, ensure the redirect endpoint is reachable from the internet (for production) or matches your local dev host/port for development.
- Common Error: `redirect_uri_mismatch` — this occurs when the redirect URI used by the app does not match the URI registered in Google Cloud Console. Fix by updating the redirect URI in your `.env` and in Google Console.
- Ensure PHP session and cURL extensions are enabled, as they are used in the OAuth flow.

---

## 🔁 After SSO (user mapping)
The app uses a simple XML store under `/data/users.xml`. On successful SSO sign-in, `core/auth.php` looks up the user by email and calls `findOrCreateUserByEmail()` to map or create a local user record. This links the Google account to a local user entry and persists basic profile data needed by the app.

---

## ✅ Security Recommendations
- Do NOT commit `.env` to your repository.
- Remove any committed secrets from history and rotate credentials if needed.
- Replace `callback.php` hardcoded credential usage with environment variables and remove the secret from the code base.
- For production, replace the XML-based storage with a secure DB-backed store and use secure password storage and password reset flows.

---

## 🗂 Key Files & Locations
- `sso_google.php` — SSO entrypoint
- `sso_callback.php` — OAuth callback wrapper
- `core/auth.php` — Centralized login, registration logic, and `ssoLogin()` handler
- `core/env.php` — Minimal `.env` loader (safe if `load_dotenv_single()` is left unchanged)
- `data/users.xml` — User records (sensitive; add to `.gitignore`)
- `login.php` & `register.php` — UI pages including SSO buttons
- `admin.php` — Admin dashboard and admin-only features
- `callback.php` — Legacy callback (contains hardcoded secret — remove/replace)

---

## ♻️ Cleanup & Migration Steps (If you accidentally committed secrets)
1. Delete `.env` from the repo and add `.env` to `.gitignore`.
2. Use the BFG Repo-Cleaner or `git filter-branch` to remove the secret from Git history.
3. Rotate credentials in Google Cloud Console (create new client secret) and update `.env` in local/dev server.

---

## 🛠 Optional: Additions & Improvements
- Replace XML store with a DB (MySQL/Postgres) and use prepared statements and password hashing.
- Add unit tests and a test suite to validate key flows.
- Consolidate SSO callback logic into a single file and remove `callback.php` legacy code.
- Add email confirmation and password reset with PHPMailer (composer install required).

---

## 🧑‍💻 Contributing
Contributions are welcome. Please open a PR for major changes. For anything that affects security (secrets, API keys, etc.), please discuss before committing.

---

## 📄 License
MIT (or choose your license) — add a `LICENSE` file if desired.

---

Thanks for testing EventSphere! If you want, I can also:
- Add a `.env.example` file with recommended keys (no secrets)
- Create a small `tools/` script to validate Google redirect URIs
- Remove `callback.php` and consolidate SSO callback logic into a single canonical handler

If any of the suggestions above would be helpful, tell me which one to start and I will implement it next. 🎯

