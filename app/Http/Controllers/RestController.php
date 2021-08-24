<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Support\LazyCollection;
use Molotov\RestController as MolotovRestController;
use Maatwebsite\Excel\Facades\Excel;

class RestController extends MolotovRestController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function respondWithCsv($request, $items, $model)
    {
        if (is_a($items, LazyCollection::class)) {
            $item = $items->first();
        } else {
            $item = $items[0];
        }

        $export = new $item::$export(
            $this->transformCollection($request, $items),
            explode(",", $request->get("fields")),
            $model
        );

        $userId = $request->user()->id;
        $date = date("YmdHmi");
        $modelClassName = get_class($model);
        $modelClassNameParts = explode("\\", $modelClassName);
        $modelName = array_pop($modelClassNameParts);
        $filename = Str::plural(strtolower($modelName));

        $path = "/storage/exports/$date.$userId.$filename.csv";

        Excel::store($export, $path, env("FILESYSTEM_DRIVER"));

        return $path;
    }
}
