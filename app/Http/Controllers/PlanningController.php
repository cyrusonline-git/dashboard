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
        ];
        $res = $core->api('/api/planning/orders', $f, 60);
        $rijen = collect($res['data'] ?? []);
        $perDag = $rijen->groupBy(fn ($r) => $r['delivery_date'] ?? 'geen');

        return view('planning.index', [
            'f' => $f,
            'res' => $res,
            'rijen' => $rijen,
            'perDag' => $perDag,
            'verkopers' => $rijen->pluck('employee_name', 'employee_id')->filter()->unique()->sort(),
            'expeditie' => $medewerkers->alle()->filter(fn ($m) => str_contains(mb_strtolower((string) ($m['function'] ?? '')), 'expedit') || str_contains(mb_strtolower((string) ($m['function'] ?? '')), 'magazijn'))->values(),
        ]);
    }

    public function show(string $nr, CoreSso $core)
    {
        $res = $core->api('/api/planning/orders/'.$nr, [], 30);
        abort_unless($res, 404, 'Order niet gevonden in de laatste planning-exports.');

        return view('planning.show', ['order' => $res['order'], 'regels' => collect($res['regels'] ?? []), 'medewerkers' => app(Medewerkers::class)->alle()]);
    }

    public function notitie(string $nr, Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magNotitie(), 403);
        $data = $request->validate(['opmerking' => ['nullable', 'string', 'max:2000'], 'expeditie_employee_id' => ['nullable', 'integer'], 'vlag' => ['nullable', 'boolean']]);
        $data['vlag'] = $request->boolean('vlag');
        $ok = $core->apiPost('/api/planning/orders/'.$nr.'/note', $data);
        audit('planning.notitie', $nr, $data);

        return back()->with($ok ? 'ok' : 'fout', $ok ? 'Opgeslagen.' : 'Opslaan in CORE mislukt.');
    }
}
