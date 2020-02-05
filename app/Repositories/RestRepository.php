<?php

namespace App\Repositories;

use App\Exceptions\ValidationException;
use App\Http\Requests\BaseRequest as Request;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class RestRepository
{
    protected $model;
    protected $columnsDefinition = [];

    protected $reserved = [
        'fields',
        'order',
        'page',
        'per_page',
        'q',
    ];

    public function get(Request $request) {
        $query = $this->model;

        if (method_exists($query, 'scopeAccessibleBy')) {
            $query = $query->accessibleBy(\Auth::user());
        }

        $params = $request->all();
        foreach ($params as $param => $value) {
            if (in_array($param, $this->reserved)) {
                continue;
            }

            $query = $this->applyFilter($param, $value, $query);
        }

        if (isset($params['q'])) {
            $query = $query->search($params['q']);
        }

        if (isset($params['order'])) {
            $query = $this->orderBy($query, $params['order']);
        }

        if ($fields = $request->getFields()) {
            $this->applyWithFromQuery($fields, $query);
        }

        $columns = $this->columnsDefinition;
        foreach ($columns as $column) {
            $query = $column($query);
        }

        // TODO Move to controller
        $perPage = $request->get('per_page') ?: 10;
        $page = $request->get('page') ?: 1;

        try {
            $total = $query->count();
        } catch (\Illuminate\Database\QueryException $e) {
            $sql = $query->toSql(); // FIXME Virtual columns will mess with count

            $bindings = array_map(function ($p) {
                return "'$p'";
            }, $query->getBindings());

            $sql = str_replace('?', '%s', $sql);
            $subquery = vsprintf($sql, $bindings);
            $total = \DB::select(\DB::raw("SELECT COUNT(*) AS cnt FROM ($subquery) AS query"))[0]->cnt;
        }

        try {
            return [$query->take($perPage)->skip($perPage * ($page - 1))->get(), $total];
        } catch (RelationNotFoundException $e) {
            throw new ValidationException([
                'includes' => 'relationship does not exist',
            ]);
        }
    }

    public function find(Request $request, $id) {
        $query = $this->model;

        if (method_exists($query, 'scopeAccessibleBy')) {
            $query = $query->accessibleBy(\Auth::user());
        }

        $columns = $this->columnsDefinition;
        foreach ($columns as $column) {
            $query = $column($query);
        }

        return $query->findOrFail($id);
    }

    public function create($data) {
        $this->model->fill($data);

        $this->addBelongsTo($data);

        $this->model->save();

        $this->saveRelations($data);

        $this->model->save();

        return $this->model;
    }

    public function update($id, $data) {
        $query = $this->model;

        if (method_exists($query, 'scopeAccessibleBy')) {
            $query = $query->accessibleBy(\Auth::user());
        }

        $this->model = $query->findOrFail($id);

        $this->model->fill($data);

        $this->addBelongsTo($data);

        $this->model->save();

        $this->saveRelations($data);

        $this->model->save();

        return $this->model->find($id);
    }

    public function destroy($id) {
        $query = $this->model;

        if (method_exists($query, 'scopeAccessibleBy')) {
            $query = $query->accessibleBy(\Auth::user());
        }

        $model = $query->findOrFail($id);

        return $this->model->destroy($id);
    }

    protected function orderBy($query, $def) {
        $columns = explode(',', $def);

        foreach ($columns as $column) {
            if (strpos($column, '-') === 0) {
                $direction = 'desc';
                $column = substr($column, 1);
            } else {
                $direction = 'asc';
            }

            if (in_array($column, array_keys($this->columnsDefinition))) {
                $query = $query->orderBy($this->columnsDefinition[$column](), $direction);
            } else {
                $query = $query->orderBy($column, $direction);
            }
        }

        return $query;
    }

    protected function addBelongsTo($data) {
        foreach (array_diff(array_keys($data), $this->model->getFillable()) as $field) {
            if ($field === 'id') {
                continue;
            }

            if (in_array($field, $this->model->belongsTo)) {
                if (preg_match('/_id$/', $field)) {
                    $this->model->{$field} = $data[$field];
                } elseif (is_array($data[$field]) && array_key_exists('id', $data[$field])) {
                    $this->model->{"{$field}_id"} = $data[$field]['id'];
                }
            }
        }
    }

    protected function saveRelations($data) {
        foreach (array_diff(array_keys($data), $this->model->getFillable()) as $field) {
            if ($field === 'id') {
                continue;
            }

            if (preg_match('/_id$/', $field) &&
                (in_array($field, $this->model->hasOne) || in_array($field, array_keys($this->model->morphOne)))) {
                $newAssoc = $data[$field];
                if ($newAssoc) {
                    $this->model->{str_replace('_id', '', $field)}()->associate($data[$field]);
                } else {
                    $this->model->{str_replace('_id', '', $field)}()->dissociate();
                }
            } elseif (array_key_exists($field, $data)) {
                if (is_array($data[$field])
                    && array_key_exists('id', $data[$field])
                    && $data[$field]['id']) {
                    if (in_array($field, $this->model->hasOne)) {
                        $this->model->{$field}()->associate($data[$field]['id']);
                    } elseif (in_array($field, array_keys($this->model->morphOne))) {
                        if ($this->model->{$field}
                            && $this->model->{$field}->id !== $data[$field]['id']) {
                            $this->model->{$field}->where('id', '!=', $data[$field]['id'])->delete();
                        }
                        $newRelation = $this->model->{$field}()
                            ->getRelated()->find($data[$field]['id']);

                        $morphId = $this->model->morphOne[$field] . '_id';
                        $morphType = $this->model->morphOne[$field] . '_type';

                        $newRelation->{$morphId} = $this->model->id;
                        $newRelation->{$morphType} = get_class($this->model);

                        $newRelation->save();
                    } elseif (in_array($field, array_keys($this->model->morphOneField))) {
                        if ($this->model->{$field}
                            && $this->model->{$field}->count() > 0
                            && $this->model->{$field}->first()->id !== $data[$field]['id']) {
                            $this->model->{$field}()->where('id', '!=', $data[$field]['id'])->delete();
                        }
                        $newRelation = $this->model->{$field}()
                            ->getRelated()->find($data[$field]['id']);

                        $morphId = $this->model->morphOneField[$field] . '_id';
                        $morphType = $this->model->morphOneField[$field] . '_type';

                        $newRelation->{$morphId} = $this->model->id;
                        $newRelation->{$morphType} = get_class($this->model);

                        $newRelation->save();
                    }
                } elseif (!is_array($data[$field]) || !array_key_exists('id', $data[$field])
                    || !$data[$field]['id']) {
                    if (in_array($field, $this->model->hasOne)) {
                        $this->model->{$field}()->dissociate();
                    } elseif (in_array($field, array_keys($this->model->morphOne)) && $this->model->{$field}()->count()) {
                        $this->model->{$field}->delete();
                    } elseif (in_array($field, array_keys($this->model->morphOneField)) && $this->model->{$field}()->count()) {
                        $this->model->{$field}->delete();
                    }
                }
            }
        }
    }

    protected function applyFilter($param, $value, $query) {
        $negative = $param[0] === '!';
        $paramName = str_replace('!', '', $param);

        if (strpos($paramName, '_') !== false) {
            [$relation, $field] = explode('_', $paramName, 2);

            if (in_array($relation, $this->model->hasOne)
                || in_array($relation, $this->model->belongsTo)) {
            }

            if (in_array($relation, $this->model->collections)) {
                return $query->whereHas($relation, function ($q) use ($field, $value, $negative) {
                    $fieldQuery = $negative ? "!$field" : $field;
                    return $this->applyFilter($field, $value, $q);
                });
            }

            return $query;
        }

        if (in_array($paramName, $this->model->hasOne)
            || in_array($paramName, $this->model->belongsTo)) {
            if ($negative) {
                return $query->doesntHave($paramName);
            }

            return $query->whereHas($paramName);
        }

        if (in_array($paramName, array_keys($this->columnsDefinition))) {
            return $query->having($paramName, $value);
        }

        $scopedParam = "{$query->getModel()->getTable()}.$paramName";

        // If a type is defined for this filter, use the query
        // language, otherwise fallback to default Laravel filtering
        switch (dig($this->model::$filterTypes, $paramName, 'default')) {
            case 'boolean':
                return $query->where(
                    $scopedParam,
                    $value === 'true' || $value === '1' || !$negative
                );
            case 'number':
                if ($value === '') {
                    return $query;
                }

                if (strpos($value, ':') !== false) {
                    [$start, $end] = array_map('trim', (explode(':', $value)));
                    if (!$start && !$end) {
                        return $query;
                    } elseif (!$start) {
                        return $query->where($scopedParam, '<=', $end);
                    } elseif (!$end) {
                        return $query->where($scopedParam, '>=', $start);
                    }

                    return $query->whereBetween($scopedParam, [$start, $end]);
                }

                $values = array_map(
                    'intval',
                    array_map(
                        'trim',
                        array_filter(
                            explode(',', $value),
                            function ($i) {
                                return !!$i;
                            }
                        )
                    )
                );
                if ($negative) {
                    return $query->whereNotIn($scopedParam, $values);
                }

                return $query->whereIn($scopedParam, $values);
                break;
            case 'text':
                return $query->where($scopedParam, 'ILIKE', "%$value%");
                break;
            default:
                return $query->where($scopedParam, $value);
                break;
        }

        return $query;
    }

    protected function applyWithFromQuery($fields, &$query) {
        foreach (array_keys($fields) as $field) {
            if (is_numeric($field)) {
                continue;
            }

            if (!is_array($fields[$field])) {
                continue;
            }

            switch (count($fields[$field])) {
                case 2:
                    $query = $query->with($field);
                    break;
                default:
                    // Noop for now
            }
        }
    }
}
