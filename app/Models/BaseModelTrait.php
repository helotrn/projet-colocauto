<?php

namespace App\Models;

use App\Exports\BaseExport;
use App\Transformers\Transformer;
use Illuminate\Database\Eloquent\Builder;

trait BaseModelTrait
{
    public static $filterTypes = [];

    public static $export = BaseExport::class;

    public static $transformer = Transformer::class;

    public static function getColumnsDefinition()
    {
        return [];
    }

    public static function getRules($action = "", $auth = null)
    {
        switch ($action) {
            case "destroy":
                return [];
            default:
                return static::$rules;
        }
    }

    public static $rules = [];

    public static $validationMessages = [];

    public static function addJoin(
        $query,
        $table,
        $left,
        $operator = null,
        $right = null
    ) {
        if (!$query) {
            return $query;
        }

        if (isset($query->getQuery()->joins)) {
            $joins = $query->getQuery()->joins ?: [];
        } else {
            $joins = [];
        }

        $addJoin = true;
        foreach ($joins as $join) {
            if ($join->table === $table) {
                $addJoin = false;
                break;
            }
        }

        if ($addJoin) {
            if (is_callable($left)) {
                return $query->leftJoin($table, $left);
            }

            return $query->leftJoin($table, $left, $operator, $right);
        }

        return $query;
    }

    protected $appends = [];

    public $readOnly = false;

    public $items = [];

    public $collections = [];

    public $computed = [];

    public $morphOnes = [];

    public $morphManys = [];

    public function getWith()
    {
        return $this->with;
    }

    public function belongsTo(
        $related,
        $foreignKey = null,
        $ownerKey = null,
        $relation = null
    ) {
        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        $query = parent::belongsTo($related, $foreignKey, $ownerKey, $relation);

        $columns = $related::getColumnsDefinition();
        foreach ($columns as $name => $column) {
            if (is_callable($column)) {
                $query = $column($query);
            } else {
                $query = $query->selectRaw("$column AS $name");
            }
        }

        return $query;
    }

    // FIXME Doesn't add the custom keys on the resulting object
    public function belongsToMany(
        $related,
        $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null,
        $relation = null
    ) {
        $instance = $this->newRelatedInstance($related);

        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        if (is_null($table)) {
            $table = $this->joiningTable($related, $instance);
        }

        $query = $instance->newQuery();

        $columns = $related::getColumnsDefinition();
        foreach ($columns as $name => $column) {
            if (is_callable($column)) {
                $query = $column($query);
            } else {
                $query = $query->selectRaw("$column AS $name");
            }
        }

        return $this->newBelongsToMany(
            $query,
            $this,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(),
            $relation
        );
    }
}
