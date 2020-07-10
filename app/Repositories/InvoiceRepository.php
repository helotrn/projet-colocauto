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

    public function create($data) {
        $this->model->fill($data);

        if (array_key_exists('user_id', $data)) {
            $this->model->user_id = $data['user_id'];
        }

        $this->model->save();

        $this->saveRelations($data);

        $this->model->save();

        return $this->model;
    }
}
