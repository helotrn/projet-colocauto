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


    protected function orderBy($query, $def) {
                             // Replace '.' by '_' in column names. Eg.:
                             //   user.full_name
        $def = str_replace('.', '_', $def);

        return parent::orderBy($query, $def);
    }
}
