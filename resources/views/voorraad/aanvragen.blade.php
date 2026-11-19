@extends('layouts.app')
@section('titel', 'Aanvragen')
@section('main_klasse', 'breed')
@section('inhoud')
@include('voorraad._nav')
<div class="page-header"><div><h1>Aanvragen bij collega-depots</h1><p>Alle materieelaanvragen met hun status. Een depot reageert via de knoppen in de mail. Een telefonisch akkoord zet je zelf op de detailpagina.</p></div></div>
<div class="d-flex flex-wrap gap-2 mb-3 small">
    @foreach(\App\Models\Aanvraag::STATUSSEN as $k => [$label, $kleur])
        <a href="{{ request()->fullUrlWithQuery(['status' => request('status') === $k ? null : $k]) }}" class="badge text-decoration-none bg-{{ $kleur }} {{ in_array($kleur, ['warning', 'light', 'info']) ? 'text-dark' : '' }} {{ request('status') === $k ? 'border border-2 border-dark' : '' }}">{{ $label }}: {{ $telling[$k] ?? 0 }}</a>
    @endforeach
</div>
<form method="get" class="card mb-3"><div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
    <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" style="max-width:240px" placeholder="Nummer, klant, aanvrager, depot…">
    <select name="depot" class="form-select form-select-sm" style="max-width:220px"><option value="">Alle depots</option>@foreach($depots as $d)<option value="{{ $d->depot }}" @selected(request('depot') === $d->depot)>{{ $d->depot_naam ?: $d->depot }}</option>@endforeach</select>
    <input type="hidden" name="status" value="{{ request('status') }}">
    <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="mijn" value="1" @checked(request()->boolean('mijn'))> alleen mijn</label>
    <button class="btn btn-sm btn-boels">Filter</button>
</div></form>
<div class="card"><div class="table-responsive">
    <table class="table table-sm table-hover mb-0 align-middle">
        <thead><tr><th>#</th><th>Nummer</th><th>Klant</th><th>Van → naar</th><th class="text-end">Aantal</th><th>Status</th><th>Aanvrager</th><th>Verstuurd</th><th>Reactie</th></tr></thead>
        <tbody>
        @forelse($aanvragen as $a)
            <tr onclick="location='{{ route('voorraad.aanvraag', $a) }}'" style="cursor:pointer">
                <td class="text-muted">{{ $a->id }}</td>
                <td><a href="{{ route('voorraad.show', $a->nummer) }}" onclick="event.stopPropagation()"><code>{{ $a->nummer }}</code></a>@if($a->soort === 'project') <span class="small text-muted">project</span>@endif</td>
                <td class="small">{{ \Illuminate\Support\Str::limit($a->klant, 40) }}</td>
                <td class="small">{{ $a->eigen_depot_naam ?: $a->eigen_depot }} → <strong>{{ $a->depot_naam ?: $a->depot }}</strong></td>
                <td class="text-end">{{ $a->aantal }}</td>
                <td><span class="badge bg-{{ $a->kleur() }} {{ in_array($a->kleur(), ['warning', 'light', 'info']) ? 'text-dark' : '' }}">{{ $a->label() }}</span></td>
                <td class="small">{{ $a->aanvrager_naam }}</td>
                <td class="small">{{ $a->created_at->format('d-m H:i') }}@if($a->reactie_voor && $a->status === 'verzonden' && $a->reactie_voor->isPast()) <span class="text-danger">· reactie te laat</span>@endif</td>
                <td class="small">{{ $a->gereageerd_door }}@if($a->gereageerd_op) <span class="text-muted">{{ $a->gereageerd_op->format('d-m H:i') }}</span>@endif</td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-4">Geen aanvragen gevonden.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
<div class="mt-2">{{ $aanvragen->links() }}</div>
@endsection
