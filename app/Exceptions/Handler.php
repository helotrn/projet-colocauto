<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    protected $dontReport = [CommandNotFoundException::class];

    protected $dontFlash = ["password", "password_confirmation"];

    public function report(Throwable $exception)
    {
        if (app()->bound("sentry") && $this->shouldReport($exception)) {
            app("sentry")->captureException($exception);
        }

        parent::report($exception);
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof HttpException) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(
                    [
                        "message" => $exception->getMessage(),
                        "errors" => [[$exception->getMessage()]],
                    ],
                    $exception->getStatusCode()
                );
            }
        }
        return parent::render($request, $exception);
    }
}
