@extends('layouts.app')
@section('titel', 'Stockplanner')
@push('head')
<style>
    .sp-filter .label { font-size: .74rem; font-weight: 600; color: #5f6670; margin: 10px 0 4px; }
    .sp-seg { display: flex; border-radius: 8px; overflow: hidden; border: 1px solid #f0c9a8; }
    .sp-seg a { flex: 1; text-align: center; padding: 4px 6px; font-size: .78rem; text-decoration: none; color: #444; background: #fff; }
    .sp-seg a.aan { background: var(--boels-orange); color: #fff; font-weight: 600; }
    .sp-tabel { font-size: .78rem; }
    .sp-tabel th, .sp-tabel td { white-space: nowrap; padding: 3px 6px; }
    .sp-tabel thead th { position: sticky; top: 0; z-index: 2; background: #fafbfc; }
    .sp-tabel .sticky { position: sticky; left: 0; z-index: 1; background: #fff; min-width: 260px; max-width: 320px; overflow: hidden; text-overflow: ellipsis; }
    .sp-tabel thead .sticky { z-index: 3; background: #fafbfc; }
    .sp-tabel td.c { text-align: right; min-width: 46px; }
    .sp-tabel td.neg { background: #fdecec; color: #c62828; font-weight: 700; }
    .sp-tabel td.nul { background: #fff6e5; color: #b26a00; }
    .sp-tabel th.weekend, .sp-tabel td.weekend { background-image: linear-gradient(rgba(0,0,0,.025), rgba(0,0,0,.025)); }
    .sp-tabel tr.actief td { outline: 2px solid var(--boels-orange); outline-offset: -2px; }
    .sp-wrap { max-height: 70vh; overflow: auto; }
    .mut-levering { color: #c62828; } .mut-offerte { color: #6f42c1; } .mut-gepickt { color: #ef6c00; } .mut-retour, .mut-goederen_in { color: #2e7d32; }
</style>
@endpush
@section('inhoud')
@include('voorraad._nav')
@php
    $kol = $res['kolommen'] ?? [];
    $rijen = $res['rijen'] ?? [];
    $seg = function (string $param, array $opties) use ($f) {
        $html = '<div class="sp-seg">';
        foreach ($opties as $waarde => $label) {
            $aan = (string) ($f[$param] ?? '') === (string) $waarde;
            $html .= '<a class="'.($aan ? 'aan' : '').'" href="'.e(request()->fullUrlWithQuery([$param => $waarde, 'subgroep' => request('subgroep')])).'">'.e($label).'</a>';
        }

        return $html.'</div>';
    };
    $stand = $res['stand'] ?? [];
@endphp
<div class="page-header">
    <div>
        <h1>Stockplanner</h1>
        <p>Verwachte beschikbare voorraad per subgroep in de tijd: vandaag beschikbaar, min de leveringen van contracten en offertes die nog niet gepickt zijn, plus de retouren van lopende huur. Rood = tekort. Klik op een subgroep voor de tijdlijn, alle depots en de mutaties.</p>
    </div>
</div>
@if(!empty($stand['alleen_depot']))
    <div class="alert alert-light border small py-2"><i class="bi bi-info-circle me-1 text-boels"></i>Contractregels, offertes en lopende huur komen nu alleen van depot <strong>{{ $stand['alleen_depot'] }}</strong>. Andere depots tonen hun voorraad uit de vlootlijst, zonder reserveringen of retouren. Zodra de uploads alle depots bevatten, rekent de planner overal mee.</div>
@endif

<div class="row g-3">
    {{-- Filters (zoals in Qlik) --}}
    <div class="col-xl-2 col-lg-3">
        <div class="card sp-filter">
        <form method="get" class="card-body p-3">
            @foreach(['horizon', 'per', 'retour_dagen', 'offertes', 'onderhoud', 'alleen_tekort', 'alleen_mutaties', 'zonder_vloot'] as $h)<input type="hidden" name="{{ $h }}" value="{{ $f[$h] }}">@endforeach
            <div class="label mt-0">Depot</div>
            <select name="depot" class="form-select form-select-sm" onchange="this.form.submit()">@foreach($depots as $nr => $naam)<option value="{{ $nr }}" @selected((string) $f['depot'] === (string) $nr)>{{ $naam }}</option>@endforeach</select>
            <div class="label">Subgroep / omschrijving</div>
            <input type="search" name="q" value="{{ $f['q'] }}" class="form-control form-control-sm" placeholder="bv. 84335 of aggregaat">
        </form>
        <div class="card-body p-3 pt-0">
            <div class="label">Alleen tekorten?</div>{!! $seg('alleen_tekort', ['1' => 'Ja', '0' => 'Nee']) !!}
            <div class="label">Periode</div>{!! $seg('horizon', ['14' => '2 wk', '28' => '4 wk', '56' => '8 wk', '91' => '13 wk']) !!}
            <div class="label">Weergave</div>{!! $seg('per', ['dag' => 'Per dag', 'week' => 'Per week']) !!}
            <div class="label">Einde huur als…</div>{!! $seg('retour_dagen', ['0' => 'einddatum', '7' => '+7 d', '14' => '+14 d']) !!}
            <div class="label">Offertes meetellen?</div>{!! $seg('offertes', ['1' => 'Ja', '0' => 'Nee']) !!}
            <div class="label">"In onderhoud" als voorraad?</div>{!! $seg('onderhoud', ['0' => 'Nee', '1' => 'Ja']) !!}
            <div class="label">Welke subgroepen tonen?</div>{!! $seg('alleen_mutaties', ['1' => 'Met leveringen/retouren', '0' => 'Alle in het depot']) !!}
            <div class="small text-muted mt-1">"Met leveringen/retouren": alleen subgroepen waar in deze periode iets in- of uitgaat. "Alle in het depot": ook subgroepen die alleen op voorraad staan.</div>
            <div class="label">Artikelen zonder vlootgegevens?</div>{!! $seg('zonder_vloot', ['0' => 'Verbergen', '1' => 'Tonen']) !!}
            <div class="small text-muted mt-3">
                Vloot {{ !empty($stand['vloot']['moment']) ? \Carbon\Carbon::parse($stand['vloot']['moment'])->format('d-m H:i') : '—' }}<br>
                3.4 {{ !empty($stand['reserveringen']['moment']) ? \Carbon\Carbon::parse($stand['reserveringen']['moment'])->format('d-m H:i') : '—' }}<br>
                WOW {{ !empty($stand['wow']['moment']) ? \Carbon\Carbon::parse($stand['wow']['moment'])->format('d-m H:i') : '—' }}
            </div>
        </div>
        </div>
    </div>

    <div class="col-xl-10 col-lg-9">
        {{-- Detail van één subgroep --}}
        @if($detail)
            @php
                $md = collect($detail['mutaties'] ?? []);
                $eigen = collect($detail['per_depot'] ?? [])->firstWhere('depot', $f['depot']);
            @endphp
            <div class="card mb-3 border-warning">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><code>{{ $detail['subgroep'] }}</code> {{ $detail['omschrijving'] }}</span>
                    <a href="{{ request()->fullUrlWithQuery(['subgroep' => null]) }}" class="small">sluiten</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-7"><div style="height:240px"><canvas id="spGrafiek"></canvas></div></div>
                        <div class="col-lg-5">
                            <div class="small fw-semibold mb-1">Planner alle depots <span class="text-muted fw-normal">(laagste stand in de periode)</span></div>
                            <div style="max-height:240px;overflow:auto">
                            <table class="table table-sm mb-0 small">
                                <thead><tr><th>Depot</th><th class="text-end">Beschikb.</th><th class="text-end">Onderh.</th><th class="text-end">Verhuurd</th><th class="text-end">Laagste</th></tr></thead>
                                <tbody>
                                @foreach($detail['per_depot'] as $d)
                                    <tr class="{{ (string) $d['depot'] === (string) $f['depot'] ? 'table-warning' : '' }}">
                                        <td><a href="{{ request()->fullUrlWithQuery(['depot' => $d['depot']]) }}">{{ $d['depot'] }}</a> {{ $d['naam'] }}</td>
                                        <td class="text-end">{{ $d['beschikbaar'] }}</td><td class="text-end">{{ $d['onderhoud'] }}</td><td class="text-end">{{ $d['verhuurd'] }}</td>
                                        <td class="text-end fw-semibold {{ $d['min'] < 0 ? 'text-danger' : '' }}">{{ $d['min'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                    <div class="small fw-semibold mt-3 mb-1">Mutaties in {{ $f['depot'] }}</div>
                    <div style="max-height:260px;overflow:auto">
                    <table class="table table-sm mb-0 small align-middle">
                        <thead><tr><th>Datum</th><th>Soort</th><th class="text-end">Aantal</th><th>Contract</th><th>Klant</th></tr></thead>
                        <tbody>
                        @forelse($md as $m)
                            <tr>
                                <td>{{ dmy($m['datum']) }}@if($m['achterstallig']) <span class="badge bg-danger-subtle text-danger">achterstallig</span>@endif</td>
                                <td class="mut-{{ $m['type'] }}">{{ $detail['types'][$m['type']] ?? $m['type'] }}</td>
                                <td class="text-end fw-semibold mut-{{ $m['type'] }}">{{ $m['aantal'] > 0 ? '+'.$m['aantal'] : $m['aantal'] }}</td>
                                <td>@if($m['type'] === 'retour')<a href="{{ route('huur.show', $m['contract']) }}">{{ $m['contract'] }}</a>@else<a href="{{ route('voorraad.show', $m['contract']) }}">{{ $m['contract'] }}</a>@endif</td>
                                <td>{{ $m['klant'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-muted text-center">Geen mutaties in deze periode.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span>{{ $depots[$f['depot']] ?? 'Depot '.$f['depot'] }} · <strong class="{{ ($res['tekorten'] ?? 0) ? 'text-danger' : 'text-success' }}">{{ $res['tekorten'] ?? 0 }}</strong> subgroepen met tekort <span class="text-muted fw-normal small">van {{ $res['aantal'] ?? 0 }} getoond @if(!empty($res['zonder_vloot'])) · {{ $res['zonder_vloot'] }} artikelen zonder vlootgegevens verborgen @endif</span></span>
                <span class="small text-muted"><span class="badge" style="background:#fdecec;color:#c62828">−3</span> tekort · <span class="badge" style="background:#fff6e5;color:#b26a00">0</span> precies op</span>
            </div>
            @if(empty($res))
                <div class="card-body text-muted">CORE niet bereikbaar of nog geen vlootvoorraad geüpload.</div>
            @else
            <div class="sp-wrap">
                <table class="table table-sm table-hover sp-tabel mb-0">
                    <thead>
                        <tr>
                            <th class="sticky">Subgroep</th>
                            <th class="text-end" title="Nu beschikbaar{{ $f['onderhoud'] === '1' ? ' + in onderhoud' : '' }}">Nu</th>
                            <th class="text-end text-muted" title="In onderhoud (telt alleen mee als 'In onderhoud als voorraad' op Ja staat)">Onderh.</th>
                            <th class="text-end text-muted" title="In reparatie (telt nooit mee)">Rep.</th>
                            <th class="text-end" title="Totaal eraf in de periode">Uit</th>
                            <th class="text-end" title="Totaal retour in de periode">In</th>
                            @foreach($kol as $k)<th class="text-end {{ !empty($k['weekend']) ? 'weekend' : '' }}">{{ $k['label'] }}<div class="text-muted fw-normal">{{ $k['sub'] }}</div></th>@endforeach
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($rijen as $r)
                        <tr class="{{ request('subgroep') === $r['subgroep'] ? 'actief' : '' }}" onclick="location='{{ request()->fullUrlWithQuery(['subgroep' => $r['subgroep']]) }}'" style="cursor:pointer">
                            <td class="sticky" title="{{ $r['omschrijving'] }}"><code>{{ $r['subgroep'] }}</code> {{ $r['omschrijving'] }}@if(!$r['in_vloot']) <span class="badge bg-secondary">niet in vloot</span>@endif</td>
                            <td class="c fw-semibold">{{ $r['start'] }}</td>
                            <td class="c text-muted">{{ $r['onderhoud'] ?: '' }}</td>
                            <td class="c text-muted">{{ ($r['reparatie'] ?? 0) ?: '' }}</td>
                            <td class="c text-danger">{{ $r['uit'] ?: '' }}</td>
                            <td class="c text-success">{{ $r['in'] ?: '' }}</td>
                            @foreach($r['reeks'] as $i => $w)
                                <td class="c {{ $w < 0 ? 'neg' : ($w === 0 ? 'nul' : '') }} {{ !empty($kol[$i]['weekend']) ? 'weekend' : '' }}">{{ $w }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ 6 + count($kol) }}" class="text-center text-muted py-4">{{ $f['alleen_tekort'] === '1' ? 'Geen tekorten in deze periode.' : 'Geen subgroepen gevonden.' }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if(($res['aantal'] ?? 0) > count($rijen))<div class="card-footer bg-white small text-muted">De eerste {{ count($rijen) }} van {{ $res['aantal'] }} worden getoond. Zoek op subgroep of zet "Alleen tekorten" aan.</div>@endif
            @endif
        </div>
        <p class="small text-muted mt-2 mb-0">
            <strong>Zo rekent de planner.</strong> Nu = beschikbaar in de vlootvoorraad (Onderh. telt alleen mee als je dat aanzet; Rep. nooit). Eraf: contractregels die nog niet gepickt zijn (3.4, groepsboeking), offertes en gepickte machines die nog niet geleverd zijn, op hun leverdatum. Achterstallige regels tellen vanaf vandaag. Erbij: lopende huur met een bekende einddatum (ophaal- of verwachte retourdatum) en machines die al retour zijn (goederen-in, 0.05) maar in de vloot nog als verhuurd staan, telkens plus de gekozen verwerkingstijd. Depottransfers tellen (nog) niet mee: de uploads vermelden niet welke subgroepen erin zitten. Lopende huur zonder einddatum komt in deze periode niet terug. Per week toont de planner het laagste punt van die week.
        </p>
    </div>
</div>
@endsection

@push('scripts')
@if($detail)
@php
    $eigenReeks = collect($detail['per_depot'] ?? [])->firstWhere('depot', $f['depot'])['reeks'] ?? [];
    $labels = array_map(fn ($k) => $k['label'], $detail['kolommen'] ?? []);
@endphp
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
(() => {
    const data = @json($eigenReeks);
    new Chart(document.getElementById('spGrafiek'), {
        type: 'line',
        data: { labels: @json($labels), datasets: [{ label: 'Verwachte voorraad {{ $f['depot'] }}', data, stepped: true, borderColor: '#FF6600', backgroundColor: 'rgba(255,102,0,.08)', fill: true, pointRadius: 2,
            segment: { borderColor: c => (c.p1.parsed.y < 0 ? '#c62828' : '#FF6600') } }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: c => (c.tick.value === 0 ? '#c62828' : '#eee') } } } },
    });
})();
</script>
@endif
@endpush
