@extends('layouts.app')
@section('titel', 'Voorraadcheck')
@section('main_klasse', 'breed')
@section('inhoud')
@include('voorraad._nav')
<div class="page-header">
    <div>
        <h1>Voorraadcheck</h1>
        <p>Vul een contract-, offerte- of projectnummer in. Je ziet direct wat nog niet toegekend is, wat we zelf beschikbaar hebben en bij welk collega-depot de rest staat. Daar kun je het met één klik aanvragen.</p>
    </div>
</div>
@include('partials.stand', ['uploads' => $uploads, 'soorten' => ['contract_items', 'planning_regels', 'vloot', 'planning']])

<form method="get" action="{{ route('voorraad') }}" class="card mb-3">
    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <i class="bi bi-search text-boels fs-4"></i>
        <input type="text" name="nr" class="form-control form-control-lg" style="max-width:340px" placeholder="Contract- of projectnummer, bv. 7590019111" inputmode="numeric" autofocus>
        <button class="btn btn-boels btn-lg">Check voorraad</button>
        @if($openAanvragen)<a href="{{ route('voorraad.aanvragen', ['status' => 'verzonden']) }}" class="ms-auto small"><span class="badge bg-warning text-dark">{{ $openAanvragen }}</span> aanvragen wachten op reactie</a>@endif
    </div>
</form>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <span><i class="bi bi-list-check me-2 text-boels"></i>Contracten en offertes van {{ $depot }} met regels die nog niet toegekend zijn</span>
                <form method="get" class="d-flex gap-2 align-items-center small">
                    <select name="soort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Contracten + offertes</option>
                        <option value="contract" @selected(request('soort') === 'contract')>Alleen contracten</option>
                        <option value="offerte" @selected(request('soort') === 'offerte')>Alleen offertes</option>
                    </select>
                    <label class="form-check mb-0 text-nowrap"><input class="form-check-input" type="checkbox" name="mijn" value="1" @checked($mijn) onchange="this.form.submit()"> alleen mijn</label>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead><tr><th>Nummer</th><th>Klant</th><th>Leverdatum</th><th class="text-end">Open</th><th>Verkoper</th><th></th></tr></thead>
                    <tbody>
                    @forelse($lijst as $c)
                        @php($telaat = $c['leverdatum'] && $c['leverdatum'] < now()->toDateString())
                        <tr>
                            <td><a href="{{ route('voorraad.show', $c['contract_number']) }}" class="fw-semibold"><code>{{ $c['contract_number'] }}</code></a>
                                @if($c['is_offerte']) <span class="soort-badge soort-offerte">offerte</span> @endif
                                @if($c['project_number']) <div class="small text-muted">project <a href="{{ route('voorraad.show', $c['project_number']) }}">{{ $c['project_number'] }}</a></div> @endif
                            </td>
                            <td>{{ $c['customer_name'] }}<div class="small text-muted">{{ $c['aflevering'] }}</div></td>
                            <td class="{{ $telaat ? 'text-danger fw-semibold' : '' }}">{{ dmy($c['leverdatum']) }}</td>
                            <td class="text-end"><strong>{{ $c['stuks'] }}</strong> st<div class="small text-muted">{{ $c['regels'] }} regels</div></td>
                            <td class="small">{{ $c['verkoper'] ?? $c['created_by_code'] }}</td>
                            <td class="text-end"><a href="{{ route('voorraad.show', $c['contract_number']) }}" class="btn btn-sm btn-outline-boels">Check</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Geen contracten met niet-toegekende regels. Alles is toegekend, of de 3.4/8.A is nog niet geüpload.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between"><span><i class="bi bi-envelope-paper me-2 text-boels"></i>Laatste aanvragen</span><a href="{{ route('voorraad.aanvragen') }}" class="small">alle</a></div>
            <div class="list-group list-group-flush">
                @forelse($aanvragen as $a)
                    <a href="{{ route('voorraad.aanvraag', $a) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between"><span><code>{{ $a->nummer }}</code> → {{ $a->depot_naam ?: 'depot '.$a->depot }}</span><span class="badge bg-{{ $a->kleur() }} {{ in_array($a->kleur(), ['warning', 'light', 'info']) ? 'text-dark' : '' }}">{{ $a->label() }}</span></div>
                        <div class="small text-muted">{{ $a->aantal }} st · {{ $a->aanvrager_naam }} · {{ $a->created_at->format('d-m H:i') }}</div>
                    </a>
                @empty
                    <div class="list-group-item text-muted small">Nog geen aanvragen.</div>
                @endforelse
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-body small text-muted">
                <strong class="text-body">Zo rekent de check</strong>
                <ul class="mb-0 ps-3">
                    <li><strong>Niet toegekend</strong> = huurregels in de 3.4 die nog een groepsboeking zijn (TYPE "Groep", geen machinenummer). Zodra de expeditie pickt, hangt er een machine aan en telt de regel niet meer. Verkoop, brandstof, arbeid, heffingen en wat al geleverd is (WOW) tellen niet mee.</li>
                    <li>Een <strong>projectnummer</strong> (uit de 8.A) geeft alle contracten van het project samen.</li>
                    <li><strong>Beschikbaar</strong> = status beschikbaar in de vlootvoorraad. Reparatie telt nooit; "in onderhoud" alleen als je dat aanzet.</li>
                    <li>Niet-toegekende regels van <strong>andere contracten en offertes</strong> binnen de horizon gaan van de voorraad af.</li>
                    <li>Eerst het eigen depot, dan de collega-depots met de meeste beschikbaar.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
