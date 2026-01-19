<?php

namespace LaravelCommon\App\Repositories;

use LaravelCommon\App\Models\Groupuser;

class GroupuserRepository extends Repository
{
    /**
    * Constrcutor
    */
    public function __construct()
    {
        parent::__construct(Groupuser::class);
    }
}
