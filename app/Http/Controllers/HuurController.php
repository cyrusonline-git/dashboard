<?php

namespace App\Http\Controllers;

use App\Services\CoreSso;
use App\Services\Rollen;
use Illuminate\Http\Request;

/** In huur (WOW): contracten, ophaallijst, AVI per klant, contractdetail, machine-opzoeking. */
class HuurController extends Controller
{
    public function index(Request $request, CoreSso $core)
    {
        $f = ['q' => $request->input('q'), 'customer' => $request->input('customer'), 'project' => $request->input('project'), 'status' => $request->input('status'), 'sort' => $request->input('sort', 'weekhuur'), 'page' => (int) $request->input('page', 1), 'per_page' => 100];
        $res = $core->api('/api/hire/contracts', $f, 120);

        return view('huur.index', ['f' => $f, 'res' => $res, 'rijen' => collect($res['data'] ?? []), 'magAvi' => Rollen::magAvi()]);
    }

    public function show(string $nr, CoreSso $core)
    {
        $res = $core->api('/api/hire/contracts/'.$nr, [], 120);
        abort_unless($res, 404, 'Contract niet gevonden in de laatste WOW-export.');

        return view('huur.show', ['c' => $res, 'regels' => collect($res['regels'] ?? []), 'magAvi' => Rollen::magAvi()]);
    }

    public function ophalen(Request $request, CoreSso $core)
    {
        $res = $core->api('/api/hire/to-collect', ['van' => $request->input('van'), 'tm' => $request->input('tm')], 120);
        $rijen = collect($res['data'] ?? []);

        return view('huur.ophalen', ['res' => $res, 'rijen' => $rijen, 'perDag' => $rijen->groupBy(fn ($r) => $r['coll_date'] ?? 'geen'), 'f' => $request->only(['van', 'tm'])]);
    }

    public function avi(Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magAvi(), 403, 'AVI is alleen zichtbaar voor binnendienst en managers.');
        $res = $core->api('/api/hire/summary', [], 300);

        return view('huur.avi', ['s' => $res]);
    }

    public function machine(string $nummer, CoreSso $core)
    {
        $res = $core->api('/api/hire/machines/'.$nummer, [], 120);

        return view('huur.machine', ['nummer' => $nummer, 'res' => $res, 'regels' => collect($res['data'] ?? [])]);
    }
}
