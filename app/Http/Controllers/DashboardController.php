<?php

namespace App\Http\Controllers;

use App\Services\Medewerkers;

class DashboardController extends Controller
{
    /** Startpagina. Inhoud volgt zodra is bepaald wat het dashboard moet tonen. */
    public function index(Medewerkers $medewerkers)
    {
        $kaart = session('eigen_kaart');
        $teamAantal = in_array(actieve_rol(), ['manager', 'admin'], true) ? $medewerkers->team()->count() : null;

        return view('dashboard.index', compact('kaart', 'teamAantal'));
    }
}
