<?php

namespace App\Http\Controllers;

use Log;
use App\Http\Requests\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public $validator;

    public function validateRequest(FormRequest $formRequest)
    {
        $this->validator = Validator::make($formRequest->request()->all(), $formRequest->rules());
        
        if ($this->validator->fails()) {
            throw new \Exception("ValidationException");
        }
    }
}

