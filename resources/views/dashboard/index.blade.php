@extends('layouts.app')
@section('titel', 'Start')
@section('inhoud')
@php($naam = explode(' ', core_gebruiker()['name'] ?? 'collega')[0])
<div class="page-header">
    <div><h1>Goedendag, {{ $naam }}</h1><p>{{ rol_naam($rol) }} · {{ ucfirst(now()->translatedFormat('l j F Y')) }}</p></div>
</div>

@if(!$coreBereikbaar)
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Geen verbinding met Boels CORE of nog geen Smartview-uploads. De overzichten blijven leeg tot de eerste upload.</div>
@endif

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <a href="{{ route('planning', ['tm' => now()->toDateString()]) }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon"><i class="bi bi-truck"></i></div><div><div class="kpi-value">{{ $vandaag->count() }}</div><div class="kpi-label">orders te leveren vandaag</div></div></div></div></a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('planning') }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#1976d2"><i class="bi bi-calendar-week"></i></div><div><div class="kpi-value">{{ $week->count() }}</div><div class="kpi-label">orders komende 7 dagen · {{ $week->where('pick_status','volledig')->count() }} volledig gepickt</div></div></div></div></a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('huur.ophalen') }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#c62828"><i class="bi bi-arrow-return-left"></i></div><div><div class="kpi-value">{{ count($ophalen['data'] ?? []) }}</div><div class="kpi-label">afgemeld, op te halen (7 dagen) · {{ collect($ophalen['data'] ?? [])->where('te_laat', true)->count() }} te laat</div></div></div></div></a>
    </div>
    <div class="col-6 col-xl-3">
        <a href="{{ route('planning', ['verleden' => 1, 'mijn' => $rol === 'binnendienst' ? 1 : 0]) }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#777"><i class="bi bi-eraser"></i></div><div><div class="kpi-value">{{ $opTeSchonen['totaal']['orders'] ?? 0 }}</div><div class="kpi-label">{{ $rol === 'binnendienst' ? 'mijn ' : '' }}orders op te schonen (leverdatum voorbij)</div></div></div></div></a>
    </div>
    @if($factuurrun !== null)
    <div class="col-6 col-xl-3">
        <a href="{{ route('factuurrun', ['zonder_reden' => 1]) }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#6f42c1"><i class="bi bi-receipt"></i></div><div><div class="kpi-value">{{ $factuurrun['totaal']['zonder_reden'] ?? 0 }}<small class="text-muted fs-6"> / {{ $factuurrun['totaal']['contracten'] ?? 0 }}</small></div><div class="kpi-label">{{ $rol === 'binnendienst' ? 'mijn ' : '' }}contracten uit factuurrun zonder reden</div></div></div></div></a>
    </div>
    @endif
    @if($referenties !== null)
    <div class="col-6 col-xl-3">
        <a href="{{ route('referenties') }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#b26a00"><i class="bi bi-receipt-cutoff"></i></div><div><div class="kpi-value">{{ ($referenties['totaal']['open'] ?? 0) + ($referenties['totaal']['opgevraagd'] ?? 0) }}@if(($referenties['totaal']['ouder_dan_30'] ?? 0) > 0)<small class="text-danger ms-1">{{ $referenties['totaal']['ouder_dan_30'] }} &gt; 30 dagen</small>@endif</div><div class="kpi-label">zonder klantreferentie / PO · {{ euro($referenties['totaal']['weekhuur'] ?? 0) }}/wk niet te factureren</div></div></div></div></a>
    </div>
    @endif
    @if($summary && !empty($summary['beschikbaar']))
    <div class="col-6 col-xl-3">
        <a href="{{ route('huur.avi') }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#2e7d32"><i class="bi bi-cash-coin"></i></div><div><div class="kpi-value">{{ euro($summary['weekhuur']) }}</div><div class="kpi-label">weekhuur in huur (AVI) · {{ $summary['contracten'] }} contracten</div></div></div></div></a>
    </div>
    @endif
    @if($teamAantal !== null)
    <div class="col-6 col-xl-3">
        <a href="{{ route('team') }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#00897b"><i class="bi bi-people"></i></div><div><div class="kpi-value">{{ $teamAantal }}</div><div class="kpi-label">medewerkers in jouw team · acties per persoon</div></div></div></div></a>
    </div>
    @endif
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between"><span><i class="bi bi-truck me-2 text-boels"></i>Vandaag te leveren</span><a href="{{ route('planning') }}" class="small">hele planning</a></div>
            <div class="card-body p-2">
                @forelse($vandaag->take(12) as $o)
                    @include('planning.rij', ['o' => $o])
                @empty
                    <div class="text-muted p-3">Geen orders met leverdatum vandaag.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        @if($factuurrun !== null)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between"><span><i class="bi bi-receipt me-2 text-boels"></i>Uit factuurrun — nog zonder reden</span><a href="{{ route('factuurrun') }}" class="small">alles</a></div>
            <div class="list-group list-group-flush">
                @forelse(collect($factuurrun['data'] ?? [])->filter(fn($r) => empty($r['reden']))->take(8) as $r)
                    <a href="{{ route('factuurrun.show', $r['contract_number']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span><strong>{{ $r['contract_number'] }}</strong> · {{ $r['customer_name'] }}<br><small class="text-muted">{{ $r['employee_name'] ?? $r['created_by_code'] ?? 'onbekend' }} · {{ $r['nooit_gefactureerd'] ? 'nooit gefactureerd' : 'laatste factuur '.dmy($r['last_invoice_date']) }}</small></span>
                        <span class="text-end small">{{ $r['weekhuur'] ? euro($r['weekhuur']).'/wk' : '' }}</span>
                    </a>
                @empty
                    <div class="text-muted p-3">Alles heeft een reden. 👍</div>
                @endforelse
            </div>
        </div>
        @endif
        <div class="card">
            <div class="card-header d-flex justify-content-between"><span><i class="bi bi-arrow-return-left me-2 text-boels"></i>Op te halen (afgemeld)</span><a href="{{ route('huur.ophalen') }}" class="small">ophaallijst</a></div>
            <div class="list-group list-group-flush">
                @forelse(collect($ophalen['data'] ?? [])->take(8) as $r)
                    <a href="{{ route('huur.show', $r['contract_number']) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ !empty($r['te_laat']) ? 'list-group-item-danger' : '' }}">
                        <span><strong>{{ dmy($r['coll_date']) }}</strong> {{ $r['coll_time'] ?? '' }} · {{ $r['customer_name'] }}<br><small class="text-muted">{{ $r['contract_number'] }} · {{ $r['regels'] }} regels · {{ $r['delivery_name'] }}</small></span>
                        <span class="small text-end">@if(!empty($r['coll_doc_printed']))<span class="badge bg-success">bon</span>@endif</span>
                    </a>
                @empty
                    <div class="text-muted p-3">Niets afgemeld voor de komende week.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
