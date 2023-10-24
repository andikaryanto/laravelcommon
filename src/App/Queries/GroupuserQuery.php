<?php

namespace LaravelCommon\App\Queries;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\ConnectionInterface;
use LaravelCommon\App\Queries\Query;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Grammars\Grammar;
use LaravelCommon\App\Models\Groupuser;
use LaravelCommon\App\Models\Scope;
use LaravelCommon\App\ViewModels\GroupuserCollection;
use LaravelCommon\App\ViewModels\UserCollection;

class GroupuserQuery extends Query
{
    /**
     * Create a new query builder instance.
     *
     * @param  Groupuser  $groupuser
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \Illuminate\Database\Query\Grammars\Grammar|null  $grammar
     * @param  \Illuminate\Database\Query\Processors\Processor|null  $processor
     * @return void
     */
    public function __construct(
        Groupuser $groupuser,
        ConnectionInterface $connection,
        Grammar $grammar = null,
        Processor $processor = null
    ) {
        parent::__construct($groupuser, $connection, $grammar, $processor);
    }

    public function collectionClass()
    {
        return GroupuserCollection::class;
    }

    public function whereUserCreatedBefore(Carbon $date): GroupuserQuery
    {
        $this->joinWith('users', 'groupuser.id', '=', 'users.groupuser_id')
            ->where('users.created_at', '<', $date->format('Y-m-d H:i:s'));
        return $this;
    }

    public function whereUserCreatedAfter(Carbon $date): GroupuserQuery
    {
        $this->joinWith('users', 'groupusers.id', '=', 'users.groupuser_id')
            ->where('users.created_at', '>', $date->format('Y-m-d H:i:s'));
        return $this;
    }

    public function whereScope(Scope $scope): GroupuserQuery
    {

        $this->joinWith('users', 'groupusers.id', '=', 'users.groupuser_id')
            ->joinWith('user_scopes', 'users.id', '=', 'user_scopes.user_id')
            ->joinWith('scopes', 'user_scopes.scope_id', '=', 'scopes.id')
            ->where('scopes.id', '=', $scope->getId());

        return $this;
    }
}
