<?php

namespace LaravelCommon\App\ViewModels\User;

use Illuminate\Database\Eloquent\Model;
use LaravelCommon\ViewModels\PaggedCollection;

class TokenCollection extends PaggedCollection
{
    /**
     * @inheritdoc
     */
    public function shape(Model $model)
    {
        $this->addItem(new TokenViewModel($model, $this->request));
    }
}
