<?php

namespace App\Http\Controllers;

use App\Services\CoreSso;
use App\Services\Rollen;
use Illuminate\Http\Request;

/**
 * Stockplanner (naar voorbeeld van de Qlik stock planner): verwachte voorraad per subgroep in de tijd,
 * met contractleveringen, offertes en retouren van lopende huur. Berekening in CORE (/api/voorraad/planner).
 */
class StockplannerController extends Controller
{
    public function index(Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magVoorraad(), 403);
        $f = $this->filters($request);
        $res = $core->api('/api/voorraad/planner', $f + ['limiet' => 400], 120) ?? [];
        $detail = $request->filled('subgroep')
            ? $core->api('/api/voorraad/planner/'.rawurlencode($request->input('subgroep')), array_intersect_key($f, array_flip(['depot', 'horizon', 'retour_dagen', 'offertes', 'onderhoud'])), 120)
            : null;
        $vloot = $core->api('/api/vloot/stand', [], 300) ?? [];
        $depots = collect($vloot['per_depot'] ?? [])->mapWithKeys(fn ($d) => [$d['depot'] => $d['depot'].($d['naam'] ? ' · '.$d['naam'] : '')])->sortKeys();

        return view('voorraad.planner', ['res' => $res, 'f' => $f, 'detail' => $detail, 'depots' => $depots]);
    }

    private function filters(Request $r): array
    {
        return [
            'depot' => (string) ($r->input('depot') ?: setting('voorraad_depot', '759')),
            'horizon' => (int) ($r->input('horizon') ?: 28),
            'per' => $r->input('per') === 'week' ? 'week' : 'dag',
            'retour_dagen' => in_array((int) $r->input('retour_dagen', 14), [0, 7, 14, 21], true) ? (int) $r->input('retour_dagen', 14) : 14,
            'offertes' => $r->input('offertes', '1') === '0' ? '0' : '1',
            'onderhoud' => $r->input('onderhoud') === '1' ? '1' : '0',
            'alleen_tekort' => $r->input('alleen_tekort', '1') === '0' ? '0' : '1',
            'alleen_mutaties' => $r->input('alleen_mutaties', '1') === '0' ? '0' : '1',
            'zonder_vloot' => $r->input('zonder_vloot') === '1' ? '1' : '0',
            'q' => trim((string) $r->input('q')) ?: null,
        ];
    }
}
