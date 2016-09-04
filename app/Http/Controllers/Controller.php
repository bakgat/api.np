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


    public function response($data, $groups = null, $serializeNull = false)
    {
        $context = new SerializationContext();
        $context->setSerializeNull($serializeNull);


        if (is_array($groups) && count($groups) > 0) {
            $context->setGroups($groups);
        }

        $result = $this->serializer->serialize($data, 'json', $context);
        return $result;
    }
}

