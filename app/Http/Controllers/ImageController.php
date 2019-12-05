<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Image;
use App\Repositories\ImageRepository;

class ImageController extends RestController
{
    public function __construct(ImageRepository $repository, Image $model) {
        $this->repo = $repository;
        $this->model = $model;
    }
}
