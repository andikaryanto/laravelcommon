<?php

namespace LaravelCommon\App\ViewModels\Groupuser;

use Illuminate\Database\Eloquent\Model;
use LaravelCommon\ViewModels\PaggedCollection;

class ScopeMappingCollection extends PaggedCollection
{
    /**
     * @inheritdoc
     */
    public function shape(Model $model)
    {
        $this->addItem(new ScopeMappingViewModel($model, $this->request));
    }
}
