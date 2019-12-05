<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\File;
use App\Repositories\FileRepository;

class FileController extends RestController
{
    public function __construct(FileRepository $repository, File $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
