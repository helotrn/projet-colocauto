<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MaterializedViewServiceProvider extends ServiceProvider
{
    public function register() {
    }

    public function boot() {
        foreach ([
            \App\Models\Image::class,
            \App\Models\File::class,
        ] as $class) {
            $class::saved(function ($model) {
                \DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY assets');
            });

            $class::deleted(function ($model) {
                \DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY assets');
            });
        }

        foreach ([
            \App\Models\Bike::class,
            \App\Models\Car::class,
            \App\Models\Trailer::class,
        ] as $class) {
            $class::saved(function ($model) {
                \DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY loanables');
            });

            $class::deleted(function ($model) {
                \DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY loanables');
            });
        }

        foreach ([
            \App\Models\Extension::class,
            \App\Models\Handover::class,
            \App\Models\Incident::class,
            \App\Models\Intention::class,
            \App\Models\Payment::class,
            \App\Models\PrePayment::class,
            \App\Models\Takeover::class,
        ] as $class) {
            $class::saved(function ($model) {
                \DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY actions');
            });

            $class::deleted(function ($model) {
                \DB::statement('REFRESH MATERIALIZED VIEW CONCURRENTLY actions');
            });
        }
    }
}
