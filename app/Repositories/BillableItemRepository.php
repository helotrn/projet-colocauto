<?php

namespace App\Repositories;

use App\Models\BillableItem;
use Molotov\Repositories\RestRepository;

class BillableItemRepository extends RestRepository
{
    public function __construct(BillableItem $model) {
        $this->model = $model;
    }
}
