@extends('layouts.app')
@section('titel', 'Order '.$order['order_number'])
@section('inhoud')
<div class="page-header mb-3">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('planning') }}" class="small"><i class="bi bi-arrow-left"></i> terug</a>
    <h1><span class="soort-badge soort-{{ $order['soort'] }} me-2">{{ $order['soort'] }}</span>{{ $order['customer_name'] }} <code class="fs-6">{{ $order['order_number'] }}</code></h1>
    <p>@if(!empty($order['bijhuur']))<span class="badge rounded-pill me-1" style="background:#00897b">Bijhuur op lopend contract · {{ $order['in_huur_regels'] }} regels al in huur</span> @endif Leverdatum (aflevering) <strong>{{ dmy($order['delivery_date']) }}</strong> ({{ relatief($order['delivery_date']) }})@if(!empty($order['hire_start_date'])) · Verhuurdatum (start huur) <strong>{{ dmy($order['hire_start_date']) }}</strong>@if(!empty($order['hire_end_date'])) t/m {{ dmy($order['hire_end_date']) }} @endif @endif @if(!empty($order['delivery_date_contract']) && $order['delivery_date_contract'] !== $order['delivery_date']) · <span class="text-muted">contract gestart {{ dmy($order['delivery_date_contract']) }}</span>@endif · {{ $order['employee_name'] ?? $order['sales_person'] ?? $order['created_by_code'] ?? '' }} @if($order['te_laat'])· <span class="text-danger fw-semibold">leverdatum voorbij — op te schonen</span>@endif</p>
</div>
@if(!empty($order['referentie_ontbreekt']))
<div class="alert alert-warning py-2"><i class="bi bi-receipt-cutoff me-1"></i><strong>Klantreferentie of PO-nummer ontbreekt.</strong> De kolom REF van deze order is leeg of bevat "volgt" in Smartview (8.7). Vraag de referentie op bij de klant en zet die in InspHire, anders wordt de factuur niet betaald.</div>
@endif
@if(!empty($order['niet_op_scanner']))
<div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle-fill me-1"></i><strong>Profiel niet ingevuld.</strong> De kolom PickUpInfo van deze order is leeg in Smartview (8.7); de expeditie ziet hem daardoor niet in InspHire Mobile en pickt hem niet. Vul het profiel in InspHire, dan is het bij de volgende upload in orde.</div>
@endif
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card mb-3"><div class="card-header">Order</div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-5">Pickstand</dt><dd class="col-7">@if($order['pick_status'] === 'niet_pickbaar')<span class="pick geen_regels">niets te picken</span> <span class="text-muted">alleen brandstof, arbeid of heffingen</span> @elseif($order['pick_status'] === 'geleverd')<span class="pick geleverd">geleverd</span> <span class="text-muted">artikelen staan in de WOW</span> @elseif($order['pick_status'] === 'onbekend')nog niet in 8.7 @else<span class="pick {{ $order['pick_status'] }}">{{ $order['picked'] }}/{{ $order['total'] }}</span>@endif</dd>
                    <dt class="col-5">Status Smartview</dt><dd class="col-7">{{ $order['contract_status'] ?? '—' }} @if($order['offerte_status'])· offerte: {{ $order['offerte_status'] }}@endif</dd>
                    <dt class="col-5">Type</dt><dd class="col-7">{{ $order['type_raw'] ?? '—' }}</dd>
                    <dt class="col-5">Referentie</dt><dd class="col-7">{{ $order['reference'] ?? '—' }}@if(!empty($order['referentie_ontbreekt'])) <span class="badge bg-warning text-dark">ontbreekt</span>@endif</dd>
                    <dt class="col-5">Waarde</dt><dd class="col-7">{{ $order['goods_value'] !== null ? euro($order['goods_value']) : '—' }}</dd>
                    <dt class="col-5">Leverdatum</dt><dd class="col-7">{{ dmy($order['delivery_date']) }} <span class="text-muted">(aflevering)</span></dd>
                    <dt class="col-5">Verhuurdatum</dt><dd class="col-7">{{ $order['hire_start_date'] ? dmy($order['hire_start_date']) : '—' }} <span class="text-muted">(start huur)</span>@if(!empty($order['hire_end_date']))<br>t/m {{ dmy($order['hire_end_date']) }}@endif</dd>
                    <dt class="col-5">Aflevering</dt><dd class="col-7">{{ $order['delivery_name'] ?? '' }}<br>{{ $order['street'] ?? '' }} {{ $order['city'] ?? '' }}</dd>
                    <dt class="col-5">Besteld door</dt><dd class="col-7">{{ $order['ordered_by'] ?? '—' }}</dd>
                    <dt class="col-5">Memo</dt><dd class="col-7">{{ $order['memo'] ?? '—' }}</dd>
                    <dt class="col-5">PickUpInfo</dt><dd class="col-7">{{ $order['pick_up_info'] ?? '' }}@if(!empty($order['niet_op_scanner']))<span class="text-danger">leeg — profiel niet ingevuld</span>@elseif(empty($order['pick_up_info']))—@endif</dd>
                    <dt class="col-5">Bron</dt><dd class="col-7">{{ ['beide' => '8.7 + 3.4', 'planning' => 'alleen 8.7', 'reserveringen' => 'alleen 3.4'][$order['bron']] ?? $order['bron'] }}</dd>
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
        <div class="card"><div class="card-header">Artikelregels ({{ $regels->count() }}) <small class="text-muted fw-normal">{{ ($order['regels_bron'] ?? null) === 'planning_regels' ? 'uit 8.7 (regels-export)' : 'uit 3.4' }} — na levering staan de artikelen in "In huur"</small></div>
            <div class="table-responsive"><table class="table table-sm mb-0">
                <thead><tr><th>Artikelnr</th><th>Omschrijving</th><th class="text-end">Aantal</th>@if(!empty($order['bijhuur']))<th class="text-end">Al in huur</th><th class="text-end">Te leveren</th>@endif<th>Type</th><th>Lever</th><th>Huur t/m</th></tr></thead>
                <tbody>
                @forelse($regels as $r)
                    @php($nieuw = !empty($order['bijhuur']) && ($r['te_leveren'] ?? 0) > 0)
                    <tr class="{{ $nieuw ? 'table-warning' : '' }}"><td><code>{{ $r['item_number'] }}</code>@if($nieuw) <span class="badge bg-warning text-dark">nieuw</span>@endif</td><td>{{ $r['item_description'] }}@if(!empty($r['brandstof'])) <span class="badge bg-light text-muted border" title="Brandstof wordt bijgeboekt als verkoopartikel; de expeditie hoeft niets te picken">brandstof · niet picken</span>@elseif(!empty($r['niet_pickbaar'])) <span class="badge bg-light text-muted border">niet picken</span>@endif</td><td class="text-end">{{ $r['qty'] + 0 }}</td>@if(!empty($order['bijhuur']))<td class="text-end text-muted">{{ ($r['al_in_huur'] ?? 0) + 0 }}</td><td class="text-end fw-bold">{{ ($r['te_leveren'] ?? 0) + 0 }}</td>@endif<td class="small text-muted">{{ $r['type'] }}</td><td class="small">{{ dmy($r['delivery_date']) }}</td><td class="small">{{ dmy($r['hire_end_date']) }} {{ $r['hire_end_time'] ?? '' }}</td></tr>
                @empty
                    <tr><td colspan="6" class="text-muted">Geen artikelregels in de exports voor deze order. Upload in CORE naast de platte 8.7-View ook de gegroepeerde 8.7-export met artikelregels (kaart "Planning: artikelregels"), dan verschijnen ze hier.</td></tr>
                @endforelse
                </tbody></table></div>
        </div>
    </div>
</div>
@endsection
