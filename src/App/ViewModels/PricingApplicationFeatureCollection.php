<?php

namespace LaravelCommon\App\ViewModels;

use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Models\PricingApplicationFeature;
use LaravelCommon\ViewModels\PaggedCollection;

class PricingApplicationFeatureCollection extends PaggedCollection
{
    public function loadWith(): array
    {
        return PricingApplicationFeatureViewModel::loadWith($this->getEmbeds());
    }

    /**
     * @inheritdoc
     */
    public function shape(Model $model): ?PricingApplicationFeatureViewModel
    {
        if ($model instanceof PricingApplicationFeature) {
            return new PricingApplicationFeatureViewModel($model, $this->request);
        }

        return null;
    }
}
