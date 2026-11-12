@extends('layouts.app')
@section('titel', 'AVI — weekhuur in huur')
@section('inhoud')
<div class="page-header">
    <div><h1>AVI — weekhuur in huur</h1><p>Netto weekhuur (weektarief na korting × aantal) van alles wat nu bij klanten staat, uit de laatste WOW-export. De trend groeit met elke dagelijkse upload.</p></div>
    <a href="{{ route('huur.avi', ['export' => 'csv']) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel</a>
</div>
@include('partials.stand', ['uploads' => ['wow' => ($s['upload'] ?? []) + ['label' => 'In huur (WOW)']]])
@if(empty($s['beschikbaar']))
    <div class="alert alert-warning">Nog geen WOW-upload of CORE niet bereikbaar.</div>
@else
@php($trend = collect($s['trend']))
@php($vorige = $trend->count() > 1 ? $trend[$trend->count() - 2] : null)
@php($delta = $vorige && $vorige['weekhuur'] ? $s['weekhuur'] - $vorige['weekhuur'] : null)
@php($afgemeldGeld = (float) collect($s['per_status_weekhuur'])->filter(fn($r) => str_contains($r['status'], 'To Collect'))->sum('weekhuur'))
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon"><i class="bi bi-cash-coin"></i></div><div><div class="kpi-value">{{ euro($s['weekhuur']) }}</div><div class="kpi-label">weekhuur totaal @if($delta !== null)· <span class="{{ $delta >= 0 ? 'text-success' : 'text-danger' }}">{{ $delta >= 0 ? '+' : '' }}{{ euro($delta) }} t.o.v. {{ dmy($vorige['datum']) }}</span>@endif</div></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#1976d2"><i class="bi bi-file-earmark-text"></i></div><div><div class="kpi-value">{{ $s['contracten'] }}</div><div class="kpi-label">contracten · {{ number_format($s['regels'], 0, ',', '.') }} regels · {{ count($s['per_klant']) }} klanten</div></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#6f42c1"><i class="bi bi-diagram-3"></i></div><div><div class="kpi-value">{{ euro($s['weekhuur_projecten']) }}</div><div class="kpi-label">waarvan op projecten ({{ $s['weekhuur'] > 0 ? number_format($s['weekhuur_projecten'] / $s['weekhuur'] * 100, 0) : 0 }}%) · {{ count($s['per_project']) }} projecten</div></div></div></div></div>
    <div class="col-6 col-xl-3"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:#c62828"><i class="bi bi-arrow-return-left"></i></div><div><div class="kpi-value">{{ euro($afgemeldGeld) }}</div><div class="kpi-label">weekhuur afgemeld, nog op te halen · {{ $s['to_collect_contracten'] }} contracten</div></div></div></div></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-xl-8">
        <div class="card h-100"><div class="card-header d-flex justify-content-between"><span>AVI in de tijd</span><span class="small text-muted fw-normal">{{ $trend->count() }} meetpunt{{ $trend->count() === 1 ? '' : 'en' }} · één per uploaddag</span></div>
            <div class="card-body">
                @if($trend->count() < 2)<div class="small text-muted mb-2"><i class="bi bi-info-circle me-1"></i>Na de volgende dagelijkse upload verschijnt hier de lijn; nu is er één meetpunt.</div>@endif
                <canvas id="trend" height="95"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100"><div class="card-header">Verdeling weekhuur</div>
            <div class="card-body"><canvas id="verdeling" height="190"></canvas></div>
        </div>
    </div>
</div>
<div class="row g-3 mb-3">
    <div class="col-xl-6">
        <div class="card h-100"><div class="card-header">Top 10 klanten (weekhuur)</div><div class="card-body"><canvas id="topklanten" height="230"></canvas></div></div>
    </div>
    <div class="col-xl-6">
        <div class="card h-100"><div class="card-header d-flex justify-content-between"><span>Weekhuur naar startmaand van de huur</span><span class="small text-muted fw-normal">hoe lang staat het al uit</span></div><div class="card-body"><canvas id="maanden" height="230"></canvas></div></div>
    </div>
</div>
@if($trend->count() > 1)
<div class="card mb-3"><div class="card-header">Top 5 klanten in de tijd</div><div class="card-body"><canvas id="klantentrend" height="80"></canvas></div></div>
@endif

<div class="row g-3">
    <div class="col-lg-7"><div class="card"><div class="card-header">Per klant</div><div class="table-responsive"><table class="table table-sm mb-0">
        <thead><tr><th>#</th><th>Klant</th><th class="text-end">Contracten</th><th class="text-end">Regels</th><th class="text-end">Afgemeld</th><th class="text-end">Weekhuur</th><th style="width:160px">Aandeel</th></tr></thead>
        <tbody>@foreach($s['per_klant'] as $i => $k)@php($p = $s['weekhuur'] > 0 ? $k['weekhuur'] / $s['weekhuur'] * 100 : 0)<tr><td class="text-muted">{{ $i+1 }}</td><td><a href="{{ route('huur', ['customer' => $k['customer_number']]) }}">{{ $k['customer_name'] }}</a></td><td class="text-end">{{ $k['contracten'] }}</td><td class="text-end">{{ $k['regels'] }}</td><td class="text-end {{ $k['to_collect'] ? 'text-danger' : '' }}">{{ $k['to_collect'] ?: '' }}</td><td class="text-end">{{ euro($k['weekhuur']) }}</td><td><div class="d-flex align-items-center gap-2"><div class="flex-grow-1 bg-light rounded" style="height:8px"><div class="rounded" style="height:8px;width:{{ min(100, $p * 2.5) }}%;background:var(--boels-orange)"></div></div><span class="small text-muted" style="width:44px">{{ number_format($p, 1, ',', '.') }}%</span></div></td></tr>@endforeach</tbody>
    </table></div></div></div>
    <div class="col-lg-5"><div class="card"><div class="card-header">Per project</div><div class="table-responsive"><table class="table table-sm mb-0">
        <thead><tr><th>Project</th><th>Klant</th><th class="text-end">Contracten</th><th class="text-end">Weekhuur</th></tr></thead>
        <tbody>@foreach($s['per_project'] as $p)<tr><td><a href="{{ route('huur', ['project' => $p['project_number']]) }}">{{ $p['project_description'] ?? $p['project_number'] }}</a><div class="small text-muted">{{ $p['project_number'] }}</div></td><td class="small">{{ $p['customer_name'] }}</td><td class="text-end">{{ $p['contracten'] }}</td><td class="text-end">{{ euro($p['weekhuur']) }}</td></tr>@endforeach</tbody>
    </table></div></div></div>
</div>
@endif
@endsection
@push('scripts')
@if(!empty($s['beschikbaar']))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = 'Inter, system-ui, sans-serif'; Chart.defaults.color = '#6b7280';
const eur = v => '€ ' + Math.round(v).toLocaleString('nl-NL');
const nlDatum = d => new Date(d).toLocaleDateString('nl-NL', { day: 'numeric', month: 'short' });
const trend = @json($s['trend']);
new Chart(document.getElementById('trend'), { type: 'line', data: { labels: trend.map(t => nlDatum(t.datum)), datasets: [
    { label: 'Weekhuur', data: trend.map(t => t.weekhuur), borderColor: '#FF6600', backgroundColor: 'rgba(255,102,0,.12)', fill: true, tension: .3, pointRadius: trend.length < 15 ? 4 : 2, yAxisID: 'y' },
    { label: 'Contracten', data: trend.map(t => t.contracten), borderColor: '#1976d2', borderDash: [4, 4], tension: .3, pointRadius: trend.length < 15 ? 3 : 0, yAxisID: 'y2' } ] },
  options: { interaction: { mode: 'index', intersect: false }, plugins: { tooltip: { callbacks: { label: c => c.dataset.label + ': ' + (c.dataset.yAxisID === 'y' ? eur(c.raw) : c.raw) } } },
    scales: { y: { ticks: { callback: eur }, grid: { color: '#eef0f2' } }, y2: { position: 'right', grid: { display: false }, ticks: { precision: 0 } }, x: { grid: { display: false } } } } });
const top = @json(collect($s['per_klant'])->take(10)->values());
new Chart(document.getElementById('topklanten'), { type: 'bar', data: { labels: top.map(k => k.customer_name.length > 28 ? k.customer_name.slice(0, 27) + '…' : k.customer_name), datasets: [{ data: top.map(k => k.weekhuur), backgroundColor: '#FF6600', borderRadius: 6 }] },
  options: { indexAxis: 'y', plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => eur(c.raw) + ' per week' } } }, scales: { x: { ticks: { callback: eur }, grid: { color: '#eef0f2' } }, y: { grid: { display: false } } } } });
const projecten = {{ (float) $s['weekhuur_projecten'] }}, totaal = {{ (float) $s['weekhuur'] }}, afgemeld = {{ $afgemeldGeld }};
new Chart(document.getElementById('verdeling'), { type: 'doughnut', data: { labels: ['Op projecten', 'Losse huur', 'Afgemeld (op te halen)'], datasets: [{ data: [projecten, Math.max(0, totaal - projecten - afgemeld), afgemeld], backgroundColor: ['#6f42c1', '#FF6600', '#c62828'], borderWidth: 2 }] },
  options: { cutout: '62%', plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: c => c.label + ': ' + eur(c.raw) + ' (' + Math.round(c.raw / totaal * 100) + '%)' } } } } });
const alleMaanden = @json($s['per_maand']);
// Laatste 18 maanden los, alles daarvoor samengevat als "eerder"
const recent = alleMaanden.slice(-18), ouder = alleMaanden.slice(0, -18);
const maanden = ouder.length ? [{ maand: 'eerder', contracten: ouder.reduce((a, m) => a + +m.contracten, 0), weekhuur: ouder.reduce((a, m) => a + +m.weekhuur, 0) }, ...recent] : recent;
const mLabel = m => { if (m === 'eerder') return 'eerder'; const [j, mm] = m.split('-'); return ['jan','feb','mrt','apr','mei','jun','jul','aug','sep','okt','nov','dec'][+mm - 1] + ' ' + j.slice(2); };
new Chart(document.getElementById('maanden'), { type: 'bar', data: { labels: maanden.map(m => mLabel(m.maand)), datasets: [{ label: 'Weekhuur', data: maanden.map(m => m.weekhuur), backgroundColor: maanden.map((m, i) => i >= maanden.length - 3 ? '#FF6600' : '#f3b58a'), borderRadius: 4 }] },
  options: { plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => eur(c.raw) + ' per week · ' + maanden[c.dataIndex].contracten + ' contracten' } } }, scales: { y: { ticks: { callback: eur }, grid: { color: '#eef0f2' } }, x: { grid: { display: false } } } } });
@if($trend->count() > 1)
const namen = Object.keys(trend[trend.length - 1].top_klanten || {}).slice(0, 5); const kleuren = ['#FF6600','#1976d2','#2e7d32','#6f42c1','#00897b'];
new Chart(document.getElementById('klantentrend'), { type: 'line', data: { labels: trend.map(t => nlDatum(t.datum)), datasets: namen.map((n, i) => ({ label: n, data: trend.map(t => (t.top_klanten || {})[n] ?? null), borderColor: kleuren[i], tension: .3, pointRadius: 2 })) },
  options: { plugins: { tooltip: { callbacks: { label: c => c.dataset.label + ': ' + eur(c.raw) } } }, scales: { y: { ticks: { callback: eur }, grid: { color: '#eef0f2' } }, x: { grid: { display: false } } } } });
@endif
</script>
@endif
@endpush
