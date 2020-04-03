<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Molotov\Traits\BaseModel as BaseModelTrait;

class BaseModel extends Model
{
    use BaseModelTrait;

    public static function addJoin($query, $table, $left, $operator, $right) {
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
            $query = $query->leftJoin($table, $left, $operator, $right);
        }

        return $query;
    }
}
