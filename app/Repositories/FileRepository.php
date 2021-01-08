<?php

namespace App\Repositories;

use App\Models\File;
use Molotov\RestRepository;

class FileRepository extends RestRepository
{
    public function __construct(File $model) {
        $this->model = $model;
    }
}
