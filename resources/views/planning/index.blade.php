@extends('layouts.app')
@section('titel', $f['verleden'] ? 'Op te schonen' : 'Planning')
@section('inhoud')
<div class="page-header mb-2 d-flex justify-content-between align-items-end flex-wrap gap-2">
    <div>
        <h1>{{ $f['verleden'] ? 'Op te schonen' : 'Planning' }}</h1>
        <p>{{ $f['verleden'] ? 'Orders met een leverdatum vóór vandaag die nog in Smartview open staan — afsluiten of herplannen.' : 'Te leveren huur- en verkooporders, transfers en offertes; pickstand komt uit Smartview (8.7), regels en waarde uit 3.4.' }}</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('planning', array_filter(['q' => $f['q'], 'mijn' => request('mijn')])) }}" class="btn btn-sm {{ !$f['verleden'] && !$f['alles'] ? 'btn-boels' : 'btn-outline-boels' }}">Vandaag en later</a>
        <a href="{{ route('planning', array_filter(['verleden' => 1, 'q' => $f['q'], 'mijn' => request('mijn')])) }}" class="btn btn-sm {{ $f['verleden'] ? 'btn-boels' : 'btn-outline-boels' }}">Op te schonen</a>
        <a href="{{ route('planning', array_filter(['alles' => 1, 'q' => $f['q'], 'mijn' => request('mijn')])) }}" class="btn btn-sm {{ $f['alles'] ? 'btn-boels' : 'btn-outline-boels' }}">Alles</a>
    </div>
</div>
@include('partials.stand', ['uploads' => $res['uploads'] ?? [], 'soorten' => ['planning', 'reserveringen']])
@if($nietOpScanner > 0 && !$f['niet_op_scanner'])
<div class="alert alert-danger py-2 d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <span><i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Van {{ $nietOpScanner }} {{ $nietOpScanner === 1 ? 'order is het profiel' : 'orders zijn de profielen' }} niet ingevuld.</strong> De kolom PickUpInfo in Smartview (8.7) is leeg, dus de expeditie ziet {{ $nietOpScanner === 1 ? 'deze order' : 'deze orders' }} niet in InspHire Mobile en pickt {{ $nietOpScanner === 1 ? 'hem' : 'ze' }} niet. Vul {{ $nietOpScanner === 1 ? 'het profiel' : 'de profielen' }} in InspHire.</span>
    <a href="{{ request()->fullUrlWithQuery(['niet_op_scanner' => 1]) }}" class="btn btn-sm btn-danger">Toon {{ $nietOpScanner === 1 ? 'deze order' : 'deze orders' }}</a>
</div>
@elseif($f['niet_op_scanner'])
<div class="alert alert-danger py-2 mb-3"><i class="bi bi-exclamation-triangle-fill me-1"></i>Alleen orders waarvan de profielen niet ingevuld zijn (kolom PickUpInfo in Smartview leeg): de expeditie ziet ze niet in InspHire Mobile en pickt ze niet. <a href="{{ request()->fullUrlWithQuery(['niet_op_scanner' => null]) }}" class="alert-link">Alle orders tonen</a></div>
@endif

<form class="card mb-3" method="get">
    @if($f['verleden'])<input type="hidden" name="verleden" value="1">@endif @if($f['alles'])<input type="hidden" name="alles" value="1">@endif
    <div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
        <input type="search" name="q" value="{{ $f['q'] }}" class="form-control form-control-sm" style="max-width:260px" placeholder="Zoek klant, order, referentie…">
        <select name="soort" class="form-select form-select-sm" style="max-width:170px">
            <option value="">Alle soorten</option>
            @foreach(['huur'=>'Huurorders','verkoop'=>'Verkooporders','transfer_in'=>'Transfers in','transfer_uit'=>'Transfers uit','offerte'=>'Offertes','retour'=>'Retouren'] as $k=>$l)<option value="{{ $k }}" @selected($f['soort']===$k)>{{ $l }}</option>@endforeach
        </select>
        <select name="employee_id" class="form-select form-select-sm" style="max-width:200px">
            <option value="">Alle verkopers</option>
            @foreach($verkopers as $id => $naam)<option value="{{ $id }}" @selected((string)$f['employee_id']===(string)$id)>{{ $naam }}</option>@endforeach
        </select>
        <label class="form-check form-check-inline small mb-0"><input class="form-check-input" type="checkbox" name="mijn" value="1" @checked(request()->boolean('mijn'))> alleen mijn orders</label>
        <label class="form-check form-check-inline small mb-0"><input class="form-check-input" type="checkbox" name="bijhuur" value="1" @checked(request()->boolean('bijhuur'))> alleen bijhuur op lopende contracten</label>
        <label class="form-check form-check-inline small mb-0"><input class="form-check-input" type="checkbox" name="niet_op_scanner" value="1" @checked(request()->boolean('niet_op_scanner'))> alleen profielen niet ingevuld</label>
        <button class="btn btn-sm btn-boels">Filter</button>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-sm btn-outline-secondary" title="Excel/CSV"><i class="bi bi-file-earmark-spreadsheet"></i></a>
        <span class="ms-auto small text-muted">{{ $rijen->count() }} orders · {{ $rijen->where('pick_status', 'volledig')->count() }} volledig gepickt · {{ $rijen->where('pick_status', 'deels')->count() }} deels @if($transfersPerDag->isNotEmpty())· {{ $transfersPerDag->flatten(1)->count() }} transfers apart @endif</span>
    </div>
</form>

@forelse($perDag as $dag => $orders)
    <div class="dag-kop mt-3">
        <span>{{ $dag === 'geen' ? 'Zonder leverdatum' : \Carbon\Carbon::parse($dag)->translatedFormat('l j F Y') }}
            @if($dag === now()->toDateString())<span class="badge bg-boels ms-2">vandaag</span>@elseif($dag !== 'geen' && $dag < now()->toDateString())<span class="badge bg-danger ms-2">{{ relatief($dag) }}</span>@endif</span>
        <span class="small fw-normal">{{ $orders->count() }} orders · {{ $orders->where('pick_status','volledig')->count() }} volledig gepickt</span>
    </div>
    @foreach($orders as $o)
        @include('planning.rij', ['o' => $o])
    @endforeach
@empty
    <div class="card"><div class="card-body text-muted">Geen orders gevonden{{ $res === null ? ' (CORE niet bereikbaar of nog geen upload)' : '' }}.</div></div>
@endforelse

@if($transfersPerDag->isNotEmpty())
<h2 class="h5 mt-5 mb-1"><i class="bi bi-arrow-left-right me-1 text-boels"></i>Transfers tussen depots <span class="badge bg-secondary">{{ $transfersPerDag->flatten(1)->count() }}</span></h2>
<p class="small text-muted mb-2">Materieel dat van of naar een ander depot gaat; staat los van de klantplanning hierboven.</p>
@foreach($transfersPerDag as $dag => $orders)
    <div class="dag-kop mt-3" style="background:#00897b">
        <span>{{ $dag === 'geen' ? 'Zonder datum' : \Carbon\Carbon::parse($dag)->translatedFormat('l j F Y') }}
            @if($dag === now()->toDateString())<span class="badge bg-light text-dark ms-2">vandaag</span>@elseif($dag !== 'geen' && $dag < now()->toDateString())<span class="badge bg-danger ms-2">{{ relatief($dag) }}</span>@endif</span>
        <span class="small fw-normal">{{ $orders->count() }} transfers</span>
    </div>
    @foreach($orders as $o)
        @include('planning.rij', ['o' => $o])
    @endforeach
@endforeach
@endif
@endsection
