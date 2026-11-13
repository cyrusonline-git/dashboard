@php($klasse = $o['pick_status'] === 'geleverd' ? 'volledig' : ($o['is_offerte'] ? 'offerte' : (!empty($o['niet_op_scanner']) ? 'niet-op-scanner' : (!empty($o['referentie_ontbreekt']) ? 'geen-ref' : ($o['te_laat'] ? 'te-laat' : ($o['pick_status'] === 'volledig' ? 'volledig' : ($o['pick_status'] === 'deels' ? 'deels' : '')))))))
<div class="order-rij {{ $klasse }}">
    <div>
        <a href="{{ route('planning.show', $o['order_number']) }}" class="fw-semibold text-reset text-decoration-none">{{ $o['customer_name'] ?? '—' }}</a>
        <div class="small text-muted">@if(in_array($o['soort'], ['transfer_in','transfer_uit']) && !empty($o['hiring_depot'])){{ $o['soort'] === 'transfer_in' ? 'van' : 'naar' }} {{ $o['hiring_depot'] }}@else{{ $o['delivery_name'] ?? '' }}{{ !empty($o['city']) ? ' · '.$o['city'] : '' }}@endif</div>
    </div>
    <div><span class="soort-badge soort-{{ $o['soort'] }}">{{ ['huur'=>'Huurorder','verkoop'=>'Verkooporder','transfer_in'=>'Transfer in','transfer_uit'=>'Transfer uit','retour'=>'Retour','offerte'=>'Offerte','overig'=>'Order'][$o['soort']] ?? $o['soort'] }}</span>
        @if(!empty($o['note']['vlag']))<i class="bi bi-flag-fill text-danger ms-1" title="{{ $o['note']['opmerking'] ?? 'gemarkeerd' }}"></i>@endif
        @if(!empty($o['niet_op_scanner']))<div class="mt-1"><span class="badge bg-danger" title="Profiel niet ingevuld: de kolom PickUpInfo in Smartview (8.7) is leeg, dus de expeditie ziet deze order niet in InspHire Mobile en pickt hem niet. Vul het profiel in InspHire."><i class="bi bi-exclamation-triangle-fill me-1"></i>Profiel niet ingevuld (PickUpInfo leeg)</span></div>@endif
        @if(!empty($o['referentie_ontbreekt']))<div class="mt-1"><span class="badge bg-warning text-dark" title="Kolom REF in Smartview (8.7) is leeg of bevat 'volgt': vraag de klant om een referentie of PO-nummer, anders wordt de factuur niet betaald"><i class="bi bi-receipt-cutoff me-1"></i>Referentie / PO ontbreekt</span></div>@endif
        @if(!empty($o['bijhuur']))<div class="mt-1"><span class="badge rounded-pill" style="background:#00897b" title="Dit contract staat al in huur; dit is een aanvulling">Bijhuur · {{ $o['in_huur_regels'] }} in huur</span>
            @if(!empty($o['nieuwe_regels']))<div class="small mt-1" style="background:#fff8e1;border-radius:6px;padding:2px 6px">@foreach(array_slice($o['nieuwe_regels'], 0, 3) as $l)<strong>{{ $l['qty'] + 0 }}×</strong> {{ $l['item_description'] }}{{ $loop->last ? '' : ' · ' }}@endforeach @if(count($o['nieuwe_regels']) > 3)<span class="text-muted">+{{ count($o['nieuwe_regels']) - 3 }}</span>@endif</div>@endif
        </div>@endif</div>
    <div>
        @if($o['pick_status'] === 'geleverd')
            <span class="pick geleverd" title="Artikelen staan in de WOW (On-Hire/Delivered)"><i class="bi bi-check2-circle me-1"></i>geleverd</span>
        @elseif($o['pick_status'] === 'onbekend')
            <span class="pick onbekend" title="nog niet in 8.7">{{ $o['regels'] ? $o['regels'].' regels' : 'nog niet in planning' }}</span>
        @elseif($o['pick_status'] === 'geen_regels')
            <span class="pick geen_regels">geen regels</span>
        @else
            <span class="pick {{ $o['pick_status'] }}">{{ rtrim(rtrim(number_format($o['picked'], 2, ',', ''), '0'), ',') }}/{{ rtrim(rtrim(number_format($o['total'], 2, ',', ''), '0'), ',') }} gepickt</span>@if(!empty($o['bijhuur']) && ($o['nog_te_picken'] ?? 0) > 0)<div class="small fw-semibold text-danger">{{ $o['nog_te_picken'] + 0 }} te picken{{ !empty($o['datum_onbekend']) ? ' · datum: zie InspHire' : '' }}</div>@endif
        @endif
    </div>
    <div class="small">{{ $o['employee_name'] ?? $o['sales_person'] ?? $o['created_by_code'] ?? '—' }}</div>
    <div class="small text-truncate {{ !empty($o['referentie_ontbreekt']) ? 'fw-semibold' : 'text-muted' }}" style="{{ !empty($o['referentie_ontbreekt']) ? 'color:#b26a00' : '' }}" title="{{ $o['reference'] }}">{{ !empty($o['referentie_ontbreekt']) ? ($o['reference'] ?: 'geen referentie') : ($o['reference'] ?? '') }}</div>
    <div class="text-end"><code>{{ $o['order_number'] }}</code>@if($o['te_laat'])<div class="small text-danger">{{ relatief($o['delivery_date']) }}</div>@elseif($o['delivery_date'])<div class="small text-muted">{{ relatief($o['delivery_date']) }}</div>@endif @if(!empty($o['hire_start_date']) && $o['hire_start_date'] !== $o['delivery_date'])<div class="small text-muted" title="Verhuurdatum (start huur) verschilt van de leverdatum">huur vanaf {{ dmy($o['hire_start_date']) }}</div>@endif</div>
</div>
