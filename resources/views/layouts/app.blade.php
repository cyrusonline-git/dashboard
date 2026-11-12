<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titel', 'Start') — {{ setting('app_titel', 'Dashboard') }}</title>
    {{-- Witte B op oranje achtergrond (Boels-huisstijl) --}}
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='12' fill='%23FF6600'/%3E%3Ctext x='32' y='46' font-family='Arial,Helvetica,sans-serif' font-size='40' font-weight='bold' fill='%23fff' text-anchor='middle'%3EB%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --boels-orange: #FF6600; --boels-orange-dark: #E55A00; --ink: #1f2328; --muted: #6b7280; --line: #e6e8eb; --bg: #f4f5f7; --side: #23272b; --side-2: #2d3237; --radius: 14px; }
        * { -webkit-font-smoothing: antialiased; }
        html, body { height: 100%; }
        body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: var(--bg); color: var(--ink); font-size: .9rem; }
        a { color: var(--boels-orange-dark); } a:hover { color: var(--boels-orange); }
        code { color: var(--ink); background: #f1f3f5; padding: 1px 6px; border-radius: 6px; font-size: .85em; }
        /* --- layout: zijbalk + werkruimte --- */
        .shell { display: flex; min-height: 100vh; }
        .side { width: 236px; flex: 0 0 236px; background: var(--side); color: #cfd3d7; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
        .side .brand { display: flex; align-items: center; gap: 10px; padding: 18px 18px 14px; color: #fff; text-decoration: none; }
        .side .brand img { height: 38px; width: auto; background: #fff; border-radius: 8px; padding: 4px 6px; }
        .side .brand span { font-weight: 700; letter-spacing: .2px; font-size: .95rem; }
        .side .sectie { font-size: .68rem; text-transform: uppercase; letter-spacing: .8px; color: #868c93; padding: 14px 18px 6px; }
        .side a.item { display: flex; align-items: center; gap: 10px; padding: 9px 18px; color: #d7dbdf; text-decoration: none; font-weight: 500; border-left: 3px solid transparent; font-size: .88rem; }
        .side a.item i { width: 18px; text-align: center; color: #9aa0a6; }
        .side a.item:hover { background: var(--side-2); color: #fff; }
        .side a.item.active { background: var(--side-2); color: #fff; border-left-color: var(--boels-orange); } .side a.item.active i { color: var(--boels-orange); }
        .side .onder { margin-top: auto; padding: 14px 18px 18px; border-top: 1px solid #343a40; font-size: .8rem; }
        .side .onder .naam { color: #fff; font-weight: 600; }
        .side .onder a { color: #aeb4ba; text-decoration: none; } .side .onder a:hover { color: #fff; }
        .rol-badge { display: inline-block; background: rgba(255,102,0,.18); color: #ffb380; border-radius: 10px; padding: 1px 8px; font-size: .72rem; font-weight: 600; }
        .main { flex: 1; min-width: 0; padding: 26px 30px 48px; }
        .topbar { display: none; }
        @media (max-width: 991px) {
            .side { display: none; } .main { padding: 16px; }
            .topbar { display: flex; align-items: center; justify-content: space-between; background: var(--side); color: #fff; padding: 10px 14px; position: sticky; top: 0; z-index: 10; }
            .topbar img { height: 32px; background: #fff; border-radius: 6px; padding: 3px 5px; }
        }
        .offcanvas.side-mobile { background: var(--side); color: #d7dbdf; width: 260px; }
        /* --- koppen & kaarten --- */
        .page-header { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px; margin-bottom: 14px; }
        .page-header h1 { font-size: 1.45rem; font-weight: 800; letter-spacing: -.2px; margin: 0; }
        .page-header p { color: var(--muted); margin: 2px 0 0; max-width: 820px; }
        .card { border: 1px solid var(--line); border-radius: var(--radius); box-shadow: none; background: #fff; }
        .card-header { background: #fff; border-bottom: 1px solid var(--line); font-weight: 600; border-radius: var(--radius) var(--radius) 0 0 !important; padding: 12px 16px; }
        .btn { border-radius: 10px; font-weight: 500; } .btn-sm { border-radius: 8px; }
        .btn-boels { background: var(--boels-orange); border-color: var(--boels-orange); color: #fff; }
        .btn-boels:hover { background: var(--boels-orange-dark); border-color: var(--boels-orange-dark); color: #fff; }
        .btn-outline-boels { border-color: var(--boels-orange); color: var(--boels-orange); } .btn-outline-boels:hover { background: var(--boels-orange); color: #fff; }
        .btn-outline-secondary { border-color: #d6d9dd; color: #444; } .btn-outline-secondary:hover { background: #f1f3f5; color: #111; border-color: #c9cdd2; }
        .text-boels { color: var(--boels-orange) !important; } .bg-boels { background: var(--boels-orange) !important; color: #fff; }
        .form-control, .form-select { border-radius: 10px; border-color: #d9dcdf; } .form-control-sm, .form-select-sm { border-radius: 8px; }
        .form-control:focus, .form-select:focus { border-color: var(--boels-orange); box-shadow: 0 0 0 .2rem rgba(255,102,0,.12); }
        .badge { font-weight: 600; border-radius: 8px; padding: .35em .6em; }
        .table { --bs-table-bg: transparent; } .table thead th { font-size: .72rem; text-transform: uppercase; letter-spacing: .5px; color: #7a8088; border-bottom: 1px solid var(--line); font-weight: 600; background: #fafbfc; }
        .table td { vertical-align: middle; border-color: #eef0f2; }
        .table-warning > td { background: #fff7e6 !important; } .table-danger > td { background: #fdeeee !important; }
        .alert { border-radius: var(--radius); border: 0; }
        .list-group-item { border-color: #eef0f2; }
        /* --- KPI-tegels --- */
        .kpi-tile { height: 100%; transition: transform .12s ease, box-shadow .12s ease; } .kpi-tile:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(0,0,0,.06); }
        .kpi-tile .kpi-body { display: flex; align-items: center; gap: 14px; padding: 16px 18px; }
        .kpi-tile .kpi-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; color: #fff; flex-shrink: 0; background: var(--boels-orange); }
        .kpi-tile .kpi-value { font-size: 1.5rem; font-weight: 800; line-height: 1.1; letter-spacing: -.3px; }
        .kpi-tile .kpi-label { font-size: .78rem; color: var(--muted); margin-top: 2px; }
        /* --- stand van de data (zoals de importbalken bij H&H) --- */
        .stand-balk { display: flex; flex-wrap: wrap; gap: 8px 18px; align-items: center; font-size: .78rem; color: #5f6670; background: #fff; border: 1px dashed #cfd4d9; border-radius: 12px; padding: 8px 14px; margin-bottom: 14px; }
        .stand-balk .verouderd { color: #c62828; font-weight: 600; }
        /* --- planning per dag --- */
        .dag-kop { background: #2b2f33; color: #fff; border-radius: 12px; padding: 9px 16px; font-weight: 700; display: flex; justify-content: space-between; align-items: center; font-size: .92rem; }
        .dag-kop .small { color: #c7ccd1; font-weight: 500; }
        .order-rij { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 10px 16px; margin-top: 6px; display: grid; grid-template-columns: 1.7fr 1fr 1.1fr 1fr 1.2fr .9fr; gap: 12px; align-items: center; }
        .order-rij:hover { border-color: #d3d7dc; }
        .order-rij.volledig { background: #eaf6ec; border-color: #d4ead8; } .order-rij.deels { background: #fff4e5; border-color: #f5e2c4; } .order-rij.te-laat { background: #fdecec; border-color: #f3cfcf; } .order-rij.offerte { background: #efeafa; border-color: #ddd3f0; }
        .pick { display: inline-block; min-width: 96px; text-align: center; border-radius: 999px; padding: 3px 11px; font-size: .76rem; font-weight: 600; }
        .pick.volledig { background: #2e7d32; color: #fff; } .pick.deels { background: #ef6c00; color: #fff; } .pick.niets { background: #e9ecef; color: #555; } .pick.onbekend, .pick.geen_regels { background: #f1f3f5; color: #98a0a8; }
        .soort-badge { font-size: .66rem; font-weight: 700; letter-spacing: .4px; border-radius: 6px; padding: 3px 8px; color: #fff; text-transform: uppercase; white-space: nowrap; }
        .soort-huur { background: #FF6600; } .soort-verkoop { background: #1976d2; } .soort-transfer_in, .soort-transfer_uit { background: #00897b; } .soort-retour { background: #c62828; } .soort-offerte { background: #6f42c1; } .soort-overig { background: #777; }
        @media (max-width: 991px) { .order-rij { grid-template-columns: 1fr 1fr; } }
        /* --- planbord --- */
        .bord { display: flex; gap: 14px; overflow-x: auto; padding-bottom: 12px; align-items: flex-start; }
        .kolom { flex: 0 0 300px; background: #eceff1; border-radius: 14px; padding: 10px; min-height: 160px; }
        .kolom-kop { display: flex; justify-content: space-between; align-items: center; font-weight: 700; padding: 4px 6px 10px; font-size: .9rem; }
        .kaart { background: #fff; border-radius: 12px; border: 1px solid #e3e6e9; margin-bottom: 8px; overflow: hidden; }
        .kaart-kop { color: #fff; font-size: .66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; padding: 5px 10px; display: flex; justify-content: space-between; }
        .kaart-body { padding: 9px 11px; font-size: .84rem; }
        .kaart-titel { font-weight: 600; } .kaart .text-rood { color: #c62828; font-weight: 600; }
        .kaart.te-laat .kaart-body { background: #fdecec; } .kaart.volledig .kaart-body { background: #eaf6ec; }
        .reden-form textarea { min-height: 38px; }
        footer { color: #9aa0a6; font-size: .76rem; padding: 24px 0 0; }
    </style>
    @stack('head')
</head>
<body>
@php($rol = actieve_rol())
@php($rollen = (array) session('rollen', []))
@php($gebruiker = core_gebruiker())
@php($titel = setting('app_titel', 'Dashboard'))

<div class="shell">
    @if($rol)
    <aside class="side">
        <a class="brand" href="{{ route('dashboard') }}"><img src="{{ asset('images/boels-industrial.png') }}" alt="Boels Industrial"><span>{{ $titel }}</span></a>
        <nav>@include('partials.menu')</nav>
        <div class="onder">
            @if($gebruiker)
                <div class="naam">{{ $gebruiker['name'] ?? 'Gebruiker' }}</div>
                <div class="mb-2"><span class="rol-badge">{{ rol_naam($rol) }}</span>@if(eigen_personeelsnummer()) <span class="text-secondary ms-1">#{{ eigen_personeelsnummer() }}</span>@endif</div>
                <div class="d-flex flex-wrap gap-3">
                    @if(count($rollen) > 1)<a href="{{ route('kies-rol', ['wissel' => 1]) }}"><i class="bi bi-arrow-left-right me-1"></i>Wissel rol</a>@endif
                    <a href="{{ config('core.url') }}"><i class="bi bi-grid-3x3-gap me-1"></i>CORE</a>
                    <a href="{{ route('uitloggen') }}"><i class="bi bi-box-arrow-right me-1"></i>Uitloggen</a>
                </div>
            @endif
        </div>
    </aside>
    @endif
    <div class="flex-grow-1 min-w-0">
        <div class="topbar">
            <a class="d-flex align-items-center gap-2 text-white text-decoration-none" href="{{ route('dashboard') }}"><img src="{{ asset('images/boels-industrial.png') }}" alt="Boels Industrial"><strong>{{ $titel }}</strong></a>
            @if($rol)<button class="btn btn-sm btn-outline-light" data-bs-toggle="offcanvas" data-bs-target="#menuMobiel"><i class="bi bi-list"></i></button>@endif
        </div>
        <main class="main">
            @if(session('ok'))<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('ok') }}</div>@endif
            @if(session('fout'))<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('fout') }}</div>@endif
            @yield('inhoud')
            <footer>Boels Industrial · {{ $titel }} · ingelogd via Boels CORE</footer>
        </main>
    </div>
</div>
@if($rol)
<div class="offcanvas offcanvas-start side-mobile" tabindex="-1" id="menuMobiel">
    <div class="offcanvas-header"><strong class="text-white">{{ $titel }}</strong><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button></div>
    <div class="offcanvas-body p-0 side" style="position:static; height:auto; width:auto;">@include('partials.menu')
        <div class="onder"><a href="{{ route('kies-rol', ['wissel' => 1]) }}">Wissel rol</a> · <a href="{{ route('uitloggen') }}">Uitloggen</a></div>
    </div>
</div>
@endif
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
