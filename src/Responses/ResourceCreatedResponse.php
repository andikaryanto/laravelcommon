<?php

namespace LaravelCommon\Responses;

class ResourceCreatedResponse extends JsonResponse
{
    public function __construct(string $message, $responseCode = [], $data = null)
    {
        parent::__construct($message, 201, $responseCode, $data);
    }
}
