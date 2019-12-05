<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\BillableItem;
use App\Repositories\BillableItemRepository;

class BillableItemController extends RestController
{
    public function __construct(BillableItemRepository $repository, BillableItem $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
