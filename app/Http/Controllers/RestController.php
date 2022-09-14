<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Support\LazyCollection;
use Molotov\RestController as MolotovRestController;
use Maatwebsite\Excel\Facades\Excel;
use Molotov\Traits\ParsesFields;

class RestController extends MolotovRestController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ParsesFields;

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

    private function split($fields)
    {
        return array_map(function ($f) {
            return explode(".", $f, 2);
        }, $fields);
    }

    // Duplicates the logic in MolotovRestController::getCollectionFields
    // without a dependency on the request
    protected function getCollectionFields($items, $fields, $notFields = [])
    {
        $fields = $this->parseFields($this->split($fields));
        $notFields = $this->parseFields($this->split($notFields));
        return $items->map(function ($item) use ($fields, $notFields) {
            return $this->getItemFields($item, $fields, $notFields);
        });
    }

    protected function getItemFields($item, $fields, $notFields = [])
    {
        $transformer = new $item::$transformer();

        $reflect = new \ReflectionClass($this->model);
        $shortName = $reflect->getShortName();
        $context = [$shortName => true];

        return $transformer->transform($item, [
            "fields" => $fields,
            "!fields" => $notFields,
            "pivot" => isset($item->pivot) ? $item->pivot : null,
            "context" => $context,
        ]);
    }
}
