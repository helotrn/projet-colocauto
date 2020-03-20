<?php

namespace App\Repositories;

use App\Models\Invoice;
use Molotov\Repositories\RestRepository;

class InvoiceRepository extends RestRepository
{
    public function __construct(Invoice $model) {
        $this->model = $model;
        $this->columnsDefinition = $model::getColumnsDefinition();
    }
}
