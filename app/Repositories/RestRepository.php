<?php

namespace App\Repositories;

class RestRepository extends \Molotov\RestRepository
{
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
    protected function applyFilter(&$model, $param, $value, $query, $request) {
        $negative = $param[0] === '!';
        $paramName = str_replace('!', '', $param);

        if (strpos($paramName, '.') !== false) {
            [$relation, $field] = explode('.', $paramName, 2);

            if (in_array($relation, array_merge($model->collections, $model->items))) {
                $camelRelation = Str::camel($relation);
                $targetRelation = $model->{$camelRelation}();
                $targetModel = $targetRelation->getRelated();
                $fieldQuery = $negative ? "!$field" : $field;

                // Cannot query accross databases: restart the query and filter by foreign key
                $targetConnectionName = $targetModel->getConnectionName();
                if ($targetConnectionName !== $model->getConnectionName()) {
                    $keyName = $targetModel->getKeyName();
                    $foreignKeyName = $targetRelation->getForeignKeyName();

                    if (!isset($this->connectionQueries[$targetConnectionName])) {
                        $this->connectionQueries[$targetConnectionName] = [];
                    }

                    if (!isset($this->connectionQueries[$targetConnectionName][$foreignKeyName])) {
                        $this->connectionQueries[$targetConnectionName][$foreignKeyName]
                            = $targetModel->select(\DB::raw("{$keyName} AS id"));
                    }

                    $subquery = $this->connectionQueries[$targetConnectionName][$foreignKeyName];

                    if (is_a($targetRelation, BelongsTo::class)) {
                        $this->connectionQueries[$targetConnectionName][$foreignKeyName]
                            = $subquery->where(
                                function ($q) use ($targetModel, $fieldQuery, $value, $request) {
                                    return $this->applyFilter(
                                        $targetModel,
                                        $fieldQuery,
                                        $value,
                                        $q,
                                        $request
                                    );
                                }
                            );
                        return $query;
                    }
                }

                return $query->where(
                    function ($q) use (
                        $camelRelation,
                        $targetModel,
                        $fieldQuery,
                        $value,
                        $request
                    ) {
                        $q = $q->whereHas(
                            $camelRelation,
                            function ($q) use ($targetModel, $fieldQuery, $value, $request) {
                                return $this->applyFilter(
                                    $targetModel,
                                    $fieldQuery,
                                    $value,
                                    $q,
                                    $request
                                );
                            }
                        );

                        if ($fieldQuery[0] === '!') {
                            $q = $q->orDoesntHave($camelRelation);
                        }

                        return $q;
                    }
                );
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
            [$query, $scopedParam] = $this->addColumnDefinition($query, $columns, $paramName);

            if (preg_match('/(array_agg|sum)\(/i', "$scopedParam")) {
                $aggregate = true;
            }
        } else {
            $scopedParam = "{$query->getModel()->getTable()}.$paramName";
        }

        if ($paramName === 'id' && $value === 'me') {
            $value = $request->user()->id;
        }

        if ($paramName === 'deleted_at' && !!$value) {
            $query = $query->withTrashed();
        }

        // If a type is defined for this filter, use the query
        // language, otherwise fallback to default Laravel filtering
        $filterType = array_get($model::$filterTypes, $paramName);
        if (is_array($filterType)) {
            $filterType = 'enum';
        }
        if (!$filterType && $paramName === 'id') {
            $filterType = 'number';
        }

        switch ($filterType) {
            case 'enum':
                $values = explode(',', $value);
                return $this->applyWhereInFilter(
                    $values,
                    $aggregate,
                    $negative,
                    $scopedParam,
                    $paramName,
                    $query
                );
            case 'boolean':
                return $query->where(
                    $scopedParam,
                    $negative ? '!=' : '=',
                    filter_var($value, FILTER_VALIDATE_BOOLEAN) || $value === ''
                );
            case 'number':
                if (is_array($value)) {
                    $value = implode(',', $value);
                }

                if ($value === '') {
                    return $query;
                }

                if (strpos($value, ':') !== false) {
                    $matches = [];
                    $regex = '(\d*)?';
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
                    'intval',
                    array_map(
                        'trim',
                        array_filter(
                            explode(',', $value),
                            function ($i) {
                                return $i !== '';
                            }
                        )
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
            case 'text':
                if ($this->model->getConnection()->getConfig()['driver'] === 'pgsql') {
                    $escapedValue = pg_escape_string($value);
                    return $query->where(
                        \DB::raw("unaccent($scopedParam)"),
                        'ILIKE',
                        \DB::raw("unaccent('%$escapedValue%')")
                    );
                } else {
                    return $query->where($scopedParam, 'LIKE', "%$value%");
                }
                break;

            case 'date':
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
                        return $query->having($scopedParam, '!=', $value);
                    }
                    return $query->where($scopedParam, '!=', $value);
                }

                if ($aggregate) {
                    return $query->having($scopedParam, $value);
                }
                return $query->where($scopedParam, $value);
                break;
        }

        return $query;
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
        if ($value === '') {
            return $query;
        }

        $intervalRegex = '(?<start>[0-9TZ:-]*)@{0,1}(?<end>[0-9TZ:-]*)';

        $timestampRegex
            =  '(?<year>[0-9]{4})'
            .  '-(?<month>[0-9]{2})'
            .  '-(?<day>[0-9]{2})'
            .  'T(?<hour>[0-9]{2})'
            .  ':(?<minute>[0-9]{2})'
            .  ':(?<second>[0-9]{2})'
            .  '\.{0,1}(?<millisecond>[0-9]*)'
            .  '(?<timezone>Z)'
            ;

        $intervalMatches = [];
        $timestampMatches = [];

        if (preg_match("/^$intervalRegex?$/", $value, $intervalMatches)) {
                             // Apply left boundary if exists
            if ($intervalMatches['start']) {
                if (preg_match(
                    "/^$timestampRegex?$/",
                    $intervalMatches['start'],
                    $timestampMatches
                )) {
                             // Match found, then add constraint.
                    if ($aggregate) {
                        $query = $query->having($scopedParam, '>=', $intervalMatches['start']);
                    } else {
                        $query = $query->where($scopedParam, '>=', $intervalMatches['start']);
                    }
                } else {
                    throw new \Exception('Malformed timestamp.');
                }
            }

                             // Apply right boundary if exists
            if ($intervalMatches['end']) {
                if (preg_match(
                    "/^$timestampRegex?$/",
                    $intervalMatches['end'],
                    $timestampMatches
                )) {
                             // Match found, then add constraint.
                    if ($aggregate) {
                        $query = $query->having($scopedParam, '<', $intervalMatches['end']);
                    } else {
                        $query = $query->where($scopedParam, '<', $intervalMatches['end']);
                    }
                } else {
                    throw new \Exception('Malformed timestamp.');
                }
            }
        }

        return $query;
    }
}
