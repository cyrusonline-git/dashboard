<?php

namespace App\Http\Controllers;

use App\Services\CoreSso;
use App\Services\Rollen;

/** Managementoverzicht (area-manager, bu-manager, admin): alles uit /api/overzicht van CORE. */
class OverzichtController extends Controller
{
    public function index(CoreSso $core)
    {
        abort_unless(Rollen::magTeam(), 403, 'Alleen voor managers en beheerders.');
        $o = $core->api('/api/overzicht', [], 120);

        return view('overzicht.index', ['o' => $o, 'kpi' => $o['kpi'] ?? null, 'apps' => $o['apps'] ?? [], 'team' => $o['team'] ?? [], 'uploads' => $o['uploads'] ?? []]);
    }
}
