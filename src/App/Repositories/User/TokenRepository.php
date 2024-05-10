<?php

namespace LaravelCommon\App\Repositories\User;

use LaravelCommon\App\Models\User\Token;
use LaravelCommon\App\Repositories\Repository;

class TokenRepository extends Repository
{
    /**
    * Constrcutor
    */
    public function __construct()
    {
        parent::__construct(Token::class);
    }
}
