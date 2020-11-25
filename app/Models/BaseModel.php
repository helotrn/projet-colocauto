<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Molotov\Traits\BaseModel as BaseModelTrait;

class BaseModel extends Model
{
    use BaseModelTrait;

    public static function addJoin($query, $table, $left, $operator = null, $right = null) {
                             // Don't join on empty query.
        if (!$query) {
            return $query;
        }

                             // Get joins from query if any.
        if (isset($query->getQuery()->joins)) {
            $joins = $query->getQuery()->joins ?: [];
        } else {
            $joins = [];
        }

                             // Don't add join if exists already.
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
