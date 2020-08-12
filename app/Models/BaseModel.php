<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Molotov\Traits\BaseModel as BaseModelTrait;

class BaseModel extends Model
{
    use BaseModelTrait;

    public static function addJoin($query, $table, $left, $operator = null, $right = null) {
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
}
