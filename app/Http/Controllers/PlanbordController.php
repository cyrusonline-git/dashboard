<?php

namespace App\Http\Controllers;

use App\Services\CoreSso;
use Illuminate\Http\Request;

/**
 * Planbord: de echte planning als kaarten in tijdkolommen (vandaag · morgen · rest van de week ·
 * later) + retouren/op te halen; transfers in een eigen blok eronder. Geen oude orders (die staan
 * onder Planning → Op te schonen). Alleen-lezen: toewijzen en picken gebeurt in InspHire/SAP.
 */
class PlanbordController extends Controller
{
    public function index(Request $request, CoreSso $core)
    {
        $dagen = max(1, min(30, (int) $request->input('dagen', 7)));
        $tm = now()->addDays($dagen)->toDateString();
        $res = $core->api('/api/planning/orders', ['tm' => $tm], 60); // standaard: vandaag en later
        $alle = collect($res['data'] ?? [])->filter(fn ($o) => ! $o['is_offerte'] && ! $o['te_laat']);
        $orders = $alle->reject(fn ($o) => in_array($o['soort'], ['transfer_in', 'transfer_uit'], true));
        $transfers = $alle->filter(fn ($o) => in_array($o['soort'], ['transfer_in', 'transfer_uit'], true));
        $ophalen = collect($core->api('/api/hire/to-collect', ['van' => now()->toDateString(), 'tm' => $tm], 120)['data'] ?? []);

        $vandaag = now()->toDateString();
        $morgen = now()->addDay()->toDateString();
        $eindWeek = now()->endOfWeek()->toDateString();
        $kolom = fn ($d) => $d === null ? 'later' : ($d === $vandaag ? 'vandaag' : ($d === $morgen ? 'morgen' : ($d <= $eindWeek ? 'week' : 'later')));
        $kolommen = ['vandaag' => 'Vandaag', 'morgen' => 'Morgen', 'week' => 'Rest van de week', 'later' => 'Later'];
        $verdeel = function ($lijst) use ($kolommen, $kolom) {
            $per = [];
            foreach (array_keys($kolommen) as $k) {
                $per[$k] = collect(); // per kolom een EIGEN collectie (array_fill_keys deelt één object)
            }
            foreach ($lijst->sortBy('delivery_date') as $o) {
                $per[$kolom($o['delivery_date'])]->push($o);
            }

            return $per;
        };

        return view('planbord.index', [
            'kolommen' => $kolommen, 'perKolom' => $verdeel($orders), 'perKolomTransfers' => $verdeel($transfers), 'ophalen' => $ophalen,
            'dagen' => $dagen, 'uploads' => $res['uploads'] ?? [],
            'totaal' => ['orders' => $orders->count(), 'volledig' => $orders->where('pick_status', 'volledig')->count(), 'transfers' => $transfers->count()],
        ]);
    }
}
