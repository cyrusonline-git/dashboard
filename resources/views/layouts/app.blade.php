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
        /* --- layout: smalle kopbalk, werkruimte eronder (navigatie gebeurt vanuit CORE) --- */
        .kop { position: sticky; top: 0; z-index: 20; display: flex; align-items: center; gap: 16px; height: 56px; padding: 0 22px; background: #fff; border-bottom: 1px solid var(--line); }
        .kop-logo img { height: 34px; width: auto; display: block; }
        .kop-pad { display: flex; align-items: center; gap: 8px; font-size: .86rem; min-width: 0; }
        .kop-pad a { color: #4b5563; text-decoration: none; } .kop-pad a:hover { color: var(--boels-orange); }
        .kop-pad .sep { color: #b0b6bc; } .kop-pad .huidig { font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .kop-rechts { margin-left: auto; display: flex; align-items: center; gap: 8px; font-size: .84rem; }
        .kop-naam { color: #4b5563; text-decoration: none; }
        .rol-badge { display: inline-block; background: rgba(255,102,0,.12); color: var(--boels-orange-dark); border-radius: 10px; padding: 2px 9px; font-size: .74rem; font-weight: 600; }
        .main { max-width: 1480px; margin: 0 auto; padding: 24px 28px 48px; }
        @media (max-width: 767px) { .kop { padding: 0 12px; gap: 10px; } .main { padding: 14px 12px 36px; } }
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
        .order-rij.volledig { background: #eaf6ec; border-color: #d4ead8; } .order-rij.deels { background: #fff4e5; border-color: #f5e2c4; } .order-rij.te-laat { background: #fdecec; border-color: #f3cfcf; } .order-rij.offerte { background: #efeafa; border-color: #ddd3f0; } .order-rij.niet-op-scanner { background: #fde0e0; border-color: #e53935; border-left: 5px solid #e53935; } .order-rij.geen-ref { background: #fff3cd; border-color: #f0ad4e; border-left: 5px solid #f0ad4e; }
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
        .kaart.te-laat .kaart-body { background: #fdecec; } .kaart.niet-op-scanner { border: 2px solid #e53935; } .kaart.niet-op-scanner .kaart-body { background: #fde0e0; } .kaart.geen-ref { border: 2px solid #f0ad4e; } .kaart.geen-ref .kaart-body { background: #fff3cd; } .kaart.volledig .kaart-body { background: #eaf6ec; }
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

<header class="kop">
    <a class="kop-logo" href="{{ config('core.url') }}/launcher" title="Terug naar Boels CORE"><img src="{{ asset('images/boels-industrial.png') }}" alt="Boels Industrial"></a>
    <nav class="kop-pad" aria-label="breadcrumb">
        <a href="{{ config('core.url') }}/launcher">Boels CORE</a>
        <span class="sep">›</span>
        <a href="{{ route('dashboard') }}">{{ $titel }}</a>
        @hasSection('titel')
            @if(trim($__env->yieldContent('titel')) !== 'Start')<span class="sep">›</span><span class="huidig">@yield('titel')</span>@endif
        @endif
    </nav>
    <div class="kop-rechts">
        @if($gebruiker && $rol)
            <span class="kop-naam d-none d-md-inline">{{ $gebruiker['name'] ?? '' }}</span>
            <span class="rol-badge">{{ rol_naam($rol) }}</span>
            <div class="dropdown">
                <button class="btn btn-sm btn-link text-reset p-0 ms-1" data-bs-toggle="dropdown" aria-label="Account"><i class="bi bi-three-dots-vertical"></i></button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">{{ $gebruiker['email'] ?? '' }}@if(eigen_personeelsnummer()) · #{{ eigen_personeelsnummer() }}@endif</h6></li>
                    @if(count($rollen) > 1)<li><a class="dropdown-item" href="{{ route('kies-rol', ['wissel' => 1]) }}"><i class="bi bi-arrow-left-right me-2"></i>Wissel rol</a></li>@endif
                    @if($rol === 'admin')
                        <li><a class="dropdown-item" href="{{ route('beheer') }}"><i class="bi bi-gear me-2"></i>Beheer</a></li>
                        <li><a class="dropdown-item" href="{{ config('core.url') }}/admin/sap-uploads" target="_blank"><i class="bi bi-cloud-upload me-2"></i>Uploads Smartview (CORE)</a></li>
                    @endif
                    <li><a class="dropdown-item" href="{{ config('core.url') }}/launcher"><i class="bi bi-grid-3x3-gap me-2"></i>Naar Boels CORE</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('uitloggen') }}"><i class="bi bi-box-arrow-right me-2"></i>Uitloggen</a></li>
                </ul>
            </div>
        @else
            <a class="kop-naam" href="{{ config('core.url') }}/launcher">Naar Boels CORE</a>
        @endif
    </div>
</header>
<main class="main">
    @if(session('ok'))<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('ok') }}</div>@endif
    @if(session('fout'))<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('fout') }}</div>@endif
    @yield('inhoud')
    <footer>Boels Industrial · {{ $titel }} · onderdeel van Boels CORE</footer>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
