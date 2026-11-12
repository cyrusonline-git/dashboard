<?php

namespace App\Http\Controllers;

use App\Services\CoreSso;
use App\Services\Medewerkers;
use App\Services\Rollen;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /** Startpagina per rol: stand van de data, tegels en de eigen acties. */
    public function index(Request $request, CoreSso $core, Medewerkers $medewerkers)
    {
        $rol = actieve_rol();
        $uploads = $core->api('/api/sap/uploads', [], 120) ?? [];
        $acties = $core->api('/api/acties', [], 60);
        $eigenId = Rollen::eigenEmployeeId();

        $planning = $core->api('/api/planning/orders', ['tm' => now()->addDays(7)->toDateString()], 120);
        $vandaag = collect($planning['data'] ?? [])->where('delivery_date', now()->toDateString());
        $week = collect($planning['data'] ?? []);
        $opTeSchonen = $core->api('/api/planning/orders', ['verleden' => 1] + ($rol === 'binnendienst' && $eigenId ? ['employee_id' => $eigenId] : []), 120);
        $ophalen = $core->api('/api/hire/to-collect', ['tm' => now()->addDays(7)->toDateString()], 120);
        $factuurrun = Rollen::magFactuurrun($rol) ? $core->api('/api/hire/invoice-run', $rol === 'binnendienst' && $eigenId ? ['employee_id' => $eigenId] : [], 120) : null;
        $summary = Rollen::magAvi($rol) ? $core->api('/api/hire/summary', [], 300) : null;

        return view('dashboard.index', [
            'rol' => $rol,
            'uploads' => $uploads,
            'acties' => $acties,
            'vandaag' => $vandaag,
            'week' => $week,
            'opTeSchonen' => $opTeSchonen,
            'ophalen' => $ophalen,
            'factuurrun' => $factuurrun,
            'summary' => $summary,
            'teamAantal' => Rollen::magTeam($rol) ? $medewerkers->team()->count() : null,
            'coreBereikbaar' => $uploads !== [],
        ]);
    }
}
