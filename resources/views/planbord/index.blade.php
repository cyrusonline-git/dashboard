@extends('layouts.app')
@section('titel', 'Planbord')
@section('inhoud')
<div class="page-header mb-2 d-flex justify-content-between align-items-end flex-wrap gap-2">
    <div><h1>Planbord</h1><p>Huur- en verkooporders die eraan komen, op leverdatum. Pickstand komt uit InspHire/Smartview en verandert met de volgende upload. Oude orders staan onder Planning → Op te schonen.</p></div>
    <form method="get" class="d-flex gap-2 align-items-center small">tot <select name="dagen" class="form-select form-select-sm" onchange="this.form.submit()">@foreach([1=>'morgen',3=>'3 dagen',7=>'7 dagen',14=>'14 dagen',30=>'30 dagen'] as $d=>$l)<option value="{{ $d }}" @selected($dagen===$d)>{{ $l }}</option>@endforeach</select></form>
</div>
@include('partials.stand', ['uploads' => $uploads, 'soorten' => ['planning', 'reserveringen']])
<div class="small text-muted mb-2">{{ $totaal['orders'] }} orders · {{ $totaal['volledig'] }} volledig gepickt · {{ $ophalen->count() }} op te halen · {{ $totaal['transfers'] }} transfers</div>

<div class="bord">
    @foreach($kolommen as $k => $label)
    <div class="kolom">
        <div class="kolom-kop"><span>{{ $label }}</span><span class="badge bg-boels">{{ $perKolom[$k]->count() }}</span></div>
        @foreach($perKolom[$k] as $o)@include('planbord.kaart', ['o' => $o])@endforeach
        @if($perKolom[$k]->isEmpty())<div class="small text-muted px-1">—</div>@endif
    </div>
    @endforeach
    <div class="kolom">
        <div class="kolom-kop"><span><i class="bi bi-arrow-return-left me-1"></i>Op te halen</span><span class="badge bg-danger">{{ $ophalen->count() }}</span></div>
        @foreach($ophalen as $r)
        <div class="kaart" style="cursor:default">
            <div class="kaart-kop" style="background:#c62828"><span>Retour</span>@if($r['coll_doc_printed'])<span title="ophaalbon geprint"><i class="bi bi-printer"></i></span>@endif</div>
            <div class="kaart-body"><a href="{{ route('huur.show', $r['contract_number']) }}" class="kaart-titel text-reset text-decoration-none"><code class="text-boels">{{ $r['contract_number'] }}</code> {{ $r['customer_name'] }}</a>
                <div class="small text-muted">{{ $r['coll_date'] ? \Carbon\Carbon::parse($r['coll_date'])->translatedFormat('D j M') : 'geen datum' }} {{ $r['coll_time'] }} · {{ relatief($r['coll_date']) }}</div>
                <div class="small">{{ $r['regels'] }} regels · {{ $r['delivery_name'] }}</div></div>
        </div>
        @endforeach
        @if($ophalen->isEmpty())<div class="small text-muted px-1">—</div>@endif
    </div>
</div>

<h2 class="h5 mt-4 mb-2"><i class="bi bi-arrow-left-right me-1 text-boels"></i>Transfers tussen depots <span class="badge bg-secondary">{{ $totaal['transfers'] }}</span></h2>
<div class="bord">
    @foreach($kolommen as $k => $label)
    <div class="kolom" style="background:#e3f2f1">
        <div class="kolom-kop"><span>{{ $label }}</span><span class="badge" style="background:#00897b">{{ $perKolomTransfers[$k]->count() }}</span></div>
        @foreach($perKolomTransfers[$k] as $o)@include('planbord.kaart', ['o' => $o])@endforeach
        @if($perKolomTransfers[$k]->isEmpty())<div class="small text-muted px-1">—</div>@endif
    </div>
    @endforeach
</div>
@endsection
