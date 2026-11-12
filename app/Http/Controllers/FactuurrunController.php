<?php

namespace App\Http\Controllers;

use App\Services\CoreSso;
use App\Services\Rollen;
use Illuminate\Http\Request;

/** Uit factuurrun: per contract met reden/status; iedereen ziet standaard zijn eigen contracten. */
class FactuurrunController extends Controller
{
    public function index(Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magFactuurrun(), 403, 'Niet beschikbaar voor deze rol.');
        $mijn = $request->has('mijn') ? $request->boolean('mijn') : (actieve_rol() === 'binnendienst');
        $f = [
            'employee_id' => $mijn ? Rollen::eigenEmployeeId() : $request->input('employee_id'),
            'customer' => $request->input('customer'),
            'status' => $request->input('status'),
            'zonder_reden' => $request->boolean('zonder_reden') ? 1 : null,
            'nooit' => $request->boolean('nooit') ? 1 : null,
        ];
        $res = $core->api('/api/hire/invoice-run', $f, 60);
        if ($request->input('export') === 'csv') {
            return \App\Support\Csv::download('uit-factuurrun', ['Contract', 'Klant', 'Klantnr', 'Aanmaker', 'Regels', 'Start', 'Laatste factuur', 'Dagen zonder factuur', 'Weekhuur', 'Reden', 'Status', 'Referentie', 'Besteld door'],
                collect($res['data'] ?? [])->map(fn ($r) => [$r['contract_number'], $r['customer_name'], $r['customer_number'], $r['employee_name'] ?? $r['created_by_code'], $r['regels'], $r['hire_start_date'], $r['last_invoice_date'] ?? 'nooit', $r['dagen_zonder_factuur'], $r['weekhuur'], $r['reden'], $r['note_status'], $r['customer_reference'], $r['ordered_by']]));
        }
        $alle = $core->api('/api/hire/invoice-run', [], 300);

        return view('factuurrun.index', [
            'f' => $f + ['mijn' => $mijn],
            'res' => $res,
            'rijen' => collect($res['data'] ?? []),
            'aanmakers' => collect($alle['data'] ?? [])->pluck('employee_name', 'employee_id')->filter()->unique()->sort(),
        ]);
    }

    public function show(string $nr, CoreSso $core)
    {
        abort_unless(Rollen::magFactuurrun(), 403);
        $res = $core->api('/api/hire/invoice-run/'.$nr, [], 30);
        abort_unless($res, 404, 'Contract niet gevonden in de laatste factuurrun-export.');
        $wow = $core->api('/api/hire/contracts/'.$nr, [], 300);

        return view('factuurrun.show', ['res' => $res, 'wow' => $wow]);
    }

    public function notitie(string $nr, Request $request, CoreSso $core)
    {
        abort_unless(Rollen::magFactuurrun() && Rollen::magNotitie(), 403);
        $data = $request->validate(['reden' => ['nullable', 'string', 'max:2000'], 'status' => ['nullable', 'in:te_controleren,verklaard,opgelost']]);
        $ok = $core->apiPost('/api/hire/invoice-run/'.$nr.'/note', $data);
        audit('factuurrun.reden', $nr, $data);
        if ($request->expectsJson()) {
            return response()->json(['ok' => (bool) $ok, 'note' => $ok['note'] ?? null], $ok ? 200 : 502);
        }

        return back()->with($ok ? 'ok' : 'fout', $ok ? 'Reden opgeslagen.' : 'Opslaan in CORE mislukt.');
    }
}
