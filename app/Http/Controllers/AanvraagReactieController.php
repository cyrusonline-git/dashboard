<?php

namespace App\Http\Controllers;

use App\Mail\AanvraagReactieMail;
use App\Models\Aanvraag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

/**
 * Reactie van het collega-depot via de knoppen in de mail (beveiligde, ondertekende link; geen login nodig).
 * Akkoord = alles toegezegd, Deels = per subgroep een aantal, Nee = afgewezen. De aanvrager krijgt een mail.
 */
class AanvraagReactieController extends Controller
{
    public function toon(string $token, Request $request)
    {
        $a = Aanvraag::where('token', $token)->firstOrFail();

        return view('voorraad.reactie', ['a' => $a, 'keuze' => in_array($request->input('keuze'), ['akkoord', 'deels', 'nee'], true) ? $request->input('keuze') : null]);
    }

    public function opslaan(string $token, Request $request)
    {
        $a = Aanvraag::where('token', $token)->firstOrFail();
        if (in_array($a->status, ['geannuleerd', 'ontvangen'], true)) {
            return back()->with('fout', 'Deze aanvraag is al afgesloten ('.$a->label().').');
        }
        $data = $request->validate([
            'keuze' => ['required', 'in:akkoord,deels,nee'],
            'naam' => ['required', 'string', 'max:120'],
            'opmerking' => ['nullable', 'string', 'max:2000'],
            'toegezegd' => ['nullable', 'array'],
            'toegezegd.*' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);
        $regels = collect($a->regels)->map(function ($r) use ($data) {
            $r['toegezegd'] = match ($data['keuze']) {
                'akkoord' => (int) $r['aantal'],
                'nee' => 0,
                default => min((int) $r['aantal'], (int) ($data['toegezegd'][$r['subgroep']] ?? 0)),
            };

            return $r;
        })->all();
        $totaal = array_sum(array_column($regels, 'toegezegd'));
        $status = match (true) {
            $data['keuze'] === 'nee' || $totaal === 0 => 'afgewezen',
            $totaal >= $a->aantal => 'toegezegd',
            default => 'deels',
        };
        $a->update([
            'regels' => $regels,
            'status' => $status,
            'gereageerd_door' => $data['naam'],
            'gereageerd_op' => now(),
            'reactie' => trim(($a->reactie ? $a->reactie."\n" : '').now()->format('d-m H:i').' '.$data['naam'].' ('.($a->depot_naam ?? $a->depot).'): '.Aanvraag::STATUSSEN[$status][0].($data['opmerking'] ? ' — '.$data['opmerking'] : '')),
        ]);
        audit('voorraad.aanvraag.reactie', (string) $a->id, ['status' => $status, 'door' => $data['naam']]);
        if ($a->aanvrager_email) {
            try {
                Mail::to($a->aanvrager_email)->send(new AanvraagReactieMail($a, $data['opmerking'] ?? null));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect($request->fullUrl())->with('ok', 'Bedankt, je reactie is doorgegeven aan '.($a->aanvrager_naam ?: 'de aanvrager').'.');
    }
}
