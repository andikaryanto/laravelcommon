<?php

namespace LaravelCommon\App\Queries\User;

use LaravelCommon\App\Queries\Query;
use LaravelCommon\App\Models\User\Token;

class TokenQuery extends Query
{
    public function identityClass(): string
    {
        return Token::class;
    }

    /**
     * find logging by name
     *
     * @param string $name
     * @return $this
     */
    public function whereToken(string $token): TokenQuery
    {
        $this->where('token', '=', $token);
        return $this;
    }
}
