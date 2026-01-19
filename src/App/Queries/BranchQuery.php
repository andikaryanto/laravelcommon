<?php

namespace LaravelCommon\App\Queries;

use LaravelCommon\App\Models\Branch;
use LaravelCommon\App\Queries\Query;

class BranchQuery extends Query
{
    public function identityClass(): string
    {
        return Branch::class;
    }
}
