<?php

namespace LaravelCommon\App\Services;

use LaravelCommon\App\Models\User;

class IncomingRequestService
{
    public function getUser(): ?User
    {
        return request()->getUserToken()?->getUser();
    }
}
