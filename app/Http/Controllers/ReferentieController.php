<?php

namespace App\Http\Controllers;

use App\Services\CoreSso;
use App\Services\Rollen;
use Illuminate\Http\Request;

/**
 * PO-bewaking: contracten en orders zonder klantreferentie/PO-nummer, gevolgd tot de referentie in Smartview
 * staat (Wim 13-11-2026: "de PO volgt" en dan raakt hij uit beeld; zonder referentie wordt de factuur niet betaald).
 */
class ReferentieController extends Controller
{
    public function index(Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magFactuurrun(), 403, 'Niet beschikbaar voor deze rol.');
        $mijn = $request->boolean('mijn');
        $f = [
            'employee_id' => $mijn ? Rollen::eigenEmployeeId() : $request->input('employee_id'),
            'status' => $request->input('status'),
            'bron' => $request->input('bron'),
            'q' => $request->input('q'),
            'alles' => $request->boolean('alles') ? 1 : null,
        ];
        $res = $core->api('/api/hire/references', $f, 60);
        $rijen = collect($res['data'] ?? []);
        if ($request->input('export') === 'csv') {
            return \App\Support\Csv::download('po-bewaking', ['Contract', 'Klant', 'Klantnr', 'Project/aflevering', 'Waar', 'Sinds', 'Dagen zonder referentie', 'Weekhuur', 'Verkoper', 'Referentie in Smartview', 'Status', 'Keer gevraagd', 'Laatst gevraagd op', 'Laatst gevraagd aan', 'Opmerking', 'Logboek'],
                $rijen->map(fn ($r) => [$r['contract_number'], $r['customer_name'], $r['customer_number'], $r['project'], $r['bron'] === 'in_huur' ? 'in huur' : 'planning', $r['sinds'] ?? $r['delivery_date'], $r['dagen'], $r['weekhuur'], $r['employee_name'] ?? $r['created_by_code'], $r['reference'], $r['status'], $r['keer_gevraagd'] ?? 0, $r['laatst_gevraagd_op'] ?? null, $r['laatst_gevraagd_aan'] ?? null, $r['opmerking'], collect($r['verzoeken'] ?? [])->map(fn ($v) => $v['op'].' aan '.($v['aan'] ?: '?').' door '.($v['door'] ?: '?').($v['opmerking'] ? ': '.$v['opmerking'] : ''))->implode(' | ')]));
        }
        $alle = $core->api('/api/hire/references', ['alles' => 1], 300);

        return view('referenties.index', [
            'f' => $f + ['mijn' => $mijn],
            'res' => $res,
            'rijen' => $rijen,
            'historie' => collect($res['historie'] ?? []),
            'verkopers' => collect($alle['data'] ?? [])->pluck('employee_name', 'employee_id')->filter()->unique()->sort(),
        ]);
    }

    public function notitie(string $nr, Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magFactuurrun() && Rollen::magNotitie(), 403);
        $data = $request->validate(['opmerking' => ['nullable', 'string', 'max:2000'], 'status' => ['nullable', 'in:open,opgevraagd,afgehandeld'], 'gevraagd_aan' => ['nullable', 'string', 'max:255'], 'gevraagd_op' => ['nullable', 'date_format:Y-m-d']]);
        $ok = $core->apiPost('/api/hire/references/'.$nr.'/note', $data);
        audit('referenties.notitie', $nr, $data);
        if ($request->expectsJson()) {
            return response()->json(['ok' => (bool) $ok, 'note' => $ok['note'] ?? null, 'verzoeken' => $ok['verzoeken'] ?? []], $ok ? 200 : 502);
        }

        return back()->with($ok ? 'ok' : 'fout', $ok ? 'Opgeslagen.' : 'Opslaan in CORE mislukt.');
    }
}
