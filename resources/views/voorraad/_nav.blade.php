{{-- Subnavigatie voorraad & vloot --}}
@php
$tabs = [
    ['voorraad', 'bi-search', 'Voorraadcheck'],
    ['voorraad.aanvragen', 'bi-envelope-paper', 'Aanvragen'],
    ['voorraad.werkplaats', 'bi-tools', 'Werkplaats'],
    ['voorraad.minimum', 'bi-sliders', 'Minimumvoorraad'],
    ['vloot', 'bi-speedometer2', 'Vlootmeters'],
];
@endphp
<ul class="nav nav-pills gap-1 mb-3 small">
    @foreach($tabs as [$route, $icoon, $label])
        <li class="nav-item"><a class="nav-link py-1 px-3 {{ request()->routeIs($route) || ($route === 'voorraad' && request()->routeIs('voorraad.show', 'voorraad.nieuw')) || ($route === 'voorraad.aanvragen' && request()->routeIs('voorraad.aanvraag')) ? 'active bg-boels' : 'text-reset' }}" href="{{ route($route) }}"><i class="bi {{ $icoon }} me-1"></i>{{ $label }}</a></li>
    @endforeach
</ul>
