<?php

namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Molotov\Traits\BaseRequest as BaseRequestTrait;

class BaseRequest extends FormRequest
{
    use BaseRequestTrait;
}
