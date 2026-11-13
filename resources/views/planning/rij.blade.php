@php($klasse = $o['is_offerte'] ? 'offerte' : ($o['te_laat'] ? 'te-laat' : ($o['pick_status'] === 'volledig' ? 'volledig' : ($o['pick_status'] === 'deels' ? 'deels' : ''))))
<div class="order-rij {{ $klasse }}">
    <div>
        <a href="{{ route('planning.show', $o['order_number']) }}" class="fw-semibold text-reset text-decoration-none">{{ $o['customer_name'] ?? '—' }}</a>
        <div class="small text-muted">@if(in_array($o['soort'], ['transfer_in','transfer_uit']) && !empty($o['hiring_depot'])){{ $o['soort'] === 'transfer_in' ? 'van' : 'naar' }} {{ $o['hiring_depot'] }}@else{{ $o['delivery_name'] ?? '' }}{{ !empty($o['city']) ? ' · '.$o['city'] : '' }}@endif</div>
    </div>
    <div><span class="soort-badge soort-{{ $o['soort'] }}">{{ ['huur'=>'Huurorder','verkoop'=>'Verkooporder','transfer_in'=>'Transfer in','transfer_uit'=>'Transfer uit','retour'=>'Retour','offerte'=>'Offerte','overig'=>'Order'][$o['soort']] ?? $o['soort'] }}</span>
        @if(!empty($o['note']['vlag']))<i class="bi bi-flag-fill text-danger ms-1" title="{{ $o['note']['opmerking'] ?? 'gemarkeerd' }}"></i>@endif
        @if(!empty($o['bijhuur']))<div class="mt-1"><span class="badge rounded-pill" style="background:#00897b" title="Dit contract staat al in huur; dit is een aanvulling">Bijhuur · {{ $o['in_huur_regels'] }} in huur</span></div>@endif</div>
    <div>
        @if($o['pick_status'] === 'onbekend')
            <span class="pick onbekend" title="nog niet in 8.7">{{ $o['regels'] ? $o['regels'].' regels' : 'nog niet in planning' }}</span>
        @elseif($o['pick_status'] === 'geen_regels')
            <span class="pick geen_regels">geen regels</span>
        @else
            <span class="pick {{ $o['pick_status'] }}">{{ rtrim(rtrim(number_format($o['picked'], 2, ',', ''), '0'), ',') }}/{{ rtrim(rtrim(number_format($o['total'], 2, ',', ''), '0'), ',') }} gepickt</span>@if(!empty($o['bijhuur']) && ($o['nog_te_picken'] ?? 0) > 0)<div class="small text-muted">nog {{ $o['nog_te_picken'] + 0 }} te leveren</div>@endif
        @endif
    </div>
    <div class="small">{{ $o['employee_name'] ?? $o['sales_person'] ?? $o['created_by_code'] ?? '—' }}</div>
    <div class="small text-muted text-truncate" title="{{ $o['reference'] }}">{{ $o['reference'] ?? '' }}</div>
    <div class="text-end"><code>{{ $o['order_number'] }}</code>@if($o['te_laat'])<div class="small text-danger">{{ relatief($o['delivery_date']) }}</div>@elseif($o['delivery_date'])<div class="small text-muted">{{ relatief($o['delivery_date']) }}</div>@endif</div>
</div>
