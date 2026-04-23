# Authentication Portfolio Project

A PHP authentication project showcasing solid backend practices, security, and code organization in a realistic login flow.

## What this project demonstrates

- Login with credential validation and session control.
- First-access flow with mandatory password change.
- Password recovery with single-use, expiring tokens.
- Basic security measures:
  - CSRF protection on sensitive forms.
  - Session hardening (`HttpOnly`, `SameSite`, `Secure` on HTTPS, `session_regenerate_id`).
  - Reset tokens stored as hashes in the database.
  - Basic rate limiting for login and recovery.

## Project layout

- `public/`: Pages and HTTP handlers.
- `public/assets/`: CSS and JavaScript.
- `src/Security/auth_functions.php`: Auth and security orchestration.
- `src/Support/connection.php`: Shared PDO connection.
- `src/Repositories/`: Data access.
- `src/Services/`: Business rules.
- `config/`: Application configuration.
- `database/schema.sql`: Initial schema.
- `database/seeds/create_demo_user.php`: Demo user seed.
- `scripts/install_mailhog_laragon.ps1` / `scripts/start_mailhog.bat`: Local MailHog (see section below).
- `tests/run_tests.php`: Automated tests for critical flows.

## Local setup

1. Copy `config/config.example.php` to `config/config.php` and set your credentials.
2. Import `database/schema.sql` into MySQL.
3. (Optional) Run `php database/seeds/create_demo_user.php`.
4. Serve the project from Laragon and open `http://localhost/authentication_page/`.

## MailHog (password recovery email in development)

Password recovery sends via SMTP to **127.0.0.1:1025** when `mail_smtp_enabled` is enabled in `config/config.php`. MailHog captures those messages locally.

**If Laragon does not start MailHog automatically:**

1. Try in Laragon: **Menu → Tools → MailHog → Start** (if available).
2. Or start it manually:
   - Default executable: **`C:\laragon\bin\mailhog\MailHog.exe`** (double-click), **or**
   - **`scripts\start_mailhog.bat`** (uses `C:\laragon\bin\mailhog\MailHog.exe`).
3. If `bin\mailhog` is missing, install once:
   ```powershell
   cd scripts
   powershell -ExecutionPolicy Bypass -File .\install_mailhog_laragon.ps1
   ```
   (Use `-LaragonRoot "D:\laragon"` if Laragon is not under `C:\laragon`.)

**Web UI (test inbox):** open **http://localhost:8025** in the browser — “Forgot password” emails and reset links appear there.

**Optional:** In Laragon, **Menu → Laragon → Procfile**, add a line so MailHog starts with the stack, e.g. `MailHog: autorun C:\laragon\bin\mailhog\MailHog.exe`, then restart Laragon.

In production, disable local SMTP (`mail_smtp_enabled = false`) and configure real outbound mail.

## How to test

- Automated:
  - `php tests/run_tests.php`
- Manual checks:
  - Valid and invalid login.
  - First access with a temporary password.
  - Password recovery and token reuse attempt.

## Technical choices

- **Unified PDO** to keep data access consistent.
- **Service/repository layer** to separate business logic from persistence.
- **Generic error messages** so internal details are not exposed to users.

## Future improvements

- Move configuration to environment variables (`.env`).
- Add structured logging.
- Introduce PHPUnit and a CI pipeline for tests.

## Git tags

### `v1.0.0`

Versão inicial estável da aplicação de autenticação: interface e textos voltados exclusivamente ao público em **português**. Use esta tag se precisar reproduzir o comportamento ou o conjunto de strings da primeira release sem suporte a outros idiomas.

### `v1.1.0`

Release that introduces **multilingual** UI strings and locale handling.

- **PT:** Versão com interface e mensagens disponíveis em português, italiano, inglês e espanhol; escolha esta tag para ambientes multilíngues.
- **IT:** Versione con interfaccia e messaggi in portoghese, italiano, inglese e spagnolo; usare questo tag per ambienti multilingue.
- **EN:** Build with Portuguese, Italian, English, and Spanish strings; use this tag when you need those four locales.
- **ES:** Versión con interfaz y textos en portugués, italiano, inglés y español; usa esta etiqueta para despliegues multilingües.
