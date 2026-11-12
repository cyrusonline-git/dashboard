# Dashboard — ontwerpvoorstel (nog NIET gebouwd)

Wim bepaalt nog wat er inhoudelijk in het dashboard komt. Dit is het voorstel voor de
onderliggende structuur, zodat we daar straks snel op verder kunnen:

## Wat CORE nu al weet
- CORE houdt per (app, gebruiker) een **badge-teller** bij (`app_badges`): Projectschade en
  Tankapp melden al het aantal openstaande items per medewerker (POST /api/badge).
  Een nieuw CORE-endpoint `GET /api/badges` (eigen tellers) en `GET /api/badges/alle`
  (managers) maakt die direct bruikbaar in het dashboard: "je hebt 3 open schades".
- `GET /api/employees` geeft de medewerkerkaarten (depot/area/personeelsnummer) — basis voor
  een teamoverzicht per manager.

## Voorgestelde structuur
- **bronnen**: gekoppelde apps (slug = CORE-app-slug, naam, url, icoon, sync_key).
- **taken**: één stuk werk per medewerker per bron: extern_id, email/personeelsnummer,
  titel, omschrijving, deeplink, soort (taak/order/melding/goedkeuring), prioriteit,
  deadline, status, extra (json: klant, ordernummer, bedrag …).
- **Push-API** `POST /api/taken` met `{app, k, items:[…]}` (vervangt de open set per
  medewerker) — elke app levert zelf aan wat een medewerker nog moet doen.
- Schermen: *Mijn overzicht* (tegels per app + lijst te laat/vandaag/deze week),
  *Team* (manager: per medewerker aantallen per app), *Beheer* (bronnen, sleutels, logboek).

Open vragen voor Wim: welke apps, welke items per app (schades, tankbeurten, ruilingen,
orders uit het ERP?), wie ziet wat (manager-scope op depot/area), en of items in het
dashboard afgevinkt mogen worden of alleen in de bron-app.
