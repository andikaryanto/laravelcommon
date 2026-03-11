<?php

namespace LaravelCommon\App\ViewModels;

use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Models\Pricing;
use LaravelCommon\ViewModels\PaggedCollection;

class PricingCollection extends PaggedCollection
{
    public function loadWith(): array
    {
        return PricingViewModel::loadWith($this->getEmbeds());
    }

    /**
     * @inheritdoc
     */
    public function shape(Model $model): ?PricingViewModel
    {
        if ($model instanceof Pricing) {
            return new PricingViewModel($model, $this->request);
        }
    }
}
