@php($rol = actieve_rol())
<div class="sectie">Werk</div>
<a class="item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i>Start</a>
<a class="item {{ request()->routeIs('planning', 'planning.*') ? 'active' : '' }}" href="{{ route('planning') }}"><i class="bi bi-calendar-check"></i>Planning</a>
<a class="item {{ request()->routeIs('planbord') ? 'active' : '' }}" href="{{ route('planbord') }}"><i class="bi bi-kanban"></i>Planbord</a>
@if(\App\Services\Rollen::magFactuurrun($rol))<a class="item {{ request()->routeIs('factuurrun', 'factuurrun.*') ? 'active' : '' }}" href="{{ route('factuurrun') }}"><i class="bi bi-receipt"></i>Uit factuurrun</a>@endif
<div class="sectie">In huur</div>
<a class="item {{ request()->routeIs('huur', 'huur.show', 'huur.machine') ? 'active' : '' }}" href="{{ route('huur') }}"><i class="bi bi-box-seam"></i>Contracten in huur</a>
<a class="item {{ request()->routeIs('huur.ophalen') ? 'active' : '' }}" href="{{ route('huur.ophalen') }}"><i class="bi bi-arrow-return-left"></i>Ophaallijst</a>
@if(\App\Services\Rollen::magAvi($rol))<a class="item {{ request()->routeIs('huur.avi') ? 'active' : '' }}" href="{{ route('huur.avi') }}"><i class="bi bi-cash-coin"></i>AVI</a>@endif
@if(\App\Services\Rollen::magTeam($rol) || $rol === 'admin')<div class="sectie">Overzicht</div>@endif
@if(\App\Services\Rollen::magTeam($rol))<a class="item {{ request()->routeIs('team') ? 'active' : '' }}" href="{{ route('team') }}"><i class="bi bi-people"></i>Acties per persoon</a>@endif
@if($rol === 'admin')
    <a class="item {{ request()->routeIs('beheer') ? 'active' : '' }}" href="{{ route('beheer') }}"><i class="bi bi-gear"></i>Beheer</a>
    <a class="item" href="{{ config('core.url') }}/admin/sap-uploads" target="_blank"><i class="bi bi-cloud-upload"></i>Uploads SAP <i class="bi bi-box-arrow-up-right ms-auto small"></i></a>
@endif
