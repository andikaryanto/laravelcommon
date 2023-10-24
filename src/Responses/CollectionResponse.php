<?php

namespace LaravelCommon\Responses;

use LaravelCommon\ViewModels\AbstractCollection;

class CollectionResponse extends BaseResponse
{
    public function __construct(string $message, int $code, $responseCode = [], $data = null, $additionalData = [])
    {
        $newData = [];
        if ($data instanceof AbstractCollection) {
             $newData = $data->proceed()->getElements();
        }

        parent::__construct($message, $code, $responseCode, $newData, $additionalData);
    }
}
