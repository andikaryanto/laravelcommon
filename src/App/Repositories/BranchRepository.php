<?php

namespace LaravelCommon\App\Repositories;

use LaravelCommon\App\Models\Branch;
use LaravelCommon\App\ViewModels\BranchCollection;
use LaravelCommon\App\ViewModels\BranchViewModel;
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

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function collectionClass(): string
    {
        return BranchCollection::class;
    }

    /**
     * @inheritDoc
     *
     * @return stirng
     */
    public function viewModelClass(): string
    {
        return BranchViewModel::class;
    }
}
