@extends('layouts.app')
@section('titel', 'Acties per persoon')
@section('inhoud')
<div class="page-header"><div><h1>Acties per persoon</h1><p>Alleen werk dat echt op naam staat: ruilverzoeken (Spoedverhuur), eigen onderdelenbestellingen (Servicedesk), eigen transportaanvragen (Planner), eigen inhuur die retour maar niet afgemeld is, en de Smartview-signalen (factuurrun zonder reden, orders op te schonen, verlopen offertes). Tellers per depot, zoals open schades of tankbeurten, staan op de CORE-tegels en tellen hier niet mee.</p></div></div>
@if(!$bereikbaar)<div class="alert alert-warning">CORE niet bereikbaar of geen rechten voor het teamoverzicht.</div>@endif
<div class="row g-3 mb-4">
@foreach($team->where('totaal', '>', 0) as $m)
    <div class="col-md-6 col-xl-3">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start"><div><strong>{{ $m['name'] }}</strong><div class="small text-muted">{{ $m['function'] ?? '' }}{{ !empty($m['depot']) ? ' · '.$m['depot'] : '' }}</div></div><div class="fs-3 fw-bold text-boels">{{ $m['totaal'] }}</div></div>
            <ul class="list-unstyled small mb-0 mt-2">
                @foreach($m['acties'] as $a)<li class="d-flex justify-content-between"><a href="{{ $a['url'] ?? '#' }}" class="text-reset text-decoration-none">{{ $a['label'] }}</a><span class="fw-semibold">{{ $a['aantal'] }}</span></li>@endforeach
            </ul>
        </div></div>
    </div>
@endforeach
</div>
@if($team->where('totaal', '>', 0)->isNotEmpty())
<div class="card mb-4"><div class="card-header">Overzicht</div><div class="card-body"><canvas id="acties" height="90"></canvas></div></div>
@endif
<div class="card"><div class="card-header">Alle teamleden ({{ $team->count() }})</div><div class="table-responsive"><table class="table table-sm mb-0">
    <thead><tr><th>Medewerker</th><th>Functie</th><th>Depot</th><th class="text-end">Open acties</th></tr></thead>
    <tbody>@foreach($team as $m)<tr><td>{{ $m['name'] }}</td><td class="small text-muted">{{ $m['function'] ?? '' }}</td><td class="small text-muted">{{ $m['depot'] ?? '' }}</td><td class="text-end {{ $m['totaal'] ? 'fw-bold' : 'text-muted' }}">{{ $m['totaal'] }}</td></tr>@endforeach</tbody>
</table></div></div>
@endsection
@push('scripts')
@if($team->where('totaal', '>', 0)->isNotEmpty())
<script src="{{ asset('js/chart.umd.min.js') }}?v=4.4.1"></script>
<script>if (typeof Chart === 'undefined') { document.querySelectorAll('canvas').forEach(c => c.insertAdjacentHTML('afterend', '<div class="text-danger small mt-2">Grafiekbibliotheek niet geladen (js/chart.umd.min.js).</div>')); }</script>
<script>
try {
const team = @json($chart);
const labels = @json($labels); const kleuren = ['#FF6600','#1976d2','#2e7d32','#6f42c1','#c62828','#00897b','#ef6c00','#5d4037','#455a64','#8e24aa'];
new Chart(document.getElementById('acties'), { type: 'bar', data: { labels: team.map(t => t.naam), datasets: labels.map((l, i) => ({ label: l, data: team.map(t => t.acties[l] || 0), backgroundColor: kleuren[i % kleuren.length] })) }, options: { responsive: true, scales: { x: { stacked: false }, y: { beginAtZero: true, ticks: { precision: 0 } } } } });

} catch (e) {
    console.error('Grafiek', e);
    document.querySelectorAll('canvas').forEach(c => { if (!c.nextElementSibling || !c.nextElementSibling.classList.contains('grafiek-fout')) c.insertAdjacentHTML('afterend', '<div class="grafiek-fout text-danger small mt-2">Grafiek kon niet laden: ' + (e && e.message ? e.message : e) + '</div>'); });
}
</script>
@endif
@endpush
