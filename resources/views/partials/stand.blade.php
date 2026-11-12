{{-- $uploads: antwoord van /api/sap/uploads (array per soort), $soorten: welke tonen --}}
@php($soorten = $soorten ?? array_keys((array) $uploads))
<div class="stand-balk mb-3">
    @forelse(collect($uploads)->only($soorten) as $k => $u)
        <span class="me-3"><i class="bi bi-file-earmark-excel"></i> {{ $u['label'] ?? $k }}:
            @if(!empty($u['moment']))
                <span class="{{ !empty($u['verouderd']) ? 'verouderd' : '' }}">stand {{ \Carbon\Carbon::parse($u['moment'])->format('d-m H:i') }}@if(!empty($u['door'])) door {{ $u['door'] }}@endif</span>
            @else
                <span class="verouderd">nog niet geüpload</span>
            @endif
        </span>
    @empty
        <span class="verouderd"><i class="bi bi-exclamation-triangle"></i> CORE niet bereikbaar of nog geen uploads.</span>
    @endforelse
    @if(actieve_rol() === 'admin')<a class="ms-2" href="{{ config('core.url') }}/admin/sap-uploads" target="_blank">uploaden <i class="bi bi-box-arrow-up-right"></i></a>@endif
</div>
