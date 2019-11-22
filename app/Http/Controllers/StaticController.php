<?php

namespace App\Http\Controllers;

class StaticController extends Controller
{
    public function redirectToSolon() {
        return redirect('https://solon-collectif.org/locomotion/');
    }
}
