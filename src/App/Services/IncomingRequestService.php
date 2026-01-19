<?php

namespace LaravelCommon\App\Services;

use LaravelCommon\App\Models\User;
use LaravelCommon\System\Http\Request;

class IncomingRequestService
{
    public function getUser(): ?User
    {
        if (request() instanceof Request) {
            return request()->getUserToken()?->getUser();
        }

        return null;
    }
}
