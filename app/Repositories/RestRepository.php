<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RestRepository
{
    protected $model;
    protected $columnsDefinition = [];
    protected $connectionQueries = [];

    protected $reserved = [
        "!fields",
        "fields",
        "for",
        "order",
        "page",
        "per_page",
        "q",
    ];

    public function get($request)
    {
        $query = $this->model;

        if (method_exists($this->model, "scopeAccessibleBy")) {
            $query = $query->accessibleBy($request->user());
        }

        if (method_exists($this->model, "scopeFor")) {
            $query = $query->for($request->get("for"), $request->user());
        }

        $params =
            $this->parseQueryString(
                array_get($request->server->all(), "QUERY_STRING")
            ) ?:
            $request->all();

        $extraParams = array_diff_key($request->all(), $params);
        $paramKeys = array_keys($params);
        foreach ($extraParams as $extraParam => $value) {
            if (strpos($extraParam, "_")) {
                $parts = explode("_", $extraParam);
                for ($i = 1; $i < count($parts); $i++) {
                    $relation = implode("_", array_slice($parts, 0, $i));
                    if (
                        in_array(
                            $relation,
                            array_merge(
                                $this->model->collections,
                                $this->model->items
                            )
                        )
                    ) {
                        continue 2;
                    }
                }

                if (in_array(str_replace("_", ".", $extraParam), $paramKeys)) {
                    continue;
                }
            }

            $params[$extraParam] = $value;
        }

        foreach ($params as $param => $value) {
            if (in_array($param, $this->reserved)) {
                continue;
            }

            $query = $this->applyFilter(
                $this->model,
                $param,
                $value,
                $query,
                $request
            );
        }

        foreach ($this->connectionQueries as $connection => $queries) {
            foreach ($queries as $foreignKeyName => $subquery) {
                $rawSql = $this->queryToRawSql($subquery);

                $keys = array_map(function ($obj) {
                    return $obj->id;
                }, \DB::connection($connection)->select($rawSql));

                $query = $query->whereIn($foreignKeyName, $keys);
            }
        }

        if (isset($params["q"]) && method_exists($this->model, "scopeSearch")) {
            $query = $query->search($params["q"]);
        }

        if (isset($params["order"])) {
            $query = $this->orderBy($query, $params["order"]);
        }

        if ($fields = $request->getFields()) {
            $this->applyWithFromQuery($fields, $query);
        }

        $columns = $this->columnsDefinition;
        foreach ($columns as $paramName => $column) {
            // FIXME Should not query all columns all the time
            [$query, $_] = $this->addColumnDefinition(
                $query,
                $columns,
                $paramName
            );
        }

        // TODO Move to controller
        $perPage = $request->get("per_page") ?: 10;
        $page = $request->get("page") ?: 1;

        if (strpos($query->toSql(), "group by") !== false) {
            $total = $this->wrapQueryForRealTotal(
                $this->model->getConnectionName(),
                $query
            );
        } else {
            try {
                $total = $query->count();
            } catch (\Illuminate\Database\QueryException $e) {
                $total = $this->wrapQueryForRealTotal(
                    $this->model->getConnectionName(),
                    $query
                );
            }
        }

        if ($perPage === "*") {
            $perPage = -1;
        } else {
            $query = $query->take($perPage)->skip($perPage * ($page - 1));
        }

        try {
            $items = $query->cursor();
        } catch (RelationNotFoundException $e) {
            throw new ValidationException([
                "includes" => "relationship does not exist",
            ]);
        }

        return [$items, $total];
    }

    public function find($request, $id)
    {
        if (!intval($id)) {
            return abort(422, "Numeric ids.");
        }

        $query = $this->model;

        if (method_exists($this->model, "scopeAccessibleBy")) {
            $query = $query->accessibleBy($request->user());
        }

        if (method_exists($this->model, "scopeFor")) {
            $query = $query->for($request->get("for"), $request->user());
        }

        $params = $request->all();
        foreach ($params as $param => $value) {
            if (in_array($param, $this->reserved)) {
                continue;
            }

            $query = $this->applyFilter(
                $this->model,
                $param,
                $value,
                $query,
                $request
            );
        }

        if ($fields = $request->getFields()) {
            $this->applyWithFromQuery($fields, $query);
        }

        $columns = $this->columnsDefinition;
        foreach ($columns as $paramName => $column) {
            [$query, $_] = $this->addColumnDefinition(
                $query,
                $columns,
                $paramName
            );
        }

        return $query->findOrFail($id);
    }

    public function create($data)
    {
        $this->model->fill($data);

        $this->model->save();

        $this->saveRelations($data);

        $this->model->save();

        $model = $this->model;

        $className = get_class($this->model);
        $this->model = new $className();

        return $model;
    }

    public function update($request, $id, $data)
    {
        $query = $this->model;

        if (method_exists($query, "scopeAccessibleBy")) {
            $query = $query->accessibleBy($request->user());
        }

        $this->model = $query->findOrFail($id);

        $this->model->fill($data);

        $this->model->save();

        $this->saveRelations($data);

        $this->model->save();

        $className = get_class($this->model);
        $this->model = new $className();

        return $this->model->find($id);
    }

    public function destroy($request, $id)
    {
        $query = $this->model;

        if (method_exists($query, "scopeAccessibleBy")) {
            $query = $query->accessibleBy($request->user());
        }

        $this->model = $query->findOrFail($id);

        $this->model->delete();

        return $this->model;
    }

    public function restore($request, $id)
    {
        $query = $this->model->withTrashed();

        if (method_exists($query, "scopeAccessibleBy")) {
            $query = $query->accessibleBy($request->user());
        }

        $this->model = $query->findOrFail($id);

        $this->model->restore();

        return $this->model;
    }

    protected function orderBy($query, $def)
    {
        if ($def === "") {
            return $query;
        }

        $columns = explode(",", $def);

        foreach ($columns as $column) {
            if (strpos($column, "-") === 0) {
                $direction = "desc";
                $column = substr($column, 1);
            } else {
                $direction = "asc";
            }

            if (in_array($column, array_keys($this->columnsDefinition))) {
                $query = $query->orderBy($column, $direction);
            } else {
                $table = $this->model->getTable();
                $query = $query->orderBy("$table.$column", $direction);
            }
        }

        return $query;
    }

    protected function saveRelations($data)
    {
        $this->saveItems($data);
        $this->saveCollections($data);
    }

    protected function saveItems($data)
    {
        $skipObjectRelation = [];
        foreach (
            array_diff(array_keys($data), $this->model->getFillable())
            as $field
        ) {
            if ($field === "id") {
                continue;
            }

            if (preg_match('/_id$/', $field)) {
                $relationName = str_replace("_id", "", $field);

                if (in_array($relationName, $skipObjectRelation)) {
                    continue;
                }

                if (in_array($relationName, $this->model->items)) {
                    $newAssoc = $data[$field];

                    $skipObjectRelation[] = $relationName;

                    $camelRelationName = Str::camel($relationName);
                    $relation = $this->model->{$camelRelationName}();

                    if (is_a($relation, BelongsTo::class)) {
                        if ($newAssoc) {
                            $relation->associate($data[$field]);
                        } else {
                            $relation->dissociate();
                        }
                    } else {
                        if ($newAssoc) {
                            $newData = ["id" => $newAssoc];
                            $this->saveRelatedItem(
                                $this->model,
                                $relationName,
                                $newData
                            );
                        } else {
                            $this->deleteRelatedItem(
                                $this->model,
                                $relationName
                            );
                        }
                    }
                }
            } elseif (
                in_array($field, $this->model->items) &&
                is_array($data[$field])
            ) {
                $camelField = Str::camel($field);
                $related = $this->saveRelatedItem(
                    $this->model,
                    $field,
                    $data[$field]
                );
                $relation = $this->model->{$camelField}();

                if (
                    $this->model->{$camelField} &&
                    $this->model->{$camelField}->id !== $related->id
                ) {
                    if (is_a($relation, HasOne::class)) {
                        $this->model->{$camelField}[
                            $this->model->{$camelField}()->getForeignKeyName()
                        ] = null;
                        $this->model->{$camelField}->save();
                    } else {
                        $relation->dissociate();
                    }
                }

                foreach (array_keys($related->morphOnes) as $morphOne) {
                    $this->savePolymorphicRelation(
                        $related,
                        $morphOne,
                        $data[$field]
                    );
                }

                if (is_a($relation, BelongsTo::class)) {
                    $relation->associate($related);
                }
            } elseif (
                in_array($field, array_keys($this->model->morphOnes)) &&
                array_key_exists($field, $data)
            ) {
                if (is_array($data[$field]) && !!$data[$field]) {
                    if (
                        $this->model->{$field} &&
                        $this->model->{$field}->id !== $data[$field]["id"]
                    ) {
                        $this->model->{$field}->delete();
                    }

                    $this->savePolymorphicRelation($this->model, $field, $data);
                } else {
                    $this->deletePolymorphicRelation($this->model, $field);
                }
            }
        }
    }

    protected function saveCollections($data)
    {
        foreach (
            array_diff(array_keys($data), $this->model->getFillable())
            as $field
        ) {
            if ($field === "id") {
                continue;
            }

            if (array_key_exists($field, $data)) {
                if (is_array($data[$field])) {
                    $allowedRelations = array_merge(
                        $this->model->collections,
                        array_keys($this->model->morphManys)
                    );
                    if (in_array($field, $allowedRelations)) {
                        $relation = $this->model->{Str::camel($field)}();

                        if (is_a($relation, HasManyThrough::class)) {
                            continue;
                        }

                        $ids = [];

                        if (method_exists($relation, "getPivotClass")) {
                            $pivotClass = $relation->getPivotClass();
                            $pivot = new $pivotClass();
                            $pivotAttributes = $pivot->getFillable();

                            $isExtendedPivot = $this->isBaseModel($pivot);
                            if ($isExtendedPivot) {
                                $pivotItems = array_merge(
                                    $pivot->items,
                                    array_keys($pivot->morphOnes)
                                );
                                $pivotCollections = array_merge(
                                    $pivot->collections,
                                    array_keys($pivot->morphManys)
                                );
                            } else {
                                $pivotItems = [];
                                $pivotCollections = [];
                            }

                            foreach ($data[$field] as $element) {
                                $pivotData = [];
                                $pivotItemData = [];

                                foreach ($pivotAttributes as $pivotAttribute) {
                                    if (
                                        !array_key_exists(
                                            $pivotAttribute,
                                            $element
                                        )
                                    ) {
                                        continue;
                                    }

                                    $pivotData[$pivotAttribute] =
                                        $element[$pivotAttribute];
                                    unset($element[$pivotAttribute]);
                                }

                                foreach ($pivotItems as $pivotItem) {
                                    if (
                                        !array_key_exists($pivotItem, $element)
                                    ) {
                                        continue;
                                    }

                                    $pivotItemData[$pivotItem] =
                                        $element[$pivotItem];
                                    unset($element[$pivotItem]);
                                }

                                if (
                                    array_key_exists("id", $element) &&
                                    $element["id"]
                                ) {
                                    $sync = [];
                                    $sync[$element["id"]] = $pivotData;
                                    $relation->syncWithoutDetaching($sync);

                                    $targetPivot = $this->model
                                        ->{Str::camel($field)}()
                                        ->find($element["id"])->pivot;

                                    foreach ($pivotItems as $pivotItem) {
                                        $this->savePolymorphicRelation(
                                            $targetPivot,
                                            $pivotItem,
                                            $pivotItemData
                                        );
                                    }

                                    foreach (
                                        $pivotCollections
                                        as $pivotCollection
                                    ) {
                                        if (isset($element[$pivotCollection])) {
                                            $targetPivot
                                                ->{$pivotCollection}()
                                                ->sync(
                                                    array_map(function ($i) {
                                                        return $i["id"];
                                                    }, $element[
                                                        $pivotCollection
                                                    ])
                                                );
                                        }
                                    }

                                    $ids[] = $element["id"];
                                }
                            }
                        } else {
                            foreach ($data[$field] as $element) {
                                if (
                                    array_key_exists("id", $element) &&
                                    $element["id"]
                                ) {
                                    $ids[] = $element["id"];
                                }
                            }
                        }

                        $relatedClass = $relation->getRelated();

                        if ($relatedClass->readOnly) {
                            continue;
                        }

                        if (is_a($relation, MorphMany::class)) {
                            foreach ($ids as $id) {
                                $item = $relatedClass->find($id);
                                $relation->save($id);
                            }
                            $relation->sync($ids);
                        } elseif (is_a($relation, HasMany::class)) {
                            $newItems = [];
                            foreach ($data[$field] as $element) {
                                if (
                                    array_key_exists("id", $element) &&
                                    $element["id"]
                                ) {
                                    $existingItem = $relatedClass->find(
                                        $element["id"]
                                    );
                                    $existingItem->fill($element);
                                    $newItems[] = $existingItem;
                                } else {
                                    $newItem = new $relatedClass();
                                    $newItem->fill($element);
                                    $newItems[] = $newItem;
                                }
                            }

                            $existingIds = $relation
                                ->get()
                                ->pluck("id")
                                ->toArray();
                            $removedIds = array_diff($existingIds, $ids);

                            if (!empty($removedIds)) {
                                $relation
                                    ->getRelated()
                                    ->whereIn("id", $removedIds)
                                    ->delete();
                            }

                            $relation->saveMany($newItems);
                        } else {
                            $relation->sync($ids);
                        }
                    }
                }
            }
        }
    }

    /*
      @param model
        Instance of Laravel Eloquent Model.

      @param param
        Name of the parameter to filter with.

      @param value
        Value of the parameter to filter with.

      @param query
        Query object to apply filter to.

      @param request
        Instance of Molotov\Request which exends Laravel's FormRequest.
    */
    protected function applyFilter(&$model, $param, $value, $query, $request)
    {
        $negative = $param[0] === "!";
        $paramName = str_replace("!", "", $param);

        if (strpos($paramName, ".") !== false) {
            [$relation, $field] = explode(".", $paramName, 2);

            if (
                in_array(
                    $relation,
                    array_merge($model->collections, $model->items)
                )
            ) {
                $camelRelation = Str::camel($relation);
                $targetRelation = $model->{$camelRelation}();
                $targetModel = $targetRelation->getRelated();
                $fieldQuery = $negative ? "!$field" : $field;

                // Cannot query accross databases: restart the query and filter by foreign key
                $targetConnectionName = $targetModel->getConnectionName();
                if ($targetConnectionName !== $model->getConnectionName()) {
                    $keyName = $targetModel->getKeyName();
                    $foreignKeyName = $targetRelation->getForeignKeyName();

                    if (
                        !isset($this->connectionQueries[$targetConnectionName])
                    ) {
                        $this->connectionQueries[$targetConnectionName] = [];
                    }

                    if (
                        !isset(
                            $this->connectionQueries[$targetConnectionName][
                                $foreignKeyName
                            ]
                        )
                    ) {
                        $this->connectionQueries[$targetConnectionName][
                            $foreignKeyName
                        ] = $targetModel->select(\DB::raw("{$keyName} AS id"));
                    }

                    $subquery =
                        $this->connectionQueries[$targetConnectionName][
                            $foreignKeyName
                        ];

                    if (is_a($targetRelation, BelongsTo::class)) {
                        $this->connectionQueries[$targetConnectionName][
                            $foreignKeyName
                        ] = $subquery->where(function ($q) use (
                            $targetModel,
                            $fieldQuery,
                            $value,
                            $request
                        ) {
                            return $this->applyFilter(
                                $targetModel,
                                $fieldQuery,
                                $value,
                                $q,
                                $request
                            );
                        });
                        return $query;
                    }
                }

                return $query->where(function ($q) use (
                    $camelRelation,
                    $targetModel,
                    $fieldQuery,
                    $value,
                    $request
                ) {
                    $q = $q->whereHas($camelRelation, function ($q) use (
                        $targetModel,
                        $fieldQuery,
                        $value,
                        $request
                    ) {
                        return $this->applyFilter(
                            $targetModel,
                            $fieldQuery,
                            $value,
                            $q,
                            $request
                        );
                    });

                    if ($fieldQuery[0] === "!") {
                        $q = $q->orDoesntHave($camelRelation);
                    }

                    return $q;
                });
            }

            return $query;
        }

        $camelParamName = Str::camel($paramName);
        if (method_exists($this->model, "scope$camelParamName")) {
            return $query->{$camelParamName}($value, $negative);
        }

        if (in_array($paramName, $this->model->items)) {
            if ($negative) {
                return $query->doesntHave($camelParamName);
            }

            return $query->whereHas($camelParamName);
        }

        $columns = $model::getColumnsDefinition();
        $aggregate = false;
        if (in_array($paramName, array_keys($columns))) {
            [$query, $scopedParam] = $this->addColumnDefinition(
                $query,
                $columns,
                $paramName
            );

            if (preg_match("/(array_agg|sum)\(/i", "$scopedParam")) {
                $aggregate = true;
            }
        } else {
            $scopedParam = "{$query->getModel()->getTable()}.$paramName";
        }

        if ($paramName === "id" && $value === "me") {
            $value = $request->user()->id;
        }

        if ($paramName === "deleted_at" && !!$value) {
            $query = $query->withTrashed();
        }

        // If a type is defined for this filter, use the query
        // language, otherwise fallback to default Laravel filtering
        $filterType = array_get($model::$filterTypes, $paramName);
        if (is_array($filterType)) {
            $filterType = "enum";
        }
        if (!$filterType && $paramName === "id") {
            $filterType = "number";
        }

        switch ($filterType) {
            case "enum":
                $values = explode(",", $value);
                return $this->applyWhereInFilter(
                    $values,
                    $aggregate,
                    $negative,
                    $scopedParam,
                    $paramName,
                    $query
                );
            case "boolean":
                return $query->where(
                    $scopedParam,
                    $negative ? "!=" : "=",
                    filter_var($value, FILTER_VALIDATE_BOOLEAN) || $value === ""
                );
            case "number":
                if (is_array($value)) {
                    $value = implode(",", $value);
                }

                if ($value === "") {
                    return $query;
                }

                if (strpos($value, ":") !== false) {
                    $matches = [];
                    $regex = "(\d*)?";
                    preg_match("/^$regex:$regex$/", $value, $matches);

                    $start = $end = null;
                    if (isset($matches[1])) {
                        $start = $matches[1];
                    }
                    if (isset($matches[2])) {
                        $end = $matches[2];
                    }
                    $range = [$start, $end];

                    return $this->parseRangeFilter(
                        $scopedParam,
                        $paramName,
                        $range,
                        $query,
                        $aggregate
                    );
                }

                $values = array_map(
                    "intval",
                    array_map(
                        "trim",
                        array_filter(explode(",", $value), function ($i) {
                            return $i !== "";
                        })
                    )
                );

                return $this->applyWhereInFilter(
                    $values,
                    $aggregate,
                    $negative,
                    $scopedParam,
                    $paramName,
                    $query
                );
                break;
            case "text":
                if (
                    $this->model->getConnection()->getConfig()["driver"] ===
                    "pgsql"
                ) {
                    $escapedValue = pg_escape_string($value);
                    return $query->where(
                        \DB::raw("unaccent($scopedParam)"),
                        "ILIKE",
                        \DB::raw("unaccent('%$escapedValue%')")
                    );
                } else {
                    return $query->where($scopedParam, "LIKE", "%$value%");
                }
                break;

            case "date":
                return $this->applyDateRangeFilter(
                    $scopedParam,
                    $paramName,
                    $value,
                    $query,
                    $aggregate
                );
                break;

            default:
                if ($negative) {
                    if ($aggregate) {
                        return $query->having($scopedParam, "!=", $value);
                    }
                    return $query->where($scopedParam, "!=", $value);
                }

                if ($aggregate) {
                    return $query->having($scopedParam, $value);
                }
                return $query->where($scopedParam, $value);
                break;
        }

        return $query;
    }

    protected function applyWithFromQuery($fields, &$query)
    {
        foreach (array_keys($fields) as $field) {
            $relations = array_merge(
                $this->model->items,
                $this->model->collections,
                array_keys($this->model->morphOnes)
            );
            if (in_array($field, $relations)) {
                $query = $query->with(Str::camel($field));
            }
        }
    }

    protected function deletePolymorphicRelation(&$model, $field)
    {
        if ($currentRelation = $model->{$field}()->first()) {
            $currentRelation->delete();
        }

        return $currentRelation;
    }

    protected function savePolymorphicRelation(&$model, $field, &$data)
    {
        if (!array_key_exists($field, $data)) {
            return $model->{$field};
        }

        if (array_key_exists($field, $data)) {
            if (!$data[$field]) {
                if ($model->{$field}) {
                    $model->{$field}->delete();
                }

                return null;
            }
        }

        if (array_key_exists("id", $data[$field])) {
            $newRelation = $model
                ->{$field}()
                ->getRelated()
                ->find($data[$field]["id"]);
        } else {
            $newRelation = $model->{$field}()->getRelated();
        }

        if (!$newRelation) {
            return null;
        }

        $morphId = $model->morphOnes[$field] . "_id";
        $morphType = $model->morphOnes[$field] . "_type";

        $newRelation->{$morphId} = $model->id;
        $newRelation->{$morphType} = get_class($model);

        $newRelation->save();

        return $newRelation;
    }

    protected function deleteRelatedItem(&$model, $field)
    {
        $relation = $model->{$field}();
        $related = $model->{$field};

        if (!$related) {
            return $related;
        }

        if (is_a($relation, HasOne::class)) {
            $related->{$relation->getForeignKeyName()} = null;
        }

        $related->save();

        return $related;
    }

    protected function saveRelatedItem(&$model, $field, &$data)
    {
        $camelField = Str::camel($field);
        $relation = $model->{$camelField}();
        if (!array_key_exists("id", $data)) {
            $related = $relation->getRelated();
        } else {
            $related = $relation->getRelated()->find($data["id"]);
        }

        if ($related->readOnly) {
            return $related;
        }

        $relatedData = array_intersect_key(
            $data,
            array_flip($related->getFillable())
        );

        if (is_a($relation, HasOne::class)) {
            $related->{$relation->getForeignKeyName()} = $model->id;
        }

        $related->fill($relatedData);
        $related->save();

        return $related;
    }

    protected function parseRangeFilter(
        $scopedParam,
        $paramName,
        $range,
        &$query,
        $aggregate = false
    ) {
        [$start, $end] = $range;

        if (!$start && !$end) {
            return $query;
        } elseif (!$start) {
            if ($aggregate) {
                return $query->having($paramName, "<=", $end);
            }
            return $query->where($scopedParam, "<=", $end);
        } elseif (!$end) {
            if ($aggregate) {
                return $query->having($paramName, ">=", $start);
            }
            return $query->where($scopedParam, ">=", $start);
        }

        if ($aggregate) {
            throw new \Exception("aggregate between not supported yet");
        }

        return $query->whereBetween($scopedParam, [$start, $end]);
    }

    // PHP's default query string parser replaces . by _
    // which would break our query language
    protected function parseQueryString($data)
    {
        $data = preg_replace_callback(
            "/(?:^|(?<=&))[^=[]+/",
            function ($match) {
                return bin2hex(urldecode($match[0]));
            },
            $data
        );

        parse_str($data, $values);

        return array_combine(
            array_map("hex2bin", array_keys($values)),
            $values
        );
    }

    // FIXME Virtual columns can mess with count
    protected function wrapQueryForRealTotal($connection, $query)
    {
        $subquery = $this->queryToRawSql($query);

        return \DB::connection($connection)->select(
            \DB::connection($connection)->raw(
                "SELECT COUNT(*) AS cnt FROM ($subquery) AS query"
            )
        )[0]->cnt;
    }

    protected function isBaseModel($class, $autoload = true)
    {
        $traits = [];
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));
        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }
        return in_array("Molotov\Traits\BaseModel", $traits);
    }

    protected function addColumnDefinition($query, $definition, $column)
    {
        $currentColumns = array_map(
            function ($c) {
                return "$c";
            },
            $query->getQuery()->columns ?: []
        );

        if (is_callable($definition[$column])) {
            $scopedParam = \DB::raw($definition[$column]());
            $sql = $definition[$column]();
            if (!in_array("{$sql} AS $column", $currentColumns)) {
                $query = $definition[$column]($query);
            }
        } else {
            $scopedParam = \DB::raw($definition[$column]);
            if (
                !in_array("{$definition[$column]} AS $column", $currentColumns)
            ) {
                $query = $query->selectRaw("{$definition[$column]} AS $column");
            }
        }

        return [$query, $scopedParam];
    }

    public static function applyWhereInFilter(
        $values,
        $aggregate,
        $negative,
        $scopedParam,
        $paramName,
        &$query
    ) {
        if ($aggregate) {
            $placeholders = implode(
                ",",
                array_map(function () {
                    return "?";
                }, $values)
            );

            if ($negative) {
                return $query->whereNot(function ($q) use (
                    $scopedParam,
                    $placeholders,
                    $values
                ) {
                    return $q->havingRaw(
                        "$paramName in ($placeholders)",
                        $values
                    );
                });
            }

            return $query->havingRaw("$paramName in ($placeholders)", $values);
        }

        if ($negative) {
            return $query->whereNotIn($scopedParam, $values);
        }

        return $query->whereIn($scopedParam, $values);
    }

    /*
      @param scopedParam
        Database column name prepended by table name if necessary.

      @param paramName
        Name of the parameter to filter with.

      @param value
        Right-open interval : [, ) containing ISO-8601 timestamps separated by @.

        Examples:
           Bounded interval:
            2021-06-01T14:00:00Z@2021-07-01T08:00:00Z
          Left-bounded interval
            2021-06-01T14:00:00Z
            2021-06-01T14:00:00Z@
          Right-bounded interval
            @2021-07-01T08:00:00Z
          Unbounded interval
            @
            Empty string.

      @param query
        Query object to apply filter to.

      @param aggregate
        Boolean indicating that the query is an aggregate that will switch
        "where" conditions to "having" conditions.
    */
    public static function applyDateRangeFilter(
        $scopedParam,
        $paramName,
        $value,
        &$query,
        $aggregate
    ) {
        // Don't filter if value is empty.
        if ($value === "") {
            return $query;
        }

        $intervalRegex = "(?<start>[0-9TZ\.:-]*)@{0,1}(?<end>[0-9TZ\.:-]*)";

        $timestampRegex =
            "(?<year>[0-9]{4})" .
            "-(?<month>[0-9]{2})" .
            "-(?<day>[0-9]{2})" .
            "T(?<hour>[0-9]{2})" .
            ":(?<minute>[0-9]{2})" .
            ":(?<second>[0-9]{2})" .
            "\.{0,1}(?<millisecond>[0-9]*)" .
            "(?<timezone>Z)";

        $intervalMatches = [];
        $timestampMatches = [];

        if (preg_match("/^$intervalRegex?$/", $value, $intervalMatches)) {
            // Apply left boundary if exists
            if ($intervalMatches["start"]) {
                if (
                    preg_match(
                        "/^$timestampRegex?$/",
                        $intervalMatches["start"],
                        $timestampMatches
                    )
                ) {
                    // Match found, then add constraint.
                    if ($aggregate) {
                        $query = $query->having(
                            $scopedParam,
                            ">=",
                            $intervalMatches["start"]
                        );
                    } else {
                        $query = $query->where(
                            $scopedParam,
                            ">=",
                            $intervalMatches["start"]
                        );
                    }
                } else {
                    throw new \Exception("Malformed timestamp.");
                }
            }

            // Apply right boundary if exists
            if ($intervalMatches["end"]) {
                if (
                    preg_match(
                        "/^$timestampRegex?$/",
                        $intervalMatches["end"],
                        $timestampMatches
                    )
                ) {
                    // Match found, then add constraint.
                    if ($aggregate) {
                        $query = $query->having(
                            $scopedParam,
                            "<",
                            $intervalMatches["end"]
                        );
                    } else {
                        $query = $query->where(
                            $scopedParam,
                            "<",
                            $intervalMatches["end"]
                        );
                    }
                } else {
                    throw new \Exception("Malformed timestamp.");
                }
            }
        }

        return $query;
    }

    protected function queryToRawSql($query)
    {
        $sql = $query->toSql();

        $bindings = array_map(function ($p) {
            $escapedP = pg_escape_string($p);
            return "'$escapedP'";
        }, $query->getBindings());

        $sql = str_replace("%", "~!~!~", $sql);
        $sql = str_replace("?", "%s", $sql);
        $subquery = vsprintf($sql, $bindings);
        $subquery = str_replace("~!~!~", "%", $subquery);

        return $subquery;
    }
}
