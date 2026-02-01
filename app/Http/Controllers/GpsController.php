<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan;

class GpsController extends Controller
{
    public function update(Request $request)
    {
        // 1. SÉCURITÉ
        if ($request->input('secret') !== 'projet_colocauto_2026') {
            return response()->json(['error' => 'Non autorisé'], 401);
        }

        // 2. VALIDATION
        $request->validate([
            'car_id' => 'required|integer',
            'distance_km' => 'required|numeric',
        ]);

        $carId = $request->input('car_id');
        $distance = $request->input('distance_km');

        // 3. RECHERCHE
        $activeLoan = Loan::with('loanable')
            ->where('loanable_id', $carId)
            ->whereNull('distance_driven')
            ->latest()
            ->first();

        if ($activeLoan) {
            // 4. FILTRE COMMUNAUTÉ (Marcelcave=36, Tests=1,5)
            $communautesAutorisees = [1, 5, 36];
            if (!in_array($activeLoan->loanable->community_id, $communautesAutorisees)) {
                return response()->json(['message' => 'Ignoré : Communauté non suivie'], 200);
            }

            // 5. SAUVEGARDE
            $activeLoan->gps_measured_distance = $distance;
            $activeLoan->save();

            return response()->json(['message' => 'Succès', 'id' => $activeLoan->id], 200);
        }

        return response()->json(['error' => 'Pas de réservation active'], 404);
    }
}