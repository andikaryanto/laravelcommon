<?php

namespace LaravelCommon\App\Queries;

use LaravelCommon\App\Queries\Query;
use LaravelCommon\App\Models\User;

class UserQuery extends Query
{
    public function identityClass(): string
    {
        return User::class;
    }

    /**
     * find logging by name
     *
     * @param string $username
     * @return $this
     */
    public function whereUsername(string $username): UserQuery
    {
        $this->where('username', '=', $username);
        return $this;
    }

    /**
     * find logging by name
     *
     * @param string $username
     * @return $this
     */
    public function whereEmail(string $email): UserQuery
    {
        $this->where('email', '=', $email);
        return $this;
    }
}
