@extends('layouts.app')
@section('titel', 'Contract '.$c['contract_number'])
@section('inhoud')
<div class="page-header mb-3">
    <a href="{{ route('huur') }}" class="small"><i class="bi bi-arrow-left"></i> in huur</a>
    <h1>{{ $c['customer_name'] }} <code class="fs-6">{{ $c['contract_number'] }}</code></h1>
    <p>Sinds {{ dmy($c['hire_date']) }} · {{ $c['project_description'] ? 'project '.$c['project_description'].' · ' : '' }}{{ $c['delivery_name'] }} {{ $c['delivery_address'] }} {{ $c['delivery_postal_code'] }} · ref. {{ $c['your_reference'] ?? '—' }}@if($magAvi) · weekhuur <strong>{{ euro($c['weekhuur']) }}</strong>@endif</p>
</div>
@if(!empty($c['open_order']))
@php($oo = $c['open_order'])
<div class="card mb-3" style="border-left:4px solid #00897b"><div class="card-body py-2 d-flex flex-wrap align-items-center gap-3">
    <strong><i class="bi bi-plus-circle me-1" style="color:#00897b"></i>Nog te leveren op dit contract (bijhuur)</strong>
    <span>leverdatum <strong>{{ dmy($oo['delivery_date']) }}</strong> ({{ relatief($oo['delivery_date']) }})</span>
    <span>pickstand {{ $oo['picked'] + 0 }}/{{ $oo['total'] + 0 }} · <strong>nog {{ $oo['nog_te_picken'] + 0 }} te leveren</strong></span>
    @if($oo['sales_person'])<span class="text-muted">{{ $oo['sales_person'] }}</span>@endif
    <a href="{{ route('planning.show', $oo['order_number']) }}" class="ms-auto small">order in planning →</a>
</div>
@if(count($oo['regels_26']))<div class="card-body pt-0 small text-muted">Nieuwe regels volgens 2.6: @foreach($oo['regels_26'] as $r){{ $r['qty'] + 0 }}× {{ $r['item_description'] }} ({{ dmy($r['delivery_date']) }}){{ $loop->last ? '' : ' · ' }}@endforeach</div>@endif
</div>
@endif
<div class="card"><div class="card-header">Artikelen ({{ $regels->count() }}) <small class="text-muted fw-normal">stand {{ \Carbon\Carbon::parse($c['upload']['moment'])->format('d-m-Y H:i') }}</small></div>
    <div class="table-responsive"><table class="table table-sm mb-0 align-middle">
        <thead><tr><th>Artikel / machinenr</th><th>Omschrijving</th><th class="text-end">Aantal</th><th>Status</th><th>Sinds</th><th>Retour verwacht</th><th>Ophalen</th>@if($magAvi)<th class="text-end">€/week</th>@endif<th>Notitie</th></tr></thead>
        <tbody>
        @foreach($regels as $r)
            <tr class="{{ str_contains($r['status'], 'To Collect') ? 'table-danger' : '' }}">
                <td>@if(strlen($r['item_number']) >= 7)<a href="{{ route('huur.machine', $r['item_number']) }}"><code>{{ $r['item_number'] }}</code></a>@else<code>{{ $r['item_number'] }}</code>@endif</td>
                <td>{{ $r['item_description'] }}</td>
                <td class="text-end">{{ $r['qty_out'] + 0 }}</td>
                <td class="small">{{ str_replace('On-Hire', '', $r['status']) }}</td>
                <td class="small">{{ dmy($r['hire_date']) }}</td>
                <td class="small">{{ $r['est_return_date'] ? dmy($r['est_return_date']) : '—' }}</td>
                <td class="small">{{ $r['coll_date'] ? dmy($r['coll_date']).' '.($r['coll_time'] ?? '') : '' }} @if($r['coll_doc_printed'])<span class="badge bg-success">bon</span>@endif</td>
                @if($magAvi)<td class="text-end small">{{ euro($r['weekly_hire_rate']) }}@if($r['discount'] > 0)<div class="text-muted">{{ $r['discount'] + 0 }}% korting</div>@endif</td>@endif
                <td class="small text-muted">{{ $r['notes'] }}</td>
            </tr>
        @endforeach
        </tbody></table></div>
</div>
@endsection
