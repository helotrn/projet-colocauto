<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use Storage;

class MinioStorageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend("minio", function ($app, $config) {
            $client = new S3Client([
                "credentials" => [
                    "key" => env("S3_KEY"),
                    "secret" => env("S3_SECRET"),
                ],
                "region" => env("S3_REGION"),
                "version" => "latest",
                "bucket_endpoint" => false,
                "use_path_style_endpoint" => true,
                "endpoint" => env("S3_URL"),
            ]);
            $options = [
                "override_visibility_on_copy" => true,
            ];
            return new Filesystem(
                new AwsS3Adapter($client, $config["bucket"], "", $options)
            );
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
