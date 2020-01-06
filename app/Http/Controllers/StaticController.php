<?php

namespace App\Http\Controllers;

use DB;

class StaticController extends Controller
{
    public function redirectToSolon() {
        return redirect('https://solon-collectif.org/locomotion/');
    }

    public function blank() {
        return response('', 204);
    }

    public function status() {
        return view('status', [
            'database' => DB::statement('SELECT 1') ? 'OK' : 'Erreur',
        ]);
    }

    public function app() {
        return view('app');
    }
}
