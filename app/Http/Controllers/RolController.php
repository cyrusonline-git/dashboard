<?php

namespace App\Http\Controllers;

use App\Services\CoreSso;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function kies(Request $request)
    {
        $rollen = (array) session('rollen', []);
        if (count($rollen) === 1 && ! $request->boolean('wissel')) {
            session(['actieve_rol' => $rollen[0]]);

            return redirect()->route('dashboard');
        }

        return view('kies-rol', ['rollen' => $rollen]);
    }

    public function opslaan(Request $request)
    {
        $rol = (string) $request->input('rol');
        abort_unless(in_array($rol, (array) session('rollen', []), true), 403);
        session(['actieve_rol' => $rol]);

        return redirect()->route('dashboard');
    }

    public function geenToegang()
    {
        return view('geen-toegang');
    }

    /** Eigen sessie wissen en naar CORE uitloggen. */
    public function uitloggen(Request $request, CoreSso $sso)
    {
        $sso->vergeet();
        $request->session()->flush();

        return redirect()->away(config('core.url').'/logout');
    }
}
