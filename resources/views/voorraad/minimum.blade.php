@extends('layouts.app')
@section('titel', 'Minimumvoorraad')
@section('inhoud')
@include('voorraad._nav')
<div class="page-header">
    <div><h1>Minimumvoorraad</h1><p>Per depot per subgroep hoeveel er minimaal beschikbaar moet zijn. De werkplaats ziet tekorten en welke machines nagekeken moeten worden. Zet het minimum op 0 om een regel te verwijderen.</p></div>
    <form method="get"><select name="depot" class="form-select form-select-sm" onchange="this.form.submit()">@foreach($depots as $nr => $naam)<option value="{{ $nr }}" @selected((string) $depot === (string) $nr)>{{ $naam }}</option>@endforeach</select></form>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card"><div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead><tr><th>Subgroep</th><th class="text-end">Beschikb.</th><th class="text-end">Onderh.</th><th class="text-end">Rep.</th><th class="text-end">Verhuurd</th><th style="width:150px">Minimum</th><th>Gewijzigd door</th></tr></thead>
            <tbody>
            @forelse($minima as $m)
                @php($s = $stand[$m->subgroep] ?? [])
                <tr class="{{ ($s['beschikbaar'] ?? 0) < $m->minimum ? 'table-warning' : '' }}">
                    <td><code>{{ $m->subgroep }}</code> {{ $m->omschrijving ?? ($s['omschrijving'] ?? '') }}</td>
                    <td class="text-end fw-semibold {{ ($s['beschikbaar'] ?? 0) < $m->minimum ? 'text-danger' : '' }}">{{ $s['beschikbaar'] ?? 0 }}</td>
                    <td class="text-end">{{ $s['onderhoud'] ?? 0 }}</td>
                    <td class="text-end">{{ $s['reparatie'] ?? 0 }}</td>
                    <td class="text-end">{{ $s['verhuurd'] ?? 0 }}</td>
                    <td>
                        @if($mag)
                        <form method="post" action="{{ route('voorraad.minimum.opslaan') }}" class="d-flex gap-1">@csrf
                            <input type="hidden" name="depot" value="{{ $depot }}"><input type="hidden" name="subgroep" value="{{ $m->subgroep }}"><input type="hidden" name="omschrijving" value="{{ $m->omschrijving }}">
                            <input type="number" name="minimum" min="0" value="{{ $m->minimum }}" class="form-control form-control-sm text-end"><button class="btn btn-sm btn-outline-boels"><i class="bi bi-check-lg"></i></button>
                        </form>
                        @else {{ $m->minimum }} @endif
                    </td>
                    <td class="small text-muted">{{ $m->gewijzigd_door }}<br>{{ $m->updated_at?->format('d-m-Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Nog geen minima voor dit depot.</td></tr>
            @endforelse
            </tbody>
        </table></div></div>
    </div>
    <div class="col-lg-4">
        @if($mag)
        <form method="post" action="{{ route('voorraad.minimum.opslaan') }}" class="card mb-3">@csrf
            <div class="card-header">Subgroep toevoegen</div>
            <div class="card-body">
                <input type="hidden" name="depot" value="{{ $depot }}">
                <div class="row g-2"><div class="col-7"><input type="text" name="subgroep" class="form-control form-control-sm" placeholder="Subgroepnr, bv. 14561" required></div><div class="col-5"><input type="number" name="minimum" min="1" class="form-control form-control-sm" placeholder="Minimum" required></div></div>
                <button class="btn btn-boels btn-sm w-100 mt-2">Toevoegen</button>
                <div class="small text-muted mt-1">De omschrijving komt uit de vlootvoorraad.</div>
            </div>
        </form>
        @endif
        @if($andereDepots->isNotEmpty() && \App\Services\Rollen::heeft(['vlootmanager', 'area_manager', 'bu_manager', 'admin']))
        <form method="post" action="{{ route('voorraad.minimum.kopieer') }}" class="card">@csrf
            <div class="card-header">Kopiëren van ander depot</div>
            <div class="card-body">
                <input type="hidden" name="depot" value="{{ $depot }}">
                <select name="van" class="form-select form-select-sm">@foreach($andereDepots as $d => $n)<option value="{{ $d }}">{{ $depots[$d] ?? $d }} ({{ $n }} minima)</option>@endforeach</select>
                <button class="btn btn-outline-secondary btn-sm w-100 mt-2" onclick="return confirm('Minima kopiëren? Bestaande subgroepen worden overschreven.')">Kopiëren naar {{ $depots[$depot] ?? $depot }}</button>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
