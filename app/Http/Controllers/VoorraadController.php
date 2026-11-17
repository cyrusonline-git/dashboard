<?php

namespace App\Http\Controllers;

use App\Mail\AanvraagMail;
use App\Models\Aanvraag;
use App\Services\CoreSso;
use App\Services\Rollen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Voorraadcheck: contract- of projectnummer invoeren → wat hebben we zelf, waar staat het anders, aanvragen bij
 * een collega-depot. De berekening gebeurt in CORE (/api/voorraad/contracten/{nr}); aanvragen staan hier.
 */
class VoorraadController extends Controller
{
    public function index(Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magVoorraad(), 403);
        if ($nr = preg_replace('/\D/', '', (string) $request->input('nr'))) {
            return redirect()->route('voorraad.show', $nr);
        }
        $mijn = $request->boolean('mijn');
        $depot = setting('voorraad_depot', '759');
        $res = $core->api('/api/voorraad/contracten', ['depot' => $depot, 'employee_id' => $mijn ? Rollen::eigenEmployeeId() : null], 120);
        $lijst = collect($res['data'] ?? []);
        if ($request->input('soort') === 'contract') {
            $lijst = $lijst->where('is_offerte', false);
        } elseif ($request->input('soort') === 'offerte') {
            $lijst = $lijst->where('is_offerte', true);
        }
        $aanvragen = Aanvraag::orderByDesc('id')->limit(8)->get();

        return view('voorraad.index', [
            'lijst' => $lijst->values(),
            'mijn' => $mijn,
            'depot' => $depot,
            'aanvragen' => $aanvragen,
            'openAanvragen' => Aanvraag::whereIn('status', Aanvraag::WACHT)->count(),
            'uploads' => $core->api('/api/sap/uploads', [], 120) ?? [],
        ]);
    }

    public function show(string $nr, Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magVoorraad(), 403);
        $opties = $this->opties($request);
        $check = $core->api('/api/voorraad/contracten/'.$nr, $opties, 60);
        if (! $check) {
            return redirect()->route('voorraad')->with('fout', 'CORE niet bereikbaar of nummer onbekend.');
        }
        [$aanvragen, $perSub, $perDepotSub] = $this->aanvragenVoor($nr);
        $rijen = collect($check['subgroepen'] ?? [])->map(function ($r) use ($perSub) {
            $sub = (string) $r['subgroep'];
            $r['toegezegd'] = $perSub[$sub]['toegezegd'] ?? 0;
            $r['wacht'] = $perSub[$sub]['wacht'] ?? 0;
            $r['geregeld'] = $r['uit_eigen'] + $r['toegezegd'];
            $r['nog_te_regelen'] = max(0, $r['nodig'] - $r['geregeld'] - $r['wacht']);

            return $r;
        });
        // Per depot: wat er nog aan te vragen valt (advies min wat daar al loopt/toegezegd is)
        $perDepot = collect($check['per_depot'] ?? [])->map(function ($d) use ($perDepotSub, $rijen) {
            $d['regels'] = collect($d['regels'])->map(function ($r) use ($d, $perDepotSub, $rijen) {
                $al = $perDepotSub[$d['depot']][(string) $r['subgroep']] ?? 0;
                $open = (int) ($rijen->firstWhere('subgroep', (string) $r['subgroep'])['nog_te_regelen'] ?? 0);
                $r['al_aangevraagd'] = $al;
                $r['voorstel'] = max(0, min($r['aantal'] - $al, $open));

                return $r;
            })->all();
            $d['voorstel'] = array_sum(array_column($d['regels'], 'voorstel'));
            $d['al_aangevraagd'] = array_sum(array_column($d['regels'], 'al_aangevraagd'));

            return $d;
        });

        return view('voorraad.show', [
            'nr' => $nr,
            'c' => $check,
            'rijen' => $rijen,
            'perDepot' => $perDepot,
            'aanvragen' => $aanvragen,
            'opties' => $opties,
            'totaal' => [
                'nodig' => $rijen->sum('nodig'),
                'uit_eigen' => $rijen->sum('uit_eigen'),
                'toegezegd' => $rijen->sum('toegezegd'),
                'wacht' => $rijen->sum('wacht'),
                'nog' => $rijen->sum('nog_te_regelen'),
                'tekort' => $rijen->sum(fn ($r) => max(0, min($r['tekort'], $r['nog_te_regelen']))),
            ],
        ]);
    }

    public function nieuw(string $nr, string $depot, Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magAanvragen(), 403);
        $check = $core->api('/api/voorraad/contracten/'.$nr, $this->opties($request), 60);
        abort_unless($check && ! empty($check['gevonden']), 404);
        [, , $perDepotSub] = $this->aanvragenVoor($nr);
        $info = collect($check['per_depot'] ?? [])->firstWhere('depot', $depot);
        // Alle subgroepen van de order met wat dit depot vrij heeft (ook als het advies een ander depot koos)
        $regels = collect($check['subgroepen'] ?? [])->map(function ($r) use ($depot, $info, $perDepotSub) {
            $advies = (int) ($r['advies'][$depot] ?? 0);
            $al = (int) ($perDepotSub[$depot][(string) $r['subgroep']] ?? 0);

            return [
                'subgroep' => (string) $r['subgroep'],
                'omschrijving' => $r['omschrijving'],
                'nodig' => $r['nodig'],
                'vrij' => (int) ($r['beschikbaar_per_depot'][$depot] ?? 0),
                'advies' => $advies,
                'al_aangevraagd' => $al,
                'voorstel' => max(0, $advies - $al),
            ];
        })->filter(fn ($r) => $r['vrij'] > 0 || $r['advies'] > 0)->sortByDesc('voorstel')->values();
        $depots = $check['depots'] ?? [];
        $eigen = $check['eigen_depot'];

        return view('voorraad.nieuw', [
            'nr' => $nr,
            'c' => $check,
            'depot' => $depot,
            'depotNaam' => $depots[$depot]['naam'] ?? ($info['naam'] ?? null),
            'depotEmail' => $depots[$depot]['email'] ?? null,
            'eigen' => $eigen,
            'eigenNaam' => $depots[$eigen]['naam'] ?? null,
            'eigenEmail' => $depots[$eigen]['email'] ?? null,
            'regels' => $regels,
            'verhuurdatum' => collect($check['kop'] ?? [])->pluck('verhuurdatum')->filter()->min() ?? collect($check['kop'] ?? [])->pluck('leverdatum')->filter()->min(),
        ]);
    }

    public function verstuur(string $nr, string $depot, Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magAanvragen(), 403);
        $data = $request->validate([
            'regels' => ['required', 'array'],
            'regels.*.aantal' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'regels.*.omschrijving' => ['nullable', 'string', 'max:255'],
            'regels.*.mee' => ['nullable'],
            'aan_email' => ['required', 'email', 'max:255'],
            'verhuurdatum' => ['nullable', 'date'],
            'reactie_voor' => ['nullable', 'date'],
            'opmerking' => ['nullable', 'string', 'max:2000'],
        ]);
        $regels = collect($data['regels'])->map(fn ($r, $sub) => ['subgroep' => (string) $sub, 'omschrijving' => $r['omschrijving'] ?? null, 'aantal' => (int) ($r['aantal'] ?? 0), 'toegezegd' => null])
            ->filter(fn ($r) => $r['aantal'] > 0 && ! empty($data['regels'][$r['subgroep']]['mee']))->values()->all();
        if (! $regels) {
            return back()->withInput()->with('fout', 'Vink minstens één subgroep aan met een aantal.');
        }
        $check = $core->api('/api/voorraad/contracten/'.$nr, $this->opties($request), 60) ?? [];
        $depots = $check['depots'] ?? [];
        $eigen = (string) ($check['eigen_depot'] ?? substr($nr, 0, 3));
        $gebruiker = core_gebruiker() ?? [];
        $cc = collect([$depots[$eigen]['email'] ?? null, $gebruiker['email'] ?? null, setting('voorraad_mail_cc')])
            ->flatMap(fn ($x) => preg_split('/[;,\s]+/', (string) $x))->filter(fn ($x) => filter_var($x, FILTER_VALIDATE_EMAIL))
            ->reject(fn ($x) => strcasecmp($x, $data['aan_email']) === 0)->unique()->values();

        $a = Aanvraag::create([
            'nummer' => $nr,
            'soort' => $check['soort'] ?? 'contract',
            'contracten' => $check['contracten'] ?? [$nr],
            'klant' => collect($check['kop'] ?? [])->pluck('customer_name')->filter()->unique()->implode(', ') ?: null,
            'eigen_depot' => $eigen,
            'eigen_depot_naam' => $depots[$eigen]['naam'] ?? null,
            'depot' => $depot,
            'depot_naam' => $depots[$depot]['naam'] ?? null,
            'aan_email' => $data['aan_email'],
            'cc' => $cc->implode(', ') ?: null,
            'regels' => $regels,
            'aantal' => array_sum(array_column($regels, 'aantal')),
            'verhuurdatum' => $data['verhuurdatum'] ?? null,
            'reactie_voor' => $data['reactie_voor'] ?? null,
            'opmerking' => $data['opmerking'] ?? null,
            'status' => 'verzonden',
            'token' => Str::random(40),
            'core_user_id' => $gebruiker['id'] ?? null,
            'aanvrager_naam' => $gebruiker['name'] ?? null,
            'aanvrager_email' => $gebruiker['email'] ?? null,
        ]);
        try {
            $mail = Mail::to($a->aan_email);
            if ($cc->isNotEmpty()) {
                $mail->cc($cc->all());
            }
            $mail->send(new AanvraagMail($a));
        } catch (\Throwable $e) {
            report($e);
            $a->update(['status' => 'mislukt', 'fout' => mb_substr($e->getMessage(), 0, 1000)]);
        }
        audit('voorraad.aanvraag', $nr, ['depot' => $depot, 'aantal' => $a->aantal, 'status' => $a->status]);

        return redirect()->route('voorraad.show', $nr)->with($a->status === 'mislukt' ? 'fout' : 'ok', $a->status === 'mislukt'
            ? 'Aanvraag opgeslagen, maar de mail kon niet worden verstuurd: '.$a->fout
            : 'Aanvraag verstuurd naar '.($a->depot_naam ?? 'depot '.$depot).' ('.$a->aan_email.'). Je krijgt een mail zodra zij reageren.');
    }

    private function opties(Request $request): array
    {
        return [
            'horizon' => (int) ($request->input('horizon') ?: setting('voorraad_horizon', 21)),
            'onderhoud' => $request->input('onderhoud', setting('voorraad_onderhoud', '0')) === '1' ? '1' : '0',
            'offertes' => $request->input('offertes', '1') === '0' ? '0' : '1',
        ];
    }

    /** Aanvragen voor dit nummer: lijst, per subgroep [toegezegd, wacht] en per depot per subgroep (lopend + toegezegd). */
    private function aanvragenVoor(string $nr): array
    {
        $aanvragen = Aanvraag::where('nummer', $nr)->orderByDesc('id')->get();
        $perSub = [];
        $perDepotSub = [];
        foreach ($aanvragen as $a) {
            $toeg = $a->toegezegdPerSubgroep();
            foreach ($a->aantalPerSubgroep() as $sub => $n) {
                if (in_array($a->status, Aanvraag::WACHT, true)) {
                    $perSub[$sub]['wacht'] = ($perSub[$sub]['wacht'] ?? 0) + $n;
                    $perDepotSub[$a->depot][$sub] = ($perDepotSub[$a->depot][$sub] ?? 0) + $n;
                } elseif (in_array($a->status, Aanvraag::GEREGELD, true)) {
                    $perSub[$sub]['toegezegd'] = ($perSub[$sub]['toegezegd'] ?? 0) + ($toeg[$sub] ?? 0);
                    $perDepotSub[$a->depot][$sub] = ($perDepotSub[$a->depot][$sub] ?? 0) + ($toeg[$sub] ?? 0);
                }
            }
        }

        return [$aanvragen, $perSub, $perDepotSub];
    }
}
