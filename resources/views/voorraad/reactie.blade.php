<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Materieelaanvraag {{ $a->nummer }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f5f7; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        .kop { background: #fff; border-bottom: 3px solid #FF6600; padding: 10px 16px; display: flex; align-items: center; gap: 10px; }
        .logo { width: 34px; height: 34px; background: #FF6600; color: #fff; font-weight: 800; font-size: 20px; display: flex; align-items: center; justify-content: center; border-radius: 6px; }
        .card { border-radius: 14px; border: 1px solid #e3e6e9; }
        .btn-boels { background: #FF6600; border-color: #FF6600; color: #fff; } .btn-boels:hover { background: #e55c00; color: #fff; }
        .keuze input { display: none; } .keuze label { cursor: pointer; } .keuze input:checked + label { outline: 3px solid #FF6600; }
    </style>
</head>
<body>
<div class="kop"><div class="logo">B</div><strong>Boels Industrial</strong><span class="text-muted">· materieelaanvraag</span></div>
<div class="container py-4" style="max-width:760px">
    @if(session('ok'))<div class="alert alert-success">{{ session('ok') }}</div>@endif
    @if(session('fout'))<div class="alert alert-danger">{{ session('fout') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="card mb-3"><div class="card-body">
        <h1 class="h5 fw-bold mb-1">{{ $a->eigen_depot_naam ?: 'Depot '.$a->eigen_depot }} vraagt materieel aan bij {{ $a->depot_naam ?: 'depot '.$a->depot }}</h1>
        <div class="text-muted small mb-2">{{ $a->soort === 'project' ? 'Project' : 'Contract' }} {{ $a->nummer }} · {{ $a->klant }} · aangevraagd door {{ $a->aanvrager_naam }} op {{ $a->created_at->format('d-m-Y H:i') }}@if($a->verhuurdatum) · nodig vanaf <strong>{{ $a->verhuurdatum->format('d-m-Y') }}</strong>@endif</div>
        @if($a->opmerking)<div class="alert alert-light border small mb-2">{{ $a->opmerking }}</div>@endif
        <div>Status: <span class="badge bg-{{ $a->kleur() }} {{ in_array($a->kleur(), ['warning', 'light', 'info']) ? 'text-dark' : '' }}">{{ $a->label() }}</span>@if($a->gereageerd_door) <span class="small text-muted">door {{ $a->gereageerd_door }} op {{ $a->gereageerd_op?->format('d-m H:i') }}</span>@endif</div>
    </div></div>

    @if(in_array($a->status, ['geannuleerd', 'ontvangen']))
        <div class="alert alert-secondary">Deze aanvraag is afgesloten ({{ $a->label() }}). Reageren is niet meer nodig.</div>
    @else
    <form method="post" action="{{ request()->fullUrl() }}" class="card"><div class="card-body">
        @csrf
        <div class="d-flex gap-2 mb-3 keuze flex-wrap">
            @foreach(['akkoord' => ['Akkoord, alles', 'btn-success'], 'deels' => ['Deels', 'btn-warning'], 'nee' => ['Nee, kan niet', 'btn-danger']] as $k => [$l, $cls])
                <input type="radio" name="keuze" id="k{{ $k }}" value="{{ $k }}" @checked(old('keuze', $keuze ?? ($a->status === 'deels' ? 'deels' : null)) === $k) onchange="document.getElementById('aantallen').classList.toggle('opacity-50', this.value !== 'deels')">
                <label for="k{{ $k }}" class="btn {{ $cls }}">{{ $l }}</label>
            @endforeach
        </div>
        <table class="table table-sm align-middle" id="aantallen">
            <thead><tr><th>Subgroep</th><th class="text-end">Gevraagd</th><th style="width:120px">Kunnen we leveren</th></tr></thead>
            <tbody>
            @foreach($a->regels as $r)
                <tr><td><code>{{ $r['subgroep'] }}</code> {{ $r['omschrijving'] }}</td><td class="text-end fw-semibold">{{ $r['aantal'] }}</td>
                    <td><input type="number" min="0" max="{{ $r['aantal'] }}" name="toegezegd[{{ $r['subgroep'] }}]" value="{{ old('toegezegd.'.$r['subgroep'], $r['toegezegd'] ?? $r['aantal']) }}" class="form-control form-control-sm text-end"></td></tr>
            @endforeach
            </tbody>
        </table>
        <div class="small text-muted mb-2">De aantallen tellen alleen bij "Deels". Bij "Akkoord" geldt alles, bij "Nee" niets.</div>
        <div class="row g-2">
            <div class="col-md-5"><input type="text" name="naam" class="form-control" placeholder="Je naam" value="{{ old('naam') }}" required></div>
            <div class="col-md-7"><input type="text" name="opmerking" class="form-control" placeholder="Opmerking, bv. gaat donderdag mee met de transfer" value="{{ old('opmerking') }}"></div>
        </div>
        <button class="btn btn-boels mt-3 w-100">Reactie versturen</button>
    </div></form>
    @endif
    <p class="text-center text-muted small mt-3">Boels Industrial · Dashboard · reactie wordt per mail aan {{ $a->aanvrager_naam ?: 'de aanvrager' }} gemeld</p>
</div>
</body>
</html>
