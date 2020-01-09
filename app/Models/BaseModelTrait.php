<?php

namespace App\Models;

trait BaseModelTrait
{
    public static $transformer = BaseTransformer::class;

    public static function getColumnsDefinition() {
        return [];
    }

    public static function getRules() {
        return static::$rules;
    }

    public static $rules = [];

    public static $validationMessages = [];

    public $hasOne = [];

    public $collections = [];

    public $morphOne = [];

    public $morphOneField = [];

    public $belongsTo = [];

    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null) {
        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        $query = parent::belongsTo($related, $foreignKey, $ownerKey, $relation);

        $columns = $related::getColumnsDefinition();
        foreach ($columns as $column) {
            $query = $column($query);
        }

        return $query;
    }

    // FIXME Doesn't add the custom keys on the resulting object
    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null) {
        $instance = $this->newRelatedInstance($related);

        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        if (is_null($table)) {
            $table = $this->joiningTable($related, $instance);
        }

        $query = $instance->newQuery();

        $columns = $related::getColumnsDefinition();
        foreach ($columns as $column) {
            $query = $column($query);
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

    public function scopeSearch(Builder $query, $q) {
        return $query;
    }
}
