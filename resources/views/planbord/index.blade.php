@extends('layouts.app')
@section('titel', 'Planbord')
@section('inhoud')
<div class="page-header mb-2 d-flex justify-content-between align-items-end flex-wrap gap-2">
    <div><h1>Planbord</h1><p>Aankomende orders op tijd. Pickstand en toewijzing komen uit InspHire/SAP en veranderen met de volgende upload; kaartjes zijn eigen notities voor het team.</p></div>
    <form method="get" class="d-flex gap-2 align-items-center small">
        <select name="soort" class="form-select form-select-sm" onchange="this.form.submit()"><option value="">Alle soorten</option>@foreach(['huur'=>'Huurorders','verkoop'=>'Verkooporders','transfer_in'=>'Transfers in','transfer_uit'=>'Transfers uit','retour'=>'Retouren'] as $k=>$l)<option value="{{ $k }}" @selected($soort===$k)>{{ $l }}</option>@endforeach</select>
        tot <select name="dagen" class="form-select form-select-sm" onchange="this.form.submit()">@foreach([1=>'morgen',3=>'3 dagen',7=>'7 dagen',14=>'14 dagen',30=>'30 dagen'] as $d=>$l)<option value="{{ $d }}" @selected($dagen===$d)>{{ $l }}</option>@endforeach</select>
        @if($magBewerken)<button type="button" class="btn btn-boels btn-sm text-nowrap" id="nieuwKaartje"><i class="bi bi-plus-lg"></i> Kaartje</button>@endif
    </form>
</div>
@include('partials.stand', ['uploads' => $uploads, 'soorten' => ['planning', 'reserveringen']])
<div class="small text-muted mb-2">{{ $totaal['orders'] }} orders · {{ $totaal['volledig'] }} volledig gepickt · <span class="{{ $totaal['te_laat'] ? 'text-danger fw-semibold' : '' }}">{{ $totaal['te_laat'] }} te laat</span></div>

<div class="bord">
    @if($taken->isNotEmpty() || $magBewerken)
    <div class="kolom" style="background:#e4efe5">
        <div class="kolom-kop"><span><i class="bi bi-sticky me-1"></i>Kaartjes</span><span class="badge bg-success">{{ $taken->count() }}</span></div>
        @foreach($taken as $t)@include('planbord.taak', ['t' => $t])@endforeach
        @if($taken->isEmpty())<div class="small text-muted px-1">Geen open kaartjes.</div>@endif
    </div>
    @endif
    @foreach($kolommen as $k => $label)
    <div class="kolom {{ $k === 'te_laat' ? 'border border-danger' : '' }}">
        <div class="kolom-kop"><span>{{ $label }}</span><span class="badge {{ $k === 'te_laat' ? 'bg-danger' : 'bg-boels' }}">{{ $perKolom[$k]->count() }}</span></div>
        @foreach($perKolom[$k]->take($k === 'te_laat' ? 40 : 200) as $o)@include('planbord.kaart', ['o' => $o])@endforeach
        @if($k === 'te_laat' && $perKolom[$k]->count() > 40)<a href="{{ route('planning', ['verleden' => 1]) }}" class="small">… alle {{ $perKolom[$k]->count() }} in "Op te schonen"</a>@endif
    </div>
    @endforeach
    <div class="kolom">
        <div class="kolom-kop"><span><i class="bi bi-arrow-return-left me-1"></i>Retouren / op te halen</span><span class="badge bg-danger">{{ $ophalen->count() }}</span></div>
        @foreach($ophalen as $r)
        <div class="kaart {{ $r['te_laat'] ? 'te-laat' : '' }}" style="cursor:default">
            <div class="kaart-kop" style="background:#c62828"><span>Retour</span>@if($r['coll_doc_printed'])<span title="ophaalbon geprint"><i class="bi bi-printer"></i></span>@endif</div>
            <div class="kaart-body"><a href="{{ route('huur.show', $r['contract_number']) }}" class="kaart-titel text-reset text-decoration-none"><code class="text-boels">{{ $r['contract_number'] }}</code> {{ $r['customer_name'] }}</a>
                <div class="small text-muted">{{ $r['coll_date'] ? \Carbon\Carbon::parse($r['coll_date'])->translatedFormat('D j M') : 'geen datum' }} {{ $r['coll_time'] }} · <span class="{{ $r['te_laat'] ? 'text-rood' : '' }}">{{ relatief($r['coll_date']) }}</span></div>
                <div class="small">{{ $r['regels'] }} regels · {{ $r['delivery_name'] }}</div></div>
        </div>
        @endforeach
    </div>
</div>

@if($magBewerken)
<div class="modal fade" id="taakModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="post" action="{{ route('planbord.taak') }}">
    @csrf <input type="hidden" name="id" id="taakId">
    <div class="modal-header"><h5 class="modal-title">Kaartje</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-2"><label class="form-label small mb-1">Titel</label><input name="titel" id="taakTitel" class="form-control" required maxlength="150" placeholder="bijv. Takels aftesten die klaarstaan"></div>
        <div class="mb-2"><label class="form-label small mb-1">Omschrijving</label><textarea name="omschrijving" id="taakOms" class="form-control" rows="2"></textarea></div>
        <div class="row g-2">
            <div class="col-6"><label class="form-label small mb-1">Deadline</label><input type="date" name="deadline" id="taakDeadline" class="form-control"></div>
            <div class="col-6"><label class="form-label small mb-1">Prioriteit</label><select name="prioriteit" id="taakPrio" class="form-select"><option value="0">Normaal</option><option value="1">Hoog</option></select></div>
        </div>
    </div>
    <div class="modal-footer justify-content-between"><button type="button" class="btn btn-outline-danger btn-sm d-none" id="taakVerwijder">Verwijderen</button><div><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuleren</button> <button class="btn btn-boels btn-sm">Opslaan</button></div></div>
</form></div></div>
<form method="post" id="verwijderForm" class="d-none">@csrf @method('DELETE')</form>
@endif
@endsection
@push('scripts')
@if($magBewerken)
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
const modal = new bootstrap.Modal('#taakModal');
const vul = t => { taakId.value = t.id || ''; taakTitel.value = t.titel || ''; taakOms.value = t.oms || ''; taakDeadline.value = t.deadline || ''; taakPrio.value = t.prio || 0; document.getElementById('taakVerwijder').classList.toggle('d-none', !t.id); };
document.getElementById('nieuwKaartje').addEventListener('click', () => { vul({}); modal.show(); });
document.querySelectorAll('.taak-bewerk').forEach(a => a.addEventListener('click', e => { e.preventDefault(); vul(a.dataset); modal.show(); }));
document.querySelectorAll('.taak-klaar').forEach(a => a.addEventListener('click', async e => { e.preventDefault(); const r = await fetch(a.dataset.url, { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } }).then(r => r.json()); if (r.ok) a.closest('.kaart').remove(); }));
document.getElementById('taakVerwijder').addEventListener('click', () => { if (!confirm('Kaartje verwijderen?')) return; const f = document.getElementById('verwijderForm'); f.action = `{{ url('/planbord/taak') }}/${taakId.value}`; f.submit(); });
</script>
@endif
@endpush
