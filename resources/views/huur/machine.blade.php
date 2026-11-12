@extends('layouts.app')
@section('titel', 'Machine '.$nummer)
@section('inhoud')
<div class="page-header mb-3"><h1>Machine <code class="fs-5">{{ $nummer }}</code></h1><p>{{ $res && $res['in_huur'] ? 'Staat in huur volgens de laatste WOW-export.' : 'Niet gevonden in de laatste WOW-export (niet in huur bij depot 759, of geen upload).' }}</p></div>
@foreach($regels as $r)
<div class="card mb-2"><div class="card-body">
    <strong>{{ $r['item_description'] }}</strong> · <a href="{{ route('huur.show', $r['contract_number']) }}">contract {{ $r['contract_number'] }}</a><br>
    <span class="small">{{ $r['customer_name'] }} · {{ $r['project_description'] ?? $r['delivery_name'] }} · {{ $r['delivery_address'] }} · sinds {{ dmy($r['hire_date']) }} · {{ $r['status'] }}@if($r['coll_date']) · ophalen {{ dmy($r['coll_date']) }}@endif</span>
</div></div>
@endforeach
@endsection
