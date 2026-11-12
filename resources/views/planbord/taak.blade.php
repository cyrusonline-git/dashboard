<div class="kaart kaart-taak {{ $t->prioriteit ? 'hoog' : '' }}" style="cursor:default">
    <div class="kaart-kop"><span>{{ $t->prioriteit ? 'Kaartje · hoog' : 'Kaartje' }}</span>
        @if($magBewerken)<span><a href="#" class="text-white me-2 taak-bewerk" data-id="{{ $t->id }}" data-titel="{{ $t->titel }}" data-oms="{{ $t->omschrijving }}" data-deadline="{{ $t->deadline?->toDateString() }}" data-prio="{{ $t->prioriteit }}" title="bewerken"><i class="bi bi-pencil"></i></a><a href="#" class="text-white taak-klaar" data-url="{{ route('planbord.taak.klaar', $t) }}" title="klaar"><i class="bi bi-check2-square"></i></a></span>@endif
    </div>
    <div class="kaart-body">
        <div class="kaart-titel">{{ $t->titel }}</div>
        @if($t->omschrijving)<div class="small">{{ $t->omschrijving }}</div>@endif
        @if($t->deadline)<div class="small {{ $t->deadline->isPast() && !$t->deadline->isToday() ? 'text-rood' : 'text-muted' }}">{{ dmy($t->deadline) }} · {{ relatief($t->deadline) }}</div>@endif
        <div class="small text-muted">{{ $t->gemaakt_door }}</div>
    </div>
</div>
