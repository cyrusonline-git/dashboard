{{-- $uploads: antwoord van /api/sap/uploads (array per soort), $soorten: welke tonen --}}
@php($soorten = $soorten ?? array_keys((array) $uploads))
<div class="stand-balk">
    @forelse(collect($uploads)->only($soorten) as $k => $u)
        <span><i class="bi bi-file-earmark-excel text-success me-1"></i>{{ $u['label'] ?? $k }}:
            @if(!empty($u['moment']))
                <strong class="{{ !empty($u['verouderd']) ? 'verouderd' : '' }}">{{ \Carbon\Carbon::parse($u['moment'])->isToday() ? 'vandaag' : \Carbon\Carbon::parse($u['moment'])->format('d-m') }} {{ \Carbon\Carbon::parse($u['moment'])->format('H:i') }}</strong>@if(!empty($u['door'])) door {{ $u['door'] }}@endif@if(!empty($u['verouderd'])) <span class="verouderd">· verouderd</span>@endif
            @else
                <span class="verouderd">nog niet geüpload</span>
            @endif
        </span>
    @empty
        <span class="verouderd"><i class="bi bi-exclamation-triangle me-1"></i>CORE niet bereikbaar of nog geen uploads.</span>
    @endforelse
    @if(actieve_rol() === 'admin')<a class="ms-auto" href="{{ config('core.url') }}/admin/sap-uploads" target="_blank">uploaden <i class="bi bi-box-arrow-up-right"></i></a>@endif
</div>
