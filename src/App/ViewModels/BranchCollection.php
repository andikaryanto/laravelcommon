<?php

namespace LaravelCommon\App\ViewModels;

use LaravelCommon\App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use LaravelCommon\ViewModels\PaggedCollection;

class BranchCollection extends PaggedCollection
{
    /**
     * @inheritdoc
     */
    public function shape(Model $model): ?BranchViewModel
    {
        if ($model instanceof Branch) {
            return new BranchViewModel($model, $this->request);
        }
    }
}
