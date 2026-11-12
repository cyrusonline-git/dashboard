<?php

namespace App\Http\Controllers;

use App\Models\Taak;
use App\Services\CoreSso;
use App\Services\Rollen;
use Illuminate\Http\Request;

/**
 * Planbord: aankomende orders als kaarten in tijdkolommen (te laat · vandaag · morgen · deze
 * week · later) plus retouren/op te halen en vrije kaartjes. Alleen-lezen voor de orders:
 * toewijzen en picken gebeurt in InspHire/SAP en komt met de volgende upload mee.
 */
class PlanbordController extends Controller
{
    public function index(Request $request, CoreSso $core)
    {
        $dagen = max(1, min(30, (int) $request->input('dagen', 7)));
        $tm = now()->addDays($dagen)->toDateString();
        $soort = $request->input('soort');
        $res = $core->api('/api/planning/orders', ['tm' => $tm, 'alles' => 1], 60);
        $orders = collect($res['data'] ?? [])->filter(fn ($o) => ! $o['is_offerte'] && ($o['delivery_date'] === null || $o['delivery_date'] <= $tm))
            ->when($soort, fn ($c) => $c->where('soort', $soort));
        $ophalen = collect($core->api('/api/hire/to-collect', ['tm' => $tm], 120)['data'] ?? []);
        $vandaag = now()->toDateString();
        $morgen = now()->addDay()->toDateString();
        $eindWeek = now()->endOfWeek()->toDateString();
        $kolom = fn ($d) => $d === null ? 'later' : ($d < $vandaag ? 'te_laat' : ($d === $vandaag ? 'vandaag' : ($d === $morgen ? 'morgen' : ($d <= $eindWeek ? 'week' : 'later'))));
        $kolommen = ['te_laat' => 'Te laat / op te schonen', 'vandaag' => 'Vandaag', 'morgen' => 'Morgen', 'week' => 'Rest van de week', 'later' => 'Later'];
        $perKolom = array_fill_keys(array_keys($kolommen), collect());
        foreach ($orders->sortBy('delivery_date') as $o) {
            $perKolom[$kolom($o['delivery_date'])]->push($o);
        }

        return view('planbord.index', [
            'kolommen' => $kolommen, 'perKolom' => $perKolom, 'ophalen' => $ophalen, 'dagen' => $dagen, 'soort' => $soort,
            'taken' => Taak::whereNull('klaar_op')->orderBy('prioriteit', 'desc')->orderBy('deadline')->get(),
            'uploads' => $res['uploads'] ?? [], 'magBewerken' => Rollen::magNotitie(),
            'totaal' => ['orders' => $orders->count(), 'volledig' => $orders->where('pick_status', 'volledig')->count(), 'te_laat' => $perKolom['te_laat']->count()],
        ]);
    }

    public function taakOpslaan(Request $request)
    {
        abort_unless(Rollen::magNotitie(), 403);
        $data = $request->validate([
            'id' => ['nullable', 'integer', 'exists:taken,id'],
            'titel' => ['required', 'string', 'max:150'],
            'omschrijving' => ['nullable', 'string', 'max:2000'],
            'deadline' => ['nullable', 'date'],
            'prioriteit' => ['nullable', 'integer', 'in:0,1'],
        ]);
        $taak = ! empty($data['id']) ? Taak::findOrFail($data['id']) : new Taak(['gemaakt_door' => core_gebruiker()['name'] ?? null]);
        $taak->fill(['titel' => $data['titel'], 'omschrijving' => $data['omschrijving'] ?? null, 'deadline' => $data['deadline'] ?? null, 'prioriteit' => (int) ($data['prioriteit'] ?? 0)])->save();
        audit('planbord.taak', $taak->titel, ['id' => $taak->id]);

        return $request->expectsJson() ? response()->json(['ok' => true, 'id' => $taak->id]) : back()->with('ok', 'Kaartje opgeslagen.');
    }

    public function taakKlaar(Request $request, Taak $taak)
    {
        abort_unless(Rollen::magNotitie(), 403);
        $taak->update($taak->klaar_op ? ['klaar_op' => null, 'klaar_door' => null] : ['klaar_op' => now(), 'klaar_door' => core_gebruiker()['name'] ?? null]);

        return $request->expectsJson() ? response()->json(['ok' => true, 'klaar' => (bool) $taak->klaar_op]) : back();
    }

    public function taakVerwijderen(Taak $taak)
    {
        abort_unless(Rollen::magNotitie(), 403);
        audit('planbord.taak.verwijderd', $taak->titel, ['id' => $taak->id]);
        $taak->delete();

        return back()->with('ok', 'Kaartje verwijderd.');
    }
}
