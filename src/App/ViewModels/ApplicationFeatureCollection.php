<?php

namespace LaravelCommon\App\ViewModels;

use Illuminate\Database\Eloquent\Model;
use LaravelCommon\App\Models\ApplicationFeature;
use LaravelCommon\ViewModels\PaggedCollection;

class ApplicationFeatureCollection extends PaggedCollection
{
    public function loadWith(): array
    {
        return ApplicationFeatureViewModel::loadWith();
    }

    /**
     * @inheritdoc
     */
    public function shape(Model $model): ?ApplicationFeatureViewModel
    {
        if ($model instanceof ApplicationFeature) {
            return new ApplicationFeatureViewModel($model, $this->request);
        }

        return null;
    }
}
