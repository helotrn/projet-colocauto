<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\Community;
use App\Models\Loanable;
use App\Models\User;
use DB;

class StaticController extends Controller
{
    public function redirectToSolon()
    {
        return redirect("https://solon-collectif.org/locomotion/");
    }

    public function blank()
    {
        return response("", 204);
    }

    public function notFound()
    {
        return abort(404);
    }

    public function status()
    {
        return view("status", [
            "database" => DB::statement("SELECT 1") ? "OK" : "Erreur",
        ]);
    }

    public function stats()
    {
        $communityQuery = Community::whereIn("type", ["neighborhood"]);
        $columnsDefinition = Community::getColumnsDefinition();

        $communityQuery = $columnsDefinition["*"]($communityQuery);
        $communityQuery = $columnsDefinition["center"]($communityQuery);

        return response(
            [
                "communities" => $communityQuery->get()->map(function ($c) {
                    return [
                        "id" => $c->id,
                        "name" => $c->name,
                        "center" => $c->center,
                        "area_google" => $c->area_google,
                        "center_google" => $c->center_google,
                    ];
                }),
                // User is not super admin
                "users" => User::whereRole(null)
                    // User is not suspended
                    ->whereSuspendedAt(null)
                    // User is approved in at least one community
                    ->whereHas("approvedCommunities")
                    ->count(),
                "loanables" => Loanable::count(),
            ],
            200
        );
    }

    public function app()
    {
        return view("app");
    }
}
