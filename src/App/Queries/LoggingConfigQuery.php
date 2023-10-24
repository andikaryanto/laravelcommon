<?php

namespace LaravelCommon\App\Queries;

use Illuminate\Database\ConnectionInterface;
use LaravelCommon\App\Models\LoggingConfig;
use LaravelCommon\App\Queries\Query;
use LaravelCommon\App\ViewModels\LoggingConfigCollection;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Grammars\Grammar;

class LoggingConfigQuery extends Query
{
    /**
     * Create a new query builder instance.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \Illuminate\Database\Query\Grammars\Grammar|null  $grammar
     * @param  \Illuminate\Database\Query\Processors\Processor|null  $processor
     * @return void
     */
    public function __construct(
        LoggingConfig $loggingConfig,
        ConnectionInterface $connection,
        Grammar $grammar = null,
        Processor $processor = null
    ) {
        parent::__construct($loggingConfig, $connection, $grammar, $processor);
    }

    public function collectionClass()
    {
        return LoggingConfigCollection::class;
    }

    /**
     * find logging by name
     *
     * @param string $name
     * @return $this
     */
    public function whereName(string $name): LoggingConfigQuery
    {
        $this->where('name', '=', $name);
        return $this;
    }

    /**
    * find logging by enabled
    *
    * @return $this
    */
    public function whereIsEnabled(): LoggingConfigQuery
    {
        $this->where('is_enabled', '=', 1);
        return $this;
    }
}
