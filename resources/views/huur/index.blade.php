@extends('layouts.app')
@section('titel', 'Contracten in huur')
@section('inhoud')
<div class="page-header mb-2"><h1>Contracten in huur</h1><p>Alles wat volgens de laatste WOW-export bij klanten staat (depot 759).</p></div>
@include('partials.stand', ['uploads' => ['wow' => ($res['upload'] ?? []) + ['label' => 'In huur (WOW)']]])
<form class="card mb-3" method="get"><div class="card-body py-2 d-flex flex-wrap gap-2 align-items-center">
    <input type="search" name="q" value="{{ $f['q'] }}" class="form-control form-control-sm" style="max-width:280px" placeholder="Klant, contract, project, artikel, referentie…">
    <select name="status" class="form-select form-select-sm" style="max-width:200px"><option value="">Alle statussen</option><option value="Delivered" @selected($f['status']==='Delivered')>In huur</option><option value="To Collect" @selected($f['status']==='To Collect')>Afgemeld (op te halen)</option></select>
    <select name="sort" class="form-select form-select-sm" style="max-width:170px">@foreach(['weekhuur'=>'Weekhuur ↓','hire_date'=>'Startdatum ↓','customer_name'=>'Klant','coll_date'=>'Ophaaldatum'] as $k=>$l)<option value="{{ $k }}" @selected($f['sort']===$k)>{{ $l }}</option>@endforeach</select>
    <button class="btn btn-sm btn-boels">Zoek</button>
    <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-sm btn-outline-secondary" title="Excel/CSV"><i class="bi bi-file-earmark-spreadsheet"></i></a>
    <span class="ms-auto small text-muted">{{ $res['total'] ?? 0 }} contracten</span>
</div></form>
<div class="card"><div class="table-responsive"><table class="table table-sm align-middle mb-0">
    <thead><tr><th>Contract</th><th>Klant</th><th>Project / aflevering</th><th>Referentie</th><th class="text-end">Regels</th><th>Start</th><th>Ophalen</th>@if($magAvi)<th class="text-end">Weekhuur</th>@endif</tr></thead>
    <tbody>
    @forelse($rijen as $r)
        <tr class="{{ $r['to_collect_regels'] > 0 ? 'table-danger' : '' }}">
            <td><a href="{{ route('huur.show', $r['contract_number']) }}"><code>{{ $r['contract_number'] }}</code></a></td>
            <td>{{ $r['customer_name'] }}</td>
            <td class="small">{{ $r['project_description'] ?? $r['delivery_name'] }}<div class="text-muted">{{ $r['delivery_address'] }}</div></td>
            <td class="small text-muted">{{ $r['your_reference'] }}</td>
            <td class="text-end">{{ $r['regels'] }}@if($r['to_collect_regels'] > 0)<div class="small text-danger">{{ $r['to_collect_regels'] }} afgemeld</div>@endif</td>
            <td class="small">{{ dmy($r['hire_date']) }}</td>
            <td class="small">{{ $r['coll_date'] ? dmy($r['coll_date']) : '—' }}</td>
            @if($magAvi)<td class="text-end">{{ euro($r['weekhuur']) }}</td>@endif
        </tr>
    @empty
        <tr><td colspan="8" class="text-muted p-3">Geen contracten{{ $res === null ? ' (CORE niet bereikbaar of nog geen upload)' : '' }}.</td></tr>
    @endforelse
    </tbody></table></div>
    @if(($res['total'] ?? 0) > ($res['per_page'] ?? 100))
    <div class="card-body py-2 small">Pagina {{ $res['page'] }} van {{ ceil($res['total'] / $res['per_page']) }} ·
        @if($res['page'] > 1)<a href="{{ request()->fullUrlWithQuery(['page' => $res['page'] - 1]) }}">vorige</a> @endif
        @if($res['page'] * $res['per_page'] < $res['total'])<a href="{{ request()->fullUrlWithQuery(['page' => $res['page'] + 1]) }}">volgende</a>@endif</div>
    @endif
</div>
@endsection
