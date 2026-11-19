@extends('layouts.app')
@section('titel', 'Aanvraag #'.$a->id)
@section('main_klasse', 'breed')
@section('inhoud')
@include('voorraad._nav')
<div class="page-header">
    <div>
        <h1>Aanvraag #{{ $a->id }} <span class="badge bg-{{ $a->kleur() }} {{ in_array($a->kleur(), ['warning', 'light', 'info']) ? 'text-dark' : '' }} align-middle fs-6">{{ $a->label() }}</span></h1>
        <p>{{ $a->eigen_depot_naam ?: $a->eigen_depot }} → <strong>{{ $a->depot_naam ?: 'depot '.$a->depot }}</strong> · {{ $a->soort === 'project' ? 'project' : 'contract' }} <a href="{{ route('voorraad.show', $a->nummer) }}">{{ $a->nummer }}</a> · {{ $a->klant }}</p>
    </div>
    <a href="{{ route('voorraad.aanvragen') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Alle aanvragen</a>
</div>
@if($a->fout)<div class="alert alert-danger small"><strong>Mail mislukt:</strong> {{ $a->fout }}</div>@endif
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Aangevraagd</div>
            <table class="table table-sm mb-0">
                <thead><tr><th>Subgroep</th><th class="text-end">Aantal</th><th class="text-end">Toegezegd</th></tr></thead>
                <tbody>
                @php($toeg = $a->toegezegdPerSubgroep())
                @foreach($a->regels as $r)
                    <tr><td><code>{{ $r['subgroep'] }}</code> {{ $r['omschrijving'] }}</td><td class="text-end">{{ $r['aantal'] }}</td><td class="text-end {{ ($toeg[$r['subgroep']] ?? 0) ? 'text-success fw-semibold' : 'text-muted' }}">{{ in_array($a->status, ['verzonden', 'mislukt', 'geannuleerd']) ? '—' : ($toeg[$r['subgroep']] ?? 0) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($a->reactie)
        <div class="card mt-3"><div class="card-header">Verloop</div><div class="card-body small" style="white-space:pre-line">{{ $a->reactie }}</div></div>
        @endif
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-5">Aanvrager</dt><dd class="col-7">{{ $a->aanvrager_naam }}<br><span class="text-muted">{{ $a->aanvrager_email }}</span></dd>
                    <dt class="col-5">Verstuurd</dt><dd class="col-7">{{ $a->created_at->format('d-m-Y H:i') }}</dd>
                    <dt class="col-5">Aan</dt><dd class="col-7">{{ $a->aan_email }}</dd>
                    <dt class="col-5">Kopie</dt><dd class="col-7">{{ $a->cc ?: '—' }}</dd>
                    <dt class="col-5">Verhuurdatum</dt><dd class="col-7">{{ $a->verhuurdatum?->format('d-m-Y') ?? '—' }}</dd>
                    <dt class="col-5">Reactie vóór</dt><dd class="col-7">{{ $a->reactie_voor?->format('d-m-Y') ?? '—' }}</dd>
                    <dt class="col-5">Opmerking</dt><dd class="col-7">{{ $a->opmerking ?: '—' }}</dd>
                    <dt class="col-5">Gereageerd</dt><dd class="col-7">{{ $a->gereageerd_door ?: '—' }}@if($a->gereageerd_op) <span class="text-muted">{{ $a->gereageerd_op->format('d-m H:i') }}</span>@endif</dd>
                </dl>
            </div>
        </div>
        @if(\App\Services\Rollen::magAanvragen())
        <form method="post" action="{{ route('voorraad.aanvraag.status', $a) }}" class="card mt-3">
            @csrf
            <div class="card-header">Status zelf bijwerken</div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 mb-2">
                    @foreach(['ontvangen' => ['Ontvangen', 'btn-outline-secondary'], 'toegezegd' => ['Telefonisch toegezegd', 'btn-outline-success'], 'afgewezen' => ['Afgewezen', 'btn-outline-danger'], 'geannuleerd' => ['Annuleren', 'btn-outline-dark'], 'verzonden' => ['Terug naar wacht', 'btn-outline-warning']] as $k => [$l, $cls])
                        @if($a->status !== $k)<button name="status" value="{{ $k }}" class="btn btn-sm {{ $cls }}">{{ $l }}</button>@endif
                    @endforeach
                </div>
                <input type="text" name="opmerking" class="form-control form-control-sm" placeholder="Opmerking (optioneel)">
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
