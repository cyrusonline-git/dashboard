<?php

namespace App\Http\Controllers;

use App\Services\CoreSso;
use App\Services\Medewerkers;
use App\Services\Rollen;
use Illuminate\Http\Request;

/** Planning (8.7 + 2.6 uit CORE): per leverdag, tabs per soort, "op te schonen" en orderdetail. */
class PlanningController extends Controller
{
    public function index(Request $request, CoreSso $core, Medewerkers $medewerkers)
    {
        $f = [
            'soort' => $request->input('soort'),
            'q' => $request->input('q'),
            'verleden' => $request->boolean('verleden') ? 1 : null,
            'alles' => $request->boolean('alles') ? 1 : null,
            'employee_id' => $request->boolean('mijn') ? Rollen::eigenEmployeeId() : $request->input('employee_id'),
            'van' => $request->input('van'),
            'tm' => $request->input('tm'),
            'bijhuur' => $request->boolean('bijhuur') ? 1 : null,
            'niet_op_scanner' => $request->boolean('niet_op_scanner') ? 1 : null,
            'referentie_ontbreekt' => $request->boolean('referentie_ontbreekt') ? 1 : null,
        ];
        $res = $core->api('/api/planning/orders', $f, 60);
        $alle = collect($res['data'] ?? []);
        $isTransfer = fn ($o) => in_array($o['soort'], ['transfer_in', 'transfer_uit'], true);
        // Transfers apart van de echte planning (Wim); bij een expliciet transfer-filter staan ze in de hoofdlijst
        $rijen = in_array($f['soort'], ['transfer_in', 'transfer_uit'], true) ? $alle : $alle->reject($isTransfer);
        $transfers = in_array($f['soort'], ['transfer_in', 'transfer_uit'], true) ? collect() : $alle->filter($isTransfer);
        if ($request->input('export') === 'csv') {
            return \App\Support\Csv::download($f['verleden'] ? 'op-te-schonen' : 'planning', ['Leverdatum', 'Order', 'Soort', 'Klant', 'Status Smartview', 'Gepickt', 'Totaal', 'Pickstand', 'Verkoper', 'Referentie', 'Waarde', 'Regels', 'Aflevering', 'Plaats', 'Opmerking'],
                $rijen->merge($transfers)->sortBy('delivery_date')->map(fn ($o) => [$o['delivery_date'], $o['order_number'], $o['soort'], $o['customer_name'], $o['contract_status'], $o['picked'], $o['total'], $o['pick_status'], $o['employee_name'] ?? $o['sales_person'], $o['reference'], $o['goods_value'], $o['regels'], $o['delivery_name'], $o['city'], $o['note']['opmerking'] ?? null]));
        }
        $perDag = $rijen->groupBy(fn ($r) => $r['delivery_date'] ?? 'geen');

        return view('planning.index', [
            'f' => $f,
            'res' => $res,
            'rijen' => $rijen,
            'perDag' => $perDag,
            'transfersPerDag' => $transfers->groupBy(fn ($r) => $r['delivery_date'] ?? 'geen'),
            'verkopers' => $rijen->pluck('employee_name', 'employee_id')->filter()->unique()->sort(),
            // 8.7 PickUpInfo leeg = 'profiel niet ingevuld': expeditie ziet de order niet in InspHire Mobile (Wim 13-11-2026)
            'nietOpScanner' => (int) ($res['totaal']['niet_op_scanner'] ?? $rijen->where('niet_op_scanner', true)->count()),
            // 8.7 REF leeg of 'volgt' = klantreferentie/PO ontbreekt → factuur wordt niet betaald
            'refOntbreekt' => (int) ($res['totaal']['referentie_ontbreekt'] ?? $rijen->where('referentie_ontbreekt', true)->count()),
        ]);
    }

    public function show(string $nr, CoreSso $core)
    {
        $res = $core->api('/api/planning/orders/'.$nr, [], 30);
        abort_unless($res, 404, 'Order niet gevonden in de laatste planning-exports.');

        return view('planning.show', ['order' => $res['order'], 'regels' => collect($res['regels'] ?? [])]);
    }

    public function notitie(string $nr, Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magNotitie(), 403);
        $data = $request->validate(['opmerking' => ['nullable', 'string', 'max:2000'], 'vlag' => ['nullable', 'boolean']]);
        $data['vlag'] = $request->boolean('vlag');
        $ok = $core->apiPost('/api/planning/orders/'.$nr.'/note', $data);
        audit('planning.notitie', $nr, $data);

        return back()->with($ok ? 'ok' : 'fout', $ok ? 'Opgeslagen.' : 'Opslaan in CORE mislukt.');
    }
}
