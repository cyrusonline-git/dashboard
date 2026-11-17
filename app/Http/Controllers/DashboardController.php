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
        $eigenId = Rollen::eigenEmployeeId();

        $planning = $core->api('/api/planning/orders', ['tm' => now()->addDays(7)->toDateString()], 120);
        $zonderTransfers = collect($planning['data'] ?? [])->reject(fn ($o) => in_array($o['soort'], ['transfer_in', 'transfer_uit'], true) || $o['is_offerte']);
        $vandaag = $zonderTransfers->where('delivery_date', now()->toDateString());
        $week = $zonderTransfers;
        $opTeSchonen = $core->api('/api/planning/orders', ['verleden' => 1] + ($rol === 'binnendienst' && $eigenId ? ['employee_id' => $eigenId] : []), 120);
        $ophalen = $core->api('/api/hire/to-collect', ['tm' => now()->addDays(7)->toDateString()], 120);
        $factuurrun = Rollen::magFactuurrun($rol) ? $core->api('/api/hire/invoice-run', $rol === 'binnendienst' && $eigenId ? ['employee_id' => $eigenId] : [], 120) : null;
        $summary = Rollen::magAvi($rol) ? $core->api('/api/hire/summary', [], 300) : null;
        $referenties = Rollen::magFactuurrun($rol) ? $core->api('/api/hire/references', [], 120) : null;

        $voorraad = collect($core->api('/api/voorraad/contracten', ['depot' => setting('voorraad_depot', '759')], 120)['data'] ?? []);
        $vloot = $core->api('/api/vloot/stand', [], 300);

        return view('dashboard.index', [
            'voorraadOpen' => $voorraad->where('is_offerte', false)->count(),
            'voorraadOffertes' => $voorraad->where('is_offerte', true)->count(),
            'aanvragenWacht' => \App\Models\Aanvraag::whereIn('status', \App\Models\Aanvraag::WACHT)->count(),
            'vloot' => $vloot,
            'rol' => $rol,
            'uploads' => $uploads,
            'vandaag' => $vandaag,
            'week' => $week,
            'opTeSchonen' => $opTeSchonen,
            'ophalen' => $ophalen,
            'factuurrun' => $factuurrun,
            'referenties' => $referenties,
            'summary' => $summary,
            'teamAantal' => Rollen::magTeam($rol) ? $medewerkers->team()->count() : null,
            'coreBereikbaar' => $uploads !== [],
        ]);
    }
}
