<?php

namespace App\Utils\Traits;

trait ErrorResponseTrait
{
    protected function respondWithErrors($errors, $message = null) {
        return response()->json([
            'message' => $message ?: 'Validation error',
            'errors' => $errors,
        ], 422);
    }
}
