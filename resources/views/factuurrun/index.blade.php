@extends('layouts.app')
@section('titel', 'Uit factuurrun')
@section('inhoud')
<div class="page-header mb-2">
    <h1>Uit factuurrun</h1>
    <p>Contracten die nu niet gefactureerd worden. Geef per contract de reden op; die blijft staan bij elke nieuwe upload. Oranje = nog zonder reden.</p>
</div>
@include('partials.stand', ['uploads' => ['factuurrun' => ($res['upload'] ?? []) + ['label' => 'Uit factuurrun']]])

<form class="card mb-3" method="get">
    <div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
        <label class="form-check form-check-inline small mb-0"><input class="form-check-input" type="checkbox" name="mijn" value="1" @checked($f['mijn']) onchange="this.form.submit()"> alleen mijn contracten</label>
        <input type="hidden" name="mijn" value="0" disabled>
        <select name="employee_id" class="form-select form-select-sm" style="max-width:200px" onchange="this.form.mijn.checked=false; this.form.submit()">
            <option value="">Alle aanmakers</option>
            @foreach($aanmakers as $id => $naam)<option value="{{ $id }}" @selected(!$f['mijn'] && (string)$f['employee_id']===(string)$id)>{{ $naam }}</option>@endforeach
        </select>
        <input type="search" name="customer" value="{{ $f['customer'] }}" class="form-control form-control-sm" style="max-width:220px" placeholder="Klant…">
        <select name="status" class="form-select form-select-sm" style="max-width:170px">
            <option value="">Alle statussen</option>
            @foreach(['te_controleren'=>'Te controleren','verklaard'=>'Verklaard','opgelost'=>'Opgelost'] as $k=>$l)<option value="{{ $k }}" @selected($f['status']===$k)>{{ $l }}</option>@endforeach
        </select>
        <label class="form-check form-check-inline small mb-0"><input class="form-check-input" type="checkbox" name="zonder_reden" value="1" @checked($f['zonder_reden'])> zonder reden</label>
        <label class="form-check form-check-inline small mb-0"><input class="form-check-input" type="checkbox" name="nooit" value="1" @checked($f['nooit'])> nooit gefactureerd</label>
        <button class="btn btn-sm btn-boels">Filter</button>
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-sm btn-outline-secondary" title="Excel/CSV"><i class="bi bi-file-earmark-spreadsheet"></i></a>
    </div>
</form>

@php($t = $res['totaal'] ?? [])
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="card p-2 text-center"><div class="fs-4 fw-bold">{{ $t['contracten'] ?? 0 }}</div><div class="small text-muted">contracten</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-2 text-center"><div class="fs-4 fw-bold text-warning">{{ $t['zonder_reden'] ?? 0 }}</div><div class="small text-muted">zonder reden</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-2 text-center"><div class="fs-4 fw-bold">{{ $t['nooit_gefactureerd'] ?? 0 }}</div><div class="small text-muted">nooit gefactureerd</div></div></div>
    <div class="col-6 col-md-3"><div class="card p-2 text-center"><div class="fs-4 fw-bold">{{ euro($t['weekhuur'] ?? 0) }}</div><div class="small text-muted">weekhuur niet gefactureerd</div></div></div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Contract</th><th>Klant</th><th>Aanmaker</th><th class="text-end">Regels</th><th>Start</th><th>Laatste factuur</th><th class="text-end">Weekhuur</th><th style="min-width:320px">Reden uit factuurrun</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($rijen as $r)
                <tr class="{{ empty($r['reden']) ? 'table-warning' : '' }}" id="c{{ $r['contract_number'] }}">
                    <td><a href="{{ route('factuurrun.show', $r['contract_number']) }}"><code>{{ $r['contract_number'] }}</code></a></td>
                    <td>{{ $r['customer_name'] }}<div class="small text-muted">{{ $r['customer_reference'] }}</div></td>
                    <td class="small">{{ $r['employee_name'] ?? ($r['created_by_code'] ? $r['created_by_code'].' (niet gekoppeld)' : 'onbekend') }}</td>
                    <td class="text-end">{{ $r['regels'] }}</td>
                    <td class="small">{{ dmy($r['hire_start_date']) }}</td>
                    <td class="small {{ $r['nooit_gefactureerd'] ? 'text-danger fw-semibold' : '' }}">{{ $r['nooit_gefactureerd'] ? 'nooit' : dmy($r['last_invoice_date']) }}<div class="text-muted">{{ $r['dagen_zonder_factuur'] }} dagen</div></td>
                    <td class="text-end small">{{ $r['weekhuur'] ? euro($r['weekhuur']) : '—' }}</td>
                    <td>
                        @if(\App\Services\Rollen::magNotitie())
                        <form method="post" action="{{ route('factuurrun.reden', $r['contract_number']) }}" class="reden-form d-flex gap-1">
                            @csrf
                            <textarea name="reden" class="form-control form-control-sm" rows="1" placeholder="Waarom staat dit contract uit de factuurrun?">{{ $r['reden'] }}</textarea>
                            <select name="status" class="form-select form-select-sm" style="max-width:130px">
                                @foreach(['te_controleren'=>'Te controleren','verklaard'=>'Verklaard','opgelost'=>'Opgelost'] as $k=>$l)<option value="{{ $k }}" @selected($r['note_status']===$k)>{{ $l }}</option>@endforeach
                            </select>
                            <button class="btn btn-sm btn-boels" title="Opslaan"><i class="bi bi-check-lg"></i></button>
                        </form>
                        @else
                            {{ $r['reden'] ?: '—' }}
                        @endif
                    </td>
                    <td><span class="badge {{ ['te_controleren'=>'bg-warning text-dark','verklaard'=>'bg-primary','opgelost'=>'bg-success'][$r['note_status']] ?? 'bg-secondary' }}">{{ str_replace('_',' ',$r['note_status']) }}</span>
                        @if(!empty($r['note_updated_at']) && !empty($r['reden']))<div class="small text-muted">{{ \Carbon\Carbon::parse($r['note_updated_at'])->format('d-m') }}</div>@endif</td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-muted p-3">Geen contracten{{ $res === null ? ' (CORE niet bereikbaar of nog geen upload)' : ' met deze filters' }}.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
// Reden opslaan zonder de pagina te verlaten
document.querySelectorAll('.reden-form').forEach(f => f.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = f.querySelector('button'); btn.disabled = true;
    try {
        const r = await fetch(f.action, { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: new FormData(f) });
        const d = await r.json();
        const tr = f.closest('tr');
        if (d.ok) { tr.classList.toggle('table-warning', !f.reden.value.trim()); btn.classList.replace('btn-boels', 'btn-success'); setTimeout(() => btn.classList.replace('btn-success', 'btn-boels'), 1500);
            const badge = tr.querySelector('td:last-child .badge'); const st = d.note?.status || f.status.value; badge.textContent = st.replace('_',' '); badge.className = 'badge ' + ({te_controleren:'bg-warning text-dark', verklaard:'bg-primary', opgelost:'bg-success'}[st] || 'bg-secondary'); }
        else { alert('Opslaan mislukt.'); }
    } catch (err) { alert('Opslaan mislukt: ' + err); } finally { btn.disabled = false; }
}));
</script>
@endpush
