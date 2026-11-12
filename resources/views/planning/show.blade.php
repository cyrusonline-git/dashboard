@extends('layouts.app')
@section('titel', 'Order '.$order['order_number'])
@section('inhoud')
<div class="page-header mb-3">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('planning') }}" class="small"><i class="bi bi-arrow-left"></i> terug</a>
    <h1><span class="soort-badge soort-{{ $order['soort'] }} me-2">{{ $order['soort'] }}</span>{{ $order['customer_name'] }} <code class="fs-6">{{ $order['order_number'] }}</code></h1>
    <p>Leverdatum {{ dmy($order['delivery_date']) }} ({{ relatief($order['delivery_date']) }}) · {{ $order['employee_name'] ?? $order['sales_person'] ?? $order['created_by_code'] ?? '' }} @if($order['te_laat'])· <span class="text-danger fw-semibold">leverdatum voorbij — op te schonen</span>@endif</p>
</div>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header">Order</div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-5">Pickstand</dt><dd class="col-7">@if($order['pick_status'] === 'onbekend')nog niet in 8.7 @else<span class="pick {{ $order['pick_status'] }}">{{ $order['picked'] }}/{{ $order['total'] }}</span>@endif</dd>
                    <dt class="col-5">Status SAP</dt><dd class="col-7">{{ $order['contract_status'] ?? '—' }} @if($order['offerte_status'])· offerte: {{ $order['offerte_status'] }}@endif</dd>
                    <dt class="col-5">Type</dt><dd class="col-7">{{ $order['type_raw'] ?? '—' }}</dd>
                    <dt class="col-5">Referentie</dt><dd class="col-7">{{ $order['reference'] ?? '—' }}</dd>
                    <dt class="col-5">Waarde</dt><dd class="col-7">{{ $order['goods_value'] !== null ? euro($order['goods_value']) : '—' }}</dd>
                    <dt class="col-5">Huurperiode</dt><dd class="col-7">{{ dmy($order['hire_start_date']) }} – {{ dmy($order['hire_end_date']) }}</dd>
                    <dt class="col-5">Aflevering</dt><dd class="col-7">{{ $order['delivery_name'] ?? '' }}<br>{{ $order['street'] ?? '' }} {{ $order['city'] ?? '' }}</dd>
                    <dt class="col-5">Besteld door</dt><dd class="col-7">{{ $order['ordered_by'] ?? '—' }}</dd>
                    <dt class="col-5">Memo</dt><dd class="col-7">{{ $order['memo'] ?? '—' }}</dd>
                    <dt class="col-5">Bron</dt><dd class="col-7">{{ ['beide' => '8.7 + 2.6', 'planning' => 'alleen 8.7', 'reserveringen' => 'alleen 2.6'][$order['bron']] ?? $order['bron'] }}</dd>
                </dl>
            </div>
        </div>
        @if(\App\Services\Rollen::magNotitie())
        <div class="card"><div class="card-header">Opmerking</div>
            <form method="post" action="{{ route('planning.notitie', $order['order_number']) }}" class="card-body">
                @csrf
                <div class="mb-2"><label class="form-label small mb-1">Opmerking</label><textarea name="opmerking" class="form-control form-control-sm" rows="2">{{ $order['note']['opmerking'] ?? '' }}</textarea></div>
                <div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="vlag" value="1" id="vlag" @checked(!empty($order['note']['vlag']))><label class="form-check-label small" for="vlag">Markeren (vlag)</label></div>
                <button class="btn btn-boels btn-sm">Opslaan</button>
                @if(!empty($order['note']['updated_at']))<div class="small text-muted mt-2">Laatst gewijzigd {{ $order['note']['updated_at'] }}</div>@endif
            </form>
        </div>
        @endif
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-header">Artikelregels ({{ $regels->count() }}) <small class="text-muted fw-normal">{{ ($order['regels_bron'] ?? null) === 'planning_regels' ? 'uit 8.7 (regels-export)' : 'uit 2.6' }} — na levering staan de artikelen in "In huur"</small></div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead><tr><th>Artikelnr</th><th>Omschrijving</th><th class="text-end">Aantal</th><th>Type</th><th>Lever</th><th>Huur t/m</th></tr></thead>
                <tbody>
                @forelse($regels as $r)
                    <tr><td><code>{{ $r['item_number'] }}</code></td><td>{{ $r['item_description'] }}</td><td class="text-end">{{ $r['qty'] + 0 }}</td><td class="small text-muted">{{ $r['type'] }}</td><td class="small">{{ dmy($r['delivery_date']) }}</td><td class="small">{{ dmy($r['hire_end_date']) }} {{ $r['hire_end_time'] ?? '' }}</td></tr>
                @empty
                    <tr><td colspan="6" class="text-muted">Geen artikelregels in de exports voor deze order. Upload in CORE naast de platte 8.7-View ook de gegroepeerde 8.7-export met artikelregels (kaart "Planning: artikelregels"), dan verschijnen ze hier.</td></tr>
                @endforelse
                </tbody></table></div>
        </div>
    </div>
</div>
@endsection
