<?php

namespace App\Http\Controllers;

class StaticController extends Controller
{
    public function redirectToSolon() {
        return redirect('https://solon-collectif.org/locomotion/');
    }

    public function blank() {
        return response('', 204);
    }

    public function app() {
        return view('app');
    }
}
