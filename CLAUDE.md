# Boels Dashboard (dashboard.sorai.nl)

Child-app van Boels CORE (Laravel 12, SQLite). Overzicht per medewerker van wat hij/zij in de
andere Boels-apps nog moet doen. **Inhoud nog te bepalen door Wim** — zie `docs/ontwerp-voorstel.md`.

## Regels
- Inloggen gebeurt NOOIT hier: `CoreAuth`-middleware relayt de CORE-cookie naar `/api/me` en
  `/api/access/dashboard`. Rollen: medewerker, manager, admin (`config/core.php`), beheerd in
  CORE → Beheer → Applicaties/Rollen.
- Huisstijl: oranje `#FF6600`, witte B op oranje (favicon + navbar-brand in `layouts/app.blade.php`).
- Nederlands in UI en code-commentaar; helpers in `app/Support/helpers.php`.
- Eigen data (database/*.sqlite, storage/app/uploads) nooit in git; migraties idempotent houden.

## Deploy (doet Claude zelf)
`git push origin main` → GitHub Action bouwt release `deploy-YYYYMMDD-HHMMSS-<sha>` →
`curl "https://dashboard.sorai.nl/__pull_deploy.php?k=<DEPLOY_SECRET>&tag=<tag>"` →
`curl "https://dashboard.sorai.nl/__migrate.php?k=<DEPLOY_SECRET>"`.
Eerste installatie via CORE: `https://databasehub.sorai.nl/__deploy-child.php?k=<DEPLOY_SECRET>&app=dashboard`
(maakt .env met APP_KEY, neemt DEPLOY_SECRET + MAIL_* over, zet subdomein in SANCTUM_STATEFUL_DOMAINS).
Diagnose: `__log.php?k=&n=120`, `__check.php?k=`.

## Lokaal testen
`.env` met `APP_ENV=local`, `CORE_DEV_FAKE_USER=true` (nep-superadmin), `php artisan migrate`,
`php -S 127.0.0.1:8088 -t public`.
