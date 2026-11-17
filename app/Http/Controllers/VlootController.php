<?php

namespace App\Http\Controllers;

use App\Services\CoreSso;
use App\Services\Rollen;
use Illuminate\Http\Request;

/** Vlootmeters: bezetting en status per depot en subgroep uit de vlootvoorraad (meerdere uploads per dag → trend). */
class VlootController extends Controller
{
    public function index(Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magVoorraad(), 403);
        $depot = $request->input('depot') ?: null;
        $dagen = (int) ($request->input('dagen') ?: 30);
        $stand = $core->api('/api/vloot/stand', ['depot' => $depot, 'dagen' => $dagen], 120) ?? [];
        $q = trim((string) $request->input('q'));
        $subgroepen = collect($core->api('/api/vloot/subgroepen', ['depot' => $depot, 'q' => $q ?: null, 'limiet' => 300], 120)['data'] ?? []);
        $sort = $request->input('sort', 'totaal');
        $subgroepen = match ($sort) {
            'bezetting' => $subgroepen->sortByDesc('bezetting'),
            'beschikbaar' => $subgroepen->sortByDesc('beschikbaar'),
            'onderhoud' => $subgroepen->sortByDesc(fn ($s) => $s['onderhoud'] + $s['reparatie']),
            default => $subgroepen,
        };
        $detail = $request->input('subgroep') ? $core->api('/api/vloot/subgroepen/'.rawurlencode($request->input('subgroep')), [], 120) : null;

        return view('vloot.index', [
            'stand' => $stand,
            'depot' => $depot,
            'dagen' => $dagen,
            'q' => $q,
            'sort' => $sort,
            'subgroepen' => $subgroepen->take(150)->values(),
            'detail' => $detail,
        ]);
    }
}
