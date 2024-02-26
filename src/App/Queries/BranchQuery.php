<?php

namespace LaravelCommon\App\Queries;

use LaravelCommon\App\Models\Branch;
use LaravelCommon\App\ViewModels\BranchCollection;
use LaravelCommon\App\Queries\Query;

class BranchQuery extends Query
{
    public function identityClass(): string
    {
        return Branch::class;
    }

    public function collectionClass()
    {
        return BranchCollection::class;
    }
}
