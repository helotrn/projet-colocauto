<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequest as Request;
use App\Http\Requests\Borrower\ApproveRequest as ApproveRequest;
use App\Models\Borrower;
use App\Repositories\BorrowerRepository;
use Carbon\Carbon;

class BorrowerController extends RestController
{
    public function __construct(BorrowerRepository $repository, Borrower $model) {
        $this->repo = $repository;
        $this->model = $model;
    }

    public function index(Request $request) {
        try {
            [$items, $total] = $this->repo->get($request);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $this->respondWithCollection($request, $items, $total);
    }

    public function retrieve(Request $request, $id) {
        $item = $this->repo->find($request, $id);

        try {
            $response = $this->respondWithItem($request, $item);
        } catch (ValidationException $e) {
            return $this->respondWithErrors($e->errors(), $e->getMessage());
        }

        return $response;
    }
}
