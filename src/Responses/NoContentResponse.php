<?php

namespace LaravelCommon\Responses;

class NoContentResponse extends BaseResponse
{
    public function __construct(string $message, $responseCode = [], $data = null)
    {
        parent::__construct($message, 204, $responseCode, $data);
    }
}
