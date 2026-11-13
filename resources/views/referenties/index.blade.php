@extends('layouts.app')
@section('titel', 'PO-bewaking')
@section('inhoud')
<div class="page-header mb-2">
    <h1>PO-bewaking</h1>
    <p>Contracten en orders zonder klantreferentie of PO-nummer in Smartview (kolom REF in de 8.7, kolom "Your Reference" in de WOW: leeg of "volgt"). Zonder referentie wordt de factuur niet betaald. Een contract blijft hier staan, ook na levering, tot de referentie in InspHire is ingevuld; dan verdwijnt het vanzelf. Leg per contract vast of de PO al is opgevraagd.</p>
</div>
@include('partials.stand', ['uploads' => $res['uploads'] ?? [], 'soorten' => ['wow', 'planning']])

<form class="card mb-3" method="get">
    <div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
        <input type="search" name="q" value="{{ $f['q'] }}" class="form-control form-control-sm" style="max-width:220px" placeholder="Klant, contract, project…">
        <select name="employee_id" class="form-select form-select-sm" style="max-width:200px">
            <option value="">Alle verkopers</option>
            @foreach($verkopers as $id => $naam)<option value="{{ $id }}" @selected(!$f['mijn'] && (string)$f['employee_id']===(string)$id)>{{ $naam }}</option>@endforeach
        </select>
        <label class="form-check form-check-inline small mb-0"><input class="form-check-input" type="checkbox" name="mijn" value="1" @checked($f['mijn'])> alleen mijn contracten</label>
        <select name="bron" class="form-select form-select-sm" style="max-width:170px">
            <option value="">In huur én planning</option>
            <option value="in_huur" @selected($f['bron']==='in_huur')>Alleen in huur</option>
            <option value="planning" @selected($f['bron']==='planning')>Alleen nog te leveren</option>
        </select>
        <select name="status" class="form-select form-select-sm" style="max-width:170px">
            <option value="">Open + opgevraagd</option>
            @foreach(['open'=>'Open','opgevraagd'=>'Opgevraagd','afgehandeld'=>'Afgehandeld'] as $k=>$l)<option value="{{ $k }}" @selected($f['status']===$k)>{{ $l }}</option>@endforeach
        </select>
        <button class="btn btn-sm btn-boels">Filter</button>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-sm btn-outline-secondary" title="Excel/CSV"><i class="bi bi-file-earmark-spreadsheet"></i></a>
    </div>
</form>

@php($t = $res['totaal'] ?? [])
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="card p-2 text-center"><div class="fs-4 fw-bold">{{ ($t['open'] ?? 0) + ($t['opgevraagd'] ?? 0) }}</div><div class="small text-muted">zonder referentie · {{ $t['in_huur'] ?? 0 }} in huur, {{ $t['planning'] ?? 0 }} nog te leveren</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-2 text-center"><div class="fs-4 fw-bold text-warning">{{ $t['open'] ?? 0 }}</div><div class="small text-muted">nog niet opgevraagd</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-2 text-center"><div class="fs-4 fw-bold {{ ($t['ouder_dan_30'] ?? 0) ? 'text-danger' : '' }}">{{ $t['ouder_dan_30'] ?? 0 }}</div><div class="small text-muted">langer dan 30 dagen</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-2 text-center"><div class="fs-4 fw-bold">{{ euro($t['weekhuur'] ?? 0) }}</div><div class="small text-muted">weekhuur zonder referentie</div></div></div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Contract</th><th>Klant</th><th>Waar</th><th class="text-end">Dagen</th><th class="text-end">Weekhuur</th><th>Verkoper</th><th>Referentie in Smartview</th><th style="min-width:330px">Opvolging</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($rijen as $r)
                @php($kleur = $r['status'] === 'afgehandeld' ? 'table-light text-muted' : ($r['dagen'] > 30 ? 'table-danger' : ($r['dagen'] > 7 ? 'table-warning' : '')))
                <tr class="{{ $kleur }}" id="c{{ $r['contract_number'] }}">
                    <td><a href="{{ $r['bron'] === 'in_huur' ? route('huur.show', $r['contract_number']) : route('planning.show', $r['contract_number']) }}"><code>{{ $r['contract_number'] }}</code></a></td>
                    <td>{{ $r['customer_name'] }}<div class="small text-muted">{{ $r['project'] }}</div></td>
                    <td class="small">@if($r['bron'] === 'in_huur')<span class="badge bg-primary">in huur</span><div class="text-muted">sinds {{ dmy($r['sinds']) }}@if($r['to_collect']) · {{ $r['to_collect'] }} afgemeld @endif</div>@else<span class="badge bg-secondary">nog te leveren</span><div class="text-muted">lever {{ dmy($r['delivery_date']) }}</div>@endif</td>
                    <td class="text-end fw-semibold {{ $r['dagen'] > 30 ? 'text-danger' : '' }}">{{ $r['dagen'] }}</td>
                    <td class="text-end small">{{ $r['weekhuur'] ? euro($r['weekhuur']) : '—' }}</td>
                    <td class="small">{{ $r['employee_name'] ?? ($r['created_by_code'] ? $r['created_by_code'].' (niet gekoppeld)' : 'onbekend') }}</td>
                    <td class="small"><span class="{{ empty(trim((string) $r['reference'])) ? 'text-muted fst-italic' : 'fw-semibold' }}" style="{{ empty(trim((string) $r['reference'])) ? '' : 'color:#b26a00' }}">{{ trim((string) $r['reference']) !== '' ? $r['reference'] : 'leeg' }}</span></td>
                    <td>
                        @if(\App\Services\Rollen::magNotitie())
                        <form method="post" action="{{ route('referenties.notitie', $r['contract_number']) }}" class="ref-form d-flex gap-1">
                            @csrf
                            <textarea name="opmerking" class="form-control form-control-sm" rows="1" placeholder="Bij wie opgevraagd, wat is afgesproken…">{{ $r['opmerking'] }}</textarea>
                            <select name="status" class="form-select form-select-sm" style="max-width:135px">
                                @foreach(['open'=>'Open','opgevraagd'=>'Opgevraagd','afgehandeld'=>'Afgehandeld'] as $k=>$l)<option value="{{ $k }}" @selected($r['status']===$k)>{{ $l }}</option>@endforeach
                            </select>
                            <button class="btn btn-sm btn-boels" title="Opslaan"><i class="bi bi-check-lg"></i></button>
                        </form>
                        @else
                            {{ $r['opmerking'] ?: '—' }}
                        @endif
                    </td>
                    <td><span class="badge {{ ['open'=>'bg-warning text-dark','opgevraagd'=>'bg-primary','afgehandeld'=>'bg-success'][$r['status']] ?? 'bg-secondary' }}">{{ $r['status'] }}</span>
                        @if(!empty($r['opgevraagd_op']))<div class="small text-muted">opgevraagd {{ dmy($r['opgevraagd_op']) }}</div>@endif</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-muted p-3">Geen contracten zonder referentie{{ $res === null ? ' (CORE niet bereikbaar of nog geen upload)' : ' met deze filters' }}. 👍</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($historie->isNotEmpty())
<div class="card mt-3">
    <div class="card-header small"><i class="bi bi-check2-circle text-success me-1"></i>Onlangs opgelost ({{ $historie->count() }}, laatste 30 dagen) — referentie staat er nu of het contract is weg</div>
    <div class="table-responsive"><table class="table table-sm small mb-0"><thead><tr><th>Contract</th><th>Klant</th><th>Gevolgd sinds</th><th>Opgelost op</th><th>Opmerking</th></tr></thead><tbody>
        @foreach($historie as $h)<tr><td><code>{{ $h['contract_number'] }}</code></td><td>{{ $h['customer_name'] }}</td><td>{{ dmy($h['eerst_gezien']) }}</td><td>{{ dmy($h['verdwenen_op']) }}</td><td class="text-muted">{{ $h['opmerking'] }}</td></tr>@endforeach
    </tbody></table></div>
</div>
@endif
@endsection
@push('scripts')
<script>
// Opvolging opslaan zonder de pagina te verlaten
document.querySelectorAll('.ref-form').forEach(f => f.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = f.querySelector('button'); btn.disabled = true;
    try {
        const r = await fetch(f.action, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData(f) });
        const d = await r.json();
        if (d.ok) {
            btn.classList.replace('btn-boels', 'btn-success'); setTimeout(() => btn.classList.replace('btn-success', 'btn-boels'), 1500);
            const tr = f.closest('tr'); const st = d.note?.status || f.status.value;
            const badge = tr.querySelector('td:last-child .badge'); badge.textContent = st; badge.className = 'badge ' + ({open: 'bg-warning text-dark', opgevraagd: 'bg-primary', afgehandeld: 'bg-success'}[st] || 'bg-secondary');
            if (st === 'afgehandeld') { tr.classList.remove('table-danger', 'table-warning'); tr.classList.add('table-light', 'text-muted'); }
        } else { alert('Opslaan mislukt.'); }
    } catch (err) { alert('Opslaan mislukt: ' + err); } finally { btn.disabled = false; }
}));
</script>
@endpush
