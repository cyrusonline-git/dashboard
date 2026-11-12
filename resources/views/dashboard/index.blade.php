@extends('layouts.app')
@section('titel', 'Start')
@section('inhoud')
<div class="page-header mb-4">
    <h1>Welkom, {{ explode(' ', core_gebruiker()['name'] ?? 'collega')[0] }}</h1>
    <p>
        Je bent ingelogd via Boels CORE als <strong>{{ rol_naam(actieve_rol()) }}</strong>
        @if($kaart && ($kaart['depot'] ?? null)) · {{ $kaart['function'] ?? '' }} {{ $kaart['depot'] }}@if($kaart['area'] ?? null) ({{ $kaart['area'] }})@endif @endif
    </p>
</div>

<div class="row g-3">
    <div class="col-md-6 col-xl-4">
        <div class="card kpi-tile">
            <div class="kpi-body">
                <div class="kpi-icon"><i class="bi bi-check2-circle"></i></div>
                <div>
                    <div class="kpi-value">Klaar</div>
                    <div class="kpi-label">Koppeling met Boels CORE werkt</div>
                </div>
            </div>
        </div>
    </div>
    @if($teamAantal !== null)
    <div class="col-md-6 col-xl-4">
        <div class="card kpi-tile">
            <div class="kpi-body">
                <div class="kpi-icon"><i class="bi bi-people"></i></div>
                <div>
                    <div class="kpi-value">{{ $teamAantal }}</div>
                    <div class="kpi-label">medewerkers in jouw team (uit CORE)</div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="card mt-4">
    <div class="card-header"><i class="bi bi-info-circle me-2 text-boels"></i>In opbouw</div>
    <div class="card-body">
        <p class="mb-0">Het dashboard wordt ingericht. Hier komt per medewerker het overzicht van openstaand werk uit de andere Boels-apps.</p>
    </div>
</div>
@endsection
