<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Client;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        Client::creating(function (Client $client) {
            $client->incrementing = false;
            $client->id = $this->generateClientId();
        });

        Client::retrieved(function (Client $client) {
            $client->incrementing = false;
        });

        Blade::directive("money", function ($amount) {
            return "<?php echo number_format($amount, 2, ',', ' ') . '$'; ?>";
        });
    }

    private function generateClientId(
        int $length = 16,
        string $keyspace = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
    ): string {
        if ($length < 1) {
            throw new \RangeException("Length must be a positive integer");
        }

        $pieces = [];
        $max = mb_strlen($keyspace, "8bit") - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }

        return implode("", $pieces);
    }
}
