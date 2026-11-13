@php($kleur = ['huur'=>'#FF6600','verkoop'=>'#1976d2','transfer_in'=>'#00897b','transfer_uit'=>'#00897b','retour'=>'#c62828','overig'=>'#777'][$o['soort']] ?? '#777')
<div class="kaart {{ !empty($o["niet_op_scanner"]) ? "niet-op-scanner" : (!empty($o["referentie_ontbreekt"]) ? "geen-ref" : ($o["te_laat"] ? "te-laat" : ($o["pick_status"] === "volledig" ? "volledig" : ""))) }}" style="cursor:default">
    <div class="kaart-kop" style="background: {{ $kleur }}"><span>{{ ['huur'=>'Huurorder','verkoop'=>'Verkooporder','transfer_in'=>'Transfer in','transfer_uit'=>'Transfer uit','retour'=>'Retour','overig'=>'Order'][$o['soort']] ?? $o['soort'] }}</span>@if(!empty($o['note']['vlag']))<i class="bi bi-flag-fill"></i>@endif</div>
    <div class="kaart-body">
        <a href="{{ route('planning.show', $o['order_number']) }}" class="kaart-titel text-reset text-decoration-none"><code class="text-boels">{{ $o['order_number'] }}</code> {{ $o['customer_name'] }}</a>@if(in_array($o['soort'], ['transfer_in','transfer_uit']) && !empty($o['hiring_depot']))<div class="small text-muted">{{ $o['soort'] === 'transfer_in' ? 'van' : 'naar' }} {{ $o['hiring_depot'] }}</div>@endif
        <div class="small text-muted">{{ $o['delivery_date'] ? \Carbon\Carbon::parse($o['delivery_date'])->translatedFormat('D j M') : 'geen datum' }} · <span class="{{ $o['te_laat'] ? 'text-rood' : '' }}">{{ relatief($o['delivery_date']) }}</span></div>
        @if(!empty($o['niet_op_scanner']))<div class="small text-danger fw-semibold" title="Profiel niet ingevuld: kolom PickUpInfo in Smartview (8.7) is leeg; vul het profiel in InspHire"><i class="bi bi-exclamation-triangle-fill me-1"></i>Profiel niet ingevuld (PickUpInfo leeg)</div>@endif
        @if(!empty($o['referentie_ontbreekt']))<div class="small fw-semibold" style="color:#b26a00" title="Kolom REF in Smartview is leeg of 'volgt': vraag de klant om referentie/PO-nummer, anders wordt de factuur niet betaald"><i class="bi bi-receipt-cutoff me-1"></i>Referentie / PO ontbreekt</div>@endif
        @if(!empty($o['bijhuur']))<div class="small"><span class="badge rounded-pill" style="background:#00897b">Bijhuur · {{ $o['in_huur_regels'] }} al in huur</span>@if(($o['nog_te_picken'] ?? 0) > 0) <strong class="text-rood">{{ $o['nog_te_picken'] + 0 }} te picken</strong>@endif @if(!empty($o['datum_onbekend']))<span class="text-muted">· leverdatum nieuwe regel niet in export, zie InspHire</span>@endif</div>
            @if(!empty($o['nieuwe_regels']))<ul class="small mb-0 mt-1 ps-3" style="background:#fff8e1;border-radius:6px;padding:4px 4px 4px 18px">@foreach(array_slice($o['nieuwe_regels'], 0, 5) as $l)<li><strong>{{ $l['qty'] + 0 }}×</strong> {{ $l['item_description'] }}</li>@endforeach @if(count($o['nieuwe_regels']) > 5)<li class="text-muted">… en {{ count($o['nieuwe_regels']) - 5 }} meer</li>@endif</ul>@endif
        @endif
        <div class="d-flex justify-content-between align-items-center mt-1">
            @if($o['pick_status'] === 'onbekend')<span class="pick onbekend">{{ $o['regels'] ? $o['regels'].' regels' : 'nog niet in 8.7' }}</span>
            @elseif($o['pick_status'] === 'geen_regels')<span class="pick geen_regels">geen regels</span>
            @else<span class="pick {{ $o['pick_status'] }}">{{ $o['picked'] + 0 }}/{{ $o['total'] + 0 }} gepickt</span>@endif
            <span class="small text-muted text-truncate ms-2" style="max-width:120px" title="{{ $o['reference'] }}">{{ $o['reference'] }}</span>
        </div>
        @if(!empty($o['note']['opmerking']))<div class="small mt-1"><i class="bi bi-chat-left-text me-1"></i>{{ $o['note']['opmerking'] }}</div>@endif
    </div>
</div>
