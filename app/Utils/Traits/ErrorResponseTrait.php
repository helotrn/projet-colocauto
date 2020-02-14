<?php

namespace App\Utils\Traits;

trait ErrorResponseTrait
{
    protected static $errorMessages = [
        422 => 'Validation errors',
        400 => 'Bad request',
    ];

    protected function respondWithMessage($message = null, $status = 400) {
        return response()->json([
            'message' => $message ?: dig(static::$errorMessages, $status, 'Error'),
        ], $status);
    }

    protected function respondWithErrors($errors, $message = null) {
        return response()->json([
            'message' => $message ?: static::$errorMessages[422],
            'errors' => $errors,
        ], 422);
    }
}
