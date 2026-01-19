<?php

namespace LaravelCommon\App\Queries;

use LaravelCommon\App\Models\LoggingConfig;
use LaravelCommon\App\Queries\Query;

class LoggingConfigQuery extends Query
{
    public function identityClass(): string
    {
        return LoggingConfig::class;
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
