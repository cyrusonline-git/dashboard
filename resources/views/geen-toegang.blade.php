@extends('layouts.app')
@section('titel', 'Geen toegang')
@section('inhoud')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-shield-lock text-boels" style="font-size:3rem"></i>
                <h1 class="h4 mt-3">Geen rol in deze app</h1>
                <p class="text-muted">Je bent ingelogd in Boels CORE, maar hebt (nog) geen rol voor {{ setting('app_titel', 'Dashboard') }}. Vraag de beheerder om je in CORE een rol te geven (Medewerker, Manager of Beheerder).</p>
                <a href="{{ config('core.url') }}" class="btn btn-boels"><i class="bi bi-grid-3x3-gap me-1"></i>Terug naar Boels CORE</a>
            </div>
        </div>
    </div>
</div>
@endsection
