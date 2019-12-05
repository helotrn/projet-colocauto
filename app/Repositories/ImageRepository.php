<?php

namespace App\Repositories;

use App\Http\Requests\BaseRequest as Request;
use App\Models\Image;

class ImageRepository extends RestRepository
{
    public function __construct(Image $model) {
        $this->model = $model;
    }
}
