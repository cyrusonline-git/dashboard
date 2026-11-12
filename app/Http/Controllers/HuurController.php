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
        if ($request->input('export') === 'csv') {
            $alles = $core->api('/api/hire/contracts', ['per_page' => 500] + $f, 120);
            return \App\Support\Csv::download('in-huur', ['Contract', 'Klant', 'Klantnr', 'Project', 'Aflevering', 'Adres', 'Referentie', 'Regels', 'Aantal', 'Start', 'Ophalen', 'Weekhuur', 'Afgemelde regels'],
                collect($alles['data'] ?? [])->map(fn ($r) => [$r['contract_number'], $r['customer_name'], $r['customer_number'], $r['project_description'], $r['delivery_name'], $r['delivery_address'], $r['your_reference'], $r['regels'], $r['aantal'], $r['hire_date'], $r['coll_date'], Rollen::magAvi() ? (float) $r['weekhuur'] : null, $r['to_collect_regels']]));
        }

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
        if ($request->input('export') === 'csv') {
            return \App\Support\Csv::download('ophaallijst', ['Ophaaldatum', 'Tijd', 'Contract', 'Klant', 'Aflevering', 'Adres', 'Postcode', 'Regels', 'Stuks', 'Bon geprint', 'Te laat', 'Project'],
                $rijen->map(fn ($r) => [$r['coll_date'], $r['coll_time'], $r['contract_number'], $r['customer_name'], $r['delivery_name'], $r['delivery_address'], $r['delivery_postal_code'], $r['regels'], $r['aantal'], (bool) $r['coll_doc_printed'], (bool) $r['te_laat'], $r['project_description']]));
        }

        return view('huur.ophalen', ['res' => $res, 'rijen' => $rijen, 'perDag' => $rijen->groupBy(fn ($r) => $r['coll_date'] ?? 'geen'), 'f' => $request->only(['van', 'tm'])]);
    }

    public function avi(Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magAvi(), 403, 'AVI is alleen zichtbaar voor binnendienst en managers.');
        $res = $core->api('/api/hire/summary', [], 300);
        if ($request->input('export') === 'csv') {
            return \App\Support\Csv::download('avi-per-klant', ['Klantnr', 'Klant', 'Contracten', 'Regels', 'Afgemeld', 'Weekhuur'],
                collect($res['per_klant'] ?? [])->map(fn ($k) => [$k['customer_number'], $k['customer_name'], $k['contracten'], $k['regels'], $k['to_collect'], (float) $k['weekhuur']]));
        }

        return view('huur.avi', ['s' => $res]);
    }

    public function machine(string $nummer, CoreSso $core)
    {
        $res = $core->api('/api/hire/machines/'.$nummer, [], 120);

        return view('huur.machine', ['nummer' => $nummer, 'res' => $res, 'regels' => collect($res['data'] ?? [])]);
    }
}
