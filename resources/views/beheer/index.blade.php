@extends('layouts.app')
@section('titel', 'Beheer')
@section('inhoud')
<div class="page-header mb-3"><h1>Beheer</h1><p>Uploads van de SAP-bestanden en de koppeling van SAP-codes aan medewerkers gebeuren in CORE (menu Beheer → Uploads SAP).</p></div>
<div class="row g-3">
    <div class="col-lg-5">
        <form method="post" class="card"><div class="card-header">Instellingen</div>
            <div class="card-body">@csrf
                <div class="mb-3"><label class="form-label">Naam in de kop</label><input name="app_titel" value="{{ $app_titel }}" class="form-control" maxlength="40" required></div>
                <button class="btn btn-boels">Opslaan</button>
            </div>
        </form>
        <div class="card mt-3"><div class="card-header">Koppelingen</div><div class="list-group list-group-flush">
            <a class="list-group-item list-group-item-action" href="{{ config('core.url') }}/admin/sap-uploads" target="_blank"><i class="bi bi-cloud-upload me-2 text-boels"></i>Uploads SAP in CORE</a>
            <a class="list-group-item list-group-item-action" href="{{ config('core.url') }}/admin/sap-uploads/codes" target="_blank"><i class="bi bi-person-badge me-2 text-boels"></i>SAP-codes → medewerkers in CORE</a>
            <a class="list-group-item list-group-item-action" href="{{ config('core.url') }}/admin/applications" target="_blank"><i class="bi bi-grid me-2 text-boels"></i>Applicatie & rollen in CORE</a>
        </div></div>
    </div>
    <div class="col-lg-7">
        <div class="card"><div class="card-header">Logboek (laatste 40)</div>
            <div class="table-responsive" style="max-height:520px; overflow:auto"><table class="table table-sm small mb-0"><tbody>
                @foreach($log as $l)<tr><td class="text-muted text-nowrap">{{ $l->created_at->format('d-m H:i') }}</td><td>{{ $l->wie }}</td><td>{{ $l->actie }}</td><td>{{ $l->onderwerp }}</td></tr>@endforeach
            </tbody></table></div>
        </div>
    </div>
</div>
@endsection
