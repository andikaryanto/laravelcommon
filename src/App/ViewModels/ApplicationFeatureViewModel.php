<?php

namespace LaravelCommon\App\ViewModels;

use LaravelCommon\App\Models\ApplicationFeature;
use LaravelCommon\ViewModels\AbstractViewModel;

class ApplicationFeatureViewModel extends AbstractViewModel
{
    /**
     * @var bool $autoAddResource;
     */
    protected $isAutoAddResource = true;

    /**
     * @var ApplicationFeature
     */
    protected $model;

    /**
     * @inheritdoc
     */
    public function addResource()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return [
            'id' => $this->model->getId(),
            'key' => $this->model->getFeatureKey(),
            'name' => $this->model->getName(),
            'description' => $this->model->getDescription()
        ];
    }
}
