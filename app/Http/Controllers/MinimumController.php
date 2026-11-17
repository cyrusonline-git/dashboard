<?php

namespace App\Http\Controllers;

use App\Models\MinVoorraad;
use App\Services\CoreSso;
use App\Services\Rollen;
use Illuminate\Http\Request;

/**
 * Minimale voorraad per depot en subgroep + werkplaatslijst (zoals de oude Voorraad tool):
 * tekort = minimum − beschikbaar → na te kijken machines (eerst in onderhoud, dan in reparatie),
 * plus "nakijken voor aankomende orders": open vraag binnen de horizon die het eigen depot niet uit beschikbaar haalt.
 */
class MinimumController extends Controller
{
    public function werkplaats(Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magVoorraad(), 403);
        $depot = (string) ($request->input('depot') ?: setting('voorraad_depot', '759'));
        $horizon = (int) ($request->input('horizon') ?: setting('voorraad_orders_horizon', 14));
        $minima = MinVoorraad::where('depot', $depot)->orderBy('subgroep')->get();
        $vraag = collect($core->api('/api/voorraad/vraag', ['depot' => $depot, 'horizon' => $horizon], 120)['data'] ?? []);
        $groepen = $minima->pluck('subgroep')->merge($vraag->pluck('subgroep'))->unique()->values();
        $stand = $groepen->isEmpty() ? collect() : collect($core->api('/api/vloot/subgroepen', ['depot' => $depot, 'groepen' => $groepen->implode(',')], 120)['data'] ?? [])->keyBy('subgroep');

        $tekorten = $minima->map(function ($m) use ($stand) {
            $s = $stand[$m->subgroep] ?? [];

            return ['subgroep' => $m->subgroep, 'omschrijving' => $s['omschrijving'] ?? $m->omschrijving, 'minimum' => $m->minimum, 'beschikbaar' => $s['beschikbaar'] ?? 0,
                'onderhoud' => $s['onderhoud'] ?? 0, 'reparatie' => $s['reparatie'] ?? 0, 'verhuurd' => $s['verhuurd'] ?? 0, 'tekort' => max(0, $m->minimum - ($s['beschikbaar'] ?? 0))];
        });
        $orders = $vraag->map(function ($v) use ($stand) {
            $s = $stand[$v['subgroep']] ?? [];
            $v['beschikbaar'] = $s['beschikbaar'] ?? 0;
            $v['onderhoud'] = $s['onderhoud'] ?? 0;
            $v['reparatie'] = $s['reparatie'] ?? 0;
            $v['tekort'] = max(0, $v['aantal'] - $v['beschikbaar']);

            return $v;
        })->filter(fn ($v) => $v['tekort'] > 0 && ($v['onderhoud'] + $v['reparatie']) > 0)->values();

        // Na te kijken machines: per subgroep zoveel als het grootste tekort (minimum of order), onderhoud eerst
        $nodig = [];
        foreach ($tekorten as $t) {
            if ($t['tekort'] > 0) {
                $nodig[$t['subgroep']] = max($nodig[$t['subgroep']] ?? 0, $t['tekort']);
            }
        }
        foreach ($orders as $o) {
            $nodig[$o['subgroep']] = max($nodig[$o['subgroep']] ?? 0, $o['tekort']);
        }
        $machines = $nodig ? ($core->api('/api/vloot/machines', ['depot' => $depot, 'groepen' => implode(',', array_keys($nodig))], 120)['data'] ?? []) : [];
        $naTeKijken = collect($machines)->map(fn ($ms, $g) => array_slice($ms, 0, $nodig[$g] ?? 0))->all();

        return view('voorraad.werkplaats', [
            'depot' => $depot,
            'horizon' => $horizon,
            'depots' => $this->depots($core),
            'tekorten' => $request->boolean('alles') ? $tekorten : $tekorten->where('tekort', '>', 0)->values(),
            'aantalMinima' => $minima->count(),
            'orders' => $orders,
            'naTeKijken' => $naTeKijken,
            'alles' => $request->boolean('alles'),
        ]);
    }

    public function index(Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magVoorraad(), 403);
        $depot = (string) ($request->input('depot') ?: setting('voorraad_depot', '759'));
        $minima = MinVoorraad::where('depot', $depot)->orderBy('subgroep')->get();
        $stand = $minima->isEmpty() ? collect() : collect($core->api('/api/vloot/subgroepen', ['depot' => $depot, 'groepen' => $minima->pluck('subgroep')->implode(',')], 120)['data'] ?? [])->keyBy('subgroep');

        return view('voorraad.minimum', [
            'depot' => $depot,
            'depots' => $this->depots($core),
            'minima' => $minima,
            'stand' => $stand,
            'mag' => Rollen::magMinimum(),
            'andereDepots' => MinVoorraad::where('depot', '!=', $depot)->selectRaw('depot, COUNT(*) AS n')->groupBy('depot')->pluck('n', 'depot'),
        ]);
    }

    public function opslaan(Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magMinimum(), 403);
        $data = $request->validate(['depot' => ['required', 'string', 'max:10'], 'subgroep' => ['required', 'string', 'max:20'], 'minimum' => ['required', 'integer', 'min:0', 'max:99999'], 'omschrijving' => ['nullable', 'string', 'max:255']]);
        $sub = trim($data['subgroep']);
        $oms = $data['omschrijving'] ?? ($core->api('/api/vloot/subgroepen/'.rawurlencode($sub), [], 600)['omschrijving'] ?? null);
        if ((int) $data['minimum'] === 0) {
            MinVoorraad::where('depot', $data['depot'])->where('subgroep', $sub)->delete();
        } else {
            MinVoorraad::updateOrCreate(['depot' => $data['depot'], 'subgroep' => $sub], ['minimum' => $data['minimum'], 'omschrijving' => $oms, 'gewijzigd_door' => core_gebruiker()['name'] ?? null]);
        }
        audit('voorraad.minimum', $data['depot'].'/'.$sub, ['minimum' => $data['minimum']]);

        return redirect()->route('voorraad.minimum', ['depot' => $data['depot']])->with('ok', (int) $data['minimum'] === 0 ? "Minimum voor $sub verwijderd." : "Minimum voor $sub opgeslagen: {$data['minimum']}.");
    }

    public function kopieer(Request $request)
    {
        abort_unless(Rollen::heeft(['vlootmanager', 'area_manager', 'bu_manager', 'admin']), 403);
        $data = $request->validate(['depot' => ['required', 'string', 'max:10'], 'van' => ['required', 'string', 'max:10', 'different:depot']]);
        $n = 0;
        foreach (MinVoorraad::where('depot', $data['van'])->get() as $m) {
            MinVoorraad::updateOrCreate(['depot' => $data['depot'], 'subgroep' => $m->subgroep], ['minimum' => $m->minimum, 'omschrijving' => $m->omschrijving, 'gewijzigd_door' => core_gebruiker()['name'] ?? null]);
            $n++;
        }
        audit('voorraad.minimum.kopie', $data['depot'], $data + ['aantal' => $n]);

        return redirect()->route('voorraad.minimum', ['depot' => $data['depot']])->with('ok', "$n minima gekopieerd van depot {$data['van']}.");
    }

    /** Depots uit de vlootvoorraad, met naam uit CORE. */
    private function depots(CoreSso $core): array
    {
        $stand = $core->api('/api/vloot/stand', [], 300) ?? [];

        return collect($stand['per_depot'] ?? [])->mapWithKeys(fn ($d) => [$d['depot'] => $d['naam'] ? $d['depot'].' · '.$d['naam'] : 'Depot '.$d['depot']])->sortKeys()->all();
    }
}
