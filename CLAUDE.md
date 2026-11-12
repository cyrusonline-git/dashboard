# Boels Dashboard (dashboard.sorai.nl)

Child-app van Boels CORE (Laravel 12, SQLite). Werkdashboard Boels Industrial: Planning (8.7 + 2.6),
Op te schonen, Uit factuurrun (met redenveld), In huur / Ophaallijst / AVI (WOW), Team (acties per
persoon). **Alle SAP-data staat in CORE** (Beheer → Uploads SAP); deze app leest via de CORE-API met
de cookie-relay (`CoreSso::api()` / `apiPost()`), slaat zelf geen SAP-data op. Ontwerp + bestandsregels:
`hub_database_Boels/docs/sap-uploads.md` en het plan van 12-11-2026.

## Regels
- Inloggen gebeurt NOOIT hier: `CoreAuth`-middleware relayt de CORE-cookie naar `/api/me` en
  `/api/access/dashboard`. Rollen (12-11-2026, per functie; iedereen ziet eigen gegevens): binnendienst, werkplaats,
  servicedesk, expeditie, area-manager, bu-manager, vlootmanager, admin (`config/core.php`),
  beheerd in CORE → Beheer → Rollen. Teamzicht: area_manager (eigen area/depots), bu_manager en admin (alles).
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

## Lokaal testen (end-to-end met lokale CORE)
CORE lokaal op 127.0.0.1:8090 (SQLite, SANCTUM_STATEFUL_DOMAINS incl. 127.0.0.1:8088), deze app op
127.0.0.1:8088 met `CORE_URL=http://127.0.0.1:8090` en `CORE_DEV_FAKE_USER=false`; log in op CORE
(curl-cookiejar) en open daarna de dashboard-pagina's met dezelfde jar — de CORE-cookie wordt gerelayed.
Rollen-rechten: `app/Services/Rollen.php`. POSTs naar CORE halen eerst `/sanctum/csrf-cookie` op.
