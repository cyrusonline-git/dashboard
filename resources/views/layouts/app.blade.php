<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titel', 'Start') — {{ setting('app_titel', 'Dashboard') }}</title>
    {{-- Witte B op oranje achtergrond (Boels-huisstijl) --}}
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='12' fill='%23FF6600'/%3E%3Ctext x='32' y='46' font-family='Arial,Helvetica,sans-serif' font-size='40' font-weight='bold' fill='%23fff' text-anchor='middle'%3EB%3C/text%3E%3C/svg%3E">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --boels-orange: #FF6600; --boels-orange-dark: #E55A00; --boels-grey: #333; --boels-light: #F5F5F5; }
        body { background: var(--boels-light); color: var(--boels-grey); }
        .navbar-boels { background: var(--boels-orange); }
        .navbar-boels .navbar-brand, .navbar-boels .nav-link, .navbar-boels .dropdown-toggle { color: #fff !important; }
        .navbar-boels .nav-link:hover { color: #ffe3cf !important; }
        .navbar-boels .nav-link.active { font-weight: 600; text-decoration: underline; text-underline-offset: 6px; }
        .brand-logo { height: 44px; width: auto; border-radius: 6px; margin-right: 12px; background: #fff; padding: 5px 7px; box-shadow: 0 1px 4px rgba(0,0,0,.2); }
        .footer-logo { height: 26px; width: auto; vertical-align: middle; margin-right: 8px; opacity: .85; }
        .rol-badge { background: rgba(255,255,255,.22); color: #fff; border-radius: 12px; padding: 3px 10px; font-size: .8rem; }
        .btn-boels { background: var(--boels-orange); border-color: var(--boels-orange); color: #fff; }
        .btn-boels:hover { background: var(--boels-orange-dark); border-color: var(--boels-orange-dark); color: #fff; }
        .btn-outline-boels { border-color: var(--boels-orange); color: var(--boels-orange); }
        .btn-outline-boels:hover { background: var(--boels-orange); color: #fff; }
        .text-boels { color: var(--boels-orange) !important; }
        .bg-boels { background: var(--boels-orange) !important; color: #fff; }
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
        .card-header { background: #fff; border-bottom: 1px solid #eee; font-weight: 600; border-radius: 12px 12px 0 0 !important; }
        .kpi-tile .kpi-body { display: flex; align-items: center; gap: 14px; padding: 18px 20px; }
        .kpi-tile .kpi-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #fff; flex-shrink: 0; background: var(--boels-orange); }
        .kpi-tile .kpi-value { font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
        .kpi-tile .kpi-label { font-size: .8rem; color: #6c757d; }
        .page-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0; }
        .page-header p { color: #6c757d; margin: 0; }
        footer { color: #999; font-size: .8rem; }
        .dag-kop { background: #333; color: #fff; border-radius: 10px; padding: 8px 14px; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
        .order-rij { background: #fff; border-radius: 10px; padding: 10px 14px; margin-top: 6px; display: grid; grid-template-columns: 1.6fr 1fr 1.1fr 1fr 1.2fr .9fr; gap: 10px; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,.05); }
        .order-rij.volledig { background: #e8f5e9; } .order-rij.deels { background: #fff3e0; } .order-rij.te-laat { background: #fdecea; } .order-rij.offerte { background: #ede7f6; }
        .pick { display: inline-block; min-width: 92px; text-align: center; border-radius: 12px; padding: 2px 10px; font-size: .8rem; font-weight: 600; }
        .pick.volledig { background: #2e7d32; color: #fff; } .pick.deels { background: #ef6c00; color: #fff; } .pick.niets { background: #eee; color: #555; } .pick.onbekend, .pick.geen_regels { background: #f5f5f5; color: #999; }
        .soort-badge { font-size: .7rem; font-weight: 700; letter-spacing: .3px; border-radius: 6px; padding: 2px 8px; color: #fff; text-transform: uppercase; }
        .soort-huur { background: #FF6600; } .soort-verkoop { background: #1976d2; } .soort-transfer_in, .soort-transfer_uit { background: #00897b; } .soort-retour { background: #c62828; } .soort-offerte { background: #6f42c1; } .soort-overig { background: #777; }
        .stand-balk { font-size: .8rem; color: #666; } .stand-balk .verouderd { color: #c62828; font-weight: 600; }
        .reden-form textarea { min-height: 38px; }
        @media (max-width: 991px) { .order-rij { grid-template-columns: 1fr 1fr; } }
        .table thead th { font-size: .8rem; text-transform: uppercase; letter-spacing: .3px; color: #777; border-bottom: 2px solid #eee; }
        .form-control:focus, .form-select:focus { border-color: var(--boels-orange); box-shadow: 0 0 0 .2rem rgba(255,102,0,.15); }
    </style>
    @stack('head')
</head>
<body>
@php($rol = actieve_rol())
@php($rollen = (array) session('rollen', []))
@php($gebruiker = core_gebruiker())
<nav class="navbar navbar-expand-lg navbar-boels shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/boels-industrial.png') }}" alt="Boels Industrial" class="brand-logo">{{ setting('app_titel', 'Dashboard') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            @if($rol)
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2 me-1"></i>Start</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('planning', 'planning.*') ? 'active' : '' }}" href="{{ route('planning') }}"><i class="bi bi-calendar-check me-1"></i>Planning</a></li>
                @if(\App\Services\Rollen::magFactuurrun($rol))
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('factuurrun', 'factuurrun.*') ? 'active' : '' }}" href="{{ route('factuurrun') }}"><i class="bi bi-receipt me-1"></i>Factuurrun</a></li>
                @endif
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('huur', 'huur.*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown"><i class="bi bi-box-seam me-1"></i>In huur</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('huur') }}"><i class="bi bi-list-ul me-2"></i>Contracten in huur</a></li>
                        <li><a class="dropdown-item" href="{{ route('huur.ophalen') }}"><i class="bi bi-truck me-2"></i>Ophaallijst (afgemeld)</a></li>
                        @if(\App\Services\Rollen::magAvi($rol))<li><a class="dropdown-item" href="{{ route('huur.avi') }}"><i class="bi bi-cash-coin me-2"></i>AVI — weekhuur per klant</a></li>@endif
                    </ul>
                </li>
                @if(\App\Services\Rollen::magTeam($rol))
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('team') ? 'active' : '' }}" href="{{ route('team') }}"><i class="bi bi-people me-1"></i>Team</a></li>
                @endif
                @if($rol === 'admin')
                <li class="nav-item"><a class="nav-link" href="{{ config('core.url') }}/admin/sap-uploads" target="_blank"><i class="bi bi-cloud-upload me-1"></i>Uploads SAP <i class="bi bi-box-arrow-up-right small"></i></a></li>
                @endif
            </ul>
            @endif
            <ul class="navbar-nav ms-auto align-items-lg-center">
                @if($gebruiker)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>{{ $gebruiker['name'] ?? 'Gebruiker' }}
                        @if($rol)<span class="rol-badge ms-2">{{ rol_naam($rol) }}</span>@endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">{{ $gebruiker['email'] ?? '' }}@if(eigen_personeelsnummer())<br><small>Personeelsnummer: {{ eigen_personeelsnummer() }}</small>@endif</h6></li>
                        @if(count($rollen) > 1)
                        <li><a class="dropdown-item" href="{{ route('kies-rol', ['wissel' => 1]) }}"><i class="bi bi-arrow-left-right me-2"></i>Wissel rol</a></li>
                        @endif
                        <li><a class="dropdown-item" href="{{ config('core.url') }}"><i class="bi bi-grid-3x3-gap me-2"></i>Naar Boels CORE</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('uitloggen') }}"><i class="bi bi-box-arrow-right me-2"></i>Uitloggen</a></li>
                    </ul>
                </li>
                @endif
            </ul>
        </div>
    </div>
</nav>

<main class="container-fluid py-4">
    @if(session('ok'))<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('ok') }}</div>@endif
    @if(session('fout'))<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('fout') }}</div>@endif
    @yield('inhoud')
</main>

<footer class="text-center py-3"><img src="{{ asset('images/boels-industrial.png') }}" alt="Boels Industrial" class="footer-logo">{{ setting('app_titel', 'Dashboard') }} · ingelogd via Boels CORE</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
