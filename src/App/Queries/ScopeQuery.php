<?php

namespace LaravelCommon\App\Queries;

use LaravelCommon\App\Queries\Query;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Grammars\Grammar;
use LaravelCommon\App\Models\Scope;
use LaravelCommon\App\ViewModels\ScopeCollection;

class ScopeQuery extends Query
{
    /**
     * Create a new query builder instance.
     *
     * @param  Scope  $scope
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \Illuminate\Database\Query\Grammars\Grammar|null  $grammar
     * @param  \Illuminate\Database\Query\Processors\Processor|null  $processor
     * @return void
     */
    public function __construct(
        Scope $scope,
        ConnectionInterface $connection,
        Grammar $grammar = null,
        Processor $processor = null
    ) {
        parent::__construct($scope, $connection, $grammar, $processor);
    }


    public function collectionClass()
    {
        return ScopeCollection::class;
    }

    /**
     * Undocumented function
     *
     * @param string $name
     * @throws DatabaseException
     * @return ScopeQuery
     */
    public function whereName(string $name): ScopeQuery
    {
        $this->where('name', '=', $name);
        return $this;
    }
}
