<?php

namespace LaravelCommon\System\Support;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB as BaseDB;

class DB extends BaseDB
{
    public static function connection($name = null): ConnectionInterface
    {
        if (!is_null($name)) {
            return parent::connection($name);
        }

        if (config('multitenancy.enabled', true) && app()->bound('currentTenant')) {
            $connectionName = config('multitenancy.tenant_database_connection_name') ?: config('database.default');
            $connection = parent::connection($connectionName);
            $currentTenant = app('currentTenant');

            if (!is_null($currentTenant) && isset($currentTenant->database)) {
                $connection->setDatabaseName($currentTenant->database);
            }

            return $connection;
        }

        return parent::connection();
    }
}
