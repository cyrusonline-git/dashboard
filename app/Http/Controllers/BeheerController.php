<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;

/** Beheer (rol admin): naam in de kop en logboek. Uploads en Smartview-codes gebeuren in CORE. */
class BeheerController extends Controller
{
    public function index()
    {
        return view('beheer.index', ['app_titel' => Setting::get('app_titel', 'Dashboard'), 'log' => AuditLog::orderByDesc('id')->limit(40)->get()]);
    }

    public function opslaan(Request $request)
    {
        $data = $request->validate(['app_titel' => ['required', 'string', 'max:40']]);
        Setting::set('app_titel', $data['app_titel']);
        audit('beheer.instellingen', null, $data);

        return back()->with('ok', 'Instellingen opgeslagen.');
    }
}
