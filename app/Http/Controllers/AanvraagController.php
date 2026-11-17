<?php

namespace App\Http\Controllers;

use App\Models\Aanvraag;
use App\Services\Rollen;
use Illuminate\Http\Request;

/** Logboek van aanvragen bij collega-depots + handmatige status (ontvangen, geannuleerd, telefonisch toegezegd). */
class AanvraagController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Rollen::magVoorraad(), 403);
        $q = Aanvraag::query()->orderByDesc('id');
        if ($s = $request->input('status')) {
            $q->where('status', $s);
        }
        if ($d = $request->input('depot')) {
            $q->where('depot', $d);
        }
        if ($z = trim((string) $request->input('q'))) {
            $q->where(fn ($w) => $w->where('nummer', 'like', "%$z%")->orWhere('klant', 'like', "%$z%")->orWhere('aanvrager_naam', 'like', "%$z%")->orWhere('depot_naam', 'like', "%$z%"));
        }
        if ($request->boolean('mijn')) {
            $q->where('aanvrager_email', core_gebruiker()['email'] ?? '-');
        }

        return view('voorraad.aanvragen', [
            'aanvragen' => $q->paginate(50)->withQueryString(),
            'depots' => Aanvraag::query()->select('depot', 'depot_naam')->distinct()->orderBy('depot')->get(),
            'telling' => Aanvraag::selectRaw('status, COUNT(*) AS n')->groupBy('status')->pluck('n', 'status'),
        ]);
    }

    public function show(Aanvraag $aanvraag)
    {
        abort_unless(Rollen::magVoorraad(), 403);

        return view('voorraad.aanvraag', ['a' => $aanvraag]);
    }

    public function status(Aanvraag $aanvraag, Request $request)
    {
        abort_unless(Rollen::magAanvragen(), 403);
        $data = $request->validate(['status' => ['required', 'in:toegezegd,afgewezen,ontvangen,geannuleerd,verzonden'], 'opmerking' => ['nullable', 'string', 'max:1000']]);
        $wie = core_gebruiker()['name'] ?? 'onbekend';
        $aanvraag->update([
            'status' => $data['status'],
            'reactie' => trim(($aanvraag->reactie ? $aanvraag->reactie."\n" : '').now()->format('d-m H:i').' '.$wie.': status → '.Aanvraag::STATUSSEN[$data['status']][0].($data['opmerking'] ? ' — '.$data['opmerking'] : '')),
        ]);
        audit('voorraad.aanvraag.status', (string) $aanvraag->id, $data);

        return back()->with('ok', 'Status bijgewerkt: '.$aanvraag->label().'.');
    }
}
