@extends('layouts.app')
@section('titel', 'Voorraadcheck '.$nr)
@section('inhoud')
@include('voorraad._nav')
@php $kop = collect($c['kop'] ?? []); @endphp
@php $depots = $c['depots'] ?? []; @endphp
@php $eigen = $c['eigen_depot']; @endphp
@php $eigenNaam = $depots[$eigen]['naam'] ?? 'depot '.$eigen; @endphp
<div class="page-header">
    <div>
        <h1><span class="text-muted fw-normal">{{ ($c['soort'] ?? 'contract') === 'project' ? 'Project' : 'Contract' }}</span> {{ $nr }}
            @if($kop->contains('is_offerte', true)) <span class="soort-badge soort-offerte align-middle">offerte</span> @endif
        </h1>
        <p class="mb-0">
            {{ $kop->pluck('customer_name')->filter()->unique()->implode(', ') ?: 'Klant onbekend' }}
            @if($kop->count() === 1 && $kop[0]['aflevering']) · {{ $kop[0]['aflevering'] }} @endif
            @if($kop->pluck('leverdatum')->filter()->min()) · levering <strong>{{ dmy($kop->pluck('leverdatum')->filter()->min()) }}</strong> @endif
            @if($kop->pluck('verhuurdatum')->filter()->min()) · start huur {{ dmy($kop->pluck('verhuurdatum')->filter()->min()) }} @endif
            @if(($c['soort'] ?? '') === 'project') · {{ count($c['contracten']) }} contracten @elseif(!empty($c['project_number'])) · project <a href="{{ route('voorraad.show', $c['project_number']) }}">{{ $c['project_number'] }}</a> @endif
        </p>
    </div>
    <div class="d-flex gap-2">
        @if(($c['soort'] ?? '') !== 'project')<a href="{{ route('planning.show', $nr) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-truck me-1"></i>Orderdetail</a>@endif
        <a href="{{ route('voorraad') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Ander nummer</a>
    </div>
</div>

@foreach($c['waarschuwingen'] ?? [] as $w)<div class="alert alert-warning py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>{{ $w }}</div>@endforeach
@if(!empty($c['melding']))<div class="alert {{ empty($c['gevonden']) ? 'alert-danger' : 'alert-info' }}"><i class="bi bi-info-circle me-1"></i>{{ $c['melding'] }}</div>@endif

@if(!empty($c['gevonden']))
    {{-- Kerncijfers --}}
    <div class="row g-2 mb-3">
        @php
        $tegels = [
            ['Nog niet toegekend', $totaal['nodig'], 'bi-boxes', '#455a64', ($c['telling']['stuks_toegekend_of_geleverd'] ?? 0).' st al toegekend of geleverd'],
            ['Uit eigen depot', $totaal['uit_eigen'], 'bi-house-check', '#2e7d32', $eigenNaam],
            ['Toegezegd door collega\'s', $totaal['toegezegd'], 'bi-hand-thumbs-up', '#00897b', 'via aanvragen'],
            ['Wacht op reactie', $totaal['wacht'], 'bi-hourglass-split', '#ef6c00', 'aangevraagd, nog geen antwoord'],
            ['Nog te regelen', $totaal['nog'], 'bi-exclamation-diamond', $totaal['nog'] ? '#c62828' : '#2e7d32', $totaal['nog'] ? ($totaal['tekort'] ? $totaal['tekort'].' st nergens beschikbaar' : 'aan te vragen hieronder') : 'order compleet'],
        ];
        @endphp
        @foreach($tegels as [$label, $waarde, $icoon, $kleur, $sub])
            <div class="col-6 col-lg"><div class="card kpi-tile"><div class="kpi-body"><div class="kpi-icon" style="background:{{ $kleur }}"><i class="bi {{ $icoon }}"></i></div><div><div class="kpi-value">{{ $waarde }}</div><div class="kpi-label">{{ $label }}<br><span class="text-muted">{{ $sub }}</span></div></div></div></div></div>
        @endforeach
    </div>

    {{-- Instellingen van de berekening --}}
    <form method="get" class="d-flex flex-wrap gap-3 align-items-center small mb-2 text-muted">
        <span><i class="bi bi-sliders me-1"></i>Reserveringen meetellen t/m</span>
        <select name="horizon" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            @foreach([7, 14, 21, 28, 42, 60] as $h)<option value="{{ $h }}" @selected((int) $opties['horizon'] === $h)>{{ $h }} dagen</option>@endforeach
        </select>
        <label class="form-check mb-0"><input type="hidden" name="offertes" value="0"><input class="form-check-input" type="checkbox" name="offertes" value="1" @checked($opties['offertes'] === '1') onchange="this.form.submit()"> offertes houden voorraad vast</label>
        <label class="form-check mb-0"><input type="hidden" name="onderhoud" value="0"><input class="form-check-input" type="checkbox" name="onderhoud" value="1" @checked($opties['onderhoud'] === '1') onchange="this.form.submit()"> "in onderhoud" als tweede keus</label>
        <span class="ms-auto">Vloot: {{ !empty($c['stand']['vloot']) ? \Carbon\Carbon::parse($c['stand']['vloot'])->format('d-m H:i') : '—' }} · 3.4: {{ !empty($c['stand']['contractregels']) ? \Carbon\Carbon::parse($c['stand']['contractregels'])->format('d-m H:i') : '—' }} · 8.A: {{ !empty($c['stand']['toegekend']) ? \Carbon\Carbon::parse($c['stand']['toegekend'])->format('d-m H:i') : '—' }}</span>
    </form>

    {{-- Order compleet? per subgroep --}}
    <div class="card mb-3">
        <div class="card-header"><i class="bi bi-clipboard-check me-2 text-boels"></i>Order compleet? <span class="text-muted fw-normal small">per subgroep, alleen regels die nog niet toegekend zijn</span></div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Subgroep</th><th class="text-end">Nodig</th><th>{{ $eigenNaam }}</th><th class="text-end">Uit eigen</th><th class="text-end">Toegezegd</th><th class="text-end">Wacht</th><th class="text-end">Nog te regelen</th><th>Advies: aanvragen bij</th></tr></thead>
                <tbody>
                @forelse($rijen as $r)
                    @php $rijKleur = $r['nog_te_regelen'] === 0 ? 'table-success' : ($r['tekort'] > 0 && empty($r['advies']) ? 'table-danger' : ''); @endphp
                    <tr class="{{ $rijKleur }}">
                        <td><code>{{ $r['subgroep'] }}</code> {{ $r['omschrijving'] }}
                            @if(!$r['in_vloot'])<div class="small text-danger">niet in de vlootvoorraad</div>@endif
                            @if(count($r['contracten'] ?? []) > 1)<div class="small text-muted">{{ count($r['contracten']) }} contracten</div>@endif
                        </td>
                        <td class="text-end fw-semibold">{{ $r['nodig'] }}</td>
                        <td class="small">
                            <span class="fw-semibold {{ $r['eigen']['beschikbaar'] - $r['eigen']['gereserveerd'] > 0 ? 'text-success' : 'text-muted' }}">{{ max(0, $r['eigen']['beschikbaar'] - $r['eigen']['gereserveerd']) }} vrij</span>
                            <span class="text-muted">van {{ $r['eigen']['beschikbaar'] }} beschikbaar</span>
                            @if($r['eigen']['gereserveerd'])
                                <span class="text-muted" title="{{ collect($r['reserveringen'])->map(fn($x) => $x['contract_number'].($x['offerte'] ? ' (offerte)' : '').' '.$x['klant'].': '.$x['aantal'].' st op '.dmy($x['datum']))->implode("\n") }}" style="cursor:help;border-bottom:1px dotted">· {{ $r['eigen']['gereserveerd'] }} gereserveerd</span>
                            @endif
                            @if($r['eigen']['onderhoud'])<div class="text-muted">{{ $r['eigen']['onderhoud'] }} in onderhoud</div>@endif
                            @if($r['eigen']['onderweg'])<div class="text-muted">{{ $r['eigen']['onderweg'] }} onderweg</div>@endif
                        </td>
                        <td class="text-end">{{ $r['uit_eigen'] ?: '—' }}@if($r['uit_eigen_onderhoud'])<div class="small text-muted">{{ $r['uit_eigen_onderhoud'] }} uit onderhoud</div>@endif</td>
                        <td class="text-end {{ $r['toegezegd'] ? 'text-success fw-semibold' : 'text-muted' }}">{{ $r['toegezegd'] ?: '—' }}</td>
                        <td class="text-end {{ $r['wacht'] ? 'text-warning fw-semibold' : 'text-muted' }}">{{ $r['wacht'] ?: '—' }}</td>
                        <td class="text-end fw-bold {{ $r['nog_te_regelen'] ? 'text-danger' : 'text-success' }}">{{ $r['nog_te_regelen'] ?: '✓' }}</td>
                        <td class="small">
                            @if($r['nog_te_regelen'] > 0)
                                @foreach($r['advies'] as $d => $n)
                                    <a href="{{ route('voorraad.nieuw', [$nr, $d]) }}?{{ http_build_query($opties) }}" class="badge bg-light text-dark border text-decoration-none me-1" title="Aanvraag maken bij {{ $depots[$d]['naam'] ?? 'depot '.$d }}">{{ $depots[$d]['naam'] ?? $d }}: {{ $n }}@if(!empty($r['advies_onderhoud'][$d])) <span class="text-muted">({{ $r['advies_onderhoud'][$d] }} onderh.)</span>@endif</a>
                                @endforeach
                                @if($r['tekort'] > 0)<span class="badge bg-danger">{{ $r['tekort'] }} nergens beschikbaar</span>@endif
                            @elseif($r['nodig'] > 0)
                                <span class="text-success"><i class="bi bi-check-circle"></i> geregeld</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Niets te zoeken: alle huurregels zijn toegekend.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Per collega-depot: aanvraag maken --}}
    @if($perDepot->isNotEmpty())
    <h2 class="h6 fw-bold mt-4 mb-2"><i class="bi bi-geo-alt me-1 text-boels"></i>Aanvragen bij collega-depots</h2>
    <div class="row g-3 mb-3">
        @foreach($perDepot as $d)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 {{ $d['voorstel'] ? '' : 'opacity-75' }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>{{ $d['naam'] ?? 'Depot '.$d['depot'] }} <span class="text-muted fw-normal small">{{ $d['depot'] }}</span></span>
                        <span class="badge bg-boels">{{ $d['voorstel'] }} st</span>
                    </div>
                    <ul class="list-group list-group-flush small">
                        @foreach($d['regels'] as $x)
                            <li class="list-group-item d-flex justify-content-between">
                                <span><strong>{{ $x['voorstel'] }}×</strong> <code>{{ $x['subgroep'] }}</code> {{ \Illuminate\Support\Str::limit($x['omschrijving'], 38) }}</span>
                                <span class="text-muted text-nowrap">{{ $x['vrij_beschikbaar'] }} vrij @if($x['al_aangevraagd'])· {{ $x['al_aangevraagd'] }} al aangevraagd @endif</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="card-body py-2">
                        @if(!$d['email'])<div class="small text-warning mb-1"><i class="bi bi-exclamation-triangle"></i> Geen e-mailadres in CORE. Vul het in het formulier in.</div>@endif
                        @if(\App\Services\Rollen::magAanvragen())
                            <a href="{{ route('voorraad.nieuw', [$nr, $d['depot']]) }}?{{ http_build_query($opties) }}" class="btn btn-sm {{ $d['voorstel'] ? 'btn-boels' : 'btn-outline-secondary' }} w-100"><i class="bi bi-envelope me-1"></i>{{ $d['al_aangevraagd'] ? 'Nogmaals aanvragen' : 'Aanvraag maken' }}</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
@endif

<div class="row g-3">
    {{-- Aanvragen voor dit nummer --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><i class="bi bi-envelope-paper me-2 text-boels"></i>Aanvragen voor {{ $nr }}</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead><tr><th>Depot</th><th class="text-end">Aantal</th><th>Status</th><th>Door</th><th>Wanneer</th></tr></thead>
                    <tbody>
                    @forelse($aanvragen as $a)
                        <tr>
                            <td><a href="{{ route('voorraad.aanvraag', $a) }}">{{ $a->depot_naam ?: 'depot '.$a->depot }}</a></td>
                            <td class="text-end">{{ $a->aantal }}@if($a->status === 'deels') <span class="small text-muted">({{ array_sum($a->toegezegdPerSubgroep()) }} toegezegd)</span>@endif</td>
                            <td><span class="badge bg-{{ $a->kleur() }} {{ in_array($a->kleur(), ['warning', 'light', 'info']) ? 'text-dark' : '' }}">{{ $a->label() }}</span>@if($a->gereageerd_door)<div class="small text-muted">{{ $a->gereageerd_door }}</div>@endif</td>
                            <td class="small">{{ $a->aanvrager_naam }}</td>
                            <td class="small">{{ $a->created_at->format('d-m H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted text-center py-3">Nog niets aangevraagd.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{-- Transfers onderweg --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><i class="bi bi-arrow-left-right me-2 text-boels"></i>Transfers naar {{ $eigenNaam }}</div>
            <div class="list-group list-group-flush small">
                @forelse(collect($c['transfers'] ?? [])->filter(fn($t) => !$t['delivery_date'] || $t['delivery_date'] >= now()->subDays(14)->toDateString())->take(15) as $t)
                    @php $pk = $t['total'] > 0 ? ($t['picked'] >= $t['total'] ? 'volledig' : ($t['picked'] > 0 ? 'deels' : 'niets')) : 'onbekend'; @endphp
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><code>{{ $t['order_number'] }}</code> van <strong>{{ $t['van'] }}</strong><div class="text-muted">{{ dmy($t['delivery_date']) }}@if($t['reference']) · {{ $t['reference'] }}@endif</div></span>
                        <span class="pick {{ $pk }}">{{ $t['total'] ? $t['picked'].'/'.$t['total'].' gepickt' : 'geen regels' }}</span>
                    </div>
                @empty
                    <div class="list-group-item text-muted">Geen transfers naar dit depot in de 8.7.</div>
                @endforelse
            </div>
            <div class="card-footer small text-muted bg-white">De 8.7 kent transfers alleen per order (aantal gepickt), niet welke subgroepen erin zitten. Ze tellen daarom niet automatisch mee. Check bij een tekort of het al in een transfer zit.</div>
        </div>
    </div>
</div>
@endsection
