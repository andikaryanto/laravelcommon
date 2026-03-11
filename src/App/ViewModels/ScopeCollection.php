<?php

namespace LaravelCommon\App\ViewModels;

use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Models\Scope;
use LaravelCommon\ViewModels\PaggedCollection;

class ScopeCollection extends PaggedCollection
{
    public function loadWith(): array
    {
        return ScopeViewModel::loadWith($this->getEmbeds());
    }

    /**
     * @inheritdoc
     */
    public function shape(Model $model): ?ScopeViewModel
    {
        if ($model instanceof Scope) {
            return new ScopeViewModel($model, $this->request);
        }
    }
}
