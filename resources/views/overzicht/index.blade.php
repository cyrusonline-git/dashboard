@extends('layouts.app')
@section('titel', 'Managementoverzicht')
@section('inhoud')
<div class="page-header"><div><h1>Managementoverzicht</h1><p>De belangrijkste cijfers uit Smartview en de Boels-apps bij elkaar. Klik op een blok om naar de lijst te gaan.</p></div><a href="{{ route('team') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-people me-1"></i>Acties per persoon</a></div>
@include('partials.stand', ['uploads' => $uploads])
@if(!$kpi)
    <div class="alert alert-warning">Geen cijfers beschikbaar: CORE niet bereikbaar, geen uploads of geen managersrol.</div>
@else
@php($fr = $kpi['factuurrun'])
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><a href="{{ route('huur.avi') }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#2e7d32"><i class="bi bi-cash-coin"></i></div><div><div class="kpi-value">{{ euro($kpi['weekhuur']) }}</div><div class="kpi-label">weekhuur in huur (AVI) @if($kpi['weekhuur_delta'] !== null)· <span class="{{ $kpi['weekhuur_delta'] >= 0 ? 'text-success' : 'text-danger' }}">{{ $kpi['weekhuur_delta'] >= 0 ? '+' : '' }}{{ euro($kpi['weekhuur_delta']) }}</span>@endif</div></div></div></div></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('huur') }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#1976d2"><i class="bi bi-box-seam"></i></div><div><div class="kpi-value">{{ $kpi['contracten'] }}</div><div class="kpi-label">contracten in huur · {{ $kpi['klanten'] }} klanten</div></div></div></div></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('huur.ophalen') }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#c62828"><i class="bi bi-arrow-return-left"></i></div><div><div class="kpi-value">{{ $kpi['ophalen_contracten'] }}</div><div class="kpi-label">contracten afgemeld, op te halen · <span class="{{ $kpi['ophalen_te_laat'] ? 'text-danger fw-semibold' : '' }}">{{ $kpi['ophalen_te_laat'] }} te laat</span></div></div></div></div></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('planning') }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon"><i class="bi bi-truck"></i></div><div><div class="kpi-value">{{ $kpi['orders_week'] }}</div><div class="kpi-label">orders komende 7 dagen · {{ $kpi['orders_week'] ? round($kpi['orders_week_volledig'] / $kpi['orders_week'] * 100) : 0 }}% volledig gepickt</div></div></div></div></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('planning', ['verleden' => 1]) }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#777"><i class="bi bi-eraser"></i></div><div><div class="kpi-value">{{ $kpi['op_te_schonen'] }}</div><div class="kpi-label">orders op te schonen (leverdatum voorbij)</div></div></div></div></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('planbord') }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#00897b"><i class="bi bi-arrow-left-right"></i></div><div><div class="kpi-value">{{ $kpi['transfers_in'] + $kpi['transfers_uit'] }}</div><div class="kpi-label">transfers open · {{ $kpi['transfers_in'] }} in / {{ $kpi['transfers_uit'] }} uit</div></div></div></div></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('factuurrun', ['mijn' => 0]) }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#6f42c1"><i class="bi bi-receipt"></i></div><div><div class="kpi-value">{{ $fr['contracten'] }}</div><div class="kpi-label">uit factuurrun · <span class="{{ $fr['zonder_reden'] ? 'text-warning fw-semibold' : '' }}">{{ $fr['zonder_reden'] }} zonder reden</span> · {{ $fr['nooit'] }} nooit gefactureerd · {{ euro($fr['weekhuur']) }}/wk</div></div></div></div></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('planning', ['soort' => 'offerte', 'alles' => 1]) }}" class="text-decoration-none text-reset"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#8e24aa"><i class="bi bi-file-earmark-text"></i></div><div><div class="kpi-value">{{ $kpi['offertes']['open'] }}</div><div class="kpi-label">offertes open · <span class="{{ $kpi['offertes']['verlopen'] ? 'text-danger fw-semibold' : '' }}">{{ $kpi['offertes']['verlopen'] }} verlopen</span></div></div></div></div></a></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-xl-4"><div class="card h-100"><div class="card-header">Planning komende 7 dagen</div><div class="card-body"><canvas id="week" height="170"></canvas></div></div></div>
    <div class="col-xl-4"><div class="card h-100"><div class="card-header">AVI in de tijd</div><div class="card-body"><canvas id="avi" height="170"></canvas>@if(count($kpi['trend']) < 2)<div class="small text-muted mt-2">Vanaf de volgende WOW-upload ontstaat de lijn.</div>@endif</div></div></div>
    <div class="col-xl-4"><div class="card h-100"><div class="card-header">Factuurrun naar dagen zonder factuur <small class="text-muted fw-normal">(weekhuur)</small></div><div class="card-body"><canvas id="ouderdom" height="170"></canvas></div></div></div>
</div>

<h2 class="h5 mt-4 mb-2">Apps</h2>
<div class="row g-3 mb-3">
    @foreach($apps as $sleutel => $a)
    <div class="col-6 col-md-4 col-xl-2">
        <a href="{{ $a['url'] ?? '#' }}" target="_blank" class="text-decoration-none text-reset"><div class="card h-100 {{ $a['ok'] ? '' : 'opacity-50' }}"><div class="card-body py-3">
            <div class="fw-semibold small mb-1">{{ $a['label'] }}</div>
            @if($a['ok'])
                @foreach($a['cijfers'] as $lbl => $n)<div class="d-flex justify-content-between small"><span class="text-muted">{{ $lbl }}</span><strong class="{{ $n > 0 && in_array($lbl, ['Probleem','Retour niet afgemeld']) ? 'text-danger' : '' }}">{{ $n }}</strong></div>@endforeach
            @else
                <div class="small text-muted">niet bereikbaar</div>
            @endif
        </div></div></a>
    </div>
    @endforeach
</div>

<h2 class="h5 mt-4 mb-2">Team <small class="text-muted fw-normal">open werk op naam</small></h2>
<div class="card"><div class="table-responsive"><table class="table table-sm mb-0 align-middle">
    @php($labels = collect($team)->flatMap(fn($m) => array_keys($m['per']))->unique()->values())
    <thead><tr><th>Medewerker</th><th>Functie</th>@foreach($labels as $l)<th class="text-end small">{{ $l }}</th>@endforeach<th class="text-end">Totaal</th></tr></thead>
    <tbody>
    @forelse($team as $m)
        <tr><td>{{ $m['naam'] }}</td><td class="small text-muted">{{ $m['functie'] }}</td>@foreach($labels as $l)<td class="text-end">{{ $m['per'][$l] ?? '' }}</td>@endforeach<td class="text-end fw-bold">{{ $m['totaal'] }}</td></tr>
    @empty
        <tr><td colspan="{{ 3 + $labels->count() }}" class="text-muted p-3">Niemand heeft open werk op naam.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
@endif
@endsection
@push('scripts')
@if($kpi)
<script src="{{ asset('js/chart.umd.min.js') }}?v=4.4.1"></script>
<script>
try {
    Chart.defaults.font.family = 'Inter, system-ui, sans-serif'; Chart.defaults.color = '#6b7280';
    const eur = v => '€ ' + Math.round(v).toLocaleString('nl-NL');
    const w = @json($o['planning_week']);
    new Chart(document.getElementById('week'), { type: 'bar', data: { labels: w.map(d => new Date(d.datum).toLocaleDateString('nl-NL', { weekday: 'short', day: 'numeric' })), datasets: [
        { label: 'Volledig gepickt', data: w.map(d => d.volledig), backgroundColor: '#2e7d32', borderRadius: 4 }, { label: 'Deels', data: w.map(d => d.deels), backgroundColor: '#ef6c00', borderRadius: 4 }, { label: 'Nog niet', data: w.map(d => d.niets), backgroundColor: '#cfd4d9', borderRadius: 4 } ] },
      options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } }, scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } } } });
    const t = @json($kpi['trend']);
    new Chart(document.getElementById('avi'), { type: 'line', data: { labels: t.map(x => new Date(x.datum).toLocaleDateString('nl-NL', { day: 'numeric', month: 'short' })), datasets: [{ label: 'Weekhuur', data: t.map(x => x.weekhuur), borderColor: '#FF6600', backgroundColor: 'rgba(255,102,0,.12)', fill: true, tension: .3, pointRadius: 4 }] },
      options: { plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => eur(c.raw) } } }, scales: { y: { ticks: { callback: eur }, grid: { color: '#eef0f2' } }, x: { grid: { display: false } } } } });
    const o = @json($fr['ouderdom']), on = @json($fr['ouderdom_n']);
    new Chart(document.getElementById('ouderdom'), { type: 'bar', data: { labels: Object.keys(o).map(k => k + ' dagen'), datasets: [{ data: Object.values(o), backgroundColor: ['#9ccc65', '#ffb74d', '#ef6c00', '#c62828'], borderRadius: 6 }] },
      options: { plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => eur(c.raw) + ' per week · ' + Object.values(on)[c.dataIndex] + ' contracten' } } }, scales: { y: { ticks: { callback: eur }, grid: { color: '#eef0f2' } }, x: { grid: { display: false } } } } });
} catch (e) { console.error('Grafiek', e); document.querySelectorAll('canvas').forEach(c => c.insertAdjacentHTML('afterend', '<div class="text-danger small mt-2">Grafiek kon niet laden: ' + e.message + '</div>')); }
</script>
@endif
@endpush
