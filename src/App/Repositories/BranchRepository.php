<?php

namespace LaravelCommon\App\Repositories;

use LaravelCommon\App\Models\Branch;
use LaravelCommon\App\Repositories\Repository;

class BranchRepository extends Repository
{
    /**
    * Constrcutor
    */
    public function __construct()
    {
        parent::__construct(Branch::class);
    }
}
