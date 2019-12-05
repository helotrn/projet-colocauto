<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Extension;
use App\Repositories\ExtensionRepository;

class ExtensionController extends RestController
{
    public function __construct(ExtensionRepository $repository, Extension $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
