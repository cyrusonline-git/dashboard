@php($kleur = ['huur'=>'#FF6600','verkoop'=>'#1976d2','transfer_in'=>'#00897b','transfer_uit'=>'#00897b','retour'=>'#c62828','overig'=>'#777'][$o['soort']] ?? '#777')
<div class="kaart {{ $o["te_laat"] ? "te-laat" : ($o["pick_status"] === "volledig" ? "volledig" : "") }}" style="cursor:default">
    <div class="kaart-kop" style="background: {{ $kleur }}"><span>{{ ['huur'=>'Huurorder','verkoop'=>'Verkooporder','transfer_in'=>'Transfer in','transfer_uit'=>'Transfer uit','retour'=>'Retour','overig'=>'Order'][$o['soort']] ?? $o['soort'] }}</span>@if(!empty($o['note']['vlag']))<i class="bi bi-flag-fill"></i>@endif</div>
    <div class="kaart-body">
        <a href="{{ route('planning.show', $o['order_number']) }}" class="kaart-titel text-reset text-decoration-none"><code class="text-boels">{{ $o['order_number'] }}</code> {{ $o['customer_name'] }}</a>@if(in_array($o['soort'], ['transfer_in','transfer_uit']) && !empty($o['hiring_depot']))<div class="small text-muted">{{ $o['soort'] === 'transfer_in' ? 'van' : 'naar' }} {{ $o['hiring_depot'] }}</div>@endif
        <div class="small text-muted">{{ $o['delivery_date'] ? \Carbon\Carbon::parse($o['delivery_date'])->translatedFormat('D j M') : 'geen datum' }} · <span class="{{ $o['te_laat'] ? 'text-rood' : '' }}">{{ relatief($o['delivery_date']) }}</span></div>
        <div class="d-flex justify-content-between align-items-center mt-1">
            @if($o['pick_status'] === 'onbekend')<span class="pick onbekend">{{ $o['regels'] ? $o['regels'].' regels' : 'nog niet in 8.7' }}</span>
            @elseif($o['pick_status'] === 'geen_regels')<span class="pick geen_regels">geen regels</span>
            @else<span class="pick {{ $o['pick_status'] }}">{{ $o['picked'] + 0 }}/{{ $o['total'] + 0 }} gepickt</span>@endif
            <span class="small text-muted text-truncate ms-2" style="max-width:120px" title="{{ $o['reference'] }}">{{ $o['reference'] }}</span>
        </div>
        @if(!empty($o['note']['opmerking']))<div class="small mt-1"><i class="bi bi-chat-left-text me-1"></i>{{ $o['note']['opmerking'] }}</div>@endif
    </div>
</div>
