@extends('layouts.app')
@section('titel', 'Vlootmeters')
@section('inhoud')
@include('voorraad._nav')
@php $t = $stand['totalen'] ?? []; @endphp
@php $perDepot = collect($stand['per_depot'] ?? []); @endphp
@php $depotNaam = $depot ? (($perDepot->firstWhere('depot', $depot)['naam'] ?? null) ?: 'depot '.$depot) : 'heel Industrial'; @endphp
<div class="page-header">
    <div>
        <h1>Vlootmeters <span class="text-muted fw-normal fs-5">{{ $depotNaam }}</span></h1>
        <p>Momentopname uit de vlootvoorraad (Smartview "Items With Every Status")@if(!empty($stand['upload'])), stand <strong>{{ \Carbon\Carbon::parse($stand['upload']['moment'])->format('d-m H:i') }}</strong>@if($stand['upload']['uren_oud'] >= 24) <span class="text-danger">({{ $stand['upload']['uren_oud'] }} uur oud)</span>@endif @endif. Bezetting = verhuurd ÷ alle machines.</p>
    </div>
    <form method="get" class="d-flex gap-2">
        <select name="depot" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Heel Industrial</option>
            @foreach($perDepot->sortBy('depot') as $d)<option value="{{ $d['depot'] }}" @selected($depot === $d['depot'])>{{ $d['depot'] }}{{ $d['naam'] ? ' · '.$d['naam'] : '' }}</option>@endforeach
        </select>
        <select name="dagen" class="form-select form-select-sm" onchange="this.form.submit()">@foreach([7, 30, 90] as $d)<option value="{{ $d }}" @selected($dagen === $d)>trend {{ $d }} dagen</option>@endforeach</select>
    </form>
</div>

@if(empty($stand['upload']))
    <div class="alert alert-warning">Nog geen vlootvoorraad geüpload in CORE (Beheer → Uploads Smartview → Vlootvoorraad).</div>
@else
<div class="row g-2 mb-3">
    @php
    $tegels = [
        ['Bezetting', ($stand['bezetting'] ?? 0).'%', 'bi-speedometer2', '#FF6600', number_format($stand['totaal'] ?? 0, 0, ',', '.').' machines'],
        ['Verhuurd', $t['verhuurd'] ?? 0, 'bi-truck', '#2e7d32', null],
        ['Beschikbaar', $t['beschikbaar'] ?? 0, 'bi-check2-circle', '#1976d2', null],
        ['In onderhoud', $t['onderhoud'] ?? 0, 'bi-wrench', '#ef6c00', null],
        ['In reparatie', $t['reparatie'] ?? 0, 'bi-tools', '#c62828', null],
        ['Onderweg', $t['onderweg'] ?? 0, 'bi-arrow-left-right', '#00897b', ($t['overig'] ?? 0) ? ($t['overig'].' overig') : null],
    ];
    @endphp
    @foreach($tegels as [$label, $waarde, $icoon, $kleur, $sub])
        <div class="col-6 col-md-4 col-xl-2"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:{{ $kleur }}"><i class="bi {{ $icoon }}"></i></div><div><div class="kpi-value">{{ is_numeric($waarde) ? number_format($waarde, 0, ',', '.') : $waarde }}</div><div class="kpi-label">{{ $label }}@if($sub)<br>{{ $sub }}@endif</div></div></div></div></div>
    @endforeach
</div>

<div class="row g-3 mb-3">
    <div class="col-xl-7">
        <div class="card h-100"><div class="card-header">Verloop bezetting en status</div><div class="card-body"><div style="height:280px"><canvas id="trend"></canvas></div>
            @if(count($stand['trend'] ?? []) < 2)<div class="small text-muted mt-2">De trend vult zich met elke upload. Upload de vlootvoorraad een paar keer per dag.</div>@endif
        </div></div>
    </div>
    <div class="col-xl-5">
        <div class="card h-100"><div class="card-header">Status nu</div><div class="card-body"><div style="height:280px"><canvas id="donut"></canvas></div></div></div>
    </div>
</div>

@if(!$depot)
<div class="card mb-3">
    <div class="card-header">Per depot <span class="small text-muted fw-normal">klik voor de meters van dat depot</span></div>
    <div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0">
        <thead><tr><th>Depot</th><th class="text-end">Machines</th><th style="width:34%">Status</th><th class="text-end">Bezetting</th><th class="text-end">Beschikb.</th><th class="text-end">Onderh.</th><th class="text-end">Rep.</th><th class="text-end">Onderweg</th></tr></thead>
        <tbody>
        @foreach($perDepot as $d)
            @php $s = $d['statussen']; @endphp
            <tr onclick="location='{{ route('vloot', ['depot' => $d['depot']]) }}'" style="cursor:pointer">
                <td><strong>{{ $d['depot'] }}</strong> {{ $d['naam'] ?? '' }}@if(!$d['naam'])<span class="small text-muted">(naam niet in CORE)</span>@endif</td>
                <td class="text-end">{{ number_format($d['totaal'], 0, ',', '.') }}</td>
                <td>
                    <div class="progress" style="height:12px" title="verhuurd {{ $s['verhuurd'] }} · beschikbaar {{ $s['beschikbaar'] }} · onderhoud {{ $s['onderhoud'] }} · reparatie {{ $s['reparatie'] }} · onderweg {{ $s['onderweg'] }}">
                        @foreach(['verhuurd' => '#2e7d32', 'beschikbaar' => '#1976d2', 'onderhoud' => '#ef6c00', 'reparatie' => '#c62828', 'onderweg' => '#00897b', 'overig' => '#9e9e9e'] as $k => $kl)
                            <div class="progress-bar" style="width:{{ $d['totaal'] ? 100 * $s[$k] / $d['totaal'] : 0 }}%;background:{{ $kl }}"></div>
                        @endforeach
                    </div>
                </td>
                <td class="text-end fw-semibold">{{ $d['bezetting'] }}%</td>
                <td class="text-end">{{ $s['beschikbaar'] }}</td>
                <td class="text-end">{{ $s['onderhoud'] }}</td>
                <td class="text-end">{{ $s['reparatie'] }}</td>
                <td class="text-end">{{ $s['onderweg'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    <div class="card-footer bg-white small text-muted">
        @foreach(['verhuurd' => '#2e7d32', 'beschikbaar' => '#1976d2', 'onderhoud' => '#ef6c00', 'reparatie' => '#c62828', 'onderweg' => '#00897b', 'overig' => '#9e9e9e'] as $k => $kl)<span class="me-3"><span style="display:inline-block;width:10px;height:10px;background:{{ $kl }};border-radius:2px"></span> {{ $k }}</span>@endforeach
    </div>
</div>
@endif

@if($detail && !empty($detail['depots']))
<div class="card mb-3 border-warning">
    <div class="card-header d-flex justify-content-between"><span>Subgroep <code>{{ $detail['subgroep'] }}</code> {{ $detail['omschrijving'] }}: waar staat hij?</span><a href="{{ request()->fullUrlWithQuery(['subgroep' => null]) }}" class="small">sluiten</a></div>
    <div class="table-responsive"><table class="table table-sm mb-0">
        <thead><tr><th>Depot</th><th class="text-end">Beschikbaar</th><th class="text-end">Verhuurd</th><th class="text-end">Onderhoud</th><th class="text-end">Reparatie</th><th class="text-end">Onderweg</th><th class="text-end">Totaal</th></tr></thead>
        <tbody>@foreach($detail['depots'] as $d)<tr><td>{{ $d['depot'] }} {{ $d['naam'] }}</td><td class="text-end fw-semibold text-primary">{{ $d['beschikbaar'] }}</td><td class="text-end">{{ $d['verhuurd'] }}</td><td class="text-end">{{ $d['onderhoud'] }}</td><td class="text-end">{{ $d['reparatie'] }}</td><td class="text-end">{{ $d['onderweg'] }}</td><td class="text-end">{{ $d['totaal'] }}</td></tr>@endforeach</tbody>
    </table></div>
</div>
@endif

<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <span>Subgroepen {{ $depot ? 'in '.$depotNaam : 'overal' }}</span>
        <form method="get" class="d-flex gap-2">
            <input type="hidden" name="depot" value="{{ $depot }}"><input type="hidden" name="dagen" value="{{ $dagen }}">
            <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="Subgroepnr of omschrijving">
            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach(['totaal' => 'Meeste machines', 'bezetting' => 'Hoogste bezetting', 'beschikbaar' => 'Meeste beschikbaar', 'onderhoud' => 'Meeste onderhoud/reparatie'] as $k => $l)<option value="{{ $k }}" @selected($sort === $k)>{{ $l }}</option>@endforeach
            </select>
            <button class="btn btn-sm btn-boels">Zoek</button>
        </form>
    </div>
    <div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0">
        <thead><tr><th>Subgroep</th><th class="text-end">Totaal</th><th class="text-end">Verhuurd</th><th class="text-end">Beschikb.</th><th class="text-end">Onderh.</th><th class="text-end">Rep.</th><th class="text-end">Onderweg</th><th style="width:160px">Bezetting</th></tr></thead>
        <tbody>
        @forelse($subgroepen as $s)
            <tr onclick="location='{{ request()->fullUrlWithQuery(['subgroep' => $s['subgroep']]) }}'" style="cursor:pointer">
                <td><code>{{ $s['subgroep'] }}</code> {{ $s['omschrijving'] }}</td>
                <td class="text-end">{{ $s['totaal'] }}</td><td class="text-end">{{ $s['verhuurd'] }}</td><td class="text-end fw-semibold">{{ $s['beschikbaar'] }}</td>
                <td class="text-end">{{ $s['onderhoud'] }}</td><td class="text-end">{{ $s['reparatie'] }}</td><td class="text-end">{{ $s['onderweg'] }}</td>
                <td><div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height:8px"><div class="progress-bar" style="width:{{ $s['bezetting'] }}%;background:{{ $s['bezetting'] >= 80 ? '#c62828' : ($s['bezetting'] >= 50 ? '#FF6600' : '#2e7d32') }}"></div></div><span class="small">{{ $s['bezetting'] }}%</span></div></td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">Geen subgroepen gevonden.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endif
@endsection

@push('scripts')
@if(!empty($stand['upload']))
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
(() => {
    const trend = @json($stand['trend'] ?? []);
    const t = @json($t);
    const kl = { verhuurd: '#2e7d32', beschikbaar: '#1976d2', onderhoud: '#ef6c00', reparatie: '#c62828', onderweg: '#00897b', overig: '#9e9e9e' };
    const lbl = trend.map(x => { const d = new Date(x.moment.replace(' ', 'T')); return d.toLocaleDateString('nl-NL', { day: '2-digit', month: '2-digit' }) + ' ' + d.toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' }); });
    new Chart(document.getElementById('trend'), {
        data: { labels: lbl, datasets: [
            { type: 'line', label: 'Bezetting %', data: trend.map(x => x.bezetting), borderColor: '#FF6600', backgroundColor: '#FF6600', yAxisID: 'pct', tension: .25, pointRadius: 3 },
            ...['verhuurd', 'beschikbaar', 'onderhoud', 'reparatie'].map(k => ({ type: 'bar', label: k, data: trend.map(x => x[k]), backgroundColor: kl[k] + 'cc', stack: 's', yAxisID: 'n' })),
        ] },
        options: { maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
            scales: { n: { stacked: true, position: 'left', beginAtZero: true }, x: { stacked: true }, pct: { position: 'right', min: 0, max: 100, grid: { drawOnChartArea: false }, ticks: { callback: v => v + '%' } } },
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } } },
    });
    const keys = Object.keys(kl).filter(k => (t[k] || 0) > 0);
    new Chart(document.getElementById('donut'), {
        type: 'doughnut',
        data: { labels: keys, datasets: [{ data: keys.map(k => t[k]), backgroundColor: keys.map(k => kl[k]) }] },
        options: { maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'right' } } },
    });
})();
</script>
@endif
@endpush
