<?php

namespace LaravelCommon\Responses;

use LaravelCommon\App\Consts\ResponseConst;

class DownloadableResponse extends BaseResponse
{
    public function __construct($data)
    {
        parent::__construct("OK", 200, ResponseConst::OK, $data, [], true);
    }
}
