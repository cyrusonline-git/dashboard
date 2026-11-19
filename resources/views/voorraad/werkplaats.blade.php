@extends('layouts.app')
@section('titel', 'Werkplaats')
@section('main_klasse', 'breed')
@section('inhoud')
@include('voorraad._nav')
<div class="page-header">
    <div><h1>Werkplaats: wat moet er nagekeken worden?</h1><p>Bij een tekort ten opzichte van de minimumvoorraad, of voor aankomende orders die het depot niet uit beschikbaar kan leveren, zie je welke machines nagekeken moeten worden. Eerst die in onderhoud, dan die in reparatie.</p></div>
    <form method="get" class="d-flex gap-2 align-items-center">
        <select name="depot" class="form-select form-select-sm" onchange="this.form.submit()">@foreach($depots as $nr => $naam)<option value="{{ $nr }}" @selected((string) $depot === (string) $nr)>{{ $naam }}</option>@endforeach</select>
        <select name="horizon" class="form-select form-select-sm" onchange="this.form.submit()" title="Aankomende orders t/m">@foreach([7, 14, 21, 28] as $h)<option value="{{ $h }}" @selected($horizon === $h)>orders {{ $h }} dagen</option>@endforeach</select>
    </form>
</div>
<div class="row g-3">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center"><span><i class="bi bi-sliders me-2 text-boels"></i>Onder de minimumvoorraad</span>
                <a href="{{ request()->fullUrlWithQuery(['alles' => $alles ? null : 1]) }}" class="small fw-normal">{{ $alles ? 'alleen tekorten' : 'alle '.$aantalMinima.' minima tonen' }}</a></div>
            <div class="table-responsive"><table class="table table-sm align-middle mb-0">
                <thead><tr><th>Subgroep</th><th class="text-end">Min.</th><th class="text-end">Beschikb.</th><th class="text-end">Tekort</th><th>Na te kijken</th></tr></thead>
                <tbody>
                @forelse($tekorten as $t)
                    <tr class="{{ $t['tekort'] ? 'table-warning' : '' }}">
                        <td><code>{{ $t['subgroep'] }}</code> {{ \Illuminate\Support\Str::limit($t['omschrijving'], 40) }}</td>
                        <td class="text-end">{{ $t['minimum'] }}</td>
                        <td class="text-end">{{ $t['beschikbaar'] }}</td>
                        <td class="text-end fw-bold {{ $t['tekort'] ? 'text-danger' : 'text-success' }}">{{ $t['tekort'] ?: '✓' }}</td>
                        <td class="small">
                            @foreach($naTeKijken[$t['subgroep']] ?? [] as $m)
                                <span class="badge {{ $m['status_key'] === 'onderhoud' ? 'bg-warning text-dark' : 'bg-danger' }} me-1" title="{{ $m['status'] }}{{ $m['location'] ? ' · plaats '.$m['location'] : '' }}">{{ $m['item_number'] }}</span>
                            @endforeach
                            @if($t['tekort'] && empty($naTeKijken[$t['subgroep']]))<span class="text-muted">niets in onderhoud/reparatie: aanvragen of inkopen</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">@if($aantalMinima) Alles boven het minimum. @else Nog geen minimumvoorraad ingesteld voor dit depot: <a href="{{ route('voorraad.minimum', ['depot' => $depot]) }}">instellen</a>. @endif</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-calendar-event me-2 text-boels"></i>Nakijken voor aankomende orders <span class="small text-muted fw-normal">t/m {{ now()->addDays($horizon)->format('d-m') }}</span></div>
            <div class="table-responsive"><table class="table table-sm align-middle mb-0">
                <thead><tr><th>Subgroep</th><th class="text-end">Nodig</th><th class="text-end">Beschikb.</th><th>Eerste levering</th><th>Na te kijken</th></tr></thead>
                <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td><code>{{ $o['subgroep'] }}</code> {{ \Illuminate\Support\Str::limit($o['omschrijving'], 36) }}
                            <div class="small text-muted">@foreach(array_slice($o['contracten'], 0, 3) as $k)<a href="{{ route('voorraad.show', $k['contract_number']) }}">{{ $k['contract_number'] }}</a>{{ $k['offerte'] ? ' (offerte)' : '' }} {{ $k['aantal'] }}× · @endforeach{{ count($o['contracten']) > 3 ? '…' : '' }}</div></td>
                        <td class="text-end fw-semibold">{{ $o['aantal'] }}</td>
                        <td class="text-end">{{ $o['beschikbaar'] }}</td>
                        <td>{{ dmy($o['eerste_datum']) }}</td>
                        <td class="small">@foreach($naTeKijken[$o['subgroep']] ?? [] as $m)<span class="badge {{ $m['status_key'] === 'onderhoud' ? 'bg-warning text-dark' : 'bg-danger' }} me-1" title="{{ $m['status'] }}">{{ $m['item_number'] }}</span> @endforeach</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Geen aankomende orders die op onderhoud/reparatie wachten.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            <div class="card-footer bg-white small text-muted"><span class="badge bg-warning text-dark">nr</span> in onderhoud · <span class="badge bg-danger">nr</span> in reparatie</div>
        </div>
    </div>
</div>
@endsection
