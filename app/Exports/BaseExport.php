<?php

namespace App\Exports;

use App\Http\Requests\ParseFieldsHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BaseExport implements FromCollection, WithHeadings, WithMapping
{
    protected $items;
    protected $fields;

    public function __construct($items, $fields, $model)
    {
        $this->items = $items;
        $this->fields = $this->explodeFields($fields, $model);
    }

    public function headings(): array
    {
        return $this->fields;
    }

    public function map($item): array
    {
        return array_map(function ($f) use ($item) {
            return array_get($item, $f);
        }, $this->fields);
    }

    public function collection()
    {
        return $this->items;
    }

    protected function explodeFields($fields, $model, $parent = null)
    {
        $explodedFields = [];
        $relationsCount = [];
        $relationsFields = [];

        $parsedFields = ParseFieldsHelper::parseFields(
            ParseFieldsHelper::splitFields(implode(",", $fields))
        );

        foreach ($parsedFields as $name => $rest) {
            if (!is_array($rest)) {
                $explodedFields[] = $rest;
                continue;
            }

            if (
                in_array(
                    $name,
                    array_merge($model->items, array_keys($model->morphOnes))
                )
            ) {
                $explodedFields = array_merge(
                    $explodedFields,
                    ParseFieldsHelper::joinFieldsTree($rest, $name)
                );
            } elseif (
                in_array(
                    $name,
                    array_merge(
                        $model->collections,
                        array_keys($model->morphManys)
                    )
                )
            ) {
                if (!isset($relationsCount[$name])) {
                    $largestOccurence = $this->items->reduce(function (
                        $acc,
                        $i
                    ) use ($name) {
                        $target = array_get($i, $name);
                        if (
                            (is_array($target) ||
                                is_a($target, Collection::class)) &&
                            count($target) > $acc
                        ) {
                            return count($target);
                        }

                        return $acc;
                    },
                    1);
                    $relationsCount[$name] = $largestOccurence;
                }

                if (!isset($relationsFields[$name])) {
                    $relationsFields[$name] = [];
                }

                $camelName = Str::camel($name);
                $subfields = $this->explodeFields(
                    ParseFieldsHelper::joinFieldsTree($rest),
                    $model->{$camelName}()->getRelated(),
                    $model->{$camelName}()
                );
                $relationsFields[$name] = $subfields;
            } elseif (!!$parent && ($pivotClass = $parent->getPivotClass())) {
                $pivot = new $pivotClass();
                if (
                    in_array(
                        $name,
                        array_merge(
                            $pivot->items,
                            array_keys($pivot->morphOnes)
                        )
                    )
                ) {
                    $explodedFields = array_merge(
                        $explodedFields,
                        ParseFieldsHelper::joinFieldsTree($rest, $name)
                    );
                } elseif (
                    in_array(
                        $name,
                        array_merge(
                            $pivot->collections,
                            array_keys($pivot->morphManys)
                        )
                    )
                ) {
                    if (!isset($relationsCount[$name])) {
                        $largestOccurence = $this->items->reduce(function (
                            $acc,
                            $i
                        ) use ($name) {
                            $target = array_get($i, $name);
                            if (
                                (is_array($target) ||
                                    is_a($target, Collection::class)) &&
                                count($target) > $acc
                            ) {
                                return count($target);
                            }

                            return $acc;
                        },
                        1);
                        $relationsCount[$name] = $largestOccurence;
                    }

                    if (!isset($relationsFields[$name])) {
                        $relationsFields[$name] = [];
                    }

                    $camelName = Str::camel($name);
                    $subfields = $this->explodeFields(
                        ParseFieldsHelper::joinFieldsTree($rest),
                        $pivot->{$camelName}()->getRelated()
                    );
                    $relationsFields[$name] = $subfields;
                }
            }
        }

        foreach (array_keys($relationsCount) as $name) {
            for ($i = 0; $i < $relationsCount[$name]; $i++) {
                foreach ($relationsFields[$name] as $field) {
                    $explodedFields[] = "$name.$i.$field";
                }
            }
        }

        return $explodedFields;
    }
}
