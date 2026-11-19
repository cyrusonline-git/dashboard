@extends('layouts.app')
@section('titel', 'Aanvraag '.$nr)
@section('main_klasse', 'breed')
@section('inhoud')
@include('voorraad._nav')
<div class="page-header">
    <div>
        <h1>Aanvraag bij {{ $depotNaam ?: 'depot '.$depot }}</h1>
        <p>Voor {{ ($c['soort'] ?? 'contract') === 'project' ? 'project' : 'contract' }} <a href="{{ route('voorraad.show', $nr) }}">{{ $nr }}</a> · {{ collect($c['kop'] ?? [])->pluck('customer_name')->filter()->unique()->implode(', ') }}. Het depot krijgt een mail met knoppen <strong>Akkoord / Deels / Nee</strong>. Inloggen is daarvoor niet nodig. Pas na een toezegging telt het als geregeld.</p>
    </div>
</div>
<form method="post" action="{{ route('voorraad.verstuur', [$nr, $depot]) }}?{{ http_build_query(request()->only(['horizon', 'onderhoud', 'offertes'])) }}">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between"><span>Subgroepen</span><span class="small fw-normal"><a href="#" onclick="document.querySelectorAll('.mee').forEach(c=>c.checked=true);return false">alles</a> · <a href="#" onclick="document.querySelectorAll('.mee').forEach(c=>c.checked=false);return false">niets</a></span></div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th style="width:36px"></th><th>Subgroep</th><th class="text-end">Nodig</th><th class="text-end">Vrij bij {{ $depotNaam ?: $depot }}</th><th class="text-end">Advies</th><th style="width:110px">Aantal</th></tr></thead>
                        <tbody>
                        @forelse($regels as $r)
                            <tr>
                                <td><input type="checkbox" class="form-check-input mee" name="regels[{{ $r['subgroep'] }}][mee]" value="1" @checked(old('regels.'.$r['subgroep'].'.mee', $r['voorstel'] > 0))></td>
                                <td><code>{{ $r['subgroep'] }}</code> {{ $r['omschrijving'] }}<input type="hidden" name="regels[{{ $r['subgroep'] }}][omschrijving]" value="{{ $r['omschrijving'] }}">
                                    @if($r['al_aangevraagd'])<div class="small text-warning">{{ $r['al_aangevraagd'] }} al aangevraagd/toegezegd bij dit depot</div>@endif
                                </td>
                                <td class="text-end">{{ $r['nodig'] }}</td>
                                <td class="text-end">{{ $r['vrij'] }}</td>
                                <td class="text-end">{{ $r['advies'] ?: '—' }}</td>
                                <td><input type="number" min="0" max="{{ max($r['vrij'], $r['voorstel']) }}" name="regels[{{ $r['subgroep'] }}][aantal]" value="{{ old('regels.'.$r['subgroep'].'.aantal', $r['voorstel'] ?: min($r['vrij'], $r['nodig'])) }}" class="form-control form-control-sm text-end"></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted text-center py-4">Dit depot heeft niets vrij van wat deze order nodig heeft.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <label class="form-label small fw-semibold">Aan (e-mail van {{ $depotNaam ?: 'depot '.$depot }})</label>
                    <input type="email" name="aan_email" class="form-control form-control-sm mb-1" value="{{ old('aan_email', $depotEmail) }}" required>
                    @if(!$depotEmail)<div class="small text-warning mb-2">Niet bekend in CORE. Vul het hier in, of zet het in CORE bij Infrastructuur → depot.</div>@else<div class="small text-muted mb-2">uit CORE Infrastructuur</div>@endif
                    <div class="small text-muted mb-3">
                        Namens: {{ $eigenNaam ?: 'depot '.$eigen }}<br>
                        Kopie: {{ collect([$eigenEmail, core_gebruiker()['email'] ?? null, setting('voorraad_mail_cc')])->filter()->implode(', ') ?: '—' }}<br>
                        Antwoorden per mail gaan naar jou ({{ core_gebruiker()['email'] ?? '?' }}).
                    </div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label small fw-semibold">Gewenste verhuurdatum</label><input type="date" name="verhuurdatum" class="form-control form-control-sm" value="{{ old('verhuurdatum', $verhuurdatum) }}"></div>
                        <div class="col-6"><label class="form-label small fw-semibold">Reactie graag vóór</label><input type="date" name="reactie_voor" class="form-control form-control-sm" value="{{ old('reactie_voor', now()->addWeekday()->toDateString()) }}"></div>
                    </div>
                    <label class="form-label small fw-semibold mt-2">Opmerking</label>
                    <textarea name="opmerking" rows="3" class="form-control form-control-sm" placeholder="bv. graag mee met de transfer van donderdag">{{ old('opmerking') }}</textarea>
                    <button class="btn btn-boels w-100 mt-3" @disabled($regels->isEmpty())><i class="bi bi-send me-1"></i>Aanvraag versturen</button>
                    <a href="{{ route('voorraad.show', $nr) }}" class="btn btn-link btn-sm w-100">Annuleren</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
