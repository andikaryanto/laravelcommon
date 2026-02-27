<?php

namespace LaravelCommon\App\Services;

use LaravelCommon\App\Models\User;
use LaravelCommon\System\Http\CommonRequest;

class IncomingRequestService
{
    public function getUser()
    {
        if (request() instanceof CommonRequest) {
            return request()->getUserToken()?->getUser();
        }

        return null;
    }
}
