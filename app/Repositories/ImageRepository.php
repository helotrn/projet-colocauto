<?php

namespace App\Repositories;

use App\Models\Image;
use Molotov\Repositories\RestRepository;

class ImageRepository extends RestRepository
{
    public function __construct(Image $model) {
        $this->model = $model;
    }
}
