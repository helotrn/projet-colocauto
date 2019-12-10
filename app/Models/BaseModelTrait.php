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

    public function scopeSearch(Builder $query, $q) {
        return $query;
    }
}
