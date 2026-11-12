@extends('layouts.app')
@section('titel', 'Kies je rol')
@section('inhoud')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header">Met welke rol wil je werken?</div>
            <div class="card-body">
                <form method="post" action="{{ route('kies-rol.opslaan') }}">
                    @csrf
                    <div class="d-grid gap-2">
                        @foreach($rollen as $slug)
                        <button class="btn btn-outline-boels btn-lg" name="rol" value="{{ $slug }}">{{ rol_naam($slug) }}</button>
                        @endforeach
                    </div>
                </form>
                <p class="text-muted small mt-3 mb-0">Je rollen worden beheerd in Boels CORE (Beheer → Gebruikers).</p>
            </div>
        </div>
    </div>
</div>
@endsection
