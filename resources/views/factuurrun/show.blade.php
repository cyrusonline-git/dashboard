@extends('layouts.app')
@section('titel', 'Factuurrun '.$res['contract_number'])
@section('inhoud')
@php($k = $res['kop'])
<div class="page-header mb-3">
    <a href="{{ route('factuurrun') }}" class="small"><i class="bi bi-arrow-left"></i> uit factuurrun</a>
    <h1>{{ $k['customer_name'] }} <code class="fs-6">{{ $res['contract_number'] }}</code></h1>
    <p>Aanmaker {{ $k['created_by_code'] ?? 'onbekend' }} · start {{ dmy($k['hire_start_date']) }} · laatste factuur {{ $k['last_invoice_date'] ? dmy($k['last_invoice_date']) : 'nooit' }} · {{ $k['delivery_name'] }} {{ $k['city'] }} · besteld door {{ $k['ordered_by'] ?? '—' }}</p>
</div>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header">Reden uit factuurrun</div>
            <form method="post" action="{{ route('factuurrun.reden', $res['contract_number']) }}" class="card-body">
                @csrf
                <textarea name="reden" class="form-control mb-2" rows="4" placeholder="Waarom staat dit contract uit de factuurrun?">{{ $res['note']['reden'] ?? '' }}</textarea>
                <select name="status" class="form-select form-select-sm mb-2">@foreach(['te_controleren'=>'Te controleren','verklaard'=>'Verklaard','opgelost'=>'Opgelost'] as $kk=>$l)<option value="{{ $kk }}" @selected(($res['note']['status'] ?? 'te_controleren')===$kk)>{{ $l }}</option>@endforeach</select>
                @if(\App\Services\Rollen::magNotitie())<button class="btn btn-boels btn-sm">Opslaan</button>@endif
                @if(!empty($res['note']['updated_at']))<div class="small text-muted mt-2">Laatst gewijzigd {{ $res['note']['updated_at'] }} · eerst gezien {{ dmy($res['note']['eerst_gezien'] ?? null) }}</div>@endif
            </form>
        </div>
        @if($wow)
        <div class="card"><div class="card-header">In huur volgens WOW</div>
            <div class="card-body small">Weekhuur <strong>{{ euro($wow['weekhuur']) }}</strong> · {{ count($wow['regels']) }} regels · <a href="{{ route('huur.show', $res['contract_number']) }}">bekijk contract</a></div>
        </div>
        @endif
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-header">Regels in de factuurrun-export ({{ count($res['regels']) }})</div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead><tr><th>Artikelnr</th><th>Omschrijving</th><th class="text-end">Aantal</th><th>Status</th><th>Start regel</th><th>Laatste factuur</th></tr></thead>
                <tbody>@foreach($res['regels'] as $r)<tr><td><code>{{ $r['item_number'] }}</code></td><td>{{ $r['item_description'] }}</td><td class="text-end">{{ $r['quantity'] + 0 }}</td><td class="small">{{ $r['item_status'] }}</td><td class="small">{{ dmy($r['item_hire_start_date']) }}</td><td class="small">{{ $r['last_invoice_date'] ? dmy($r['last_invoice_date']) : 'nooit' }}</td></tr>@endforeach</tbody>
            </table></div>
        </div>
    </div>
</div>
@endsection
