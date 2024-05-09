<?php

namespace LaravelCommon\App\Queries;

use LaravelCommon\App\Queries\Query;
use LaravelCommon\App\Models\User;
use LaravelCommon\App\ViewModels\UserCollection;

class UserQuery extends Query
{
    public function identityClass(): string
    {
        return User::class;
    }

    public function collectionClass()
    {
        return UserCollection::class;
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
