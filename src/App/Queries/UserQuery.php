<?php

namespace LaravelCommon\App\Queries;

use Illuminate\Database\ConnectionInterface;
use LaravelCommon\App\Models\LoggingConfig;
use LaravelCommon\App\Queries\Query;
use LaravelCommon\App\ViewModels\LoggingConfigCollection;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Grammars\Grammar;
use LaravelCommon\App\Models\User;
use LaravelCommon\App\Models\User\Token;
use LaravelCommon\App\ViewModels\User\TokenCollection;
use LaravelCommon\App\ViewModels\UserCollection;

class UserQuery extends Query
{
    /**
     * Create a new query builder instance.
     *
     * @param  User  $user
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \Illuminate\Database\Query\Grammars\Grammar|null  $grammar
     * @param  \Illuminate\Database\Query\Processors\Processor|null  $processor
     * @return void
     */
    public function __construct(
        User $user,
        ConnectionInterface $connection,
        Grammar $grammar = null,
        Processor $processor = null
    ) {
        parent::__construct($user, $connection, $grammar, $processor);
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
}
