<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Loanable;
use App\Models\User;
use DB;

class StaticController extends RestController
{
    public function redirectToSolon() {
        return redirect('https://solon-collectif.org/locomotion/');
    }

    public function blank() {
        return response('', 204);
    }

    public function notFound() {
        return abort(404);
    }

    public function status() {
        return view('status', [
            'database' => DB::statement('SELECT 1') ? 'OK' : 'Erreur',
        ]);
    }

    public function stats() {
        return response([
            'communities' => Community::whereIn('type', ['neighborhood', 'borough'])->count(),
            'users' => User::whereRole(null)->whereSuspendedAt(null)->count(),
            'loanables' => Loanable::count(),
        ], 200);
    }

    public function app() {
        return view('app');
    }
}
