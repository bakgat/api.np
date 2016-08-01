<?php

namespace App\Http\Controllers;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /** @var SerializerInterface */
    protected $serializer;

    public $validator;


    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function validateRequest(FormRequest $formRequest)
    {
        $this->validator = Validator::make($formRequest->request()->all(), $formRequest->rules());

        if ($this->validator->fails()) {
            throw new \Exception("ValidationException");
        }
    }

    public function response($data, $groups= null)
    {
        if (is_array($groups) && count($groups) > 0) {
            return $this->serializer->serialize($data, 'json', SerializationContext::create()->setGroups($groups));
        }

        $result = $this->serializer->serialize($data, 'json');
        return $result;
    }
}

