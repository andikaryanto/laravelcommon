<?php

namespace LaravelCommon\Responses;

class SuccessResponse extends JsonResponse
{
    public function __construct(string $message, $responseCode = [], $data = null)
    {
        parent::__construct($message, 200, $responseCode, $data);
    }
}
