<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\BillableItem;

class BillableItemRepository extends RestRepository
{
    public function __construct(BillableItem $model) {
        $this->model = $model;
    }
}
