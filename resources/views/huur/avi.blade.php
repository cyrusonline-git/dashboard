@extends('layouts.app')
@section('titel', 'AVI — weekhuur per klant')
@section('inhoud')
<div class="page-header mb-2"><h1>AVI — weekhuur in huur</h1><p>Som van de netto weekhuur (weektarief na korting × aantal) van alles wat in huur staat, per klant en per project, uit de laatste WOW-export.</p></div>
@include('partials.stand', ['uploads' => ['wow' => ($s['upload'] ?? []) + ['label' => 'In huur (WOW)']]])
@if(empty($s['beschikbaar']))
    <div class="alert alert-warning">Nog geen WOW-upload of CORE niet bereikbaar.</div>
@else
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="card p-3 text-center"><div class="fs-3 fw-bold text-boels">{{ euro($s['weekhuur']) }}</div><div class="small text-muted">weekhuur totaal</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 text-center"><div class="fs-3 fw-bold">{{ $s['contracten'] }}</div><div class="small text-muted">contracten in huur</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 text-center"><div class="fs-3 fw-bold">{{ count($s['per_klant']) }}</div><div class="small text-muted">klanten</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-3 text-center"><div class="fs-3 fw-bold">{{ $s['to_collect_contracten'] }}</div><div class="small text-muted">contracten (deels) afgemeld</div></div></div>
</div>
@if(count($s['trend']) > 1)
<div class="card mb-3"><div class="card-header">Trend weekhuur (per upload)</div><div class="card-body"><canvas id="trend" height="70"></canvas></div></div>
@endif
<div class="row g-3">
    <div class="col-lg-7"><div class="card"><div class="card-header">Per klant</div><div class="table-responsive"><table class="table table-sm mb-0">
        <thead><tr><th>#</th><th>Klant</th><th class="text-end">Contracten</th><th class="text-end">Regels</th><th class="text-end">Afgemeld</th><th class="text-end">Weekhuur</th><th class="text-end">%</th></tr></thead>
        <tbody>@foreach($s['per_klant'] as $i => $k)<tr><td class="text-muted">{{ $i+1 }}</td><td><a href="{{ route('huur', ['customer' => $k['customer_number']]) }}">{{ $k['customer_name'] }}</a></td><td class="text-end">{{ $k['contracten'] }}</td><td class="text-end">{{ $k['regels'] }}</td><td class="text-end {{ $k['to_collect'] ? 'text-danger' : '' }}">{{ $k['to_collect'] ?: '' }}</td><td class="text-end">{{ euro($k['weekhuur']) }}</td><td class="text-end text-muted">{{ $s['weekhuur'] > 0 ? number_format($k['weekhuur'] / $s['weekhuur'] * 100, 1, ',', '.') : 0 }}%</td></tr>@endforeach</tbody>
    </table></div></div></div>
    <div class="col-lg-5"><div class="card"><div class="card-header">Per project</div><div class="table-responsive"><table class="table table-sm mb-0">
        <thead><tr><th>Project</th><th>Klant</th><th class="text-end">Contracten</th><th class="text-end">Weekhuur</th></tr></thead>
        <tbody>@foreach($s['per_project'] as $p)<tr><td><a href="{{ route('huur', ['project' => $p['project_number']]) }}">{{ $p['project_description'] ?? $p['project_number'] }}</a><div class="small text-muted">{{ $p['project_number'] }}</div></td><td class="small">{{ $p['customer_name'] }}</td><td class="text-end">{{ $p['contracten'] }}</td><td class="text-end">{{ euro($p['weekhuur']) }}</td></tr>@endforeach</tbody>
    </table></div></div></div>
</div>
@endif
@endsection
@push('scripts')
@if(!empty($s['trend']) && count($s['trend']) > 1)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('trend'), { type: 'line', data: { labels: @json(collect($s['trend'])->pluck('datum')), datasets: [{ label: 'Weekhuur €', data: @json(collect($s['trend'])->pluck('weekhuur')), borderColor: '#FF6600', backgroundColor: 'rgba(255,102,0,.15)', fill: true, tension: .3 }] }, options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => '€ ' + v.toLocaleString('nl-NL') } } } } });
</script>
@endif
@endpush
