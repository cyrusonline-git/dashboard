@extends('layouts.app')
@section('titel', 'Ophaallijst')
@section('inhoud')
<div class="page-header mb-2"><h1>Ophaallijst — afgemeld, op te halen</h1><p>Contracten met status "To Collect" in de laatste WOW-export, op ophaaldatum. Rood = datum voorbij.</p></div>
@include('partials.stand', ['uploads' => ['wow' => ($res['upload'] ?? []) + ['label' => 'In huur (WOW)']]])
<form class="card mb-3" method="get"><div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
    <label class="small">van <input type="date" name="van" value="{{ $f['van'] ?? '' }}" class="form-control form-control-sm d-inline-block" style="width:auto"></label>
    <label class="small">t/m <input type="date" name="tm" value="{{ $f['tm'] ?? '' }}" class="form-control form-control-sm d-inline-block" style="width:auto"></label>
    <button class="btn btn-sm btn-boels">Filter</button>
    <span class="ms-auto small text-muted">{{ $rijen->count() }} contracten · {{ $rijen->sum('regels') }} regels · {{ $rijen->where('te_laat', true)->count() }} te laat</span>
</div></form>
@forelse($perDag as $dag => $items)
    <div class="dag-kop mt-3"><span>{{ $dag === 'geen' ? 'Zonder ophaaldatum' : \Carbon\Carbon::parse($dag)->translatedFormat('l j F Y') }} @if($dag !== 'geen' && $dag < now()->toDateString())<span class="badge bg-danger ms-2">{{ relatief($dag) }}</span>@elseif($dag === now()->toDateString())<span class="badge bg-boels ms-2">vandaag</span>@endif</span><span class="small fw-normal">{{ $items->count() }} contracten</span></div>
    @foreach($items as $r)
    <div class="order-rij {{ $r['te_laat'] ? 'te-laat' : '' }}">
        <div><a href="{{ route('huur.show', $r['contract_number']) }}" class="fw-semibold text-reset text-decoration-none">{{ $r['customer_name'] }}</a><div class="small text-muted">{{ $r['delivery_name'] }} · {{ $r['delivery_address'] }} {{ $r['delivery_postal_code'] }}</div></div>
        <div class="small">{{ $r['coll_time'] ? 'om '.$r['coll_time'] : '' }} @if($r['klant_haalt_af'])<span class="badge bg-secondary">klant brengt</span>@endif</div>
        <div class="small">{{ $r['regels'] }} regels · {{ $r['aantal'] + 0 }} stuks</div>
        <div>@if($r['coll_doc_printed'])<span class="badge bg-success">ophaalbon geprint</span>@else<span class="badge bg-light text-muted">geen bon</span>@endif</div>
        <div class="small text-muted text-truncate">{{ $r['project_description'] }}</div>
        <div class="text-end"><code>{{ $r['contract_number'] }}</code></div>
    </div>
    @endforeach
@empty
    <div class="card"><div class="card-body text-muted">Niets afgemeld{{ $res === null ? ' (CORE niet bereikbaar of nog geen upload)' : '' }}.</div></div>
@endforelse
@endsection
