<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Response;
use App\Models\Community;
use App\Models\Loanable;
use App\Models\User;
use Intervention\Image\ImageManager as ImageManager;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use DB;
use Storage;

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
        $communityQuery = Community::whereIn("type", [
            "neighborhood",
            "borough",
            "private",
        ]);
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
                        "type" => $c->type,
                        "description" => $c->description,
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

    public function storage($path)
    {
        try {
            $file = Storage::disk(env("FILESYSTEM_DRIVER"))->get(
                "/storage/" . $path
            );
        } catch (FileNotFoundException $e) {
            return null;
        }
        preg_match("/.*\.(.*)/", $path, $out);
        $mimeType = str_ends_with($path, "pdf")
            ? "application/pdf"
            : "image/" . $out[1];
        return (new Response($file, 200))->header("Content-Type", $mimeType);
    }

    public function app()
    {
        return view("app");
    }
}
