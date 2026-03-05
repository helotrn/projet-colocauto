<?php

namespace App\Http\Controllers;

use App\Http\Requests\Padlock\CreateRequest;
use App\Http\Requests\Padlock\IndexRequest;
use App\Http\Requests\Padlock\RestoreRequest;
use App\Http\Requests\Padlock\RetrieveRequest;
use App\Http\Requests\Padlock\UpdateRequest;
use App\Http\Requests\BaseRequest as Request;
use Illuminate\Http\Request as HttpRequest;
use App\Models\Padlock;
use App\Models\Loan;
use App\Repositories\PadlockRepository;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon; // Indispensable pour l'heure

class PadlockController extends RestController
{
    public function __construct(PadlockRepository $repository, Padlock $model)
    {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function index(IndexRequest $request)
    {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        switch ($request->headers->get("accept")) {
            case "text/csv":
                $filename = $this->respondWithCsv(
                    $request,
                    $items,
                    $this->model
                );
                return response(
                    env("BACKEND_URL_FROM_BROWSER") . $filename,
                    201
                );
            default:
                return $this->respondWithCollection($request, $items, $total);
        }
    }

    public function retrieve(RetrieveRequest $request, $id)
    {
        try {
            $item = $this->repo->find($request, $id);
            return $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $item = parent::validateAndUpdate($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithItem($request, $item);
    }

    public function restore(RestoreRequest $request, $id)
    {
        try {
            $response = parent::validateAndRestore($request, $id);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }

    public function template(Request $request)
    {
        $template = [
            "item" => [
                "name" => "",
                "mac_address" => "",
                "external_id" => "",
                "loanable_id" => null,
            ],
            "form" => [
                "name" => [
                    "type" => "text",
                ],
                "mac_address" => [
                    "type" => "text",
                ],
                "external_id" => [
                    "type" => "text",
                ],
                "loanable_id" => [
                    "type" => "relation",
                    "query" => [
                        "slug" => "loanables",
                        "value" => "id",
                        "text" => "name",
                        "params" => [
                            "fields" => "id,name",
                            "!type" => "car",
                        ],
                    ],
                ],
            ],
            "filters" => $this->model::$filterTypes,
        ];

        $modelRules = $this->model->getRules("template", $request->user());
        foreach ($modelRules as $field => $rules) {
            $template["form"][$field]["rules"] = $this->formatRules($rules);
        }

        return $template;
    }

    // -------------------------------------------------------------------------
    // --- VERIFICATION ARDUINO CORRIGÉE (BONS NOMS DE COLONNES) ---------------
    // -------------------------------------------------------------------------
    public function verify(HttpRequest $request)
    {
        $request->validate([
            'access_code' => 'required|string',
            'car_id'      => 'required|integer',
        ]);

        $code = $request->input('access_code');
        $carId = $request->input('car_id');

        // 1. Chercher la réservation
        $loan = Loan::where('loanable_id', $carId)
            ->where('unique_access_code', $code)
            ->whereIn('status', ['accepted', 'ongoing', 'in_process'])
            ->first();

        if (!$loan) {
            return response()->json([
                'authorized' => false,
                'message' => 'Code introuvable ou réservation non valide.'
            ], 403);
        }

        // 2. VÉRIFICATION TEMPORELLE
        
        // On force l'heure actuelle sur le fuseau de Paris
        $now = Carbon::now('Europe/Paris'); 
        
        // --- CORRECTION MAJEURE ICI : departure_at et arrival_at ---
        // On utilise les vrais noms de colonnes de ta base de données
        $start = Carbon::parse($loan->departure_at)->setTimezone('Europe/Paris');
        $end   = Carbon::parse($loan->arrival_at)->setTimezone('Europe/Paris');

        // Marge de tolérance de 15 minutes avant le début
        $startWithBuffer = $start->copy()->subMinutes(15);

        // Debug : On affiche les heures comparées pour être sûr
        $debugTime = "Serv: " . $now->format('d/m H:i') . " | Resa: " . $start->format('d/m H:i');

        // A. Trop tôt ?
        if ($now->lessThan($startWithBuffer)) {
            return response()->json([
                'authorized' => false,
                'message' => 'Trop tôt ! Début à ' . $start->format('H:i'),
                'debug' => $debugTime
            ], 403);
        }

        // B. Trop tard ?
        if ($now->greaterThan($end)) {
            return response()->json([
                'authorized' => false,
                'message' => 'Trop tard ! Finie à ' . $end->format('H:i'),
                'debug' => $debugTime
            ], 403);
        }

        // 3. Tout est bon
        return response()->json([
            'authorized' => true,
            'message' => 'Accès autorisé.',
            'user' => $loan->user_id
        ], 200);
    }
}