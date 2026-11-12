<?php

namespace App\Http\Controllers;

use App\Services\CoreSso;
use App\Services\Medewerkers;
use App\Services\Rollen;

/** Acties per persoon (managers/beheer): open werk uit alle apps + SAP-signalen per teamlid. */
class TeamController extends Controller
{
    public function index(CoreSso $core, Medewerkers $medewerkers)
    {
        abort_unless(Rollen::magTeam(), 403, 'Alleen voor managers en beheerders.');
        $alle = $core->api('/api/acties/alle', [], 120);
        $perEmail = collect($alle['data'] ?? [])->keyBy(fn ($r) => mb_strtolower($r['email']));
        $team = $medewerkers->team()->map(function ($m) use ($perEmail) {
            $a = $m['email'] ? $perEmail->get($m['email']) : null;
            $m['acties'] = $a['items'] ?? [];
            $m['totaal'] = $a['totaal'] ?? 0;

            return $m;
        })->sortByDesc('totaal')->values();
        $labels = $team->flatMap(fn ($m) => collect($m['acties'])->pluck('label'))->unique()->values();

        $chart = $team->where('totaal', '>', 0)->values()->map(fn ($m) => [
            'naam' => explode(' ', $m['name'])[0],
            'acties' => collect($m['acties'])->groupBy('label')->map(fn ($g) => $g->sum('aantal')),
        ]);

        return view('team.index', ['team' => $team, 'labels' => $labels, 'chart' => $chart, 'bereikbaar' => $alle !== null]);
    }
}
